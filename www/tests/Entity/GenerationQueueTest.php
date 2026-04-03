<?php

namespace App\Tests\Entity;

use App\Entity\GenerationQueue;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour l'entité GenerationQueue (file d'attente de fusions PDF).
 */
class GenerationQueueTest extends TestCase
{
    public function testDefaultStatusIsPending(): void
    {
        $queue = new GenerationQueue();
        $this->assertEquals(GenerationQueue::STATUS_PENDING, $queue->getStatus());
    }

    public function testCreatedAtIsSetAutomatically(): void
    {
        $queue = new GenerationQueue();
        $this->assertInstanceOf(\DateTimeImmutable::class, $queue->getCreatedAt());
    }

    public function testCanSetAndGetFiles(): void
    {
        $queue = new GenerationQueue();
        $files = ['doc1.pdf', 'doc2.pdf', 'doc3.pdf'];
        $queue->setFiles($files);

        $this->assertEquals($files, $queue->getFiles());
        $this->assertCount(3, $queue->getFiles());
    }

    public function testCanSetAndGetUser(): void
    {
        $user = new User();
        $user->setEmail('queue_owner@test.com');

        $queue = new GenerationQueue();
        $queue->setUser($user);

        $this->assertSame($user, $queue->getUser());
    }

    public function testCanSetStatus(): void
    {
        $queue = new GenerationQueue();

        $queue->setStatus(GenerationQueue::STATUS_PROCESSING);
        $this->assertEquals(GenerationQueue::STATUS_PROCESSING, $queue->getStatus());

        $queue->setStatus(GenerationQueue::STATUS_DONE);
        $this->assertEquals(GenerationQueue::STATUS_DONE, $queue->getStatus());

        $queue->setStatus(GenerationQueue::STATUS_ERROR);
        $this->assertEquals(GenerationQueue::STATUS_ERROR, $queue->getStatus());
    }

    public function testResultFileIsNullByDefault(): void
    {
        $queue = new GenerationQueue();
        $this->assertNull($queue->getResultFile());
    }

    public function testCanSetResultFile(): void
    {
        $queue = new GenerationQueue();
        $queue->setResultFile('merge_20260327_1.pdf');

        $this->assertEquals('merge_20260327_1.pdf', $queue->getResultFile());
    }
}
