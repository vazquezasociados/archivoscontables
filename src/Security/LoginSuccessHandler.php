<?php
namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\HttpFoundation\Request;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(private RouterInterface $router) {}
    
    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        $user = $token->getUser();
        // dd($user);
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return new RedirectResponse($this->router->generate('admin'));
        }

        if (in_array('ROLE_USER', $user->getRoles(), true)) {
            return new RedirectResponse($this->router->generate('archivo_index'));
        }

        // return new RedirectResponse($this->router->generate('app_home'));
        throw new \LogicException('El usuario autenticado no tiene un rol válido para redirección.');
    }
}
