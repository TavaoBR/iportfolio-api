<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PortfolioSiteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PortfolioSiteRepository::class)]
#[ORM\Table(name: 'portfolio_sites')]
#[ORM\UniqueConstraint(name: 'UNIQ_PORTFOLIO_SLUG', fields: ['slug'])]
#[ORM\Index(name: 'IDX_PORTFOLIO_USER', fields: ['user'])]
final class PortfolioSite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(length: 120)]
    private string $slug;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $templateKey = null;

    #[ORM\Column(length: 180)]
    private string $title;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $subtitle = null;

    #[ORM\Column]
    private bool $isPublic = false;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct(User $user, string $slug, string $title)
    {
        $this->user = $user;
        $this->slug = mb_strtolower(trim($slug));
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

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = mb_strtolower(trim($slug));
        $this->touch();

        return $this;
    }

    public function getTemplateKey(): ?string
    {
        return $this->templateKey;
    }

    public function setTemplateKey(?string $templateKey): static
    {
        $this->templateKey = $templateKey !== null ? trim($templateKey) : null;
        $this->touch();

        return $this;
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

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function setSubtitle(?string $subtitle): static
    {
        $this->subtitle = $subtitle !== null ? trim($subtitle) : null;
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

    public function publish(): void
    {
        $this->isPublic = true;
        $this->touch();
    }

    public function update(
        ?string $slug,
        ?string $title,
        ?string $subtitle,
        ?string $templateKey,
        ?bool $isPublic,
    ): void {
        if ($slug !== null) {
            $this->slug = mb_strtolower(trim($slug));
        }
        if ($title !== null) {
            $this->title = trim($title);
        }
        if ($subtitle !== null) {
            $this->subtitle = trim($subtitle) !== '' ? trim($subtitle) : null;
        }
        if ($templateKey !== null) {
            $this->templateKey = trim($templateKey) !== '' ? trim($templateKey) : null;
        }
        if ($isPublic !== null) {
            $this->isPublic = $isPublic;
        }
        $this->touch();
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
