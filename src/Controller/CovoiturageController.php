<?php

namespace App\Controller;

use App\Entity\Covoiturage;
use App\Form\RechercheCovoiturageType;
use App\Repository\CovoiturageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
    public function detail(Covoiturage $covoiturage): Response
    {
        return $this->render('covoiturage/detail.html.twig', [
            'covoiturage' => $covoiturage,
        ]);
    }
}