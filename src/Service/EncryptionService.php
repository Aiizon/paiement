<?php

namespace App\Service;

use Exception;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class EncryptionService
{
    private string $key;

    public function __construct(
        #[Autowire('%env(ENCRYPTION_KEY)%')]
        ?string $key
    )
    {
        $this->key = $key ? base64_decode($key) : sodium_crypto_secretbox_keygen();
    }

    public function encrypt(string $plainText): ?string
    {
        try {
            // Génère un nombre à usage unique pour rendre le chiffrement unique
            // 24 bytes est la taille recommandée pour Sodium
            $nonce = random_bytes(24);
            // Chiffre le texte brut
            $cipherText = sodium_crypto_secretbox($plainText, $nonce, $this->key);
        } catch (Exception $e) {
            return null;
        }

        return base64_encode($nonce . $cipherText);
    }

    public function decrypt(string $encodedText): ?string
    {
        $decodedText = base64_decode($encodedText);
        // Récupère la chaîne de bytes utilisée pour chiffrer
        $nonce = mb_substr($decodedText, 0, 24, '8bit');
        // Récupère le texte chiffré
        $cipherText = mb_substr($decodedText, 24, null, '8bit');

        try {
            // Déchiffre le texte chiffré
            $plainText = sodium_crypto_secretbox_open($cipherText, $nonce, $this->key);
        } catch (Exception $e) {
            return null;
        }

        return $plainText;
    }
}