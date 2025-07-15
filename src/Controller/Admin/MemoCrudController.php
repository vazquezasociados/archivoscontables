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
