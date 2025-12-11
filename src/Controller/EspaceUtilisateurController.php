<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Vehicule;
use App\Entity\Preferences;
use App\Entity\Participation;
use App\Repository\AvisRepository;
use App\Repository\CovoiturageRepository;
use App\Repository\ParticipationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/espace')]
#[IsGranted('ROLE_USER')]
class EspaceUtilisateurController extends AbstractController
{
    #[Route('', name: 'app_espace_utilisateur')]
    public function index(
        ParticipationRepository $participationRepository,
        CovoiturageRepository $covoiturageRepository,
        AvisRepository $avisRepository
    ): Response {
        $user = $this->getUser();

        // Participations passées (en tant que passager) - seulement les acceptées
        $participationsPassees = $participationRepository->findParticipationsPassees($user);

        // Participations en attente (demandes envoyées par l'utilisateur)
        $participationsEnAttente = $participationRepository->findBy([
            'utilisateur_id' => $user,
            'statut' => 'en_attente',
        ]);

        // Participations acceptées à venir
        $participationsAcceptees = $participationRepository->findParticipationsAcceptees($user);

        // Covoiturages passés (en tant que chauffeur)
        $covoituragesPasses = [];
        $demandesEnAttente = [];
        
        if ($user->isChauffeur()) {
            $covoituragesPasses = $covoiturageRepository->findCovoituragesPasses($user);
            
            // Demandes de participation en attente pour les covoiturages du chauffeur
            $demandesEnAttente = $participationRepository->findDemandesEnAttentePourChauffeur($user);
        }

        // Récupérer les avis déjà laissés par l'utilisateur
        $avisLaisses = $avisRepository->findBy(['utilisateur_id' => $user]);
        $covoituragesNotes = [];
        foreach ($avisLaisses as $avis) {
            $covoituragesNotes[] = $avis->getCovoiturageId()->getId();
        }

        return $this->render('espace_utilisateur/index.html.twig', [
            'participationsPassees' => $participationsPassees,
            'participationsEnAttente' => $participationsEnAttente,
            'participationsAcceptees' => $participationsAcceptees,
            'covoituragesPasses' => $covoituragesPasses,
            'covoituragesNotes' => $covoituragesNotes,
            'demandesEnAttente' => $demandesEnAttente,
        ]);
    }

    #[Route('/participation/{id}/accepter', name: 'app_espace_participation_accepter', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function accepterParticipation(
        Participation $participation,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        $covoiturage = $participation->getCovoiturageId();

        // Vérifier que l'utilisateur est le chauffeur de ce covoiturage
        if ($covoiturage->getUtilisateurId() !== $user) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas gérer cette demande.');
        }

        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('accepter_' . $participation->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_espace_utilisateur', ['_fragment' => 'demandes']);
        }

        // Vérifier que la participation est en attente
        if (!$participation->isEnAttente()) {
            $this->addFlash('warning', 'Cette demande a déjà été traitée.');
            return $this->redirectToRoute('app_espace_utilisateur', ['_fragment' => 'demandes']);
        }

        // Vérifier qu'il reste des places
        if ($covoiturage->getPlacesRestantes() <= 0) {
            $this->addFlash('danger', 'Il n\'y a plus de places disponibles pour ce covoiturage.');
            return $this->redirectToRoute('app_espace_utilisateur', ['_fragment' => 'demandes']);
        }

        $passager = $participation->getUtilisateurId();
        $prixCovoiturage = $participation->getCreditsUtilises();

        // Vérifier que le passager a toujours assez de crédits
        if ($passager->getCredits() < $prixCovoiturage) {
            $this->addFlash('danger', 'Le passager n\'a plus assez de crédits. La demande a été refusée automatiquement.');
            $participation->setStatut('refuse');
            $entityManager->flush();
            return $this->redirectToRoute('app_espace_utilisateur', ['_fragment' => 'demandes']);
        }

        // Accepter la participation
        $participation->setStatut('accepte');

        // Débiter les crédits du passager
        $passager->setCredits($passager->getCredits() - $prixCovoiturage);

        // Décrémenter les places restantes
        $covoiturage->setPlacesRestantes($covoiturage->getPlacesRestantes() - 1);

        $entityManager->flush();

        $this->addFlash('success', 'La demande de ' . $passager->getPseudo() . ' a été acceptée.');

        return $this->redirectToRoute('app_espace_utilisateur', ['_fragment' => 'demandes']);
    }

    #[Route('/participation/{id}/refuser', name: 'app_espace_participation_refuser', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function refuserParticipation(
        Participation $participation,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        $covoiturage = $participation->getCovoiturageId();

        // Vérifier que l'utilisateur est le chauffeur de ce covoiturage
        if ($covoiturage->getUtilisateurId() !== $user) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas gérer cette demande.');
        }

        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('refuser_' . $participation->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_espace_utilisateur', ['_fragment' => 'demandes']);
        }

        // Vérifier que la participation est en attente
        if (!$participation->isEnAttente()) {
            $this->addFlash('warning', 'Cette demande a déjà été traitée.');
            return $this->redirectToRoute('app_espace_utilisateur', ['_fragment' => 'demandes']);
        }

        $passager = $participation->getUtilisateurId();

        // Refuser la participation (pas de débit de crédits)
        $participation->setStatut('refuse');

        $entityManager->flush();

        $this->addFlash('success', 'La demande de ' . $passager->getPseudo() . ' a été refusée.');

        return $this->redirectToRoute('app_espace_utilisateur', ['_fragment' => 'demandes']);
    }

    #[Route('/avis/{id}', name: 'app_espace_avis', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function laisserAvis(
        int $id,
        Request $request,
        ParticipationRepository $participationRepository,
        CovoiturageRepository $covoiturageRepository,
        AvisRepository $avisRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();

        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('avis_' . $id, $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_espace_utilisateur', ['_fragment' => 'historique']);
        }

        // Récupérer le covoiturage
        $covoiturage = $covoiturageRepository->find($id);
        if (!$covoiturage) {
            $this->addFlash('danger', 'Covoiturage introuvable.');
            return $this->redirectToRoute('app_espace_utilisateur', ['_fragment' => 'historique']);
        }

        // Vérifier que l'utilisateur a participé à ce covoiturage (et que c'était accepté)
        $participation = $participationRepository->findOneBy([
            'utilisateur_id' => $user,
            'covoiturage_id' => $covoiturage,
            'statut' => 'accepte',
        ]);

        if (!$participation) {
            $this->addFlash('danger', 'Vous n\'avez pas participé à ce covoiturage.');
            return $this->redirectToRoute('app_espace_utilisateur', ['_fragment' => 'historique']);
        }

        // Vérifier que le covoiturage est passé
        if ($covoiturage->getDateDepart() > new \DateTime()) {
            $this->addFlash('danger', 'Vous ne pouvez pas noter un covoiturage qui n\'a pas encore eu lieu.');
            return $this->redirectToRoute('app_espace_utilisateur', ['_fragment' => 'historique']);
        }

        // Vérifier que l'utilisateur n'a pas déjà laissé un avis
        $avisExistant = $avisRepository->findOneBy([
            'utilisateur_id' => $user,
            'covoiturage_id' => $covoiturage,
        ]);

        if ($avisExistant) {
            $this->addFlash('warning', 'Vous avez déjà laissé un avis pour ce covoiturage.');
            return $this->redirectToRoute('app_espace_utilisateur', ['_fragment' => 'historique']);
        }

        // Créer l'avis
        $note = (int) $request->request->get('note');
        $commentaire = trim($request->request->get('commentaire', ''));

        if ($note < 1 || $note > 5) {
            $this->addFlash('danger', 'La note doit être comprise entre 1 et 5.');
            return $this->redirectToRoute('app_espace_utilisateur', ['_fragment' => 'historique']);
        }

        $avis = new Avis();
        $avis->setUtilisateurId($user);
        $avis->setCovoiturageId($covoiturage);
        $avis->setNote($note);
        $avis->setCommentaire($commentaire ?: null);
        $avis->setStatut('en_attente'); // En attente de validation par un employé

        $entityManager->persist($avis);
        $entityManager->flush();

        $this->addFlash('success', 'Merci pour votre avis ! Il sera visible après validation.');

        return $this->redirectToRoute('app_espace_utilisateur', ['_fragment' => 'historique']);
    }

    #[Route('/profil/update', name: 'app_espace_profil_update', methods: ['POST'])]
    public function updateProfil(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$this->isCsrfTokenValid('update_profil', $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_espace_utilisateur');
        }

        $user->setPseudo($request->request->get('pseudo'));
        $user->setEmail($request->request->get('email'));

        // Gérer l'upload de photo
        $photoFile = $request->files->get('photo');
        if ($photoFile) {
            $newFilename = uniqid() . '.' . $photoFile->guessExtension();
            $photoFile->move(
                $this->getParameter('photos_directory'),
                $newFilename
            );

            // Supprimer l'ancienne photo
            if ($user->getPhoto()) {
                $oldPhoto = $this->getParameter('photos_directory') . '/' . $user->getPhoto();
                if (file_exists($oldPhoto)) {
                    unlink($oldPhoto);
                }
            }

            $user->setPhoto($newFilename);
        }

        $entityManager->flush();
        $this->addFlash('success', 'Votre profil a été mis à jour.');

        return $this->redirectToRoute('app_espace_utilisateur');
    }

    #[Route('/role/update', name: 'app_espace_role_update', methods: ['POST'])]
    public function updateRole(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$this->isCsrfTokenValid('update_role', $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_espace_utilisateur');
        }

        $role = $request->request->get('role');
        if (in_array($role, ['PASSAGER', 'CHAUFFEUR', 'CHAUFFEUR_PASSAGER'])) {
            $user->setRole($role);
            $entityManager->flush();
            $this->addFlash('success', 'Votre rôle a été mis à jour.');

            if ($user->isChauffeur() && $user->getVehicules()->isEmpty()) {
                $this->addFlash('info', 'N\'oubliez pas d\'ajouter un véhicule pour proposer des covoiturages.');
            }
        }

        return $this->redirectToRoute('app_espace_utilisateur', ['_fragment' => 'info']);
    }

    #[Route('/vehicule/add', name: 'app_espace_vehicule_add', methods: ['POST'])]
    public function addVehicule(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$this->isCsrfTokenValid('add_vehicule', $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_espace_utilisateur', ['_fragment' => 'vehicules']);
        }

        $vehicule = new Vehicule();
        $vehicule->setUtilisateurId($user);
        $vehicule->setMarque($request->request->get('marque'));
        $vehicule->setModele($request->request->get('modele'));
        $vehicule->setCouleur($request->request->get('couleur'));
        $vehicule->setImmatriculation(strtoupper($request->request->get('immatriculation')));
        $vehicule->setEnergie($request->request->get('energie'));
        $vehicule->setPlacesDisponibles((int) $request->request->get('places_disponibles'));
        $vehicule->setDatePremiereImmatriculation(new \DateTime($request->request->get('date_premiere_immatriculation')));

        $entityManager->persist($vehicule);
        $entityManager->flush();

        $this->addFlash('success', 'Votre véhicule a été ajouté.');

        return $this->redirectToRoute('app_espace_utilisateur', ['_fragment' => 'vehicules']);
    }

    #[Route('/vehicule/{id}/update', name: 'app_espace_vehicule_update', methods: ['POST'])]
    public function updateVehicule(Vehicule $vehicule, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if ($vehicule->getUtilisateurId() !== $user) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('update_vehicule_' . $vehicule->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_espace_utilisateur', ['_fragment' => 'vehicules']);
        }

        $vehicule->setMarque($request->request->get('marque'));
        $vehicule->setModele($request->request->get('modele'));
        $vehicule->setImmatriculation(strtoupper($request->request->get('immatriculation')));
        $vehicule->setCouleur($request->request->get('couleur'));
        $vehicule->setEnergie($request->request->get('energie'));
        $vehicule->setPlacesDisponibles((int) $request->request->get('places_disponibles'));
        $vehicule->setDatePremiereImmatriculation(new \DateTime($request->request->get('date_premiere_immatriculation')));

        $entityManager->flush();

        $this->addFlash('success', 'Votre véhicule a été modifié.');

        return $this->redirectToRoute('app_espace_utilisateur', ['_fragment' => 'vehicules']);
    }

    #[Route('/vehicule/{id}/delete', name: 'app_espace_vehicule_delete', methods: ['POST'])]
    public function deleteVehicule(Vehicule $vehicule, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if ($vehicule->getUtilisateurId() !== $user) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('delete_vehicule_' . $vehicule->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_espace_utilisateur', ['_fragment' => 'vehicules']);
        }

        $entityManager->remove($vehicule);
        $entityManager->flush();

        $this->addFlash('success', 'Votre véhicule a été supprimé.');

        return $this->redirectToRoute('app_espace_utilisateur', ['_fragment' => 'vehicules']);
    }

    #[Route('/preferences/update', name: 'app_espace_preferences_update', methods: ['POST'])]
    public function updatePreferences(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$this->isCsrfTokenValid('update_preferences', $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_espace_utilisateur', ['_fragment' => 'preferences']);
        }

        $preferences = $user->getPreferences();

        if (!$preferences) {
            $preferences = new Preferences();
            $preferences->setUtilisateurId($user);
        }

        $preferences->setAccepteFumeurs($request->request->has('accepte_fumeurs'));
        $preferences->setAccepteAnimaux($request->request->has('accepte_animaux'));
        $preferences->setPreferencesPersonnalisees($request->request->get('preferences_personnalisees'));

        $entityManager->persist($preferences);
        $entityManager->flush();

        $this->addFlash('success', 'Vos préférences ont été mises à jour.');

        return $this->redirectToRoute('app_espace_utilisateur', ['_fragment' => 'preferences']);
    }
}