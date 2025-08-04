<?php

namespace App\Security;

use App\Entity\Archivo;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ArchivoVoter extends Voter
{
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const DELETE = 'delete';
    public const NEW = 'new';
    public const DOWNLOAD = 'download';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE, self::NEW, self::DOWNLOAD])
            && ($subject instanceof Archivo || $subject === 'Archivo');
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // Si no hay usuario autenticado
        if (!$user instanceof User) {
            return false;
        }

        // Los administradores pueden hacer todo
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        // Para usuarios normales (ROLE_USER)
        switch ($attribute) {
            case self::VIEW:
            case self::DOWNLOAD:
                return $this->canViewOrDownload($subject, $user);
            case self::EDIT:
            case self::DELETE:
            case self::NEW:
                // Los usuarios normales no pueden crear, editar o eliminar
                return false;
        }

        return false;
    }

    private function canViewOrDownload(Archivo $archivo, User $user): bool
    {
        // Solo puede ver/descargar archivos que le están asignados
        return $archivo->getUsuarioClienteAsignado() === $user;
    }
}