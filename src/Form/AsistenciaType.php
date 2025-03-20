<?php

namespace App\Form;

use App\Entity\Usuario;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use App\Repository\UsuarioRepository;

class AsistenciaType extends AbstractType
{

    private $usuarioRepository;

    public function __construct(UsuarioRepository $usuarioRepository)
    {
        $this->usuarioRepository = $usuarioRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $administradores = $this->usuarioRepository->listaAdministradores();
        
        $choicesAdmins = [];
        foreach ($administradores as $admin) {
            $choicesAdmins[$admin->getNombre()] = $admin->getCorreo();
        }

        $builder

            ->add('receptor', ChoiceType::class, [
                'label' => 'Administrador',
                'placeholder' => 'Selecciona un administrador',
                'choices' => $choicesAdmins,
                'attr' => ['class' => 'form-control'],
                'required' => true,
                'mapped' => false, 'mapped' => false,
            ])
            ->add('asunto', TextType::class, [
                'label' => 'Asunto',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Motivo a consultar'],
                'required' => true,
                'mapped' => false,
            ])
            ->add('mensaje', TextareaType::class, [
                'label' => 'Mensaje',
                'attr' => [
                    'class' => 'form-control', 
                    'placeholder' => 'Descripción de la cuestión a resolver',
                    'rows' => 8 
                ],
                'mapped' => false,
            ])
            
            
            ->add('submit', SubmitType::class,[
                'label' => 'Enviar',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
        ]);
    }
}
