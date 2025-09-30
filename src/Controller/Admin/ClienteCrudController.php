<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Service\MailerService;
use Doctrine\ORM\QueryBuilder;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Symfony\Component\HttpFoundation\RedirectResponse;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ClienteCrudController extends AbstractCrudController
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
        private MailerService $mailerService,
        private EntityManagerInterface $entityManager 
    ){}

    // Filtrar solo usuarios con ROLE_USER
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        
        // Filtrar solo usuarios que tengan ROLE_USER y NO tengan ROLE_ADMIN
        $queryBuilder->andWhere('entity.roles LIKE :role_user')
                    ->andWhere('entity.roles NOT LIKE :role_admin')
                    ->setParameter('role_user', '%ROLE_USER%')
                    ->setParameter('role_admin', '%ROLE_ADMIN%');

        return $queryBuilder;
    }

   public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof User) {
            return;
        }

        // Aseguramos que solo tenga el rol de cliente
        $entityInstance->setRoles(['ROLE_USER']);

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

        // Persistir la entidad en la base de datos
        parent::persistEntity($entityManager, $entityInstance);
    }




    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof User) {
            return;
        }

        // Asegurar que mantenga solo ROLE_USER
        $entityInstance->setRoles(['ROLE_USER']);

        parent::updateEntity($entityManager, $entityInstance);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Lista de Clientes')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Detalle del Cliente')
            ->setPageTitle(Crud::PAGE_NEW, 'Nuevo Cliente')
            ->setPageTitle(Crud::PAGE_EDIT, 'Editar Cliente')
            ->setDefaultSort(['nombre' => 'ASC'])
            ->setPaginatorPageSize(15);
    }

    public function configureActions(Actions $actions): Actions
    {
        // Crear la acción personalizada "Ver archivos"
        // $verArchivos = Action::new('verArchivos', 'Ver archivos')
        //     ->linkToCrudAction('verArchivosCliente')
        //     ->setHtmlAttributes([
        //         'title' => 'Ver archivos asignados al cliente'
        //     ]);

        return $actions
            // ->add(Crud::PAGE_INDEX, $verArchivos)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Crear Cliente');
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setLabel('Crear Cliente');
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $action) {
                return $action->setLabel('Crear y añadir otro cliente');
            })
            ->add(Crud::PAGE_NEW, Action::INDEX)
            ->update(Crud::PAGE_NEW, Action::INDEX, function (Action $action) {
                return $action
                    ->setLabel('Cancelar')
                    ->setCssClass('btn-custom-cancel');
            });
            // ->setPermission('verArchivos', 'ROLE_ADMIN');
    }

    public function verArchivosCliente(): RedirectResponse
    {
        $userId = $this->getContext()->getRequest()->query->get('entityId');

        if (!$userId) {
            $this->addFlash('error', 'No se pudo identificar al cliente.');
            return $this->redirect($this->adminUrlGenerator
                ->setController(ClienteCrudController::class)
                ->setAction(Action::INDEX)
                ->generateUrl()
            );
        }

        $user = $this->userRepository->find($userId);
        if (!$user) {
            $this->addFlash('error', 'Cliente no encontrado.');
            return $this->redirect($this->adminUrlGenerator
                ->setController(ClienteCrudController::class)
                ->setAction(Action::INDEX)
                ->generateUrl()
            );
        }

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
         
        // NUEVO CAMPO: Contador de archivos
        // $archivosCount = IntegerField::new('archivosAsignadosCount', 'Total de archivos')
        //     ->onlyOnIndex()
        //     ->setColumns(2)
        //     ->formatValue(function ($value, $entity) {
        //         if (!$entity instanceof User) {
        //             return 0;
        //         }
                
        //         // Contar usando DQL directamente
        //         $count = $this->entityManager
        //             ->createQuery('
        //                 SELECT COUNT(a.id) 
        //                 FROM App\Entity\Archivo a 
        //                 WHERE a.usuario_cliente_asignado = :usuario
        //             ')
        //             ->setParameter('usuario', $entity)
        //             ->getSingleScalarResult();
                    
        //         return (int) $count;
        //     })
        //     ->setSortable(false)
        //     ->setCssClass('text-center')
        //     ->hideOnForm();

        $verArchivosBoton = TextField::new('verArchivos', 'Acciones')
            ->onlyOnIndex()
            ->setColumns(2)
            ->formatValue(function ($value, $entity) {
                if (!$entity instanceof User) {
                    return '';
                }
                
                // Contar archivos para mostrar el botón condicionalmente
                $count = $this->entityManager
                    ->createQuery('
                        SELECT COUNT(a.id) 
                        FROM App\Entity\Archivo a 
                        WHERE a.usuario_cliente_asignado = :usuario
                    ')
                    ->setParameter('usuario', $entity)
                    ->getSingleScalarResult();
                
                if ($count > 0) {
                    // Generar URL para ver archivos
                    $url = $this->adminUrlGenerator
                        ->setController(ArchivoCrudController::class)
                        ->setAction(Action::INDEX)
                        ->set('clienteId', $entity->getId())
                        ->generateUrl();
                    
                    return sprintf(
                        '<a href="%s" class="btn btn-sm btn-primary" title="Ver archivos asignados">
                            <i class="fas fa-folder-open"></i> Ver (%d)
                        </a>',
                        $url,
                        $count
                    );
                }
                
                return '<span class="text-muted">Sin archivos</span>';
            })
            ->renderAsHtml()
            ->setSortable(false);

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
            ->setRequired(false)
            ->onlyOnForms()
            ->setPermission('ROLE_ADMIN');

        // Campo de rol oculto pero fijo como ROLE_USER
        $roles = ChoiceField::new('roles', 'Tipo')
            ->setChoices(['Cliente' => 'ROLE_USER'])
            ->allowMultipleChoices(false)
            ->setColumns(4)
            ->hideOnForm(); // Lo ocultamos porque siempre será Cliente

        $fechaAlta = DateField::new('createdAt', 'Fecha Alta')
            ->setFormat('dd/MM/yyyy')
            ->hideOnForm();

        if(Crud::PAGE_INDEX === $pageName){
            return [$nombre, $nombreUsuario, $email, $fechaAlta,$verArchivosBoton,$activo];
        }

        return [
            $nombre, $nombreUsuario, $email, $password, $direccion, 
            $telefono, $activo, $bienvenido, $nombreContactoInterno
        ];
    }
}