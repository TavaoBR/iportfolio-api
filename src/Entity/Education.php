<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\EducationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EducationRepository::class)]
#[ORM\Table(name: 'educations')]
#[ORM\Index(name: 'IDX_EDUCATION_USER', fields: ['user'])]
final class Education
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(length: 180)]
    private string $institution;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $degree = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $fieldOfStudy = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $endDate = null;

    #[ORM\Column]
    private bool $isCurrent = false;

    #[ORM\Column]
    private int $sortOrder = 0;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct(User $user, string $institution)
    {
        $this->user = $user;
        $this->institution = trim($institution);
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

    public function getInstitution(): string
    {
        return $this->institution;
    }

    public function setInstitution(string $institution): static
    {
        $this->institution = trim($institution);
        $this->touch();

        return $this;
    }

    public function getDegree(): ?string
    {
        return $this->degree;
    }

    public function setDegree(?string $degree): static
    {
        $this->degree = $this->normalize($degree);
        $this->touch();

        return $this;
    }

    public function getFieldOfStudy(): ?string
    {
        return $this->fieldOfStudy;
    }

    public function setFieldOfStudy(?string $fieldOfStudy): static
    {
        $this->fieldOfStudy = $this->normalize($fieldOfStudy);
        $this->touch();

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $this->normalize($description);
        $this->touch();

        return $this;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeImmutable $startDate): static
    {
        $this->startDate = $startDate;
        $this->touch();

        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeImmutable $endDate): static
    {
        $this->endDate = $this->isCurrent ? null : $endDate;
        $this->touch();

        return $this;
    }

    public function isCurrent(): bool
    {
        return $this->isCurrent;
    }

    public function setIsCurrent(bool $isCurrent): static
    {
        $this->isCurrent = $isCurrent;

        if ($isCurrent) {
            $this->endDate = null;
        }

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
        string $institution,
        ?string $degree,
        ?string $fieldOfStudy,
        ?string $description,
        ?\DateTimeImmutable $startDate,
        ?\DateTimeImmutable $endDate,
        bool $isCurrent,
        int $sortOrder,
    ): void {
        $this->institution = trim($institution);
        $this->degree = $this->normalize($degree);
        $this->fieldOfStudy = $this->normalize($fieldOfStudy);
        $this->description = $this->normalize($description);
        $this->startDate = $startDate;
        $this->endDate = $isCurrent ? null : $endDate;
        $this->isCurrent = $isCurrent;
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
