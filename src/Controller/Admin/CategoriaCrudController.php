<?php

namespace App\Controller\Admin;

use App\Entity\Categoria;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions; 

class CategoriaCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Categoria::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Listado de Categorías')
            // ->setPageTitle(Crud::PAGE_NEW, 'Subir nuevo archivo')
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
            // IdField::new('id')->hideOnIndex(),
            TextField::new('nombre', 'Nombre')
                ->setColumns(4),
             // Campo 'Principal' (la relación recursiva al padre)
            AssociationField::new('padre', 'Principal')
                ->setColumns(4)
                ->setFormTypeOption('choice_label', 'Nombre') // Muestra el 'nombre' de la categoría padre en el desplegable
                ->setRequired(false) // Permite que sea "Ninguna" (nulo)
                ->setHelp('Selecciona la categoría principal a la que pertenece esta.'),

            TextEditorField::new('descripcion','Descripción'),

        ];
    }
   
}
