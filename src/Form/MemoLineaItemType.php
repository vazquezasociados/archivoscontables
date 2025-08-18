<?php

namespace App\Form;

use App\Entity\Item;
use App\Entity\Memo;
use App\Entity\MemoLineItem;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\Date;


class MemoLineaItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('item', EntityType::class, [
                'class' => Item::class,
                'label' => 'Ítem *',
                'choice_label' => 'descripcion', 
                'placeholder' => 'Seleccione un ítem',
            ])
            ->add('descripcionAdicional', TextareaType::class, [
                'label' => 'Observaciones *',
                'required' => false,
                'attr' => [
                    'rows' => 3,
                    'class' => 'form-control',
                    'placeholder' => 'Ingrese detalles adicionales'
                ]
            ])
            ->add('periodo', DateType::class, [
                'widget' => 'single_text',
                'format' => 'MM/yyyy',
                'html5' => false,
                'label' => 'Periodo *',
                'help' => 'Ingrese el mes y año en formato MM/AAAA (ej: 02/2025)',
                'help_attr' => [
                    'class' => 'text-muted small' // Clases CSS opcionales
                ],
                'attr' => [
                    'placeholder' => 'MM/AAAA',
                    'class' => 'form-control date-picker' 
                ],
                'required' => true, // <--- Esto es clave
                'model_timezone' => 'America/Argentina/Cordoba', // O la zona horaria de tu aplicación
                'view_timezone' => 'America/Argentina/Cordoba',  // O la zona horaria de tu aplicación
                'data' => new \DateTime('now', new \DateTimeZone('America/Argentina/Cordoba')),
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MemoLineItem::class,
            
        ]);
    }
}
