<?php

namespace App\Service;

use App\Entity\User;
use Random\RandomException;

class EncryptionService
{
    private KeyManagerService $keyManagerService;

    public function __construct(KeyManagerService $keyManagerService) {
        $this->keyManagerService = $keyManagerService;
    }

    /**
     * @throws RandomException
     */
    public function encrypt(string $plainText, User $user): ?string
    {
        $userKey = $this->keyManagerService->getUserKey($user);

        $iv = random_bytes(16);
        $encryptedData = openssl_encrypt(
            $plainText,
            'aes-256-cbc',
            $userKey,
            0,
            $iv
        );

        return base64_encode($encryptedData) . '|' . base64_encode($iv);
    }

    /**
     * @throws RandomException
     */
    public function decrypt(string $encodedText, User $user): ?string
    {
        $userKey = $this->keyManagerService->getUserKey($user);

        [$encryptedData, $iv] = explode('|', $encodedText);

        return openssl_decrypt(
            base64_decode($encryptedData),
            'aes-256-cbc',
            $userKey,
            0,
            base64_decode($iv)
        );
    }
}