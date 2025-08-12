<?php

namespace App\Controller\Admin;

use App\Entity\User;

use App\Entity\Archivo;
use Doctrine\ORM\QueryBuilder;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Vich\UploaderBundle\Form\Type\VichFileType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use Symfony\Component\HttpFoundation\RequestStack;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;


class ArchivoCrudController extends AbstractCrudController
{
    public function __construct(
        private Security $security,
        private UserRepository $userRepository,
        private RequestStack $requestStack
    ) {}
    
    public static function getEntityFqcn(): string
    {
        return Archivo::class;
    }
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        // Asignar usuario automáticamente
        if ($entityInstance instanceof Archivo && !$entityInstance->getUsuarioAlta()) {
            $entityInstance->setUsuarioAlta($this->security->getUser());
        }
        
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function configureCrud(Crud $crud): Crud
    {   
         $title = 'Listado de Archivos';
                
        // Si hay un filtro por cliente, personalizar el título
        $request = $this->requestStack->getCurrentRequest();
        if ($request && $request->query->has('filters')) {
            $filters = $request->query->all('filters');
            if (isset($filters['usuario_cliente_asignado']['value']) && !empty($filters['usuario_cliente_asignado']['value'])) {
                $userId = $filters['usuario_cliente_asignado']['value'];
                $user = $this->userRepository->find($userId);
                if ($user) {
                    $title = sprintf('📁 Archivos de %s', $user->getNombre());
                }
            }
        }
        
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX,  $title)
            ->setPageTitle(Crud::PAGE_NEW, 'Subir nuevo archivo')
            ->setPaginatorPageSize(10)
            // ->overrideTemplate('crud/new', 'admin/archivo/new.html.twig')
            // ->overrideTemplate('crud/edit', 'admin/archivo/edit.html.twig')
           ;
    }

    public function configureActions(Actions $actions): Actions
    {

        $actions = $actions
        ->setPermission(Action::NEW, 'ROLE_ADMIN')
        ->setPermission(Action::EDIT, 'ROLE_ADMIN')
        ->setPermission(Action::DELETE, 'ROLE_ADMIN')
        ->setPermission(Action::DETAIL, 'ROLE_ADMIN')
        ->update(Crud::PAGE_INDEX, Action::NEW, fn(Action $action) => $action->setLabel('Subir Archivo'));        
        return $actions;
    }

    public function configureFilters(Filters $filters): Filters
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $filters->add('usuario_cliente_asignado');
            // $filters->add('titulo');
            
        }
        
        return $filters;
    }
            
    // public function configureFilters(Filters $filters): Filters 
    // {
    //     if ($this->isGranted('ROLE_ADMIN')) {
    //         $filters->add('usuario_cliente_asignado');
            
    //         // Si hay un filtro activo, mostrar mensaje
    //         $request = $this->getContext()->getRequest();
    //         $clienteId = $request->query->get('usuario_cliente_asignado');
            
    //         if ($clienteId) {
    //             // Obtener el nombre del cliente
    //             $cliente = $this->getDoctrine()->getRepository(User::class)->find($clienteId);
    //             if ($cliente) {
    //                 $this->addFlash('info', sprintf('Mostrando archivos del cliente: %s', $cliente->getNombre()));
    //             }
    //         }
    //     }
        
    //     return $filters;
    // }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        // Si no es admin, mostrar solo archivos asignados al usuario actual
        if (!$this->isGranted('ROLE_ADMIN')) {
            $qb->andWhere('entity.usuario_cliente_asignado = :user')
                ->setParameter('user', $this->getUser());
        } else {
            // Si es admin y hay un parámetro clienteId, filtrar por ese cliente
            $request = $this->getContext()?->getRequest();
            if ($request && $request->query->has('clienteId')) {
                $clienteId = $request->query->get('clienteId');
                $qb->andWhere('entity.usuario_cliente_asignado = :clienteId')
                   ->setParameter('clienteId', $clienteId);
            }
        }

        return $qb;
    }
    


   public function configureFields(string $pageName): iterable
    {
        // Si no es admin, mostrar solo campos básicos para usuarios
        if (!$this->isGranted('ROLE_ADMIN')) {
            return [
                DateField::new('createdAt', 'Añadido el ')
                    ->setFormat('dd/MM/yyyy')
                    ->setFormTypeOption('data', new \DateTime())                     
                    ->setColumns(2)
                    ->onlyOnIndex(),

                TextField::new('titulo', 'Titulo')
                    ->setColumns(4)
                    ->formatValue(function ($value, $entity) {
                        if (!$entity instanceof \App\Entity\Archivo || !$entity->getNombreArchivo()) {
                            return $value;
                        }

                        // Ruta pública donde se guardan los archivos (ajustá según tu config de VichUploader)
                        $ruta = '/uploads/archivos_pdf/' . $entity->getNombreArchivo();

                        return sprintf(
                            '<a href="%s" download style="text-decoration: underline; color: #007bff;">%s</a>',
                            $ruta,
                            htmlspecialchars($value)
                        );
                    })
                    ->renderAsHtml(),

                IntegerField::new('tamaño', 'Tamaño (KB)')
                    ->onlyOnIndex()
                    ->formatValue(function ($value) {
                        return $value ? round($value / 1024, 2) : 0;
                    })
                    ->setCustomOption(IntegerField::OPTION_NUMBER_FORMAT, '%.2f KB')
                    ->setCustomOption(IntegerField::OPTION_THOUSANDS_SEPARATOR, ',')
                    ->setColumns(2),

            ];
        }

        // Para administradores, mantener tu configuración completa original
        return [
            // IdField::new('id'),
            DateField::new('createdAt', 'Añadido el ')
                ->setFormat('dd/MM/yyyy')
                ->setFormTypeOption('data', new \DateTime())                     
                ->setColumns(2)
                ->onlyOnIndex(),

            TextField::new('titulo', 'Titulo')
                ->setColumns(4)
                ->formatValue(function ($value, $entity) {
                    if (!$entity instanceof \App\Entity\Archivo || !$entity->getNombreArchivo()) {
                        return $value;
                    }

                    // Ruta pública donde se guardan los archivos (ajustá según tu config de VichUploader)
                    $ruta = '/uploads/archivos_pdf/' . $entity->getNombreArchivo();

                    return sprintf(
                        '<a href="%s" download style="text-decoration: underline; color: #007bff;">%s</a>',
                        $ruta,
                        htmlspecialchars($value)
                    );
                })
                ->renderAsHtml(),
            

            IntegerField::new('tamaño', 'Tamaño (KB)')
                ->onlyOnIndex()
                ->formatValue(function ($value) {
                    return $value ? round($value / 1024, 2) : 0;
                })
                ->setCustomOption(IntegerField::OPTION_NUMBER_FORMAT, '%.2f KB')
                ->setCustomOption(IntegerField::OPTION_THOUSANDS_SEPARATOR, ',')
                ->setColumns(2),

            DateField::new('fecha_expira', 'Fecha de expiración')
                ->setFormat('dd/MM/yyyy')
                ->setFormTypeOption('data', new \DateTime())                     
                ->setColumns(2),
            
            // Mostrar solo el nombre del usuario en el index
            TextField::new('usuario_alta.nombre', 'Subido por')
                ->onlyOnIndex(),

 
            TextField::new('asignadoTexto', 'Asignado')
                ->onlyOnIndex()
                ->renderAsHtml(),

            // Campo para mostrar el en la vista modificar
            AssociationField::new('usuario_cliente_asignado', 'Cliente asignado')
                ->setQueryBuilder(function (QueryBuilder $qb) {
                    $qb->andWhere('entity.roles LIKE :user_role')
                    ->andWhere('entity.roles NOT LIKE :admin_role')
                    ->setParameter('user_role', '%"ROLE_USER"%')
                    ->setParameter('admin_role', '%"ROLE_ADMIN"%');
                })
                ->setColumns(4)
                ->setRequired(false)
                ->onlyOnForms(),

            AssociationField::new('categoria', 'Categoría')
                ->setFormTypeOption('choice_label', 'nombre')
                ->setRequired(false)
                ->renderAsNativeWidget()
                ->setColumns(2)
                ->onlyOnForms(), 
                     
            TextEditorField::new('descripcion', 'Descripción')
                ->onlyOnForms(),
           
            // Sección 4: Carga de archivo
            TextField::new('archivoFile', 'link de descarga')
                ->setFormType(VichFileType::class)
                ->setFormTypeOptions([
                    'allow_delete' => false,
                    'download_uri' => false,
                ])
                ->onlyOnForms(),

            // TextField::new('archivo')
            //     ->onlyOnIndex(),

            // Sección 3: Observaciones (checkboxes)
            BooleanField::new('permitido_publicar', 'Permitir descarga pública')
                ->renderAsSwitch(false)
                ->onlyOnForms(),
            
            BooleanField::new('notificar_cliente', 'Notificar al cliente')
                ->onlyOnForms()
                ->setFormTypeOption('mapped', false),
        ];
    }

   
}
