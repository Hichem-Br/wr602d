<?php

namespace App\Entity;

use App\Repository\GenerationQueueRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GenerationQueueRepository::class)]
class GenerationQueue
{
    public const STATUS_PENDING    = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_DONE       = 'done';
    public const STATUS_ERROR      = 'error';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /** JSON-encoded array of PDF file paths to merge */
    #[ORM\Column(type: 'json')]
    private array $files = [];

    #[ORM\Column(length: 20)]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(nullable: true)]
    private ?string $resultFile = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }

    public function getFiles(): array { return $this->files; }
    public function setFiles(array $files): static { $this->files = $files; return $this; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): static { $this->status = $status; return $this; }

    public function getResultFile(): ?string { return $this->resultFile; }
    public function setResultFile(?string $resultFile): static { $this->resultFile = $resultFile; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
