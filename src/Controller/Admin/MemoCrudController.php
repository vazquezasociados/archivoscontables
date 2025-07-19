<?php

namespace App\Controller\Admin;

use App\Entity\Memo;
use App\Form\MemoLineaItemType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions; 
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;

class MemoCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Memo::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Listado de Memos')
            ->setPaginatorPageSize(10);
           
    }
        // ¡Aquí es donde configuras los botones de acción!
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            // En la página de índice (listado):
            ->add(Crud::PAGE_INDEX, Action::DETAIL) // Agrega el botón "Ver" (Detail)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)  // Quita el botón "Editar" de la lista
            // En la página de detalle (cuando ves un Memo individual):
            ->remove(Crud::PAGE_DETAIL, Action::EDIT) // Quita el botón "Editar" de la vista de detalle
           
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
        DateField::new('createdAt', 'Fecha de emisión')
            ->setFormat('dd/MM/yyyy')
            ->setFormTypeOption('data', new \DateTime()) // 👈 Valor por defecto
            // ->setDisabled(true)                     
            ->setColumns(2),

            TextField::new('estado')
                ->setColumns(2)
                ->setDisabled(true), // ✅ Esto evita que lo editen

            AssociationField::new('usuario', 'Usuario')
                ->setFormTypeOption('choice_label', 'nombre')
                ->setColumns(4),
            
            CollectionField::new('lineItems', 'Ítems del Memo')
                ->setEntryType(MemoLineaItemType::class)
                ->allowAdd()
                ->allowDelete()
                ->setFormTypeOptions([
                    'by_reference' => false,
                ])
                ->setColumns(6)
                ->hideOnIndex(),
                // ->setTemplatePath('admin/memo_line_items.html.twig'), 
        ];
    }
  
}
