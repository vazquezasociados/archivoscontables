<?php

namespace App\Form;

use App\Entity\Archivo;
use App\Entity\User;
use App\Entity\Categoria;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ArchivoFormType extends AbstractType
{
    private string $appUrl;

    public function __construct(string $appUrl)
    {
        $this->appUrl = rtrim($appUrl, '/');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titulo', TextType::class, [
                'label' => 'Título del archivo',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Ingrese el título del archivo'
                ]
            ])
            ->add('descripcion', TextareaType::class, [
                'label' => 'Descripción',
                'required' => false,
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Descripción del archivo (opcional)'
                ]
            ])
            ->add('archivoFile', VichFileType::class, [
                'required' => true,
                'allow_delete' => false,
                'download_uri' => false,
                'label' => 'Seleccionar archivo',
                'attr' => [
                    'accept' => '.pdf'
                ]
            ])
            ->add('categoria', EntityType::class, [
                'class' => Categoria::class,
                'choice_label' => 'nombre',
                'label' => 'Categoría',
                'required' => false,
                'placeholder' => 'Seleccione una categoría'
            ])
            ->add('usuario_cliente_asignado', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'nombre',
                'label' => 'Asignar a cliente',
                'required' => false, // AHORA ES REQUERIDO
                'placeholder' => 'Seleccione un cliente'
            ])
            ->add('expira', CheckboxType::class, [
                'label' => '¿El archivo expira?',
                'required' => false,
                'data' => false, // Valor por defecto
            ])
            ->add('fecha_expira', DateType::class, [
                'label' => 'Fecha de expiración',
                'required' => false,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('permitido_publicar', CheckboxType::class, [
                'label' => '¿Permitir publicar?',
                'required' => false,
                'data' => true, // Valor por defecto TRUE
            ])
            ->add('url_publica', TextareaType::class, [
                'label' => 'URL Pública',
                'required' => false,
                'mapped' => false, // No se mapea directamente a una propiedad de la entidad
                'disabled' => true,
                'attr' => [
                    'readonly' => true,
                    'rows' => 2,
                    'class' => 'form-control url-publica-field'
                ]
            ])
            ->add('notificar_cliente', CheckboxType::class, [
                'label' => '¿Notificar al cliente?',
                'required' => false,
                'data' => true, // Valor por defecto TRUE
            ]);

        // Event listener para construir la URL después de submit
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    public function onPostSubmit(FormEvent $event): void
    {
        $archivo = $event->getData();
        $form = $event->getForm();

        // Si el archivo existe y tiene ID, construir la URL
        if ($archivo instanceof Archivo && $archivo->getId() && $archivo->getNombreArchivo()) {
            $urlCompleta = $this->construirUrlCompleta($archivo);
            
            // Establecer la URL en el campo del formulario para mostrar
            $form->get('url_publica')->setData($urlCompleta);
            
            // También guardarla en la entidad si tienes el setter
            if (method_exists($archivo, 'setUrlCompleta')) {
                $archivo->setUrlCompleta($urlCompleta);
            }
        }
    }

    private function construirUrlCompleta(Archivo $archivo): string
    {
        return sprintf(
            '%s/archivo/publico/%d/%s',
            $this->appUrl,
            $archivo->getId(),
            urlencode($archivo->getNombreArchivo())
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Archivo::class,
        ]);
    }
}