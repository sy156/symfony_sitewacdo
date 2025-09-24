<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response

    {
        // Si l'utilisateur est déjà connecté, rediriger selon le rôle
        $user = $this->getUser();
        if ($user) {
            $roles = $user->getRoles();
            if (in_array('ROLE_SUPER_ADMIN', $roles)) {
                return $this->redirectToRoute('app_admin_dashboard');
            }
            if (in_array('ROLE_PREPARATEUR', $roles)) {
                return $this->redirectToRoute('preparateur_dashboard');
            }
            if (in_array('ROLE_ACCUEIL', $roles)) {
                return $this->redirectToRoute('accueil_dashboard');
            }
        }

        // Récupère erreur et dernier email saisi
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastEmail = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_email' => $lastEmail,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Symfony intercepte cette route automatiquement
        throw new \LogicException('This method will be intercepted by the logout key on your firewall.');
    }
}
