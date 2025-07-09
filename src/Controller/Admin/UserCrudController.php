<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }
    public function __construct(
        private array $roles, 
        private array $rolesComplete,
        private UserPasswordHasherInterface $passwordEncoder
    
    ){}

    public function configureFields(string $pageName): iterable
    {
        
        $email     = TextField::new('email', 'Email');
        $password  = TextField::new('plainPassword', 'password')
                    ->setFormType(PasswordType::class)
                    ->setRequired(false)->onlyOnForms()
                    ->setPermission('ROLE_ADMIN');
     
        $roles = ChoiceField::new('roles', 'roles')
                ->setChoices($this->roles)
                ->allowMultipleChoices(true);
        $roles2 = ChoiceField::new('roles', 'roles')
                ->onlyOnIndex()->renderAsBadges()
                ->setChoices($this->rolesComplete);
        if(Crud::PAGE_INDEX === $pageName){
            return[$email, $roles2];
        }
        return[$email,$password,$roles];
    }

}
