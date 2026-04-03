<?php

namespace App\Tests\Entity;

use App\Entity\Generation;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour l'entité Generation (historique des PDF générés).
 */
class GenerationTest extends TestCase
{
    public function testGetterAndSetter(): void
    {
        $generation = new Generation();
        $user = new User();
        $user->setEmail('gen_owner@test.com');
        $filename = 'Google_20260327120000.pdf';
        $date = new \DateTimeImmutable('2026-03-27 12:00:00');

        $generation->setUser($user);
        $generation->setFile($filename);
        $generation->setCreatedAt($date);

        $this->assertSame($user, $generation->getUser());
        $this->assertEquals($filename, $generation->getFile());
        $this->assertEquals($date, $generation->getCreatedAt());
    }

    public function testIdIsNullInitially(): void
    {
        $generation = new Generation();
        $this->assertNull($generation->getId());
    }

    public function testFilenameCanContainTimestamp(): void
    {
        $generation = new Generation();
        $generation->setFile('document_20260327161702.pdf');

        $this->assertStringContainsString('.pdf', $generation->getFile());
        $this->assertStringContainsString('_', $generation->getFile());
    }
}
