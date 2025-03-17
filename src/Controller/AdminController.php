<?php

namespace App\Controller;

use App\Form\PaymentRefundType;
use App\Repository\PaymentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(PaymentRepository $paymentRepository): Response
    {
        $payments = $paymentRepository->findAll();

        return $this->render('admin/index.html.twig', [
            'payments' => $payments,
        ]);
    }

    #[Route(path: '/refund/{id}', name: 'app_admin_refund')]
    public function refundPayment(int $id, PaymentRepository $paymentRepository, EntityManagerInterface $entityManager): Response
    {
        $payment = $paymentRepository->find($id);

        if ($payment === null) {
            throw $this->createNotFoundException('Payment not found');
        }

        $form = $this->createForm(PaymentRefundType::class, null, [
            'payment' => $payment
        ]);

        if ($form->isSubmitted() && $form->isValid()) {
            $payment->setRefundedAmount($form->get('amount')->getData());
            $payment->setIsRefunded(true);

            $entityManager->persist($payment);
            $entityManager->flush();

            $this->addFlash('success', 'Le remboursement a bien été effectué.');
            return $this->redirectToRoute('app_admin');
        }

        return $this->render('admin/refund.html.twig', [
            'payment' => $payment,
            'form'    => $form->createView(),
        ]);
    }
}
