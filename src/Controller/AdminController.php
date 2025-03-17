<?php

namespace App\Controller;

use App\Form\PaymentRefundType;
use App\Repository\PaymentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin', name: 'app_admin')]
final class AdminController extends AbstractController
{
    #[Route('/', name: '')]
    public function index(PaymentRepository $paymentRepository): Response
    {
        $payments = $paymentRepository->findAll();

        return $this->render('admin/index.html.twig', [
            'payments' => $payments,
        ]);
    }

    #[Route(path: '/refund/{id}', name: '_refund')]
    public function refundPayment(int $id, Request $request, PaymentRepository $paymentRepository, EntityManagerInterface $entityManager): Response
    {
        $payment = $paymentRepository->find($id);

        if ($payment === null) {
            throw $this->createNotFoundException('Payment not found');
        }

        if ($payment->isRefunded()) {
            $this->addFlash('danger', 'Ce paiement a déjà été remboursé.');
            return $this->redirectToRoute('app_admin');
        }

        $form = $this->createForm(PaymentRefundType::class, null, [
            'payment' => $payment
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $payment->setRefundAmount($form->getData()['amount']);
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
