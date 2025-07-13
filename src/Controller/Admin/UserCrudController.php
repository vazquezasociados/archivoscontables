<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
        
        $nombre = TextField::new('nombre', 'Nombre');
        $nombreUsuario = TextField::new('nombreUsuario','Nombre Usuario')
            ->setFormType(TextType::class)  // Fuerza tipo texto
            ->setFormTypeOptions([
                'attr' => [
                    'inputmode' => 'numeric',
                    'pattern' => '\d{11}',
                    'minlength' => 11,
                    'maxlength' => 11,
                ],
                'invalid_message' => 'El CUIT debe contener solo números (11 dígitos)',
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\Regex([
                        'pattern' => '/^\d{11}$/',
                        'message' => 'El CUIT debe tener exactamente 11 dígitos numéricos'
                    ])
                ]
            ])
            ->setHelp('Ingrese el CUIT (11 dígitos, sin guiones ni puntos)');

        $direccion = TextField::new('direccion', 'Dirección')->hideOnIndex();
        $telefono = TextField::new('telefono', 'Teléfono')->hideOnIndex();
        $nombreContactoInterno = TextField::new('nombreContactoInterno', 'Nombre Contacto Interno')
            ->hideOnIndex();        
        $email     = TextField::new('email', 'Email');
        $password  = TextField::new('plainPassword', 'password')
            ->setFormType(PasswordType::class)
            ->setRequired(false)->onlyOnForms()
            ->setPermission('ROLE_ADMIN');
        // $maxCarga = IntegerField::new('maxCarga', 'Carga Máxima')->hideOnIndex();
        $roles = ChoiceField::new('roles', 'roles')
            ->setChoices($this->roles)
            ->allowMultipleChoices(true);
        $roles2 = ChoiceField::new('roles', 'roles')
            ->onlyOnIndex()->renderAsBadges()
            ->setChoices($this->rolesComplete);
        $fechaAlta = DateField::new('createdAt', 'Fecha Alta')
                ->setFormat('dd/MM/yyyy')
                ->hideOnForm();
        if(Crud::PAGE_INDEX === $pageName){
            return[$nombre, $nombreUsuario,$email,$fechaAlta];
        }
        return[$nombre,$nombreUsuario,$email,$password,$direccion,$telefono,
        $nombreContactoInterno,$roles, $roles2];

    }

}
