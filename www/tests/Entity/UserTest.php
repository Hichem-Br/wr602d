<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Entity\Plan;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires supplémentaires pour l'entité User.
 */
class UserTest extends TestCase
{
    public function testGetterAndSetter(): void
    {
        $user = new User();
        $email = 'test@test.com';
        $lastname = 'Doe';
        $firstname = 'John';
        $dob = new \DateTimeImmutable('1990-01-01');
        $photo = 'photo.jpg';
        $favoriteColor = 'blue';

        $user->setEmail($email);
        $user->setLastname($lastname);
        $user->setFirstname($firstname);
        $user->setDob($dob);
        $user->setPhoto($photo);
        $user->setFavoriteColor($favoriteColor);

        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($lastname, $user->getLastname());
        $this->assertEquals($firstname, $user->getFirstname());
        $this->assertEquals($dob, $user->getDob());
        $this->assertEquals($photo, $user->getPhoto());
        $this->assertEquals($favoriteColor, $user->getFavoriteColor());
    }

    public function testUserHasDefaultRoles(): void
    {
        $user = new User();
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    public function testUserCanHaveMultipleRoles(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_USER', 'ROLE_PREMIUM']);

        $this->assertContains('ROLE_USER', $user->getRoles());
        $this->assertContains('ROLE_PREMIUM', $user->getRoles());
    }

    public function testUserCanBeAssignedPlan(): void
    {
        $user = new User();
        $plan = new Plan();
        $plan->setName('Premium');
        $plan->setPrice(9.99);
        $plan->setLimitGeneration(50);

        $user->setPlan($plan);

        $this->assertSame($plan, $user->getPlan());
        $this->assertEquals('Premium', $user->getPlan()->getName());
    }

    public function testStripeCustomerIdIsNullByDefault(): void
    {
        $user = new User();
        $this->assertNull($user->getStripeCustomerId());
    }

    public function testCanSetStripeCustomerId(): void
    {
        $user = new User();
        $user->setStripeCustomerId('cus_test_12345');

        $this->assertEquals('cus_test_12345', $user->getStripeCustomerId());
    }

    public function testUserIdentifier(): void
    {
        $user = new User();
        $user->setEmail('identifier@test.com');

        $this->assertEquals('identifier@test.com', $user->getUserIdentifier());
    }
}
