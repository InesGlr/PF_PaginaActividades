<?php

namespace App\Form;

use App\Entity\Sesion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SesionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $sesion =  $options['data']; 
        $builder
           ->add('id', HiddenType::class, [
                'data' => $sesion->getId(), 
            ])

            ->add('entradas', IntegerType::class, [
                'label' => 'Entradas',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1,
                    'max' => $sesion-> getEntradas(),
                ],
                'data' => 1,
            ])
          ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sesion::class
        ]);
    }
}
