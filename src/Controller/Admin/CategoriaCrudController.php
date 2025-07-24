<?php

namespace App\Controller\Admin;

use App\Entity\Categoria;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class CategoriaCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Categoria::class;
    }

   
    public function configureFields(string $pageName): iterable
    {
        return [
            // IdField::new('id')->hideOnIndex(),
            TextField::new('nombre'),
             // Campo 'Principal' (la relación recursiva al padre)
            AssociationField::new('padre', 'Principal')
                ->setFormTypeOption('choice_label', 'nombre') // Muestra el 'nombre' de la categoría padre en el desplegable
                ->setRequired(false) // Permite que sea "Ninguna" (nulo)
                ->setHelp('Selecciona la categoría principal a la que pertenece esta.'),

            TextareaField::new('descripcion'),

        ];
    }
   
}
