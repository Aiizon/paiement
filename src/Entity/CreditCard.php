<?php

namespace App\Entity;

use App\Repository\CreditCardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CreditCardRepository::class)]
class CreditCard
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $encryptedNumber = null;

    #[ORM\Column(length: 255)]
    private ?string $encryptedCvv = null;

    #[ORM\Column(length: 4)]
    private ?string $first4 = null;

    #[ORM\Column(length: 4)]
    private ?string $last4 = null;

    #[ORM\Column]
    #[Assert\Range(min: 1, max: 12)]
    private ?int $expirationMonth = null;

    #[ORM\Column]
    #[Assert\Range(min: 2000, max: 2100)]
    private ?int $expirationYear = null;

    #[ORM\Column(length: 500)]
    private ?string $encryptedHolderName = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $cardType = null;

    /**
     * @var Collection<int, Payment>
     */
    #[ORM\OneToMany(targetEntity: Payment::class, mappedBy: 'creditCard')]
    private Collection $payments;

    #[ORM\ManyToOne(inversedBy: 'creditCards')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function __construct()
    {
        $this->payments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getEncryptedNumber(): ?string
    {
        return $this->encryptedNumber;
    }

    public function setEncryptedNumber(?string $encryptedNumber): static
    {
        $this->encryptedNumber = $encryptedNumber;

        return $this;
    }

    public function getEncryptedCvv(): ?string
    {
        return $this->encryptedCvv;
    }

    public function setEncryptedCvv(?string $encryptedCvv): static
    {
        $this->encryptedCvv = $encryptedCvv;

        return $this;
    }

    public function getFirst4(): ?string
    {
        return $this->first4;
    }

    public function setFirst4(?string $first4): static
    {
        $this->first4 = $first4;

        return $this;
    }

    public function getLast4(): ?string
    {
        return $this->last4;
    }

    public function setLast4(?string $last4): static
    {
        $this->last4 = $last4;

        return $this;
    }

    public function getEncryptedHolderName(): ?string
    {
        return $this->encryptedHolderName;
    }

    public function setEncryptedHolderName(?string $encryptedHolderName): static
    {
        $this->encryptedHolderName = $encryptedHolderName;

        return $this;
    }

    public function getCardType(): ?string
    {
        return $this->cardType;
    }

    public function setCardType(?string $cardType): static
    {
        $this->cardType = $cardType;

        return $this;
    }

    public function getExpirationMonth(): ?int
    {
        return $this->expirationMonth;
    }

    public function setExpirationMonth(int $expirationMonth): static
    {
        $this->expirationMonth = $expirationMonth;

        return $this;
    }

    public function getExpirationYear(): ?int
    {
        return $this->expirationYear;
    }

    public function setExpirationYear(int $expirationYear): static
    {
        $this->expirationYear = $expirationYear;

        return $this;
    }

    /**
     * @return Collection<int, Payment>
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function addPayment(Payment $payment): static
    {
        if (!$this->payments->contains($payment)) {
            $this->payments->add($payment);
            $payment->setCreditCard($this);
        }

        return $this;
    }

    public function removePayment(Payment $payment): static
    {
        if ($this->payments->removeElement($payment)) {
            // set the owning side to null (unless already changed)
            if ($payment->getCreditCard() === $this) {
                $payment->setCreditCard(null);
            }
        }

        return $this;
    }

    public function getUser(): ?user
    {
        return $this->user;
    }

    public function setUser(?user $user): static
    {
        $this->user = $user;

        return $this;
    }
}
