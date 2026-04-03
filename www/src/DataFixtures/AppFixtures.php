<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture implements DependentFixtureInterface
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Create a default user
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setFirstname('Test');
        $user->setLastname('User');
        $user->setRoles(['ROLE_USER']);
        $user->setPhone('0123456789');
        $user->setDob(new \DateTime('1990-01-01'));
        
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            'password'
        );
        $user->setPassword($hashedPassword);
        
        // Set Plan
        $plan = $manager->getRepository(\App\Entity\Plan::class)->findOneBy(['name' => 'Free']);
        if ($plan) {
            $user->setPlan($plan);
        }

        $manager->persist($user);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            PlanFixtures::class,
        ];
    }
}
