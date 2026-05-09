<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AIAnalysisRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AIAnalysisRepository::class)]
#[ORM\Table(name: 'ai_analyses')]
#[ORM\Index(name: 'IDX_AI_ANALYSES_RESUME', fields: ['resume'])]
final class AIAnalysis
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    public const TYPE_ANALYZE = 'analyze';
    public const TYPE_OPTIMIZE = 'optimize';
    public const TYPE_COMPARE_JOB = 'compare_job';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Resume::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Resume $resume;

    #[ORM\Column(length: 32)]
    private string $analysisType;

    #[ORM\Column(length: 24)]
    private string $status = self::STATUS_PENDING;

    /**
     * @var array<string, mixed>|null
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $requestPayload = null;

    /**
     * @var array<string, mixed>|null
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $result = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $errorMessage = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct(Resume $resume, string $analysisType, ?array $requestPayload = null)
    {
        $this->resume = $resume;
        $this->analysisType = $analysisType;
        $this->requestPayload = $requestPayload;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getResume(): Resume
    {
        return $this->resume;
    }

    public function getAnalysisType(): string
    {
        return $this->analysisType;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        $this->touch();

        return $this;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getRequestPayload(): ?array
    {
        return $this->requestPayload;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getResult(): ?array
    {
        return $this->result;
    }

    public function setResult(?array $result): static
    {
        $this->result = $result;
        $this->touch();

        return $this;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): static
    {
        $this->errorMessage = $errorMessage;
        $this->touch();

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
