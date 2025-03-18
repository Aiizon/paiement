<?php

namespace App\Service;

use App\Entity\User;
use Random\RandomException;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use RuntimeException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class EncryptionService
{
    private ParameterBagInterface $parameterBag;
    private KeyManagerService     $keyManagerService;
    private string                $privateKey; // Clé privée RSA pour déchiffrer la clé AES

    public function __construct(
        ParameterBagInterface $parameterBag,
        KeyManagerService     $keyManagerService
    ) {
        $this->parameterBag      = $parameterBag;
        $this->keyManagerService = $keyManagerService;

        $this->loadPrivateKey();
    }

    private function loadPrivateKey(): void
    {
        $privateKeyPath = $this->parameterBag->get('kernel.project_dir') . '/' . $_ENV['RSA_PRIVATE_KEY_PATH'];

        if (!file_exists($privateKeyPath)) {
            throw new RuntimeException('Clé privée RSA introuvable à : ' . $privateKeyPath);
        }

        $this->privateKey = file_get_contents($privateKeyPath);

        if ($this->privateKey === false) {
            throw new RuntimeException('Clé privée RSA illisible ou corrompue');
        }
    }

    /**
     * @throws RandomException
     */
    public function encrypt(string $plainText, User $user): ?string
    {
        $userKey = $this->keyManagerService->getUserKey($user);

        // Génération d'un vecteur d'initialisation pour AES
        $iv = random_bytes(16);
        $encryptedData = openssl_encrypt(
            $plainText,
            'aes-256-cbc',
            $userKey,
            0,
            $iv
        );

        // Retourne les données chiffrées avec le vecteur d'initialisation
        return base64_encode($encryptedData) . '|' . base64_encode($iv);
    }

    /**
     * @throws RandomException
     */
    public function decrypt(string $encodedText, User $user): ?string
    {
        $userKey = $this->keyManagerService->getUserKey($user);

        // Séparer les données chiffrées et le vecteur d'initialisation
        [$encryptedData, $iv] = explode('|', $encodedText);

        // Déchiffrer les données avec AES et la clé de l'utilisateur
        return openssl_decrypt(
            base64_decode($encryptedData),
            'aes-256-cbc',
            $userKey,
            0,
            base64_decode($iv)
        );
    }

    /**
     * Déchiffre la clé AES avec la clé privée RSA
     */
    public function decryptAESKeyWithPrivateKey(string $encryptedAESKey): string
    {
        // Déchiffre la clé AES avec la clé privée RSA
        $decryptedKey = '';
        if (openssl_private_decrypt(base64_decode($encryptedAESKey), $decryptedKey, $this->privateKey) === false) {
            throw new RuntimeException('Échec du déchiffrement de la clé AES.');
        }

        return $decryptedKey;
    }

    /**
     * Déchiffre les données avec la clé AES déchiffrée
     */
    public function decryptWithAES(string $encryptedData, string $aesKey): string
    {
        // Utilisation de la clé AES pour déchiffrer les données
        return Crypto::decrypt(base64_decode($encryptedData), Key::loadFromAsciiSafeString($aesKey));
    }


}

