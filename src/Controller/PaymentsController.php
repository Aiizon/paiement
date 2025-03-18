<?php


namespace App\Controller;

use App\Entity\CreditCard;
use App\Entity\Payment;
use App\Form\CreditCardFormType;
use App\Repository\PaymentRepository;
use App\Repository\ProductRepository;
use App\Repository\CreditCardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class PaymentsController extends AbstractController
{
    #[Route('/payments', name: 'app_payments')]
    #[IsGranted('ROLE_USER')]
    public function index(PaymentRepository $paymentRepository): Response
    {
        $payments = $paymentRepository->findBy(['user' => $this->getUser()]);

        return $this->render('payments/index.html.twig', [
            'payments' => $payments,
        ]);
    }

    #[Route('/payment/checkout/{id}', name: 'app_payment_checkout')]
    #[IsGranted('ROLE_USER')]
    public function checkout(
        int $id,
        ProductRepository $productRepository,
        CreditCardRepository $creditCardRepository,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $product = $productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Produit introuvable.');
        }

        // Récupérer les cartes existantes de l'utilisateur
        $creditCards = $creditCardRepository->findBy(['user' => $this->getUser()]);

        // Création du formulaire
        $creditCard = new CreditCard();
        $form = $this->createForm(CreditCardFormType::class, $creditCard);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $creditCard->setUser($this->getUser());
            $em->persist($creditCard);
            $em->flush();

            return $this->redirectToRoute('app_payment_process', ['id' => $product->getId()]);
        }

        return $this->render('payments/checkout.html.twig', [
            'product' => $product,
            'creditCards' => $creditCards,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/payment/process/{id}', name: 'app_payment_process')]
    #[IsGranted('ROLE_USER')]
    public function processPayment(
        int $id,
        ProductRepository $productRepository,
        CreditCardRepository $creditCardRepository,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $product = $productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Produit introuvable.');
        }

        $creditCardId = $request->request->get('creditCard');
        $creditCard = $creditCardRepository->find($creditCardId);

        if (!$creditCard || $creditCard->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('Carte de crédit introuvable ou non autorisée.');
        }

        $payment = new Payment();
        $payment->setUser($this->getUser());
        $payment->setProduct($product);
        $payment->setCreditCard($creditCard);
        $payment->setAmount($product->getPrice());
        $payment->setCreatedAt(); // Set the createdAt field

        $em->persist($payment);
        $em->flush();

        return $this->redirectToRoute('app_payments');
    }
}
