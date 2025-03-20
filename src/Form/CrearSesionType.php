<?php

namespace App\Form;

use App\Entity\Sesion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CrearSesionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
     
        $builder
         ->add('fecha', DateType::class, [
            'widget' => 'single_text',
            'label' => 'Fecha',
            'required' => true,
            'attr' => ['class' => 'form-control'],
        ])
     
    
            ->add('hora', TimeType::class, [
            'widget' => 'single_text',
            'label' => 'Hora',
            'required' => true,
            'input' => 'datetime',
            'widget' => 'choice',
            'hours' => range(0, 23),
            'minutes' => range(0, 59),
            'seconds' => [0], // Fijamos los segundos a 0
            'attr' => ['class' => 'form-control'],
        ])   
        ->add('duracion', TimeType::class, [
            'widget' => 'single_text',
            'label' => 'Duración',
            'required' => false,
            'input' => 'datetime',
            'widget' => 'choice',
            'hours' => range(0, 23),
            'minutes' => range(0, 59),
            'seconds' => [0], // Fijamos los segundos a 0
            'attr' => ['class' => 'form-control'],
        ])
        ->add('entradas', IntegerType::class, [
            'label' => 'Entradas',
            'required' => true,
            'attr' => ['class' => 'form-control', 'min' => 1],
        ])
        ->add('submit', SubmitType::class, [
            'label' => 'Guardar',
            'attr' => ['class' => 'btn btn-primary']
        ]);;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sesion::class
        ]);
    }
}
