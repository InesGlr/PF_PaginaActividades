<?php

namespace App\Form;

use App\Entity\Usuario;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PerfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $usuario = $options['data']; 

        $builder
        ->add('nombre', TextType::class, [
        'label' => false, 
        'attr' => [
            'placeholder' => $usuario->getNombre() 
        ]
    ])
            ->add('cambiar', SubmitType::class, [
        'label_html' => true, 
          'label' => '<i class="bi bi-pencil"></i>' 
      ])
           
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Usuario::class,
        ]);
    }
}
