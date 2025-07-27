<?php

namespace App\Controller\Admin;

use App\Entity\Archivo;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Vich\UploaderBundle\Form\Type\VichFileType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ArchivoCrudController extends AbstractCrudController
{
    public function __construct(
        private Security $security
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
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Listado de Archivos')
            ->setPageTitle(Crud::PAGE_NEW, 'Subir nuevo archivo')
            ->setPaginatorPageSize(10)
           ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            // Cambiar texto del botón "Nuevo"
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Subir Archivo');
            });
            
            // Cambiar texto del botón "Editar"
            // ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
            //     return $action->setLabel('Modificar');
            // })
            
            // Cambiar texto del botón "Eliminar"
            // ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
            //     return $action->setLabel('Eliminar');
            // });
    }
    
    public function configureFields(string $pageName): iterable
    {
        return [
            // IdField::new('id'),
            DateField::new('createdAt', 'Fecha de expiración')
                ->setFormat('dd/MM/yyyy')
                ->setFormTypeOption('data', new \DateTime())                     
                ->setColumns(2)
                ->onlyOnIndex(),

            TextField::new('titulo', 'Título')
                ->setColumns(4),

            DateField::new('fecha_expira', 'Fecha de expiración')
                ->setFormat('dd/MM/yyyy')
                ->setFormTypeOption('data', new \DateTime())                     
                ->setColumns(2),

            AssociationField::new('usuario_cliente_asignado', 'Cliente asignado')
                ->setQueryBuilder(function (QueryBuilder $qb) {
                    $qb->andWhere('entity.roles LIKE :user_role')
                    ->andWhere('entity.roles NOT LIKE :admin_role')
                    ->setParameter('user_role', '%"ROLE_USER"%')
                    ->setParameter('admin_role', '%"ROLE_ADMIN"%');
                })
                ->setColumns(4)
                ->setRequired(false),

            AssociationField::new('categoria', 'Categoría')
                ->setFormTypeOption('choice_label', 'nombre')
                ->setRequired(false)
                ->renderAsNativeWidget()
                ->setColumns(2),
                     
            TextEditorField::new('descripcion', 'Descripción'),
           
            
            // Sección 4: Carga de archivo
            TextField::new('archivoFile', 'link de descarga')
                ->setFormType(VichFileType::class)
                ->setFormTypeOptions([
                    'allow_delete' => false,
                    'download_uri' => false,
                ])
                ->onlyOnForms(),

            TextField::new('archivo')
                ->onlyOnIndex(),

            // Sección 3: Observaciones (checkboxes)
            BooleanField::new('permitido_publicar', 'Permitir descarga pública')
                ->renderAsSwitch(false)
                ->onlyOnForms(),
            
            // BooleanField::new('notificar_cliente', 'Notificar al cliente')
            //     ->onlyOnForms()
            //     ->setFormTypeOption('mapped', false),
            AssociationField::new('usuario_alta','Subido por')
                ->onlyOnIndex()
                ->setDisabled()
        ];
    }
   
}
