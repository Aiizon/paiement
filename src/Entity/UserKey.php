<?php

namespace App\Entity;

use App\Repository\UserKeyRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserKeyRepository::class)]
class UserKey
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userKeys')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private ?string $encryptedKey = null;

    #[ORM\Column(length: 64)]
    private ?string $iv = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getEncryptedKey(): ?string
    {
        return $this->encryptedKey;
    }

    public function setEncryptedKey(string $encryptedKey): static
    {
        $this->encryptedKey = $encryptedKey;

        return $this;
    }

    public function getIv(): ?string
    {
        return $this->iv;
    }

    public function setIv(string $iv): static
    {
        $this->iv = $iv;

        return $this;
    }
}
