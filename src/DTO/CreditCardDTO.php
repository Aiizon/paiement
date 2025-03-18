<?php

namespace App\DTO;

class CreditCardDTO
{
    public string $number;
    public string $cvv;
    public string $holderName;
    public string $expirationMonth;
    public string $expirationYear;

    public static function create
    (
        string $number,
        string $cvv,
        string $holderName,
        string $expirationMonth,
        string $expirationYear
    ): self {
        $dto = new self();

        $dto->number          = $number;
        $dto->cvv             = $cvv;
        $dto->holderName      = $holderName;
        $dto->expirationMonth = $expirationMonth;
        $dto->expirationYear  = $expirationYear;

        return $dto;
    }
}