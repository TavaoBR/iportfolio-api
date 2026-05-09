<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TemplateCatalogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TemplateCatalogRepository::class)]
#[ORM\Table(name: 'templates')]
final class TemplateCatalogItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private string $name;

    #[ORM\Column(length: 64)]
    private string $templateKey;

    #[ORM\Column(length: 20)]
    private string $type;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $previewImage = null;

    #[ORM\Column(length: 2048, nullable: true)]
    private ?string $previewUrl = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $bundleRef = null;

    /**
     * Esquema opcional (slots/labels mapeados a secções) para o frontend e autocomplete.
     *
     * @var array<string, mixed>|null
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $definitionJson = null;

    #[ORM\Column]
    private bool $isPremium = false;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $premiumPrice = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public static function createNew(
        string $name,
        string $templateKey,
        string $type,
        bool $isPremium = false,
        ?string $previewImage = null,
        ?string $previewUrl = null,
        ?string $bundleRef = null,
        ?array $definitionJson = null,
        ?string $premiumPrice = null,
    ): self {
        $e = new self();
        $e->name = trim($name);
        $e->templateKey = mb_strtolower(trim($templateKey));
        $e->type = trim($type);
        $e->isPremium = $isPremium;
        $e->previewImage = $previewImage !== null ? trim($previewImage) : null;
        $e->previewUrl = $previewUrl !== null ? trim($previewUrl) : null;
        $e->bundleRef = $bundleRef !== null ? trim($bundleRef) : null;
        $e->definitionJson = $definitionJson;
        $e->premiumPrice = self::normalizeMoneyOrNull($premiumPrice);
        $e->createdAt = new \DateTimeImmutable();

        return $e;
    }

    /**
     * @param array<string, mixed>|null $definitionJson somente quando $replaceDefinitionSchema = true (null pode limpar o JSON).
     */
    public function applyAdminUpdate(
        ?string $name,
        ?string $type,
        ?bool $isPremium,
        ?bool $isActive,
        ?string $previewImage,
        ?string $previewUrl,
        ?string $bundleRef,
        bool $replaceDefinitionSchema,
        ?array $definitionJson,
        bool $replacePremiumPrice,
        ?string $premiumPrice,
    ): void {
        if ($name !== null) {
            $this->name = trim($name);
        }
        if ($type !== null) {
            $this->type = trim($type);
        }
        if ($isPremium !== null) {
            $this->isPremium = $isPremium;
        }
        if ($isActive !== null) {
            $this->isActive = $isActive;
        }
        if ($previewImage !== null) {
            $this->previewImage = trim($previewImage) !== '' ? trim($previewImage) : null;
        }
        if ($previewUrl !== null) {
            $this->previewUrl = trim($previewUrl) !== '' ? trim($previewUrl) : null;
        }
        if ($bundleRef !== null) {
            $this->bundleRef = trim($bundleRef) !== '' ? trim($bundleRef) : null;
        }
        if ($replaceDefinitionSchema) {
            $this->definitionJson = $definitionJson;
        }
        if ($replacePremiumPrice) {
            $this->premiumPrice = self::normalizeMoneyOrNull($premiumPrice);
        }
        $this->touch();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTemplateKey(): string
    {
        return $this->templateKey;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getPreviewImage(): ?string
    {
        return $this->previewImage;
    }

    public function getPreviewUrl(): ?string
    {
        return $this->previewUrl;
    }

    public function getBundleRef(): ?string
    {
        return $this->bundleRef;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getDefinitionJson(): ?array
    {
        return $this->definitionJson;
    }

    public function isPremium(): bool
    {
        return $this->isPremium;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getPremiumPrice(): ?string
    {
        return $this->premiumPrice;
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

    private static function normalizeMoneyOrNull(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $trim = trim($value);
        if ($trim === '') {
            return null;
        }
        if (!is_numeric($trim)) {
            return null;
        }

        return number_format((float) $trim, 2, '.', '');
    }
}
