<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/connexion', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si deja connecte, on redirige directement vers le bon dashboard
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard_redirect');
        }

        // Recupere l'erreur de connexion s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();

        // Dernier email saisi par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/deconnexion', name: 'app_logout')]
    public function logout(): void
    {
        // Cette methode peut rester vide - elle est interceptee par le
        // firewall "logout" configure dans security.yaml
        throw new \LogicException('Cette methode ne doit jamais etre appelee directement.');
    }

    /**
     * Petit routeur qui redirige chaque role vers son propre espace apres connexion
     */
    #[Route(path: '/tableau-de-bord', name: 'app_dashboard_redirect')]
    public function dashboardRedirect(): Response
    {
        if ($this->isGranted('ROLE_DAIP')) {
            return $this->redirectToRoute('daip_offres_index');
        }

        if ($this->isGranted('ROLE_ENTREPRISE')) {
            return $this->redirectToRoute('entreprise_offres_index');
        }

        return $this->redirectToRoute('app_home');
    }
}
