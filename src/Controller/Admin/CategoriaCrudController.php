<?php

namespace App\Controller\Admin;

use App\Entity\Categoria;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;

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
            ->setPaginatorPageSize(10)
           ;
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
