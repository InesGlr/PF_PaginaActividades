<?php

namespace App\Form;

use App\Entity\Usuario;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class LogUpType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', TextType::class,[
                'label' => 'Nombre',
                'attr' => [
                    'placeholder' => 'nombre',
                    'autocomplete' => 'off',
                    'class' => 'form-control',
                    'required' => true
                ]
            ])
            ->add('correo', EmailType::class,[
                'label' => 'correo',
                'attr' => [
                    'placeholder' => 'correo',
                    'autocomplete' => 'off',
                    'class' => 'form-control',
                    'required' => true
                ]
            ])
           
           
            ->add('clave', PasswordType::class, [
                'label' => 'clave',
                'attr' => [
                    'placeholder' => 'clave',
                    'autocomplete' => 'off',
                    'class' => 'form-control',
                    'required' => true
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Por favor introduzca la contraseña',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Su contraseña debe tener al menos {{ limit }} caracteres',
                        // Longitud máxima permitida por Symfony por razones de seguridad
                        'max' => 20,
                    ]),
                ],
            ])
            
            
            ->add('submit', SubmitType::class,[
                'label' => 'Crear cuenta',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
           /*  'data_class' => Usuario::class, */
        ]);
    }
}
