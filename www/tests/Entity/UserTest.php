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
