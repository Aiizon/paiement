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
use Exception;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        ProductRepository    $productRepository,
        UserRepository       $userRepository,
        CreditCardRepository $creditCardRepository,
    ): Response {
        $product = $productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Produit introuvable.');
        }

        $user = $userRepository->find($this->getUser());

        // Récupérer les cartes existantes de l'utilisateur
        $creditCards = $creditCardRepository->findBy(['user' => $user]);

        return $this->render('payments/checkout.html.twig', [
            'product'     => $product,
            'creditCards' => $creditCards,
        ]);
    }

    #[Route(path:'/payment/process/{id}', name: 'app_payment_process')]
    #[IsGranted('ROLE_USER')]
    public function processPayment
    (
        int                    $id,
        Request                $request,
        ProductRepository      $productRepository,
        CreditCardRepository   $creditCardRepository,
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

        // Sauvegarder le paiement avec les données déchiffrées
        $payment = (new Payment())
            ->setUser($this->getUser())
            ->setProduct($product)
            ->setCreditCard($creditCard)
            ->setAmount($product->getPrice())
            ->setCreatedAt()
        ;

        $em->persist($payment);
        $em->flush();

        return $this->redirectToRoute('app_payments');
    }

    #[Route(path:'/save-card', name: 'app_save_card', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function saveCard
    (
        Request           $request,
        CreditCardService $cardService,
        UserRepository    $userRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $user = $userRepository->find($this->getUser());

        $encryptedAesKey     = $data['encryptedAESKey'];
        $encryptedNumber     = $data['encryptedCardNumber'];
        $encryptedCvv        = $data['encryptedCvv'];
        $encryptedHolderName = $data['encryptedHolderName'];
        $expirationMonth     = $data['expirationMonth'];
        $expirationYear      = $data['expirationYear'];

        try {
            $aesKey     = $this->encryptionService->decryptAESKeyWithPrivateKey($encryptedAesKey);

            $number     = $this->encryptionService->decryptWithAES($encryptedNumber, $aesKey);
            $cvv        = $this->encryptionService->decryptWithAES($encryptedCvv, $aesKey);
            $holderName = $this->encryptionService->decryptWithAES($encryptedHolderName, $aesKey);

            $cardService->store($number, $cvv, $holderName, $expirationMonth, $expirationYear, $user);
        } catch (Exception $e) {
            return new JsonResponse(['success' => false, 'message' => 'Une erreur est survenue lors de l\'enregistrement de la carte de crédit.'], 500);
        }

        return new JsonResponse(['success' => true, 'message' => 'Carte de crédit enregistrée avec succès.']);
    }
}
