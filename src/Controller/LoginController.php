<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
final class LoginController extends AbstractController
{
 #[Route('/login', name: 'admin_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/login.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'last_username' => $authenticationUtils->getLastUsername(),
            'page_title' => '<img src="/build/images/logo_login.svg" alt="Logo" height="200" width="200" class="mb-3">',
            'csrf_token_intention' => 'authenticate', 
            'target_path' => $this->generateUrl('admin'), 
            'username_label' => 'Nombre de usuario / Email', // Esta es la clave
            'custom_message' => 'Si necesita una cuenta, por favor contacte al administrador del servidor.',
        ]);
    }

    #[Route('/logout', name: 'admin_logout')]
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}

