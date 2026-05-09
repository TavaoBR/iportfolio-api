<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CertificationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CertificationRepository::class)]
#[ORM\Table(name: 'certifications')]
#[ORM\Index(name: 'IDX_CERTIFICATION_USER', fields: ['user'])]
final class Certification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(length: 180)]
    private string $name;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $issuer = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $credentialUrl = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $issuedAt = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $expiresAt = null;

    #[ORM\Column]
    private int $sortOrder = 0;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct(User $user, string $name)
    {
        $this->user = $user;
        $this->name = trim($name);
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = trim($name);
        $this->touch();

        return $this;
    }

    public function getIssuer(): ?string
    {
        return $this->issuer;
    }

    public function setIssuer(?string $issuer): static
    {
        $this->issuer = $this->normalize($issuer);
        $this->touch();

        return $this;
    }

    public function getCredentialUrl(): ?string
    {
        return $this->credentialUrl;
    }

    public function setCredentialUrl(?string $credentialUrl): static
    {
        $this->credentialUrl = $this->normalize($credentialUrl);
        $this->touch();

        return $this;
    }

    public function getIssuedAt(): ?\DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function setIssuedAt(?\DateTimeImmutable $issuedAt): static
    {
        $this->issuedAt = $issuedAt;
        $this->touch();

        return $this;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTimeImmutable $expiresAt): static
    {
        $this->expiresAt = $expiresAt;
        $this->touch();

        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): static
    {
        $this->sortOrder = $sortOrder;
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

    public function update(
        string $name,
        ?string $issuer,
        ?string $credentialUrl,
        ?\DateTimeImmutable $issuedAt,
        ?\DateTimeImmutable $expiresAt,
        int $sortOrder,
    ): void {
        $this->name = trim($name);
        $this->issuer = $this->normalize($issuer);
        $this->credentialUrl = $this->normalize($credentialUrl);
        $this->issuedAt = $issuedAt;
        $this->expiresAt = $expiresAt;
        $this->sortOrder = $sortOrder;
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
