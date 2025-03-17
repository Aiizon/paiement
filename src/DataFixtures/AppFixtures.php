<?php

namespace App\DataFixtures;

use App\Entity\CreditCard;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setPassword($this->hasher->hashPassword($user, 'Not24get'));
        $user->setRoles(['ROLE_USER']);

        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setPassword($this->hasher->hashPassword($admin, 'Not24get'));
        $admin->setRoles(['ROLE_ADMIN', 'ROLE_USER']);

        $manager->persist($user);
        $manager->persist($admin);
        $manager->flush();

        $products = [
            (new Product())->setName('Oupi Goupi')         ->setPrice('999.99€'),
            (new Product())->setName('Canard en plastique')->setPrice('9.99€'),
        ];

        foreach ($products as $product) {
            $manager->persist($product);
        }
        $manager->flush();

        $userCreditCard =
            (new CreditCard())
                ->setNumber('1234 5678 9123 4567')
                ->setCvv('123')
                ->setExpirationMonth(12)
                ->setExpirationYear(2028)
                ->setHolderName('Alain Ternette');
            ;

        $manager->persist($userCreditCard);
        $manager->flush();
    }
}
