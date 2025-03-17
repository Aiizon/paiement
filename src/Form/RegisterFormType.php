<?php

// src/Form/RegisterFormType.php
namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\PasswordStrength;

class RegisterFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Administrateur' => 'ROLE_ADMIN',
                    'Utilisateur' => 'ROLE_USER',
                    // Ajoutez d'autres rôles selon vos besoins
                ],
                'multiple' => true, // Permet la sélection multiple
                'expanded' => true,  // Affiche les options sous forme de cases à cocher
            ])
            ->add('password', PasswordType::class, [
                'mapped' => false,
                'required' => true,
                'help' => 'Votre mot de passe doit contenir au moins 8 caractères, une lettre majuscule, une lettre minuscule, un chiffre et un caractère spécial. Nous vous recommandeons d\'utiliser un gestionnaire de mots de passe.',
                'attr' => [
                    'placeholder' => 'Mot de passe',
                ],
                'constraints' => [
                    new Length(
                        [
                            'min' => 8,
                            'minMessage' => 'Votre mot de passe doit contenir au moins 8 caractères.',
                        ]
                    ),
                    new PasswordStrength(
                        [
                            'minScore' => PasswordStrength::STRENGTH_MEDIUM,
                            'message' => 'Votre mot de passe est trop faible. Veuillez choisir un mot de passe plus sécurisé.',
                        ]
                    )
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
