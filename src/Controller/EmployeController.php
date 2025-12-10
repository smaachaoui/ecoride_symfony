<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Repository\AvisRepository;
use App\Repository\CovoiturageRepository;
use App\Repository\ParticipationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/employe')]
#[IsGranted('ROLE_EMPLOYE')]
class EmployeController extends AbstractController
{
    #[Route('', name: 'app_employe_dashboard')]
    public function index(
        CovoiturageRepository $covoiturageRepository,
        AvisRepository $avisRepository
    ): Response {
        $today = new \DateTime('today');
        $tomorrow = new \DateTime('tomorrow');

        // Covoiturages du jour
        $covoituragesDuJour = $covoiturageRepository->findCovoituragesDuJour($today, $tomorrow);

        // Avis en attente de validation
        $avisEnAttente = $avisRepository->findBy(
            ['statut' => Avis::STATUT_EN_ATTENTE],
            ['created_at' => 'ASC']
        );

        // Statistiques
        $stats = [
            'covoiturages_jour' => count($covoituragesDuJour),
            'avis_en_attente' => count($avisEnAttente),
            'avis_valides_today' => $avisRepository->countAvisValidesAujourdHui(),
        ];

        return $this->render('employe/index.html.twig', [
            'covoituragesDuJour' => $covoituragesDuJour,
            'avisEnAttente' => $avisEnAttente,
            'stats' => $stats,
        ]);
    }

    #[Route('/covoiturages', name: 'app_employe_covoiturages')]
    public function covoiturages(
        Request $request,
        CovoiturageRepository $covoiturageRepository
    ): Response {
        $date = $request->query->get('date', (new \DateTime())->format('Y-m-d'));
        $dateObj = new \DateTime($date);
        $dateFin = (clone $dateObj)->modify('+1 day');

        $covoiturages = $covoiturageRepository->findCovoituragesDuJour($dateObj, $dateFin);

        return $this->render('employe/covoiturages.html.twig', [
            'covoiturages' => $covoiturages,
            'dateSelectionnee' => $dateObj,
        ]);
    }

    #[Route('/avis', name: 'app_employe_avis')]
    public function avis(AvisRepository $avisRepository): Response
    {
        $avisEnAttente = $avisRepository->findBy(
            ['statut' => Avis::STATUT_EN_ATTENTE],
            ['created_at' => 'ASC']
        );

        $avisTraites = $avisRepository->findAvisTraitesRecents(20);

        return $this->render('employe/avis.html.twig', [
            'avisEnAttente' => $avisEnAttente,
            'avisTraites' => $avisTraites,
        ]);
    }

    #[Route('/avis/{id}/valider', name: 'app_employe_avis_valider', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function validerAvis(
        Avis $avis,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid('avis_action_' . $avis->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_employe_avis');
        }

        $avis->setStatut(Avis::STATUT_VALIDE);
        $entityManager->flush();

        $this->addFlash('success', 'L\'avis a été validé et est maintenant visible.');

        return $this->redirectToRoute('app_employe_avis');
    }

    #[Route('/avis/{id}/refuser', name: 'app_employe_avis_refuser', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function refuserAvis(
        Avis $avis,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid('avis_action_' . $avis->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_employe_avis');
        }

        $avis->setStatut(Avis::STATUT_REFUSE);
        $entityManager->flush();

        $this->addFlash('success', 'L\'avis a été refusé.');

        return $this->redirectToRoute('app_employe_avis');
    }

    #[Route('/covoiturage/{id}/probleme', name: 'app_employe_signaler_probleme', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function signalerProbleme(
        int $id,
        Request $request,
        CovoiturageRepository $covoiturageRepository,
        ParticipationRepository $participationRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $covoiturage = $covoiturageRepository->find($id);

        if (!$covoiturage) {
            $this->addFlash('danger', 'Covoiturage introuvable.');
            return $this->redirectToRoute('app_employe_covoiturages');
        }

        if (!$this->isCsrfTokenValid('probleme_' . $id, $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_employe_covoiturages');
        }

        $motif = $request->request->get('motif', '');

        // Récupérer toutes les participations du covoiturage
        $participations = $participationRepository->findBy(['covoiturage_id' => $covoiturage]);

        $nbPassagersRembourses = 0;

        foreach ($participations as $participation) {
            $passager = $participation->getUtilisateurId();
            $creditsUtilises = $participation->getCreditsUtilises();

            // Rembourser le passager
            $passager->setCredits($passager->getCredits() + $creditsUtilises);
            $nbPassagersRembourses++;
        }

        $entityManager->flush();

        $this->addFlash('success', 'Problème signalé. ' . $nbPassagersRembourses . ' passager(s) remboursé(s).');

        return $this->redirectToRoute('app_employe_covoiturages');
    }
}