<?php

// src/Form/ActividadType.php
namespace App\Form;

use App\Entity\Actividad;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Categoria;
use FOS\CKEditorBundle\Form\Type\CKEditorType;


class ActividadType extends AbstractType
{

  

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
    $builder
        ->add('nombre', TextType::class, [
            'label' => 'Nombre',
            'attr' => ['class' => 'form-control', 'placeholder' => 'Nombre de la actividad']
        ])
        ->add('descripcion', CKEditorType::class, [
            'config_name' => 'main_config'
        ])
               
      
        ->add('categoria', EntityType::class, [
            'class' => Categoria::class,
            'choice_label' => 'nombre',
            'placeholder' => 'Selecciona una categoría',
            'required' => true,
            'attr' => ['class' => 'form-control']
        ])
        ->add('submit', SubmitType::class, [
            'label' => 'Guardar Actividad',
            'attr' => ['class' => 'btn btn-primary']
        ]);
}

public function configureOptions(OptionsResolver $resolver): void
{
    $resolver->setDefaults([
        'data_class' => Actividad::class,
    ]);
}

}
