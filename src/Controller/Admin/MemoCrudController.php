<?php

namespace App\Controller\Admin;

use App\Entity\Memo;
use App\Form\MemoLineaItemType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions; 
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

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
            ->setSearchFields(['id', 'usuario.nombre', 'estado'])
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
        IdField::new('id', 'Nro. de memo')
            ->onlyOnIndex(),

        // Campo para mostrar en el índice (solo lectura)
        TextField::new('usuario.nombre', 'Cliente')
            ->onlyOnIndex(),

        AssociationField::new('usuario', 'Clientes')
            ->setFormTypeOption('placeholder', 'Seleccionar')
            ->setFormTypeOption('choice_label', 'nombre')
            ->setColumns(4)
            ->onlyOnForms(),

        DateField::new('createdAt', 'Fecha de emisión')
            ->setFormat('dd/MM/yyyy')
            ->setFormTypeOption('data', new \DateTime()) // 👈 Valor por defecto
            // ->setDisabled(true)                     
            ->setColumns(2),

            ChoiceField::new('estado')
                ->setLabel('Estado')
                ->setChoices([
                    'Retira cliente' => 'retira_cliente',
                    'Entrega al estudio' => 'entrega_estudio',
                ])
                ->setFormTypeOption('placeholder', 'Seleccionar')
                ->setColumns(2), 
                        
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
