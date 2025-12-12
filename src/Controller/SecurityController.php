<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\RegistrationType;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/inscription', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response {
        // Rediriger si déjà connecté
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $utilisateur = new Utilisateur();
        $form = $this->createForm(RegistrationType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hasher le mot de passe
            $hashedPassword = $passwordHasher->hashPassword(
                $utilisateur,
                $form->get('plainPassword')->getData()
            );
            $utilisateur->setPassword($hashedPassword);

            // Persister l'utilisateur
            $entityManager->persist($utilisateur);
            $entityManager->flush();

            // Envoyer l'email de bienvenue
            $email = (new TemplatedEmail())
                ->from('noreply@ecoride.fr')
                ->to($utilisateur->getEmail())
                ->subject('Bienvenue sur EcoRide !')
                ->htmlTemplate('emails/bienvenue.html.twig')
                ->context([
                    'utilisateur' => $utilisateur,
                    'creditsOfferts' => Utilisateur::CREDITS_INSCRIPTION,
                ]);

            $mailer->send($email);

            $this->addFlash('success', 'Votre compte a été créé avec succès ! Vous bénéficiez de ' . Utilisateur::CREDITS_INSCRIPTION . ' crédits offerts. Connectez-vous pour commencer.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/connexion', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Rediriger si déjà connecté
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        // Récupérer l'erreur de connexion s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();

        // Dernier nom d'utilisateur saisi
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/deconnexion', name: 'app_logout')]
    public function logout(): void
    {
        // Cette méthode peut rester vide
        // Elle sera interceptée par le firewall
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}