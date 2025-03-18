<?php

namespace App\Controller;

use App\DTO\CreditCardDTO;
use App\Entity\CreditCard;
use App\Entity\Payment;
use App\Form\CreditCardFormType;
use App\Repository\PaymentRepository;
use App\Repository\ProductRepository;
use App\Repository\CreditCardRepository;
use App\Repository\UserRepository;
use App\Service\CreditCardService;
use App\Service\EncryptionService; // Service pour déchiffrement AES
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class PaymentsController extends AbstractController
{
    private EncryptionService $encryptionService;

    public function __construct(EncryptionService $encryptionService)
    {
        $this->encryptionService = $encryptionService;
    }

    #[Route(path:'/payments', name: 'app_payments')]
    #[IsGranted('ROLE_USER')]
    public function index(PaymentRepository $paymentRepository): Response
    {
        $payments = $paymentRepository->findBy(['user' => $this->getUser()]);

        return $this->render('payments/index.html.twig', [
            'payments' => $payments,
        ]);
    }

    #[Route(path:'/payment/checkout/{id}', name: 'app_payment_checkout')]
    #[IsGranted('ROLE_USER')]
    public function checkout(
        int $id,
        Request $request,
        ProductRepository $productRepository,
        UserRepository $userRepository,
        CreditCardRepository $creditCardRepository,
        CreditCardService $creditCardService
    ): Response {
        $product = $productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Produit introuvable.');
        }

        $user = $userRepository->find($this->getUser());

        // Récupérer les cartes existantes de l'utilisateur
        $creditCards = $creditCardRepository->findBy(['user' => $user]);

        // Création du formulaire
        $dto  = new CreditCardDTO();
        $form = $this->createForm(CreditCardFormType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dto = $form->getData();

            $creditCard = $creditCardService->store(
                $dto->number,
                $dto->cvv,
                $dto->holderName,
                $dto->expirationMonth,
                $dto->expirationYear,
                $user
            );

            return $this->redirectToRoute('app_payment_process', [
                'id'     => $product->getId(),
                'cardId' => $creditCard->getId(),
            ]);
        }

        return $this->render('payments/checkout.html.twig', [
            'product'     => $product,
            'creditCards' => $creditCards,
            'form'        => $form->createView(),
        ]);
    }

    #[Route(path:'/payment/process/{id}', name: 'app_payment_process')]
    #[IsGranted('ROLE_USER')]
    public function processPayment(
        int $id,
        Request $request,
        ProductRepository $productRepository,
        CreditCardRepository $creditCardRepository,
        EntityManagerInterface $em
    ): Response {
        $product = $productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Produit introuvable.');
        }

        $creditCardId = $request->get('cardId');

        if ($creditCardId) {
            $creditCard = $creditCardRepository->find($creditCardId);

            if (!$creditCard || $creditCard->getUser() !== $this->getUser()) {
                throw $this->createNotFoundException('Carte de crédit introuvable ou non autorisée.');
            }
        } else {
            $this->addFlash('error', 'Veuillez sélectionner une carte de crédit existante.');
            return $this->redirectToRoute('app_payment_checkout', ['id' => $id]);
        }

        // Déchiffrer les données sensibles (par exemple, numéro de carte)
        $encryptedNumber = $creditCard->getEncryptedNumber();  // Assurez-vous que c'est bien chiffré

        // Déchiffrement de la clé AES avec la clé privée RSA
        $aesKey = $this->encryptionService->decryptAESKeyWithPrivateKey($encryptedNumber);

        // Déchiffrement du numéro de carte avec la clé AES
//        $decryptedCardNumber = $this->encryptionService->decryptWithAES($encryptedNumber, $aesKey);

        // Sauvegarder le paiement avec les données déchiffrées
        $payment = (new Payment())
            ->setUser($this->getUser())
            ->setProduct($product)
            ->setCreditCard($creditCard)
            ->setAmount($product->getPrice())
            ->setCreatedAt(new \DateTime()) // Assurez-vous que la date soit bien définie
        ;

        $em->persist($payment);
        $em->flush();

        return $this->redirectToRoute('app_payments');
    }
}
