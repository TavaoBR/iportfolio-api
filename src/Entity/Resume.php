<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ResumeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResumeRepository::class)]
#[ORM\Table(name: 'resumes')]
#[ORM\UniqueConstraint(name: 'UNIQ_RESUME_PUBLIC_ID', fields: ['publicId'])]
#[ORM\Index(name: 'IDX_RESUME_USER', fields: ['user'])]
final class Resume
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(length: 36)]
    private string $publicId;

    #[ORM\Column(length: 180)]
    private string $title;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $targetRole = null;

    #[ORM\Column(length: 10)]
    private string $language = 'pt_BR';

    #[ORM\Column(nullable: true)]
    private ?int $atsScore = null;

    #[ORM\Column]
    private bool $isMain = false;

    #[ORM\Column]
    private bool $isPublic = false;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct(User $user, string $publicId, string $title)
    {
        $this->user = $user;
        $this->publicId = $publicId;
        $this->title = trim($title);
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getPublicId(): string
    {
        return $this->publicId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = trim($title);
        $this->touch();

        return $this;
    }

    public function getTargetRole(): ?string
    {
        return $this->targetRole;
    }

    public function setTargetRole(?string $targetRole): static
    {
        $this->targetRole = $this->normalize($targetRole);
        $this->touch();

        return $this;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): static
    {
        $this->language = $language;
        $this->touch();

        return $this;
    }

    public function getAtsScore(): ?int
    {
        return $this->atsScore;
    }

    public function setAtsScore(?int $atsScore): static
    {
        $this->atsScore = $atsScore;
        $this->touch();

        return $this;
    }

    public function isMain(): bool
    {
        return $this->isMain;
    }

    public function setIsMain(bool $isMain): static
    {
        $this->isMain = $isMain;
        $this->touch();

        return $this;
    }

    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): static
    {
        $this->isPublic = $isPublic;
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

    public function updateDraft(
        string $title,
        ?string $targetRole,
        string $language,
        bool $isMain,
        bool $isPublic = false,
    ): void {
        $this->title = trim($title);
        $this->targetRole = $this->normalize($targetRole);
        $this->language = $language;
        $this->isMain = $isMain;
        $this->isPublic = $isPublic;
        $this->touch();
    }

    public function unsetMain(): void
    {
        if (!$this->isMain) {
            return;
        }

        $this->isMain = false;
        $this->touch();
    }

    private function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
