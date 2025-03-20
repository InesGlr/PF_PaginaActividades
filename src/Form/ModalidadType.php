<?php

// src/Form/ModalidadType.php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Repository\ActividadRepository;
use App\Repository\CategoriaRepository;

class ModalidadType extends AbstractType
{
    private $actividadRepository;
    private $categoriaRepository;

    // Inyectar los repositorios de actividad y categoría
    public function __construct(ActividadRepository $actividadRepository, CategoriaRepository $categoriaRepository)
    {
        $this->actividadRepository = $actividadRepository;
        $this->categoriaRepository = $categoriaRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Obtener los valores únicos de 'tipo' desde la base de datos
        $tipos = $this->actividadRepository->listaTipos();
        $choicesTipo = array_combine($tipos, $tipos);

        // Obtener las categorías desde la base de datos
        $categorias = $this->categoriaRepository->findAll();
        $choicesCategoria = [];
        foreach ($categorias as $categoria) {
            $choicesCategoria[$categoria->getNombre()] = $categoria->getId();  
        }


        $builder
            ->add('tipo', ChoiceType::class, [
                'label' => 'Tipo:',
                'choices' => $choicesTipo,
                'placeholder' => false,
                'expanded' => true,
                'multiple' => true,
                'required' => false,
            ])
            ->add('categoria', ChoiceType::class, [
                'label' => 'Categoria:',
                'choices' => $choicesCategoria,
                'placeholder' => false,
                'expanded' => true, 
                'multiple' => true, 
                'required' => false,
            ])
          ;
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
           
        ]);
    }
}
?>