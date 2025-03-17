<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class PaymentRefundType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $payment = $options['payment'];

        $builder
            ->add('amount', MoneyType::class, [
                'label' => 'Montant à rembourser',
                'currency' => 'EUR',
                'attr' => [
                    'min' => 0,
                    'max' => $payment->getAmount(),
                    'step' => 0.01,
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new PositiveOrZero([
                        'message' => 'Le montant doit être positif ou nul.'
                    ]),
                    new LessThanOrEqual([
                        'value' => $payment->getAmount(),
                        'message' => 'Le montant doit être inférieur ou égal à {{ compared_value }}€.',
                    ]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Rembourser',
                'attr' => [
                    'class' => 'btn btn-success',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'payment' => null,
            'data_class' => null,
        ]);

        $resolver->setRequired('payment');
        $resolver->setAllowedTypes('payment', 'App\Entity\Payment');
    }
}
