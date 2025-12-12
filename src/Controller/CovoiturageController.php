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
        $filtresActifs = false;

        // Si le formulaire est soumis
        if ($form->isSubmitted()) {
            $data = $form->getData();
            
            $villeDepart = $data['ville_depart'] ?? null;
            $villeArrivee = $data['ville_arrivee'] ?? null;
            $dateDepart = $data['date_depart'] ?? null;
            $heureDepart = $data['heure_depart'] ?? null;
            $dateArrivee = $data['date_arrivee'] ?? null;
            $heureArrivee = $data['heure_arrivee'] ?? null;
            $ecologique = $data['ecologique'] ?? null;
            $prixMax = $data['prix_max'] ?? null;
            $dureeMax = $data['duree_max'] ?? null;
            $noteMin = $data['note_min'] ?? null;

            if ($villeDepart && $villeArrivee && $dateDepart) {
                $recherche = true;
                
                // Construire le datetime de départ
                if ($heureDepart) {
                    $dateTimeDepart = new \DateTime($dateDepart->format('Y-m-d') . ' ' . $heureDepart->format('H:i:s'));
                } else {
                    $dateTimeDepart = new \DateTime($dateDepart->format('Y-m-d') . ' 00:00:00');
                }
                
                // Construire le datetime d'arrivée (si fourni)
                $dateTimeArrivee = null;
                if ($dateArrivee) {
                    if ($heureArrivee) {
                        $dateTimeArrivee = new \DateTime($dateArrivee->format('Y-m-d') . ' ' . $heureArrivee->format('H:i:s'));
                    } else {
                        $dateTimeArrivee = new \DateTime($dateArrivee->format('Y-m-d') . ' 23:59:59');
                    }
                }

                // Rechercher les covoiturages avec filtres
                $covoiturages = $covoiturageRepository->findByRechercheAvecFiltres(
                    $villeDepart,
                    $villeArrivee,
                    $dateTimeDepart,
                    $dateTimeArrivee,
                    $heureDepart ? true : false,
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
                        $dateTimeDepart
                    );
                }

                // Déterminer si des filtres avancés sont actifs
                $filtresActifs = $ecologique || $prixMax || $dureeMax || $noteMin;
            }
        }
        else {
        // Par défaut, afficher tous les covoiturages disponibles
        $covoiturages = $covoiturageRepository->findAllDisponibles();
        }

        return $this->render('covoiturage/index.html.twig', [
            'form' => $form->createView(),
            'covoiturages' => $covoiturages,
            'recherche' => $recherche,
            'prochainCovoiturage' => $prochainCovoiturage,
            'filtresActifs' => $filtresActifs,
        ]);
    }

    #[Route('/covoiturage/{id}', name: 'app_covoiturage_detail', requirements: ['id' => '\d+'])]
    public function detail(Covoiturage $covoiturage, ParticipationRepository $participationRepository): Response
    {
        // Vérifier si l'utilisateur participe déjà
        $dejaParticipant = false;
        $participationEnAttente = false;
        $participationAcceptee = false;
        $estChauffeur = false;
        
        if ($this->getUser()) {
            $participation = $participationRepository->findOneBy([
                'utilisateur_id' => $this->getUser(),
                'covoiturage_id' => $covoiturage,
            ]);
            
            if ($participation) {
                $dejaParticipant = true;
                $participationEnAttente = $participation->isEnAttente();
                $participationAcceptee = $participation->isAccepte();
            }
            
            $estChauffeur = $covoiturage->getUtilisateurId() === $this->getUser();
        }

        return $this->render('covoiturage/detail.html.twig', [
            'covoiturage' => $covoiturage,
            'dejaParticipant' => $dejaParticipant,
            'participationEnAttente' => $participationEnAttente,
            'participationAcceptee' => $participationAcceptee,
            'estChauffeur' => $estChauffeur,
        ]);
    }

    #[Route('/covoiturage/creer', name: 'app_covoiturage_creer', methods: ['GET', 'POST'])]
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
        
        // Vérifier si c'est une soumission depuis _proposer.html.twig (formulaire HTML simple)
        $fromProposer = $request->isMethod('POST') && $request->request->has('ville_depart') && !$request->request->has('creer_covoiturage');
        
        if ($fromProposer) {
            // Pré-remplir avec les données POST venant de _proposer.html.twig
            $covoiturage->setVilleDepart($request->request->get('ville_depart'));
            $covoiturage->setVilleArrivee($request->request->get('ville_arrivee'));
            $covoiturage->setPrix((int) $request->request->get('prix'));
            $covoiturage->setPlacesRestantes((int) $request->request->get('places_restantes'));
            
            // Récupérer le véhicule
            $vehiculeId = $request->request->get('vehicule_id');
            if ($vehiculeId) {
                $vehicule = $entityManager->getRepository(\App\Entity\Vehicule::class)->find($vehiculeId);
                if ($vehicule && $vehicule->getUtilisateurId() === $user) {
                    $covoiturage->setVehiculeId($vehicule);
                }
            }
        }
        
        $form = $this->createForm(CreerCovoiturageType::class, $covoiturage);
        
        // Pré-remplir les champs date/heure non mappés si données POST venant de _proposer.html.twig
        if ($fromProposer) {
            $dateDepart = $request->request->get('date_depart');
            $heureDepart = $request->request->get('heure_depart');
            $dateArrivee = $request->request->get('date_arrivee');
            $heureArrivee = $request->request->get('heure_arrivee');
            
            if ($dateDepart) {
                $form->get('date_depart_jour')->setData(new \DateTime($dateDepart));
            }
            if ($heureDepart) {
                $form->get('heure_depart')->setData(new \DateTime($heureDepart));
            }
            if ($dateArrivee) {
                $form->get('date_arrivee_jour')->setData(new \DateTime($dateArrivee));
            }
            if ($heureArrivee) {
                $form->get('heure_arrivee')->setData(new \DateTime($heureArrivee));
            }
        }
        
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
            $this->addFlash('success', 'Votre participation a été annulée. Vos ' . $participation->getCreditsUtilises() . ' crédits ont été remboursés.');
        } else {
            $this->addFlash('success', 'Votre demande de participation a été annulée.');
        }

        // Supprimer la participation
        $entityManager->remove($participation);
        $entityManager->flush();

        return $this->redirectToRoute('app_espace_utilisateur', ['_fragment' => 'demandes']);
    }
}