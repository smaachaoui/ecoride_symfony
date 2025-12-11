<?php

namespace App\Controller;

use App\Entity\Covoiturage;
use App\Entity\Participation;
use App\Form\CreerCovoiturageType;
use App\Form\RechercheCovoiturageType;
use App\Repository\CovoiturageRepository;
use App\Repository\ParticipationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CovoiturageController extends AbstractController
{
    #[Route('/covoiturages', name: 'app_covoiturages')]
    public function index(Request $request, CovoiturageRepository $covoiturageRepository): Response
    {
        $form = $this->createForm(RechercheCovoiturageType::class);
        $form->handleRequest($request);

        $covoiturages = [];
        $recherche = false;
        $prochainCovoiturage = null;

        // Récupérer les paramètres GET
        $villeDepart = $request->query->get('ville_depart');
        $villeArrivee = $request->query->get('ville_arrivee');
        $date = $request->query->get('date');
        
        // Filtres avancés
        $ecologique = $request->query->get('ecologique') ? true : null;
        $prixMax = $request->query->get('prix_max') ? (int) $request->query->get('prix_max') : null;
        $dureeMax = $request->query->get('duree_max') ? (int) $request->query->get('duree_max') : null;
        $noteMin = $request->query->get('note_min') ? (int) $request->query->get('note_min') : null;

        if ($villeDepart && $villeArrivee && $date) {
            $recherche = true;
            $dateRecherche = new \DateTime($date);

            // Rechercher les covoiturages avec filtres
            $covoiturages = $covoiturageRepository->findByRechercheAvecFiltres(
                $villeDepart,
                $villeArrivee,
                $dateRecherche,
                $ecologique,
                $prixMax,
                $dureeMax,
                $noteMin
            );

            // Si aucun résultat, chercher le prochain covoiturage disponible
            if (empty($covoiturages)) {
                $prochainCovoiturage = $covoiturageRepository->findProchainCovoiturage(
                    $villeDepart,
                    $villeArrivee,
                    $dateRecherche
                );
            }

            // Pré-remplir le formulaire
            $form->setData([
                'ville_depart' => $villeDepart,
                'ville_arrivee' => $villeArrivee,
                'date' => $dateRecherche,
                'ecologique' => $ecologique,
                'prix_max' => $prixMax,
                'duree_max' => $dureeMax,
                'note_min' => $noteMin,
            ]);
        }

        return $this->render('covoiturage/index.html.twig', [
            'form' => $form->createView(),
            'covoiturages' => $covoiturages,
            'recherche' => $recherche,
            'prochainCovoiturage' => $prochainCovoiturage,
            'filtresActifs' => $ecologique || $prixMax || $dureeMax || $noteMin,
        ]);
    }

    #[Route('/covoiturage/{id}', name: 'app_covoiturage_detail', requirements: ['id' => '\d+'])]
    public function detail(Covoiturage $covoiturage, ParticipationRepository $participationRepository): Response
    {
        // Vérifier si l'utilisateur participe déjà
        $dejaParticipant = false;
        $participationEnAttente = false;
        $estChauffeur = false;
        
        if ($this->getUser()) {
            $participation = $participationRepository->findOneBy([
                'utilisateur_id' => $this->getUser(),
                'covoiturage_id' => $covoiturage,
            ]);
            
            if ($participation) {
                $dejaParticipant = true;
                $participationEnAttente = $participation->isEnAttente();
            }
            
            $estChauffeur = $covoiturage->getUtilisateurId() === $this->getUser();
        }

        return $this->render('covoiturage/detail.html.twig', [
            'covoiturage' => $covoiturage,
            'dejaParticipant' => $dejaParticipant,
            'participationEnAttente' => $participationEnAttente,
            'estChauffeur' => $estChauffeur,
        ]);
    }

    #[Route('/covoiturage/creer', name: 'app_covoiturage_creer')]
    #[IsGranted('ROLE_USER')]
    public function creer(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Vérifier que l'utilisateur est chauffeur
        if (!$user->isChauffeur()) {
            $this->addFlash('warning', 'Vous devez être chauffeur pour proposer un covoiturage.');
            return $this->redirectToRoute('app_espace_utilisateur');
        }

        // Vérifier que l'utilisateur a au moins un véhicule
        if ($user->getVehicules()->isEmpty()) {
            $this->addFlash('warning', 'Vous devez ajouter un véhicule avant de proposer un covoiturage.');
            return $this->redirectToRoute('app_espace_utilisateur', ['_fragment' => 'vehicules']);
        }

        $covoiturage = new Covoiturage();
        $form = $this->createForm(CreerCovoiturageType::class, $covoiturage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Associer le chauffeur
            $covoiturage->setUtilisateurId($user);

            // Déterminer si le voyage est écologique (véhicule électrique ou hybride)
            $vehicule = $covoiturage->getVehiculeId();
            $energie = $vehicule->getEnergie();
            $covoiturage->setEcologique($energie === 'Electrique' || $energie === 'Hybride');

            $entityManager->persist($covoiturage);
            $entityManager->flush();

            $this->addFlash('success', 'Votre covoiturage a été publié avec succès !');

            return $this->redirectToRoute('app_covoiturage_detail', ['id' => $covoiturage->getId()]);
        }

        return $this->render('covoiturage/creer.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/covoiturage/{id}/modifier', name: 'app_covoiturage_modifier', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function modifier(Covoiturage $covoiturage, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Vérifier que l'utilisateur est le chauffeur de ce covoiturage
        if ($covoiturage->getUtilisateurId() !== $user) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier ce covoiturage.');
        }

        // Vérifier que le covoiturage n'a pas encore commencé
        if ($covoiturage->getDateDepart() < new \DateTime()) {
            $this->addFlash('danger', 'Vous ne pouvez pas modifier un covoiturage déjà passé.');
            return $this->redirectToRoute('app_covoiturage_detail', ['id' => $covoiturage->getId()]);
        }

        $form = $this->createForm(CreerCovoiturageType::class, $covoiturage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Mettre à jour le statut écologique
            $vehicule = $covoiturage->getVehiculeId();
            $energie = $vehicule->getEnergie();
            $covoiturage->setEcologique($energie === 'Electrique' || $energie === 'Hybride');

            $entityManager->flush();

            $this->addFlash('success', 'Votre covoiturage a été modifié.');

            return $this->redirectToRoute('app_covoiturage_detail', ['id' => $covoiturage->getId()]);
        }

        return $this->render('covoiturage/modifier.html.twig', [
            'form' => $form->createView(),
            'covoiturage' => $covoiturage,
        ]);
    }

    #[Route('/covoiturage/{id}/participer', name: 'app_covoiturage_participer', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function participer(
        Covoiturage $covoiturage,
        ParticipationRepository $participationRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();

        // Vérification : l'utilisateur n'est pas le chauffeur
        if ($covoiturage->getUtilisateurId() === $user) {
            $this->addFlash('danger', 'Vous ne pouvez pas participer à votre propre covoiturage.');
            return $this->redirectToRoute('app_covoiturage_detail', ['id' => $covoiturage->getId()]);
        }

        // Vérification : l'utilisateur ne participe pas déjà
        $participationExistante = $participationRepository->findOneBy([
            'utilisateur_id' => $user,
            'covoiturage_id' => $covoiturage,
        ]);

        if ($participationExistante) {
            $this->addFlash('warning', 'Vous avez déjà une demande de participation pour ce covoiturage.');
            return $this->redirectToRoute('app_covoiturage_detail', ['id' => $covoiturage->getId()]);
        }

        // Vérification : places disponibles
        if ($covoiturage->getPlacesRestantes() <= 0) {
            $this->addFlash('danger', 'Désolé, ce covoiturage est complet.');
            return $this->redirectToRoute('app_covoiturage_detail', ['id' => $covoiturage->getId()]);
        }

        // Vérification : crédits suffisants
        $prixCovoiturage = (int) $covoiturage->getPrix();
        if ($user->getCredits() < $prixCovoiturage) {
            $this->addFlash('danger', 'Vous n\'avez pas assez de crédits pour participer à ce covoiturage. (Requis : ' . $prixCovoiturage . ' crédits, Disponible : ' . $user->getCredits() . ' crédits)');
            return $this->redirectToRoute('app_covoiturage_detail', ['id' => $covoiturage->getId()]);
        }

        // Afficher la page de confirmation
        return $this->render('covoiturage/participer.html.twig', [
            'covoiturage' => $covoiturage,
        ]);
    }

    #[Route('/covoiturage/{id}/confirmer-participation', name: 'app_covoiturage_confirmer', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function confirmerParticipation(
        Request $request,
        Covoiturage $covoiturage,
        ParticipationRepository $participationRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();

        // Vérification CSRF
        if (!$this->isCsrfTokenValid('participer_' . $covoiturage->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token de sécurité invalide. Veuillez réessayer.');
            return $this->redirectToRoute('app_covoiturage_detail', ['id' => $covoiturage->getId()]);
        }

        // Double vérification : l'utilisateur n'est pas le chauffeur
        if ($covoiturage->getUtilisateurId() === $user) {
            $this->addFlash('danger', 'Vous ne pouvez pas participer à votre propre covoiturage.');
            return $this->redirectToRoute('app_covoiturage_detail', ['id' => $covoiturage->getId()]);
        }

        // Double vérification : pas de participation existante
        $participationExistante = $participationRepository->findOneBy([
            'utilisateur_id' => $user,
            'covoiturage_id' => $covoiturage,
        ]);

        if ($participationExistante) {
            $this->addFlash('warning', 'Vous avez déjà une demande de participation pour ce covoiturage.');
            return $this->redirectToRoute('app_covoiturage_detail', ['id' => $covoiturage->getId()]);
        }

        // Double vérification : places disponibles
        if ($covoiturage->getPlacesRestantes() <= 0) {
            $this->addFlash('danger', 'Désolé, ce covoiturage est maintenant complet.');
            return $this->redirectToRoute('app_covoiturage_detail', ['id' => $covoiturage->getId()]);
        }

        // Double vérification : crédits suffisants
        $prixCovoiturage = (int) $covoiturage->getPrix();
        if ($user->getCredits() < $prixCovoiturage) {
            $this->addFlash('danger', 'Vous n\'avez pas assez de crédits.');
            return $this->redirectToRoute('app_covoiturage_detail', ['id' => $covoiturage->getId()]);
        }

        // Créer la participation avec statut EN ATTENTE (pas de débit immédiat)
        $participation = new Participation();
        $participation->setUtilisateurId($user);
        $participation->setCovoiturageId($covoiturage);
        $participation->setStatut('en_attente');
        $participation->setCreditsUtilises($prixCovoiturage);

        // Persister la participation (sans débiter les crédits ni décrémenter les places)
        $entityManager->persist($participation);
        $entityManager->flush();

        $this->addFlash('success', 'Votre demande de participation a été envoyée au chauffeur. Vous serez notifié de sa décision.');

        return $this->redirectToRoute('app_covoiturage_detail', ['id' => $covoiturage->getId()]);
    }

    #[Route('/covoiturage/{id}/annuler-participation', name: 'app_covoiturage_annuler', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function annulerParticipation(
        Request $request,
        Covoiturage $covoiturage,
        ParticipationRepository $participationRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();

        // Vérification CSRF
        if (!$this->isCsrfTokenValid('annuler_' . $covoiturage->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_covoiturage_detail', ['id' => $covoiturage->getId()]);
        }

        // Trouver la participation
        $participation = $participationRepository->findOneBy([
            'utilisateur_id' => $user,
            'covoiturage_id' => $covoiturage,
        ]);

        if (!$participation) {
            $this->addFlash('danger', 'Participation introuvable.');
            return $this->redirectToRoute('app_covoiturage_detail', ['id' => $covoiturage->getId()]);
        }

        // Si la participation était acceptée, rembourser les crédits et libérer la place
        if ($participation->isAccepte()) {
            $user->setCredits($user->getCredits() + $participation->getCreditsUtilises());
            $covoiturage->setPlacesRestantes($covoiturage->getPlacesRestantes() + 1);
        }

        // Supprimer la participation
        $entityManager->remove($participation);
        $entityManager->flush();

        $this->addFlash('success', 'Votre participation a été annulée.');

        return $this->redirectToRoute('app_covoiturage_detail', ['id' => $covoiturage->getId()]);
    }
}