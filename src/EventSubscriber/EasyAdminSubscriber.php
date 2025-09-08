<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected Security $security,
        private UserPasswordHasherInterface $passwordEncoder,
       
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
                     
            BeforeEntityPersistedEvent::class => [           
                ['changePassword']
            ],
            BeforeEntityUpdatedEvent::class => [
                ['updatePassword']
            ],
        ];
    }

    public function changePassword(BeforeEntityPersistedEvent $event)
    {
        $this->setPassword($event);
    }

    public function updatePassword(BeforeEntityUpdatedEvent $event)
    {         

        $this->setPassword($event);
    }

    public function setPassword($event)
    {
        $entity = $event->getEntityInstance();
        if (!($entity instanceof User)) {
            return;
        }
        if ($entity->getPlainPassword()) {
            $entity->setPassword($this->passwordEncoder->hashPassword(
                $entity,
                $entity->getPlainPassword()
            ));
        }
        
    }
 

}





