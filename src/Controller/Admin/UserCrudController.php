<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Service\MailerService;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Symfony\Component\HttpFoundation\RedirectResponse;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
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
        private UserPasswordHasherInterface $passwordEncoder,
        private AdminUrlGenerator $adminUrlGenerator,
        private UserRepository $userRepository,
         private MailerService $mailerService
    
    ){}

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof User) {
            return;
        }
            // dd($this->mailerService);

        // 👇 Lógica de envío de mail
        if ($entityInstance->isEnviarCorreoBienvenido()) {
            // dd( $entityInstance->getNombre());
            
            $this->mailerService->sendWelcomeEmail(
                $entityInstance->getEmail(),
                $entityInstance->getNombre() ?? 'Usuario'
            );
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Clientes')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Detalle del Cliente')
            ->setPageTitle(Crud::PAGE_NEW, 'Nuevo Cliente')
            ->setDefaultSort(['id' => 'DESC']) 
            ->setPaginatorPageSize(15);
            
    }

    public function configureActions(Actions $actions): Actions
    {
        // Crear la acción personalizada "Ver archivos"
        $verArchivos = Action::new('verArchivos', 'Ver archivos')
            ->linkToCrudAction('verArchivosCliente')
            ->setHtmlAttributes([
                'title' => 'Ver archivos asignados al cliente'
            ])
            ;
       
        return $actions
            ->add(Crud::PAGE_INDEX, $verArchivos)// Agregar la acción personalizada al índice
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) { // Cambiar texto del botón "Nuevo"
                return $action->setLabel('Crear Cliente');
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setLabel('Crear'); // Cambia el nombre a "Crear"
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $action) {
                return $action->setLabel('Crear y añadir otro'); // Cambia el nombre a "Crear y añadir otro"
            })
            ->add(Crud::PAGE_NEW, Action::INDEX)
            ->update(Crud::PAGE_NEW, Action::INDEX, function (Action $action) {
                return $action
                    ->setLabel('Cancelar')
                    ->setCssClass('btn-custom-cancel');
            })
            // Solo mostrar la acción para usuarios con rol USER (no para admins)
            ->setPermission('verArchivos', 'ROLE_ADMIN');
            
    }
    
    public function verArchivosCliente(): RedirectResponse
    {
        // Obtener el ID del usuario desde la URL
        $userId = $this->getContext()->getRequest()->query->get('entityId');
        
        if (!$userId) {
            $this->addFlash('error', 'No se pudo identificar al cliente.');
            return $this->redirect($this->adminUrlGenerator
                ->setController(UserCrudController::class)
                ->setAction(Action::INDEX)
                ->generateUrl()
            );
        }

        // Verificar que el usuario existe usando el Repository
        $user = $this->userRepository->find($userId);
        if (!$user) {
            $this->addFlash('error', 'Cliente no encontrado.');
            return $this->redirect($this->adminUrlGenerator
                ->setController(UserCrudController::class)
                ->setAction(Action::INDEX)
                ->generateUrl()
            );
        }

        // Redirigir al CRUD de archivos con parámetro de usuario
        return $this->redirect($this->adminUrlGenerator
            ->setController(ArchivoCrudController::class)
            ->setAction(Action::INDEX)
            ->set('clienteId', $userId)
            ->generateUrl()
        );
    }

    public function configureFields(string $pageName): iterable
    {
        
        $nombre = TextField::new('nombre', 'Nombre')
            ->setColumns(3);
        $nombreUsuario = TextField::new('nombreUsuario','cuil/cuit')
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

        $nombreContactoInterno = TextField::new('nombreContactoInterno', 'Nombre Contacto Interno')
            ->setColumns(4)
            ->hideOnIndex();

        $email = TextField::new('email', 'Email')
            ->setColumns(3);
        $password  = TextField::new('plainPassword', 'Password')
            ->setColumns(3)
            ->setFormType(PasswordType::class)
            // ->setFormTypeOptions([
            //         'attr' => [
            //             'class' => 'password-field',
            //             'autocomplete' => 'new-password',
            //             'data-toggle' => 'password',
            //         ],
            //     ])
            ->setRequired(false)
            ->onlyOnForms()
            ->setPermission('ROLE_ADMIN');

   
        // $maxCarga = IntegerField::new('maxCarga', 'Carga Máxima')->hideOnIndex();
        $roles = ChoiceField::new('roles', 'Roles')
            // ->setChoices($this->roles)
            ->allowMultipleChoices(true)
            ->setChoices([
                'Cliente' => 'ROLE_USER',  
                'Administrador' => 'ROLE_ADMIN',
            ])
            ->setColumns(4);
        $roles2 = ChoiceField::new('roles', 'Roles')
            ->onlyOnIndex()->renderAsBadges()
            // ->setChoices($this->rolesComplete)
            ->setChoices([
                'Cliente' => 'ROLE_USER',
                'Administrador' => 'ROLE_ADMIN',
            ]);
        $fechaAlta = DateField::new('createdAt', 'Fecha Alta')
                ->setFormat('dd/MM/yyyy')
                ->hideOnForm();
        if(Crud::PAGE_INDEX === $pageName){
            return[$nombre, $nombreUsuario,$email,$fechaAlta];
        }
        return[$nombre,$nombreUsuario,$email,$password,$direccion,$telefono,$activo,$bienvenido,
        $nombreContactoInterno,$roles, $roles2 ];
    }

}
