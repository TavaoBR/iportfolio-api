<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ResumeSectionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResumeSectionRepository::class)]
#[ORM\Table(name: 'resume_sections')]
#[ORM\Index(name: 'IDX_RESUME_SECTIONS_RESUME', fields: ['resume'])]
final class ResumeSection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Resume::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Resume $resume;

    #[ORM\Column(length: 32)]
    private string $sectionType;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $content = null;

    #[ORM\Column]
    private int $position = 0;

    #[ORM\Column]
    private bool $isVisible = true;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct(Resume $resume, string $sectionType, int $position)
    {
        $this->resume = $resume;
        $this->sectionType = trim($sectionType);
        $this->position = $position;
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

    public function getSectionType(): string
    {
        return $this->sectionType;
    }

    public function setSectionType(string $sectionType): static
    {
        $this->sectionType = trim($sectionType);
        $this->touch();

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title !== null ? trim($title) : null;
        $this->touch();

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;
        $this->touch();

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;
        $this->touch();

        return $this;
    }

    public function isVisible(): bool
    {
        return $this->isVisible;
    }

    public function setIsVisible(bool $isVisible): static
    {
        $this->isVisible = $isVisible;
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

    public function applyUpdate(
        ?string $sectionType,
        ?string $title,
        ?string $content,
        ?int $position,
        ?bool $isVisible,
    ): void {
        if ($sectionType !== null) {
            $this->sectionType = trim($sectionType);
        }
        if ($title !== null) {
            $this->title = trim($title) !== '' ? trim($title) : null;
        }
        if ($content !== null) {
            $this->content = $content;
        }
        if ($position !== null) {
            $this->position = $position;
        }
        if ($isVisible !== null) {
            $this->isVisible = $isVisible;
        }
        $this->touch();
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
