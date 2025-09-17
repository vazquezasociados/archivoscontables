<?php

namespace App\Controller\Admin;

use App\Entity\Archivo;
use App\Service\MailerService;
use Doctrine\ORM\QueryBuilder;
use App\Dto\ArchivoCollectionDto;
use App\Form\ArchivosMasivosType;
use App\Repository\UserRepository;
use App\Repository\ArchivoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Vich\UploaderBundle\Form\Type\VichFileType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use Symfony\Component\Validator\Constraints\File;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use Symfony\Component\HttpFoundation\RequestStack;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ArchivoCrudController extends AbstractCrudController
{
    public function __construct(
        private Security $security,
        private UserRepository $userRepository,
        private ArchivoRepository $archivoRepository,
        private RequestStack $requestStack,
        private string $appUrl,
        private MailerService $mailerService,
    ) {
        $this->appUrl = rtrim($appUrl, '/');
    }
    
    public static function getEntityFqcn(): string
    {
        return Archivo::class;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        // Asignar usuario automáticamente
        if ($entityInstance instanceof Archivo && !$entityInstance->getUsuarioAlta()) {
            $entityInstance->setUsuarioAlta($this->security->getUser());
             // No tocar permitido_publicar aquí, lo decido manualmente
        }
        
        parent::persistEntity($entityManager, $entityInstance);

    }

    public function configureCrud(Crud $crud): Crud
    {   
         
        $title = 'Listado de Archivos';
        $request = $this->requestStack->getCurrentRequest();

        // Priorizar el 'clienteId' si viene de la acción de usuario
        $clienteIdParam = $request->query->get('clienteId');
        $filteredUserId = null;

        if ($clienteIdParam) {
            $filteredUserId = $clienteIdParam;
        } elseif ($request && $request->query->has('filters')) {
            // Si no hay 'clienteId' directo, buscar en los filtros de EasyAdmin
            $filters = $request->query->all('filters');
            if (isset($filters['usuario_cliente_asignado']['value']) && !empty($filters['usuario_cliente_asignado']['value'])) {
                $filteredUserId = $filters['usuario_cliente_asignado']['value'];
            }
        }

        // Si se encontró un ID de usuario por cualquier método, personalizar el título
        if ($filteredUserId) {
            $user = $this->userRepository->find($filteredUserId);
            if ($user) {
                $title = sprintf('📁 Archivos de %s', $user->getNombre());
            }
        }
        
        
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX,  $title)
            ->setPageTitle(Crud::PAGE_NEW, 'Subir Nuevo Archivo')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['titulo'])
            ->setPaginatorPageSize(15);
    }
  
    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        \EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection $filters
    ): QueryBuilder {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $user = $this->getUser();
        $request = $this->getContext()?->getRequest();

        $clienteId = $request && $request->query->has('clienteId')
            ? (int) $request->query->get('clienteId')
            : null;

        $categoriaId = $request && $request->query->has('categoriaId')
        ? (int) $request->query->get('categoriaId')
        : null;    

        if (!in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            $qb
                ->andWhere('entity.usuario_cliente_asignado = :user')
                ->setParameter('user', $user)
                ->andWhere('entity.expira = false OR (entity.expira = true AND entity.fecha_expira >= :hoy)')
                ->setParameter('hoy', new \DateTimeImmutable('today'));
        } elseif ($clienteId) {
            $qb
                ->andWhere('entity.usuario_cliente_asignado = :clienteId')
                ->setParameter('clienteId', $clienteId);
        }
        
        if ($categoriaId) {
        $qb
            ->andWhere('entity.categoria = :categoriaId')
            ->setParameter('categoriaId', $categoriaId);
        }

        return $qb;
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);

        $uploadMasivo = Action::new('uploadMasivo', 'Crear')
            ->linkToCrudAction('uploadMasivo')
            ->addCssClass('btn btn-primary')
            ->createAsGlobalAction();
        return $actions
            ->add(Crud::PAGE_INDEX, $uploadMasivo)
            ->setPermission('uploadMasivo', 'ROLE_ADMIN')
            ->setPermission(Action::EDIT, 'ROLE_ADMIN')
            ->setPermission(Action::DELETE, 'ROLE_ADMIN')
            ->setPermission(Action::DETAIL, 'ROLE_ADMIN')
            ->disable(Action::NEW);
    }

    /**
     * Construye la URL completa del archivo
     */
    private function construirUrlCompleta(Archivo $archivo): string
    {
        // Ejemplo de construcción de URL - ajusta según tu lógica
        return sprintf(
            '%s/archivo/publico/%d/%s',
            $this->appUrl,
            $archivo->getId(),
            urlencode($archivo->getNombreArchivo())
        );
    }
    
    public function configureFields(string $pageName): iterable
    {
        // Si no es admin, mostrar solo campos básicos para usuarios
        if (!$this->isGranted('ROLE_ADMIN')) {
            return [
                DateField::new('createdAt', 'Añadido el ')
                    ->setFormat('dd/MM/yyyy')
                    ->setFormTypeOption('data', new \DateTime())                     
                    ->setColumns(2)
                    ->onlyOnIndex(),
                    
                TextField::new('dummy', 'Tipo')
                ->onlyOnIndex()
                ->setValue('📄 PDF') // Valor estático
                ->setSortable(false), 

                TextField::new('titulo', 'Título')
                    ->setColumns(4)
                    ->formatValue(function ($value, $entity) {
                        if (!$entity instanceof \App\Entity\Archivo || !$entity->getNombreArchivo()) {
                            return $value;
                        }

                        // Ruta pública donde se guardan los archivos (ajustá según tu config de VichUploader)
                        $ruta = '/uploads/archivos_pdf/' . $entity->getNombreArchivo();

                        return sprintf(
                            '<a href="%s" target="_blank" style="text-decoration: underline; color: #007bff;">%s</a>',
                            htmlspecialchars($ruta), 
                            htmlspecialchars($value));
                    })
                    ->renderAsHtml(),

                IntegerField::new('tamaño', 'Tamaño (KB)')
                    ->onlyOnIndex()
                    ->formatValue(function ($value) {
                        return $value ? round($value / 1024, 2) : 0;
                    })
                    ->setCustomOption(IntegerField::OPTION_NUMBER_FORMAT, '%.2f KB')
                    ->setCustomOption(IntegerField::OPTION_THOUSANDS_SEPARATOR, ',')
                    ->setColumns(2),

                TextField::new('estadoExpira', 'Estado')
                    ->onlyOnIndex()
                    ->formatValue(function ($value, $entity) {
                        if ($entity->isExpira()) {
                            // si expira, muestro la fecha también
                            $fecha = $entity->getFechaExpira()?->format('d/m/Y');
                            return sprintf('❌ Expira (%s)', $fecha ?? 'sin fecha');
                        }
                        return '✅ Nunca expira';
                    }),


            ];

        }

        // Para administradores, mantener tu configuración completa original
        return [
            // IdField::new('id'),
            DateField::new('createdAt', 'Añadido el ')
                ->setFormat('dd/MM/yyyy')
                ->setFormTypeOption('data', new \DateTime())                     
                ->setColumns(2)
                ->onlyOnIndex(),

            TextField::new('dummy', 'Tipo')
                ->onlyOnIndex()
                ->setValue('📄 PDF') // Valor estático
                ->setSortable(false),

            TextField::new('titulo', 'titulo')
                ->setColumns(4)
                ->formatValue(function ($value, $entity) {
                    if (!$entity instanceof \App\Entity\Archivo || !$entity->getNombreArchivo()) {
                        return $value;
                    }

                    // Ruta pública donde se guardan los archivos (ajustá según tu config de VichUploader)
                    $ruta = '/uploads/archivos_pdf/' . $entity->getNombreArchivo();

                
                    return sprintf(
                        '<a href="%s" target="_blank" style="text-decoration: underline; color: #007bff;">%s</a>',
                        htmlspecialchars($ruta),
                        htmlspecialchars($value)
                    );
                })
                ->renderAsHtml(),

            IntegerField::new('tamaño', 'Tamaño (KB)')
                ->onlyOnIndex()
                ->formatValue(function ($value) {
                    return $value ? round($value / 1024, 2) : 0;
                })
                ->setCustomOption(IntegerField::OPTION_NUMBER_FORMAT, '%.2f KB')
                ->setCustomOption(IntegerField::OPTION_THOUSANDS_SEPARATOR, ',')
                ->setColumns(2),
                        
            // Mostrar solo el nombre del usuario en el index
            TextField::new('usuario_alta.nombre', 'Subido por')
                ->onlyOnIndex(),

            TextField::new('asignadoTexto', 'Asignado')
                ->onlyOnIndex()
                ->renderAsHtml(),

            // Campo para mostrar el en la vista modificar
            AssociationField::new('usuario_cliente_asignado', 'Cliente asignado')
                ->setQueryBuilder(function (QueryBuilder $qb) {
                    $qb->andWhere('entity.roles LIKE :user_role')
                    ->andWhere('entity.roles NOT LIKE :admin_role')
                    ->setParameter('user_role', '%"ROLE_USER"%')
                    ->setParameter('admin_role', '%"ROLE_ADMIN"%');
                })
                ->setColumns(4)
                ->setRequired(false)
                ->onlyOnForms(),

            AssociationField::new('categoria', 'Categoría')
                ->setFormTypeOption('choice_label', 'Nombre')
                ->setRequired(false)
                ->renderAsNativeWidget()
                ->setColumns(4)
                ->onlyOnForms(),

            TextField::new('estadoExpira', 'Estado')
                    ->onlyOnIndex()
                    ->formatValue(function ($value, $entity) {
                        if ($entity->isExpira()) {
                            // si expira, muestro la fecha también
                            $fecha = $entity->getFechaExpira()?->format('d/m/Y');
                            return sprintf('❌ Expira (%s)', $fecha ?? 'sin fecha');
                        }
                        return '✅ Nunca expira';
                    }),

            BooleanField::new('expira', '¿Tiene fecha de expiración?')
                ->onlyOnForms()
                ->setColumns(2)
                ->setFormTypeOption('attr', ['class' => 'js-expira-toggle']), 

            DateField::new('fecha_expira', 'Fecha de expiración')
                ->setFormat('dd/MM/yyyy')
                ->onlyOnForms()
                ->setFormTypeOption('required', false)
                ->setColumns(6)
                ->setFormTypeOption('attr', ['class' => 'js-expira-field']),

            TextEditorField::new('descripcion', 'Descripción')
                ->onlyOnForms(),
        
            // Sección 4: Carga de archivo
            Field::new('archivoFile', 'Subir Archivo PDF')
                ->setFormType(VichFileType::class)
                ->setFormTypeOptions([
                    'allow_delete' => false,
                    'download_uri' => true,
                    'constraints' => [
                        new File([
                            'maxSize' => '10M',
                            'mimeTypes' => [
                                'application/pdf',
                            ],
                            'mimeTypesMessage' => 'Solo se permiten archivos PDF',
                        ])
            ],
                ])
                ->onlyOnForms(),

            BooleanField::new('permitido_publicar', 'Permitir descarga pública')
                ->onlyOnForms()
                ->setFormTypeOption('attr', [
                    'data-permitido-publicar' => 'true'
                ]),

            TextareaField::new('getUrlCompleta', 'URL publica')
                ->formatValue(function ($value, $entity) {
                    return sprintf('<a href="%s" target="_blank">%s</a>', $value, $entity->getNombreArchivo());
                })
                ->onlyOnForms()
                ->setColumns(4)
                ->setDisabled(true)
                ->renderAsHtml(),
        
            BooleanField::new('notificar_cliente', 'Notificar al cliente')
                ->onlyOnForms()
                ->setFormTypeOption('data', true)
                ,
        ];
    }



    public function uploadMasivo(AdminContext $context, Request $request, EntityManagerInterface $entityManager, AdminUrlGenerator $adminUrlGenerator): Response
    {
        $archivoCollectionDto = new ArchivoCollectionDto();
        
        $form = $this->createForm(ArchivosMasivosType::class, $archivoCollectionDto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $archivosGuardados = 0;
            $errores = [];
            $archivosParaNotificar = [];

            foreach ($archivoCollectionDto->getArchivos() as $archivo) {
                try {
                    // Solo procesar archivos que tengan un archivo subido
                    if ($archivo->getArchivoFile() === null) {
                        continue;
                    }
                    
                    // VALIDAR TÍTULO REQUERIDO
                    if (empty($archivo->getTitulo())) {
                        $errores[] = "Se requiere título para todos los archivos";
                        continue;
                    }

                    // Asignar valores por defecto para campos requeridos
                    if ($archivo->isPermitidoPublicar() === null) {
                        $archivo->setPermitidoPublicar(true); // Por defecto TRUE
                    }
                    
                    if ($archivo->isExpira() === null) {
                        $archivo->setExpira(false);
                    }
                    
                    if ($archivo->isNotificarCliente() === null) {
                        $archivo->setNotificarCliente(true); // Por defecto TRUE
                    }

                    // Asignar el usuario que subió el archivo
                    $archivo->setUsuarioAlta($this->getUser());
                    
                    // Persistir primero para obtener el ID
                    $entityManager->persist($archivo);
                    $entityManager->flush(); // Flush individual para obtener el ID
                    
                    // Ahora construir y guardar la URL completa
                    $urlCompleta = $this->construirUrlCompleta($archivo);
                    $archivo->setUrlPublica($urlCompleta); // Asume que tienes este setter
                    
                    $archivosGuardados++;
                    
                    if ($archivo->isNotificarCliente()) {
                    $archivosParaNotificar[] = $archivo;
                    }
                } catch (\Exception $e) {
                    $errores[] = "Error al procesar archivo: " . $e->getMessage();
                }
            }

            if ($archivosGuardados > 0) {
                $entityManager->flush(); // Flush final para guardar las URLs
                // $this->addFlash('success', "¡{$archivosGuardados} archivo(s) subido(s) exitosamente!");
                // ← ENVIAR NOTIFICACIONES AGRUPADAS
            if (!empty($archivosParaNotificar)) {
                try {
                    $this->mailerService->sendArchivoNotification($archivosParaNotificar);
                    $this->addFlash('success', "¡{$archivosGuardados} archivo(s) subido(s) exitosamente y notificaciones enviadas!");
                } catch (\Exception $e) {
                    $this->addFlash('warning', "¡{$archivosGuardados} archivo(s) subido(s) exitosamente, pero error enviando notificaciones: " . $e->getMessage());
                }
            } else {
                $this->addFlash('success', "¡{$archivosGuardados} archivo(s) subido(s) exitosamente!");
            }
            }

            if (!empty($errores)) {
                foreach ($errores as $error) {
                    $this->addFlash('error', $error);
                }
            }

            if ($archivosGuardados === 0) {
                $this->addFlash('warning', 'No se subieron archivos. Asegúrate de seleccionar archivos, completar título y asignar cliente.');
            }

            return $this->redirect($adminUrlGenerator->setController(self::class)->setAction(Action::INDEX)->generateUrl());
        }

        return $this->render('admin/archivo/upload_masivo.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}