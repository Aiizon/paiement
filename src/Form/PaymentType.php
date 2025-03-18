<?php

namespace App\Form;

use App\Entity\Payment;
use App\Entity\CreditCard;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

// src/Form/PaymentType.php

class PaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('creditCard', ChoiceType::class, [
                'choices' => $options['credit_cards'],
                'choice_label' => function (CreditCard $creditCard) {
                    return $creditCard->getFilteredNumberBeginEnd();  // Affiche un numéro de carte masqué
                },
                'label' => 'Carte de crédit',
                'required' => true,
            ])
            ->add('amount', NumberType::class, [
                'label' => 'Montant à payer',
                'required' => true,
                'disabled' => true,  // Le montant est automatiquement déterminé par le produit
                'data' => $options['product_price'],  // Passer le prix du produit ici
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Valider mon achat',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Payment::class,
            'credit_cards' => [],  // Passer les cartes de crédit disponibles
            'product_price' => 0,  // Prix du produit
        ]);
    }
}
