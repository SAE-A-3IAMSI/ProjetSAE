<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('price', IntegerType::class,
                [
                    'attr' => ['min' => 0]
                ]
            )
            ->add('ref', TextType::class)
            ->add('imageProduct', FileType::class, ["mapped" => false])
            ->add('type', ChoiceType::class,
                [
                    'choices'  => [
                        'Blocs' => 'Blocs',
                        'Equipements' => 'Equipements',
                        'Ressources' => 'Ressources',
                    ],
                ]
            )
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
