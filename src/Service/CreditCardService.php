<?php

namespace App\Service;

use App\Entity\CreditCard;
use Doctrine\ORM\EntityManagerInterface;

class CreditCardService
{
    private EncryptionService      $encryptionService;
    private EntityManagerInterface $entityManager;

    public function __construct
    (
        EncryptionService      $encryptionService,
        EntityManagerInterface $entityManager
    ) {
        $this->encryptionService = $encryptionService;
        $this->entityManager     = $entityManager;
    }

    public function store
    (
        string $number,
        string $cvv,
        string $holderName,
        string $expirationMonth,
        string $expirationYear,
    ): CreditCard {
        $number = preg_replace('/\D/', '', $number);
        $first4 = substr($number, 0, 4);
        $last4  = substr($number, -4);

        $type   = $this->detectCardType($number);

        $creditCard = (new CreditCard())
            ->setEncryptedNumber($this->encryptionService->encrypt($number))
            ->setEncryptedCvv($this->encryptionService->encrypt($cvv))
            ->setFirst4($first4)
            ->setLast4($last4)
            ->setExpirationMonth($expirationMonth)
            ->setExpirationYear($expirationYear)
            ->setEncryptedHolderName($this->encryptionService->encrypt($holderName))
            ->setCardType($type)
        ;

        $this->entityManager->persist($creditCard);
        $this->entityManager->flush();

        return $creditCard;
    }

    public function getCardNumber(CreditCard $card): string
    {
        return $this->encryptionService->decrypt($card->getEncryptedNumber());
    }

    public function getCardCvv(CreditCard $card): string
    {
        return $this->encryptionService->decrypt($card->getEncryptedCvv());
    }

    public function getCardHolderName(CreditCard $card): string
    {
        return $this->encryptionService->decrypt($card->getEncryptedHolderName());
    }

    private function detectCardType(string $number): string
    {
        $patterns = [
            'visa'       => '/^4[0-9]{12}(?:[0-9]{3})?$/',
            'mastercard' => '/^5[1-5][0-9]{14}$/',
            'amex'       => '/^3[47][0-9]{13}$/',
            'discover'   => '/^6(?:011|5[0-9]{2})[0-9]{12}$/',
        ];

        foreach ($patterns as $type => $pattern) {
            if (preg_match($pattern, $number)) {
                return $type;
            }
        }

        return 'unknown';
    }
}