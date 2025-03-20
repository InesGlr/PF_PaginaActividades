<?php 
// src/Form/DireccionType.php
namespace App\Form;

use App\Entity\Direccion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class CrearDireccionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
       
        $builder
             ->add('sin_direccion', CheckboxType::class, [
                'label' => 'Sin Dirección',
                'required' => false,
                'mapped' => false,
                'attr' => ['class' => 'form-check-input'],
            ]) 
        
            ->add('pais', TextType::class, [
                'label' => 'País',
                'attr' => [
                    'class' => 'form-control', 
                    'placeholder' => 'Ingresa el país',
                    'readonly' => true 
                ],
                'data' => 'España',
                'required' => false,
            ])
            
            ->add('provincia', ChoiceType::class, [
                'label' => 'Provincia',
                'placeholder' => 'Selecciona la provincia',
                'choices' => [
                    'Álava' => 'Álava',
                    'Albacete' => 'Albacete',
                    'Alicante' => 'Alicante',
                    'Almería' => 'Almería',
                    'Asturias' => 'Asturias',
                    'Ávila' => 'Ávila',
                    'Badajoz' => 'Badajoz',
                    'Barcelona' => 'Barcelona',
                    'Burgos' => 'Burgos',
                    'Cáceres' => 'Cáceres',
                    'Cádiz' => 'Cádiz',
                    'Cantabria' => 'Cantabria',
                    'Castellón' => 'Castellón',
                    'Ciudad Real' => 'Ciudad Real',
                    'Córdoba' => 'Córdoba',
                    'Cuenca' => 'Cuenca',
                    'Girona' => 'Girona',
                    'Granada' => 'Granada',
                    'Guadalajara' => 'Guadalajara',
                    'Guipúzcoa' => 'Guipúzcoa',
                    'Huelva' => 'Huelva',
                    'Huesca' => 'Huesca',
                    'Islas Baleares' => 'Islas Baleares',
                    'Jaén' => 'Jaén',
                    'La Coruña' => 'La Coruña',
                    'La Rioja' => 'La Rioja',
                    'Las Palmas' => 'Las Palmas',
                    'León' => 'León',
                    'Lérida' => 'Lérida',
                    'Lugo' => 'Lugo',
                    'Madrid' => 'Madrid',
                    'Málaga' => 'Málaga',
                    'Murcia' => 'Murcia',
                    'Navarra' => 'Navarra',
                    'Ourense' => 'Ourense',
                    'Palencia' => 'Palencia',
                    'Pontevedra' => 'Pontevedra',
                    'Salamanca' => 'Salamanca',
                    'Santa Cruz de Tenerife' => 'Santa Cruz de Tenerife',
                    'Segovia' => 'Segovia',
                    'Sevilla' => 'Sevilla',
                    'Soria' => 'Soria',
                    'Tarragona' => 'Tarragona',
                    'Teruel' => 'Teruel',
                    'Toledo' => 'Toledo',
                    'Valencia' => 'Valencia',
                    'Valladolid' => 'Valladolid',
                    'Vizcaya' => 'Vizcaya',
                    'Zamora' => 'Zamora',
                    'Zaragoza' => 'Zaragoza',
                ],
                'attr' => ['class' => 'form-control'],
                'required' => false,
            ])
            ->add('ciudad', TextType::class, [
                'label' => 'Ciudad',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ingresa la ciudad'],
                'required' => false,
            ])
            ->add('codpostal', IntegerType::class, [
                'label' => 'Código Postal',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ingresa el código postal'],
                'required' => false,
            ])
            ->add('calle', TextType::class, [
                'label' => 'Calle',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ingresa la calle'],
                'required' => false,
            ])
            ->add('numero', IntegerType::class, [
                'label' => 'Número',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ingresa el número'],
                'required' => false,
            ])
            ->add('piso', TextType::class, [
                'label' => 'Piso (opcional)',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ingresa el piso, si aplica'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Guardar Dirección',
                'attr' => ['class' => 'btn btn-primary']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Direccion::class,
        ]);
    }
}
