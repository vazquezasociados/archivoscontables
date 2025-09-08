<?php

namespace App\Controller\Admin;

use App\Entity\Item;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions; 

class ItemCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Item::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(15);
    }

    // ¡Aquí es donde configuras los botones de acción!
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            // En la página de índice (listado):
          //  ->add(Crud::PAGE_INDEX, Action::DETAIL) 
           // ->add(Crud::PAGE_DETAIL, $$downloadPdf)
          //  ->remove(Crud::PAGE_INDEX, Action::EDIT)  
            // En la página de detalle (cuando ves un Memo individual):
           // ->remove(Crud::PAGE_DETAIL, Action::EDIT)

            // Configura las acciones en la página de creación (Crud::PAGE_NEW)
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
        });
           
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            // IdField::new('id'),
            TextField::new('descripcion','Descripción'),
        ];
    }

    
}
