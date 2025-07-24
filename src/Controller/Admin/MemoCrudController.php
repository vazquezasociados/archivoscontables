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
            ->setPaginatorPageSize(10)
            ->overrideTemplate('crud/detail', 'admin/memo/detail.html.twig');
    }
        // ¡Aquí es donde configuras los botones de acción!
    public function configureActions(Actions $actions): Actions
    {
        
                // Acción para descargar PDF
        $downloadPdf = Action::new('downloadPdf', 'Imprimir', 'fa fa-file-pdf')
            ->linkToRoute('admin_memo_pdf', function (Memo $memo) {
                return ['id' => $memo->getId()];
            })
            ->setHtmlAttributes(['target' => '_blank'])
            ->setCssClass('btn btn-sm btn-primary');

        return $actions
            // En la página de índice (listado):
            ->add(Crud::PAGE_INDEX, Action::DETAIL) 
            ->add(Crud::PAGE_DETAIL, $$downloadPdf)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)  
            // En la página de detalle (cuando ves un Memo individual):
            ->remove(Crud::PAGE_DETAIL, Action::EDIT) 
           
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
                        
            TextField::new('pdfDownloadLink', 'Descarga')
                ->setTemplatePath('admin/fields/pdf_column.html.twig')
                ->onlyOnIndex()
                ->addCssClass('pdf-header-white')
                ->setColumns(1),
                

            CollectionField::new('lineItems', 'Ítems del Memo')
                ->setEntryType(MemoLineaItemType::class)
                ->allowAdd()
                ->allowDelete()
                ->setFormTypeOptions([
                    'by_reference' => false,
                ])
                ->setColumns(6)
                ->hideOnIndex(),
                
        ];
    }
  
}
