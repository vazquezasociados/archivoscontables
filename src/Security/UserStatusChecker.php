<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class UserStatusChecker implements UserCheckerInterface
{
    /**
     * @param User $user
     */
    public function checkPreAuth(UserInterface $user): void
    { }

    /**
     * @param User $user
     */
    public function checkPostAuth(UserInterface $user): void
    {
        // Se ejecuta después de que la contraseña ha sido validada.
        // Aquí es donde pondrías tu lógica.
        
        if (!$user instanceof User) {
            return;
        }

        // Si el usuario no está activo, lanza una excepción
        if (!$user->isActivo()) {
            throw new CustomUserMessageAuthenticationException('Tu cuenta está inactiva. Por favor, contacta con el administrador.');
        }
    }
}