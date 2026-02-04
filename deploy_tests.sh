#!/bin/bash
docker exec -i symfony-web-v2 bash -c "mkdir -p /var/www/tests/Entity && cat > /var/www/tests/Entity/UserTest.php" <<'PHP_SCRIPT_EOF'
<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testGetterAndSetter()
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
        // Password hashing makes straightforward setter/getter check tricky for password itself without mocking hasher, 
        // but basic set/get can be tested if the entity stores plain text temporarily or if we focus on other fields.
        // For now testing simple properties.

        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($lastname, $user->getLastname());
        $this->assertEquals($firstname, $user->getFirstname());
        $this->assertEquals($dob, $user->getDob());
        $this->assertEquals($photo, $user->getPhoto());
        $this->assertEquals($favoriteColor, $user->getFavoriteColor());
    }
}
PHP_SCRIPT_EOF

docker exec -i symfony-web-v2 bash -c "cat > /var/www/tests/Entity/PlanTest.php" <<'PHP_SCRIPT_EOF'
<?php

namespace App\Tests\Entity;

use App\Entity\Plan;
use PHPUnit\Framework\TestCase;

class PlanTest extends TestCase
{
    public function testGetterAndSetter()
    {
        $plan = new Plan();
        $name = 'Premium Plan';
        $description = 'Best plan ever';
        $limitGeneration = 100;
        $price = 19.99;
        $active = true;

        $plan->setName($name);
        $plan->setDescription($description);
        $plan->setLimitGeneration($limitGeneration);
        $plan->setPrice($price);
        $plan->setActive($active);

        $this->assertEquals($name, $plan->getName());
        $this->assertEquals($description, $plan->getDescription());
        $this->assertEquals($limitGeneration, $plan->getLimitGeneration());
        $this->assertEquals($price, $plan->getPrice());
        $this->assertEquals($active, $plan->isActive());
    }
}
PHP_SCRIPT_EOF

docker exec -i symfony-web-v2 bash -c "cat > /var/www/tests/Entity/GenerationTest.php" <<'PHP_SCRIPT_EOF'
<?php

namespace App\Tests\Entity;

use App\Entity\Generation;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class GenerationTest extends TestCase
{
    public function testGetterAndSetter()
    {
        $generation = new Generation();
        $file = 'document.pdf';
        $createdAt = new \DateTimeImmutable();
        $user = new User();

        $generation->setFile($file);
        $generation->setCreatedAt($createdAt);
        $generation->setUser($user);

        $this->assertEquals($file, $generation->getFile());
        $this->assertEquals($createdAt, $generation->getCreatedAt());
        $this->assertEquals($user, $generation->getUser());
    }
}
PHP_SCRIPT_EOF
