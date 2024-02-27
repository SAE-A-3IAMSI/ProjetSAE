<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Regex;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('login', TextType::class)
            ->add('plainPassword', PasswordType::class, [
                "mapped" => false,
                "attr" => [
                    "min" => 8,
                    "max" => 30,
                ],
                "constraints" => [
                    new NotBlank(),
                    new NotNull(),
                    new Length( min: 8,max: 30, minMessage: "Le mot de passe doit contenir au moins 8 caractères!", maxMessage:"Le mot de passe ne peut contenir que 30 caractères"),
                    new Regex(pattern: "#^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,30}$#", message: "Le mot de passe doit contenir : au moins une minuscule, au moins une majuscule et au moins un chiffre")
                ]
            ])
            ->add('registration', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
