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

class MemoLineaItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('descripcionAdicional')
              ->add('periodo', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM', // HTML5 month picker
                'html5' => false, // si querés usar un picker JS más adelante
                'label' => 'Periodo (Mes y Año)',
                'attr' => [
                    'placeholder' => 'YYYY-MM',
                    'class' => 'month-picker' // útil si querés usar un JS custom
                ],
            ])
            ->add('item', EntityType::class, [
                'class' => Item::class,
                'choice_label' => 'descripcion', // usa __toString(), pero es más explícito
                'placeholder' => 'Seleccione un ítem',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MemoLineItem::class,
        ]);
    }
}
