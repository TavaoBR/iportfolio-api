<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PortfolioSectionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PortfolioSectionRepository::class)]
#[ORM\Table(name: 'portfolio_sections')]
#[ORM\Index(name: 'IDX_PORTFOLIO_SECTIONS_SITE', fields: ['portfolioSite'])]
final class PortfolioSection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PortfolioSite::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private PortfolioSite $portfolioSite;

    #[ORM\Column(length: 32)]
    private string $sectionType;

    #[ORM\Column(length: 32)]
    private string $layoutType;

    #[ORM\Column]
    private int $position = 0;

    #[ORM\Column]
    private bool $isVisible = true;

    /**
     * @var array<string, mixed>|null
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $settingsJson = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct(PortfolioSite $portfolioSite, string $sectionType, string $layoutType, int $position)
    {
        $this->portfolioSite = $portfolioSite;
        $this->sectionType = trim($sectionType);
        $this->layoutType = trim($layoutType);
        $this->position = $position;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPortfolioSite(): PortfolioSite
    {
        return $this->portfolioSite;
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

    public function getLayoutType(): string
    {
        return $this->layoutType;
    }

    public function setLayoutType(string $layoutType): static
    {
        $this->layoutType = trim($layoutType);
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

    /**
     * @return array<string, mixed>|null
     */
    public function getSettingsJson(): ?array
    {
        return $this->settingsJson;
    }

    /**
     * @param array<string, mixed>|null $settingsJson
     */
    public function setSettingsJson(?array $settingsJson): static
    {
        $this->settingsJson = $settingsJson;
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
        ?string $layoutType,
        ?int $position,
        ?bool $isVisible,
        ?array $settingsJson,
    ): void {
        if ($sectionType !== null) {
            $this->sectionType = trim($sectionType);
        }
        if ($layoutType !== null) {
            $this->layoutType = trim($layoutType);
        }
        if ($position !== null) {
            $this->position = $position;
        }
        if ($isVisible !== null) {
            $this->isVisible = $isVisible;
        }
        if ($settingsJson !== null) {
            $this->settingsJson = $settingsJson;
        }
        $this->touch();
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
