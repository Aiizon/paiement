<?php

namespace App\DataFixtures;

use App\Entity\CreditCard;
use App\Entity\Payment;
use App\Entity\Product;
use App\Entity\User;
use App\Service\CreditCardService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Random\RandomException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;
    private CreditCardService           $creditCardService;

    public function __construct
    (
        UserPasswordHasherInterface $hasher,
        CreditCardService           $creditCardService
    ) {
        $this->hasher            = $hasher;
        $this->creditCardService = $creditCardService;
    }

    /**
     * @throws RandomException
     */
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setPassword($this->hasher->hashPassword($user, 'JqxddVm8@Cqm59BhKUWshXHm@!Scs'));
        $user->setRoles(['ROLE_USER']);

        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setPassword($this->hasher->hashPassword($admin, 'u0dmZY0^Ayb1D4D*4jQKGN37PZj9!'));
        $admin->setRoles(['ROLE_ADMIN', 'ROLE_USER']);

        $manager->persist($user);
        $manager->persist($admin);
        $manager->flush();

        $products = [
            (new Product())->setName('Oupi Goupi')         ->setPrice('999.99'),
            (new Product())->setName('Mastermind')         ->setPrice('14.99'),
            (new Product())->setName('Canard en plastique')->setPrice('9.99'),
            (new Product())->setName('La pomme')           ->setPrice('49.99'),
            (new Product())->setName('La poire')           ->setPrice('44.99'),
        ];

        foreach ($products as $product) {
            $manager->persist($product);
        }
        $manager->flush();

        $userCreditCard = $this->creditCardService->store(
            '4111111111111111',
            '123',
            'John Doe',
            '8',
            '2029',
            $user
        );

        $payment = (new Payment())
            ->setAmount('999.99')
            ->setUser($user)
            ->setProduct($products[0])
            ->setCreditCard($userCreditCard)
            ->setIsRefunded(false)
            ->setCreatedAt()
        ;
        $manager->persist($payment);
        $manager->flush();
    }
}
