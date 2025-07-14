<?php

namespace App\Controller\Admin;

use App\Entity\Memo;
use App\Form\MemoLineaItemType;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class MemoCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Memo::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('estado'),
            DateField::new('createdAt', 'Fecha Alta')
                ->setFormat('dd/MM/yyyy')
                ->hideOnForm(),
            AssociationField::new('usuario', 'Usuario')
                ->setFormTypeOption('choice_label', 'nombre'),
            
            CollectionField::new('lineItems', 'Ítems del Memo')
                ->setEntryType(MemoLineaItemType::class)
                ->allowAdd()
                ->allowDelete()
                ->setFormTypeOptions([
                    'by_reference' => false,
                ]),
        ];
    }
  
}
