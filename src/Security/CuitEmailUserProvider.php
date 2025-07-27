<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class CuitEmailUserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
   
   public function __construct(private UserRepository $userRepository) {}

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        // 1. Buscar por CUIT
        $user = $this->userRepository->findOneBy(['nombreUsuario' => $identifier]);
        
        if ($user) return $user;
        
        // 2. Buscar por email (solo admins)
        $user = $this->userRepository->findOneBy(['email' => $identifier]);
        
        if ($user && array_intersect(['ROLE_ADMIN', 'ROLE_SUPERADMIN'], $user->getRoles())) {
            return $user;
        }
        
        throw new UserNotFoundException('Usuario no encontrado');
    }
    
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Tipo de usuario "%s" no soportado', get_class($user)));
        }
        
        return $this->userRepository->find($user->getId());
    }
    
    public function supportsClass(string $class): bool
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        // Solo asegura que el password se actualice para cumplir con la interfaz
        // Tu EventSubscriber seguirá manejando la lógica compleja
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Tipo de usuario "%s" no soportado', get_class($user)));
        }
        
        $user->setPassword($newHashedPassword);
        $this->userRepository->save($user, true);
    }
}