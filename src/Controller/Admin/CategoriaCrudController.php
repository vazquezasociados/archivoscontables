<?php

namespace App\Controller\Admin;

use App\Entity\Categoria;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions; 
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\RedirectResponse;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;


class CategoriaCrudController extends AbstractCrudController
{
    public function __construct(
        private AdminUrlGenerator $adminUrlGenerator
    ) {}

    public static function getEntityFqcn(): string
    {
        return Categoria::class;
    }

    public function configureCrud(Crud $crud): Crud
    {   
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Listado de Categorías')
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(15);
    }
    
    
    public function configureActions(Actions $actions): Actions
    {

        return $actions
            // 👉 Aseguramos que en la página NEW estén bien los botones
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setLabel('Crear');
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $action) {
                return $action->setLabel('Crear y añadir otro');
            })
            ->add(Crud::PAGE_NEW, Action::INDEX)
            ->update(Crud::PAGE_NEW, Action::INDEX, function (Action $action) {
                return $action
                    ->setLabel('Cancelar')
                    ->setCssClass('btn-custom-cancel');
            });
    }


    public function verArchivos(AdminContext $context): RedirectResponse
    {
        /** @var Categoria $categoria */
        $categoria = $context->getEntity()->getInstance();

        $url = $this->adminUrlGenerator
            ->setController(ArchivoCrudController::class)
            ->setAction(Crud::PAGE_INDEX)
            ->set('categoriaId', $categoria->getId()) // pasamos la categoría
            ->set('query', null)
            ->set('page', 1) 
            ->unset('sort')
            ->generateUrl();

        return $this->redirect($url);
    }
    
    public function configureFields(string $pageName): iterable
    {
        // Inicializa un array para almacenar todos los campos
        $fields = [];

        // Agrega los campos que siempre se muestran
        $fields[] = TextField::new('nombre', 'Nombre')
            ->setColumns(4);

        // Campo 'Principal' (la relación recursiva al padre)
        $fields[] = AssociationField::new('padre', 'Principal')
            ->setColumns(4)
            ->setFormTypeOption('choice_label', 'Nombre') // Muestra el 'nombre' de la categoría padre en el desplegable
            ->setRequired(false) // Permite que sea "Ninguna" (nulo)
            ->setHelp('Selecciona la categoría principal a la que pertenece esta.');

        $fields[] = TextEditorField::new('descripcion', 'Descripción');
        
        // Solo agregar el campo 'Total Archivos' si estamos en la página de índice (listado)
        if ($pageName === Crud::PAGE_INDEX) {
            // Asegúrate de que la entidad Categoria tenga el método getTotalArchivos()
            $fields[] = IntegerField::new('totalArchivos', ' Total archivos') // Usa el método getTotalArchivos() de la entidad
                ->setSortable(true)
                ->setCssClass('text-center');
            
            // Botón "Ver Archivos" - Usando campo 'nombre' como base
            $adminUrlGenerator = $this->adminUrlGenerator;
            $fields[] = TextField::new('acciones', 'Acciones')
                ->onlyOnIndex()
                ->setLabel('Acciones') // Cambiar la etiqueta
                ->formatValue(function ($value, $categoria) use ($adminUrlGenerator) {
                    try {
                        if (!$categoria instanceof \App\Entity\Categoria) {
                            return 'ERROR: No es Categoria';
                        }

                        if (!$adminUrlGenerator) {
                            return 'ERROR: AdminUrlGenerator null';
                        }

                        $categoriaId = $categoria->getId();
                        if (!$categoriaId) {
                            return 'ERROR: ID null';
                        }

                        $url = $adminUrlGenerator
                            ->setController(ArchivoCrudController::class)
                            ->setAction(Crud::PAGE_INDEX)
                            ->set('categoriaId', $categoriaId)
                            ->set('query', null)
                            ->set('page', 1) 
                            ->unset('sort')
                            ->generateUrl();

                        return sprintf(
                            '<a href="%s" class="btn btn-sm btn-primary">
                                <i class="fas fa-folder-open"></i> Ver
                            </a>', 
                            htmlspecialchars($url)
                        );

                    } catch (\Exception $e) {
                        return 'ERROR: ' . $e->getMessage();
                    }
                })
                ->renderAsHtml();      
                
        }

        // Devuelve el array completo de campos
        return $fields;
    }
    
}
