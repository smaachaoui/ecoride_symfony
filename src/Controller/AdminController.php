<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\AvisRepository;
use App\Repository\CovoiturageRepository;
use App\Repository\ParticipationRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('', name: 'app_admin_dashboard')]
    public function index(
        UtilisateurRepository $utilisateurRepository,
        CovoiturageRepository $covoiturageRepository,
        ParticipationRepository $participationRepository,
        AvisRepository $avisRepository
    ): Response {
        // Statistiques générales
        $stats = [
            'total_utilisateurs' => $utilisateurRepository->count([]),
            'total_chauffeurs' => $utilisateurRepository->countByRole(['CHAUFFEUR', 'CHAUFFEUR_PASSAGER']),
            'total_passagers' => $utilisateurRepository->countByRole(['PASSAGER', 'CHAUFFEUR_PASSAGER']),
            'total_employes' => $utilisateurRepository->countByRole(['EMPLOYE']),
            'utilisateurs_suspendus' => $utilisateurRepository->count(['is_suspended' => true]),
            'total_covoiturages' => $covoiturageRepository->count([]),
            'covoiturages_ce_mois' => $covoiturageRepository->countCovoituragesCeMois(),
            'total_participations' => $participationRepository->count([]),
            'credits_plateforme' => $participationRepository->calculerCreditsPlateforme(),
            'avis_en_attente' => $avisRepository->count(['statut' => 'en_attente']),
        ];

        // Derniers utilisateurs inscrits
        $derniersUtilisateurs = $utilisateurRepository->findBy([], ['created_at' => 'DESC'], 5);

        // Derniers covoiturages
        $derniersCovoiturages = $covoiturageRepository->findBy([], ['date_depart' => 'DESC'], 5);

        return $this->render('admin/index.html.twig', [
            'stats' => $stats,
            'derniersUtilisateurs' => $derniersUtilisateurs,
            'derniersCovoiturages' => $derniersCovoiturages,
        ]);
    }

    // ============================================================
    // GESTION DES UTILISATEURS
    // ============================================================

    #[Route('/utilisateurs', name: 'app_admin_utilisateurs')]
    public function utilisateurs(
        Request $request,
        UtilisateurRepository $utilisateurRepository
    ): Response {
        $search = $request->query->get('search', '');
        $roleFilter = $request->query->get('role', '');
        $statutFilter = $request->query->get('statut', '');

        $utilisateurs = $utilisateurRepository->findByFilters($search, $roleFilter, $statutFilter);

        return $this->render('admin/utilisateurs.html.twig', [
            'utilisateurs' => $utilisateurs,
            'search' => $search,
            'roleFilter' => $roleFilter,
            'statutFilter' => $statutFilter,
        ]);
    }

    #[Route('/utilisateur/creer', name: 'app_admin_utilisateur_creer', methods: ['POST'])]
    public function creerUtilisateur(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        UtilisateurRepository $utilisateurRepository,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid('creer_utilisateur', $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_admin_utilisateurs');
        }

        $pseudo = trim($request->request->get('pseudo'));
        $email = trim($request->request->get('email'));
        $password = $request->request->get('password');
        $role = $request->request->get('role', 'PASSAGER');
        $credits = (int) $request->request->get('credits', Utilisateur::CREDITS_INSCRIPTION);

        // Validations
        if (empty($pseudo) || empty($email) || empty($password)) {
            $this->addFlash('danger', 'Tous les champs obligatoires doivent être remplis.');
            return $this->redirectToRoute('app_admin_utilisateurs');
        }

        if ($utilisateurRepository->findByEmail($email)) {
            $this->addFlash('danger', 'Un compte avec cet email existe déjà.');
            return $this->redirectToRoute('app_admin_utilisateurs');
        }

        if ($utilisateurRepository->findByPseudo($pseudo)) {
            $this->addFlash('danger', 'Ce pseudo est déjà utilisé.');
            return $this->redirectToRoute('app_admin_utilisateurs');
        }

        if (strlen($password) < 8) {
            $this->addFlash('danger', 'Le mot de passe doit contenir au moins 8 caractères.');
            return $this->redirectToRoute('app_admin_utilisateurs');
        }

        // Créer l'utilisateur
        $utilisateur = new Utilisateur();
        $utilisateur->setPseudo($pseudo);
        $utilisateur->setEmail($email);
        $utilisateur->setRole($role);
        $utilisateur->setCredits($credits);

        $hashedPassword = $passwordHasher->hashPassword($utilisateur, $password);
        $utilisateur->setPassword($hashedPassword);

        $entityManager->persist($utilisateur);
        $entityManager->flush();

        $this->addFlash('success', 'L\'utilisateur ' . $pseudo . ' a été créé avec succès.');

        return $this->redirectToRoute('app_admin_utilisateurs');
    }

    #[Route('/utilisateur/{id}/modifier', name: 'app_admin_utilisateur_modifier', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function modifierUtilisateur(
        Utilisateur $utilisateur,
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        UtilisateurRepository $utilisateurRepository,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid('modifier_utilisateur_' . $utilisateur->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_admin_utilisateurs');
        }

        // Ne pas modifier un admin (sauf soi-même)
        if ($utilisateur->isAdmin() && $utilisateur !== $this->getUser()) {
            $this->addFlash('danger', 'Vous ne pouvez pas modifier un autre administrateur.');
            return $this->redirectToRoute('app_admin_utilisateurs');
        }

        $pseudo = trim($request->request->get('pseudo'));
        $email = trim($request->request->get('email'));
        $password = $request->request->get('password');
        $role = $request->request->get('role');
        $credits = (int) $request->request->get('credits');

        // Vérifier l'unicité du pseudo (si changé)
        if ($pseudo !== $utilisateur->getPseudo()) {
            $existant = $utilisateurRepository->findByPseudo($pseudo);
            if ($existant && $existant->getId() !== $utilisateur->getId()) {
                $this->addFlash('danger', 'Ce pseudo est déjà utilisé.');
                return $this->redirectToRoute('app_admin_utilisateurs');
            }
        }

        // Vérifier l'unicité de l'email (si changé)
        if ($email !== $utilisateur->getEmail()) {
            $existant = $utilisateurRepository->findByEmail($email);
            if ($existant && $existant->getId() !== $utilisateur->getId()) {
                $this->addFlash('danger', 'Cet email est déjà utilisé.');
                return $this->redirectToRoute('app_admin_utilisateurs');
            }
        }

        // Mettre à jour les informations
        $utilisateur->setPseudo($pseudo);
        $utilisateur->setEmail($email);
        $utilisateur->setCredits($credits);

        // Ne pas changer le rôle d'un admin
        if (!$utilisateur->isAdmin()) {
            $utilisateur->setRole($role);
        }

        // Changer le mot de passe si fourni
        if (!empty($password)) {
            if (strlen($password) < 8) {
                $this->addFlash('danger', 'Le mot de passe doit contenir au moins 8 caractères.');
                return $this->redirectToRoute('app_admin_utilisateurs');
            }
            $hashedPassword = $passwordHasher->hashPassword($utilisateur, $password);
            $utilisateur->setPassword($hashedPassword);
        }

        $entityManager->flush();

        $this->addFlash('success', 'L\'utilisateur ' . $pseudo . ' a été modifié.');

        return $this->redirectToRoute('app_admin_utilisateurs');
    }

    #[Route('/utilisateur/{id}/supprimer', name: 'app_admin_utilisateur_supprimer', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function supprimerUtilisateur(
        Utilisateur $utilisateur,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid('supprimer_utilisateur_' . $utilisateur->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_admin_utilisateurs');
        }

        // Ne pas supprimer un admin
        if ($utilisateur->isAdmin()) {
            $this->addFlash('danger', 'Impossible de supprimer un administrateur.');
            return $this->redirectToRoute('app_admin_utilisateurs');
        }

        // Ne pas se supprimer soi-même
        if ($utilisateur === $this->getUser()) {
            $this->addFlash('danger', 'Vous ne pouvez pas supprimer votre propre compte.');
            return $this->redirectToRoute('app_admin_utilisateurs');
        }

        $pseudo = $utilisateur->getPseudo();
        $entityManager->remove($utilisateur);
        $entityManager->flush();

        $this->addFlash('success', 'L\'utilisateur ' . $pseudo . ' a été supprimé.');

        return $this->redirectToRoute('app_admin_utilisateurs');
    }

    #[Route('/utilisateur/{id}/suspendre', name: 'app_admin_suspendre', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function suspendre(
        Utilisateur $utilisateur,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid('suspendre_' . $utilisateur->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_admin_utilisateurs');
        }

        // Ne pas suspendre un admin
        if ($utilisateur->isAdmin()) {
            $this->addFlash('danger', 'Impossible de suspendre un administrateur.');
            return $this->redirectToRoute('app_admin_utilisateurs');
        }

        $utilisateur->setIsSuspended(true);
        $entityManager->flush();

        $this->addFlash('success', 'Le compte de ' . $utilisateur->getPseudo() . ' a été suspendu.');

        return $this->redirectToRoute('app_admin_utilisateurs');
    }

    #[Route('/utilisateur/{id}/reactiver', name: 'app_admin_reactiver', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function reactiver(
        Utilisateur $utilisateur,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid('reactiver_' . $utilisateur->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_admin_utilisateurs');
        }

        $utilisateur->setIsSuspended(false);
        $entityManager->flush();

        $this->addFlash('success', 'Le compte de ' . $utilisateur->getPseudo() . ' a été réactivé.');

        return $this->redirectToRoute('app_admin_utilisateurs');
    }

    #[Route('/utilisateur/{id}/credits', name: 'app_admin_utilisateur_credits', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function modifierCredits(
        Utilisateur $utilisateur,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid('credits_' . $utilisateur->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_admin_utilisateurs');
        }

        $credits = (int) $request->request->get('credits');
        
        if ($credits < 0) {
            $this->addFlash('danger', 'Le nombre de crédits ne peut pas être négatif.');
            return $this->redirectToRoute('app_admin_utilisateurs');
        }

        $utilisateur->setCredits($credits);
        $entityManager->flush();

        $this->addFlash('success', 'Les crédits de ' . $utilisateur->getPseudo() . ' ont été mis à jour (' . $credits . ' crédits).');

        return $this->redirectToRoute('app_admin_utilisateurs');
    }

    #[Route('/utilisateurs/recharger-credits', name: 'app_admin_recharger_credits_tous', methods: ['POST'])]
    public function rechargerCreditsTous(
        Request $request,
        UtilisateurRepository $utilisateurRepository,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid('recharger_credits_tous', $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_admin_utilisateurs');
        }

        $montant = (int) $request->request->get('montant', 0);
        $mode = $request->request->get('mode', 'ajouter'); // 'ajouter' ou 'definir'

        if ($montant < 0) {
            $this->addFlash('danger', 'Le montant ne peut pas être négatif.');
            return $this->redirectToRoute('app_admin_utilisateurs');
        }

        // Récupérer tous les utilisateurs (sauf employés et admins)
        $utilisateurs = $utilisateurRepository->findUtilisateursStandard();
        $count = 0;

        foreach ($utilisateurs as $utilisateur) {
            if ($mode === 'definir') {
                $utilisateur->setCredits($montant);
            } else {
                $utilisateur->setCredits($utilisateur->getCredits() + $montant);
            }
            $count++;
        }

        $entityManager->flush();

        if ($mode === 'definir') {
            $this->addFlash('success', 'Les crédits de ' . $count . ' utilisateurs ont été définis à ' . $montant . ' crédits.');
        } else {
            $this->addFlash('success', $montant . ' crédits ont été ajoutés à ' . $count . ' utilisateurs.');
        }

        return $this->redirectToRoute('app_admin_utilisateurs');
    }

    // ============================================================
    // GESTION DES EMPLOYÉS
    // ============================================================

    #[Route('/employes', name: 'app_admin_employes')]
    public function employes(UtilisateurRepository $utilisateurRepository): Response
    {
        $employes = $utilisateurRepository->findBy(['role' => Utilisateur::ROLE_EMPLOYE], ['created_at' => 'DESC']);

        return $this->render('admin/employes.html.twig', [
            'employes' => $employes,
        ]);
    }

    #[Route('/employe/creer', name: 'app_admin_employe_creer', methods: ['POST'])]
    public function creerEmploye(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        UtilisateurRepository $utilisateurRepository,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid('creer_employe', $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_admin_employes');
        }

        $pseudo = trim($request->request->get('pseudo'));
        $email = trim($request->request->get('email'));
        $password = $request->request->get('password');
        $prenom = trim($request->request->get('prenom', ''));
        $nom = trim($request->request->get('nom', ''));

        // Validations
        if (empty($pseudo) || empty($email) || empty($password)) {
            $this->addFlash('danger', 'Tous les champs obligatoires doivent être remplis.');
            return $this->redirectToRoute('app_admin_employes');
        }

        if ($utilisateurRepository->findByEmail($email)) {
            $this->addFlash('danger', 'Un compte avec cet email existe déjà.');
            return $this->redirectToRoute('app_admin_employes');
        }

        if ($utilisateurRepository->findByPseudo($pseudo)) {
            $this->addFlash('danger', 'Ce pseudo est déjà utilisé.');
            return $this->redirectToRoute('app_admin_employes');
        }

        if (strlen($password) < 8) {
            $this->addFlash('danger', 'Le mot de passe doit contenir au moins 8 caractères.');
            return $this->redirectToRoute('app_admin_employes');
        }

        // Créer l'employé
        $employe = new Utilisateur();
        $employe->setPseudo($pseudo);
        $employe->setEmail($email);
        $employe->setPrenom($prenom ?: null);
        $employe->setNom($nom ?: null);
        $employe->setRole(Utilisateur::ROLE_EMPLOYE);
        $employe->setRolesSystem(['ROLE_USER', 'ROLE_EMPLOYE']);
        $employe->setCredits(0);

        $hashedPassword = $passwordHasher->hashPassword($employe, $password);
        $employe->setPassword($hashedPassword);

        $entityManager->persist($employe);
        $entityManager->flush();

        $this->addFlash('success', 'Le compte employé de ' . $pseudo . ' a été créé.');

        return $this->redirectToRoute('app_admin_employes');
    }

    #[Route('/employe/{id}/modifier', name: 'app_admin_employe_modifier', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function modifierEmploye(
        Utilisateur $utilisateur,
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        UtilisateurRepository $utilisateurRepository,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid('modifier_employe_' . $utilisateur->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_admin_employes');
        }

        if ($utilisateur->getRole() !== Utilisateur::ROLE_EMPLOYE) {
            $this->addFlash('danger', 'Cet utilisateur n\'est pas un employé.');
            return $this->redirectToRoute('app_admin_employes');
        }

        $pseudo = trim($request->request->get('pseudo'));
        $email = trim($request->request->get('email'));
        $password = $request->request->get('password');
        $prenom = trim($request->request->get('prenom', ''));
        $nom = trim($request->request->get('nom', ''));

        // Vérifier l'unicité du pseudo (si changé)
        if ($pseudo !== $utilisateur->getPseudo()) {
            $existant = $utilisateurRepository->findByPseudo($pseudo);
            if ($existant && $existant->getId() !== $utilisateur->getId()) {
                $this->addFlash('danger', 'Ce pseudo est déjà utilisé.');
                return $this->redirectToRoute('app_admin_employes');
            }
        }

        // Vérifier l'unicité de l'email (si changé)
        if ($email !== $utilisateur->getEmail()) {
            $existant = $utilisateurRepository->findByEmail($email);
            if ($existant && $existant->getId() !== $utilisateur->getId()) {
                $this->addFlash('danger', 'Cet email est déjà utilisé.');
                return $this->redirectToRoute('app_admin_employes');
            }
        }

        // Mettre à jour les informations
        $utilisateur->setPseudo($pseudo);
        $utilisateur->setEmail($email);
        $utilisateur->setPrenom($prenom ?: null);
        $utilisateur->setNom($nom ?: null);

        // Changer le mot de passe si fourni
        if (!empty($password)) {
            if (strlen($password) < 8) {
                $this->addFlash('danger', 'Le mot de passe doit contenir au moins 8 caractères.');
                return $this->redirectToRoute('app_admin_employes');
            }
            $hashedPassword = $passwordHasher->hashPassword($utilisateur, $password);
            $utilisateur->setPassword($hashedPassword);
        }

        $entityManager->flush();

        $this->addFlash('success', 'L\'employé ' . $pseudo . ' a été modifié.');

        return $this->redirectToRoute('app_admin_employes');
    }

    #[Route('/employe/{id}/supprimer', name: 'app_admin_employe_supprimer', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function supprimerEmploye(
        Utilisateur $utilisateur,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid('supprimer_employe_' . $utilisateur->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_admin_employes');
        }

        if ($utilisateur->getRole() !== Utilisateur::ROLE_EMPLOYE) {
            $this->addFlash('danger', 'Cet utilisateur n\'est pas un employé.');
            return $this->redirectToRoute('app_admin_employes');
        }

        $pseudo = $utilisateur->getPseudo();
        $entityManager->remove($utilisateur);
        $entityManager->flush();

        $this->addFlash('success', 'Le compte employé de ' . $pseudo . ' a été supprimé.');

        return $this->redirectToRoute('app_admin_employes');
    }

    // ============================================================
    // STATISTIQUES
    // ============================================================

    #[Route('/statistiques', name: 'app_admin_statistiques')]
    public function statistiques(
        CovoiturageRepository $covoiturageRepository,
        ParticipationRepository $participationRepository,
        UtilisateurRepository $utilisateurRepository
    ): Response {
        // Stats par mois (6 derniers mois)
        $statsParMois = $covoiturageRepository->getStatistiquesParMois(6);

        // Stats crédits
        $creditsPlateforme = $participationRepository->calculerCreditsPlateforme();
        $creditsParMois = $participationRepository->getCreditsParMois(6);

        // Top chauffeurs
        $topChauffeurs = $covoiturageRepository->getTopChauffeurs(5);

        // Trajets les plus populaires
        $trajetsPopulaires = $covoiturageRepository->getTrajetsPopulaires(5);

        return $this->render('admin/statistiques.html.twig', [
            'statsParMois' => $statsParMois,
            'creditsPlateforme' => $creditsPlateforme,
            'creditsParMois' => $creditsParMois,
            'topChauffeurs' => $topChauffeurs,
            'trajetsPopulaires' => $trajetsPopulaires,
        ]);
    }
}