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
