<?php

namespace App\Form;

use App\Entity\Categoria;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoriaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
     
        $builder
        ->add('nombre', TextType::class, [
            'label' => 'Nombre',
            'required' => true,
        ])
        ->add('descripcion', TextareaType::class, [
            'label' => 'Descripción',
                'required' => true, 
                'attr' => [
                    'rows' => 5,
                    'cols' => 50,
                ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Categoria::class
        ]);
    }
}
