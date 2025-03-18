<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\UserKey;
use App\Repository\UserKeyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class KeyManagerService
{
    private string                 $masterKey;
    private UserKeyRepository      $keyRepository;
    private EntityManagerInterface $entityManager;

    public function __construct
    (
        #[Autowire('%env(MASTER_KEY)%')]
        string                 $masterKey,
        UserKeyRepository      $keyRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->masterKey     = $masterKey;
        $this->keyRepository = $keyRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @throws RandomException
     */
    public function getUserKey(User $user): string
    {
        $userKeyEntity = $this->keyRepository->findOneBy(['user' => $user]);

        if (!$userKeyEntity) {
            return $this->generateUserKey($user);
        }

        return $this->decryptUserKey($userKeyEntity);
    }

    /**
     * @throws RandomException
     */
    private function generateUserKey(User $user): string
    {
        $userKey = bin2hex(random_bytes(32));
        $iv      = random_bytes(16);

        $encryptedKey = openssl_encrypt(
            $userKey,
            'aes-256-cbc',
            $this->masterKey,
            0,
            $iv
        );

        $userKeyEntity = (new UserKey())
            ->setUser($user)
            ->setEncryptedKey($encryptedKey)
            ->setIv(base64_encode($iv))
        ;

        $this->entityManager->persist($userKeyEntity);
        $this->entityManager->flush();

        return $userKey;
    }

    private function decryptUserKey(UserKey $userKeyEntity): string
    {
        return openssl_decrypt(
            $userKeyEntity->getEncryptedKey(),
            'aes-256-cbc',
            $this->masterKey,
            0,
            base64_decode($userKeyEntity->getIv())
        );
    }
}