<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    /**
     * Render the login form. Request is handle by the form_login authentificator enable in security.yaml
     */
    #[Route('/connexion', methods: ['GET', 'POST'], name: 'login')]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        //route not accessible to logged users
        if ($this->getUser() && $this->getUser() instanceof UserInterface) {
            return $this->redirectToRoute('user_account');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login/index.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }

    #[Route('/logout', methods: ['GET'], name: 'logout')]
    public function logout(): void
    {
        // controller can be blank: it will never be called, logout is handled in security.yaml
    }
}
