<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $usersData = [
            'admin@wacdo.com'   => ['ROLE_SUPER_ADMIN', 'admin123', 'user_admin'],
            'prep@wacdo.com'    => ['ROLE_PREPARATEUR', 'prep123', 'user_prep'],
            'accueil@wacdo.com' => ['ROLE_ACCUEIL', 'accueil123', 'user_accueil'],
        ];

        foreach ($usersData as $email => [$role, $password, $reference]) {
            $user = new User();
            $user->setEmail($email);
            $user->setRoles([$role]);
            $user->setPassword($this->passwordHasher->hashPassword($user, $password));

            $manager->persist($user);
            $this->addReference($reference, $user);
        }

        $manager->flush();
    }
}
