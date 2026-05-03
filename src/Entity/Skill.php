<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SkillRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SkillRepository::class)]
#[ORM\Table(name: 'skills')]
#[ORM\Index(name: 'IDX_SKILL_USER', fields: ['user'])]
final class Skill
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(length: 120)]
    private string $name;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $category = null;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $level = null;

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

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getCategory(): ?string { return $this->category; }
    public function getLevel(): ?string { return $this->level; }
    public function getSortOrder(): int { return $this->sortOrder; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }

    public function getUser(): User { return $this->user; }

    public function setName(string $name): static
    {
        $this->name = trim($name);
        $this->touch();

        return $this;
    }

    public function setCategory(?string $category): static
    {
        $this->category = $this->normalize($category);
        $this->touch();

        return $this;
    }

    public function setLevel(?string $level): static
    {
        $this->level = $this->normalize($level);
        $this->touch();

        return $this;
    }

    public function setSortOrder(int $sortOrder): static
    {
        $this->sortOrder = $sortOrder;
        $this->touch();

        return $this;
    }

    public function update(string $name, ?string $category, ?string $level, int $sortOrder): void
    {
        $this->name = trim($name);
        $this->category = $this->normalize($category);
        $this->level = $this->normalize($level);
        $this->sortOrder = $sortOrder;
        $this->updatedAt = new \DateTimeImmutable();
    }

    private function normalize(?string $value): ?string
    {
        if ($value === null) { return null; }
        $value = trim($value);
        return $value !== '' ? $value : null;
    }
    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}