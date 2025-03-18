<?php

namespace App\Form;

use App\DTO\CreditCardDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreditCardFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('holderName', TextType::class, [
                'label' => 'Nom du titulaire',
                'attr' => ['placeholder' => 'Ex: Jean Dupont']
            ])
            ->add('number', TextType::class, [
                'label' => 'Numéro de carte',
                'attr' => ['placeholder' => '1234 5678 9012 3456']
            ])
            ->add('expirationMonth', IntegerType::class, [
                'label' => 'Mois d\'expiration',
                'attr' => ['min' => 1, 'max' => 12]
            ])
            ->add('expirationYear', IntegerType::class, [
                'label' => 'Année d\'expiration',
                'attr' => ['min' => date('Y'), 'max' => date('Y') + 20]
            ])
            ->add('cvv', IntegerType::class, [
                'label' => 'CVV',
                'attr' => ['placeholder' => '123']
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer et payer'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreditCardDTO::class,
        ]);
    }
}

