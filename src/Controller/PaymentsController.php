<?php

namespace App\Controller;

use App\Repository\PaymentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class PaymentsController extends AbstractController
{
    #[Route('/payments', name: 'app_payments')]
    #[isGranted('ROLE_USER')]
    public function index(PaymentRepository $paymentRepository): Response
    {
        $payments = $paymentRepository->findBy(['user' => $this->getUser()]);

        return $this->render('payments/index.html.twig', [
            'payments' => $payments,
        ]);
    }
}
