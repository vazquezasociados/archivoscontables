<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;

class AdministradorCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function __construct(
        private UserPasswordHasherInterface $passwordEncoder,
        private MailerService $mailerService
    ){}

    // Filtrar solo usuarios con ROLE_ADMIN
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        
        // Filtrar solo usuarios que tengan ROLE_ADMIN
        $queryBuilder->andWhere('entity.roles LIKE :role_admin')
                    ->setParameter('role_admin', '%ROLE_ADMIN%');

        return $queryBuilder;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof User) {
            return;
        }

        // Forzar que tenga ROLE_ADMIN (puede tener ROLE_USER también si se desea)
        $currentRoles = $entityInstance->getRoles();
        if (!in_array('ROLE_ADMIN', $currentRoles)) {
            $entityInstance->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        }

       // Generar la contraseña solo si es un nuevo usuario y si el campo plainPassword no está vacío
        if ($entityInstance->getId() === null && $entityInstance->getPlainPassword()) {
            
            // Obtener la contraseña en texto plano del formulario
            $plainPassword = $entityInstance->getPlainPassword();
            
            // Hashear la contraseña y establecerla en la entidad para la persistencia
            $hashedPassword = $this->passwordEncoder->hashPassword($entityInstance, $plainPassword);
            $entityInstance->setPassword($hashedPassword);
            
            // Asignar el nombre de usuario (CUIT) para la persistencia
            $cuit = $entityInstance->getNombreUsuario();
            
            // Enviar el correo electrónico con el CUIT y la contraseña en texto plano
            if ($entityInstance->isEnviarCorreoBienvenido()) {
                $this->mailerService->sendWelcomeEmail(
                    $entityInstance->getEmail(),                 // $to
                    $entityInstance->getNombre() ?? 'Usuario',   // $nombre
                    $cuit,                                       // $cuit
                    $plainPassword                               // $password (texto plano)
                );
            }
            $this->addFlash('success', "¡Se envió exitosamente el mail de bienvenida!");
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof User) {
            return;
        }

        // Asegurar que mantenga ROLE_ADMIN
        $currentRoles = $entityInstance->getRoles();
        if (!in_array('ROLE_ADMIN', $currentRoles)) {
            $entityInstance->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Lista de Administradores')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Detalle del Administrador')
            ->setPageTitle(Crud::PAGE_NEW, 'Nuevo Administrador')
            ->setPageTitle(Crud::PAGE_EDIT, 'Editar Administrador')
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(15);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Crear Administrador');
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setLabel('Crear Administrador');
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $action) {
                return $action->setLabel('Crear y añadir otro administrador');
            })
            ->add(Crud::PAGE_NEW, Action::INDEX)
            ->update(Crud::PAGE_NEW, Action::INDEX, function (Action $action) {
                return $action
                    ->setLabel('Cancelar')
                    ->setCssClass('btn-custom-cancel');
            });
    }

    public function configureFields(string $pageName): iterable
    {
        $nombre = TextField::new('nombre', 'Nombre')
            ->setColumns(3);
        
        $nombreUsuario = TextField::new('nombreUsuario','CUIT / CUIL')
            ->setFormType(TextType::class)
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
            ->setColumns(3)
            ->setHelp('Ingrese el CUIT (11 dígitos, sin guiones ni puntos)');

        $direccion = TextField::new('direccion', 'Dirección')
            ->setColumns(3)
            ->hideOnIndex();
        
        $telefono = TextField::new('telefono', 'Teléfono')
            ->setColumns(3)
            ->hideOnIndex();

        $activo = BooleanField::new('activo', 'Activo')
            ->setColumns(3);

        $bienvenido = BooleanField::new('enviarCorreoBienvenido', 'Enviar correo de bienvenida')
            ->setColumns(3);

        $nombreContactoInterno = TextField::new('nombreContactoInterno', 'Nombre del contacto interno')
            ->setColumns(4)
            ->hideOnIndex();

        $email = TextField::new('email', 'Email')
            ->setColumns(3);
        
        $password = TextField::new('plainPassword', 'Password')
            ->setColumns(3)
            ->setFormType(PasswordType::class)
            ->setFormTypeOptions([
                'attr' => [
                    'data-password-toggle' => 'true',
                ],
            ])
            ->setRequired(true)
            ->setRequired($pageName === Crud::PAGE_NEW)
            ->onlyOnForms()
            ->setPermission('ROLE_ADMIN')
            ->setHelp($pageName === Crud::PAGE_NEW 
                ? 'La contraseña es obligatoria al crear un nuevo usuario' 
                : 'Dejar en blanco para mantener la contraseña actual');;


        $rolesDisplay = ChoiceField::new('roles', 'Roles')
            ->onlyOnIndex()
            ->renderAsBadges()
            ->setChoices([
                'Usuario' => 'ROLE_USER',
                'Administrador' => 'ROLE_ADMIN',
            ]);

        $fechaAlta = DateField::new('createdAt', 'Fecha Alta')
            ->setFormat('dd/MM/yyyy')
            ->hideOnForm();

        if(Crud::PAGE_INDEX === $pageName){
            return [$nombre, $nombreUsuario, $email, $rolesDisplay, $fechaAlta, $activo];
        }

        return [
            $nombre, $nombreUsuario, $email, $password, $direccion, 
            $telefono, $activo, $bienvenido, $nombreContactoInterno
        ];
    }
}