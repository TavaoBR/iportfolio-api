<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserProfileRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserProfileRepository::class)]
#[ORM\Table(name: 'user_profiles')]
#[ORM\UniqueConstraint(name: 'UNIQ_USER_PROFILE_USER', fields: ['user'])]
final class UserProfile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $headline = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $bio = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $state = null;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $linkedinUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $githubUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $websiteUrl = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct(User $user)
    {
        $this->user = $user;
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

    public function getHeadline(): ?string
    {
        return $this->headline;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function getLinkedinUrl(): ?string
    {
        return $this->linkedinUrl;
    }

    public function getGithubUrl(): ?string
    {
        return $this->githubUrl;
    }

    public function getWebsiteUrl(): ?string
    {
        return $this->websiteUrl;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setHeadline(?string $headline): static
    {
        $this->headline = $this->normalize($headline);
        $this->touch();

        return $this;
    }

    public function setBio(?string $bio): static
    {
        $this->bio = $this->normalize($bio);
        $this->touch();

        return $this;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $this->normalize($phone);
        $this->touch();

        return $this;
    }

    public function setCity(?string $city): static
    {
        $this->city = $this->normalize($city);
        $this->touch();

        return $this;
    }

    public function setState(?string $state): static
    {
        $this->state = $this->normalize($state);
        $this->touch();

        return $this;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $this->normalize($country);
        $this->touch();

        return $this;
    }

    public function setLinkedinUrl(?string $linkedinUrl): static
    {
        $this->linkedinUrl = $this->normalize($linkedinUrl);
        $this->touch();

        return $this;
    }

    public function setGithubUrl(?string $githubUrl): static
    {
        $this->githubUrl = $this->normalize($githubUrl);
        $this->touch();

        return $this;
    }

    public function setWebsiteUrl(?string $websiteUrl): static
    {
        $this->websiteUrl = $this->normalize($websiteUrl);
        $this->touch();

        return $this;
    }
    public function update(
        ?string $headline,
        ?string $bio,
        ?string $phone,
        ?string $city,
        ?string $state,
        ?string $country,
        ?string $linkedinUrl,
        ?string $githubUrl,
        ?string $websiteUrl,
    ): void {
        $this->headline = $this->normalize($headline);
        $this->bio = $this->normalize($bio);
        $this->phone = $this->normalize($phone);
        $this->city = $this->normalize($city);
        $this->state = $this->normalize($state);
        $this->country = $this->normalize($country);
        $this->linkedinUrl = $this->normalize($linkedinUrl);
        $this->githubUrl = $this->normalize($githubUrl);
        $this->websiteUrl = $this->normalize($websiteUrl);
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