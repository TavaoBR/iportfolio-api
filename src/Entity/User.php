<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\Index(name: 'idx_users_is_active', fields: ['isActive'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private string $email;

    /**
     * @var list<string>
     */
    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private string $password;

    #[ORM\Column(length: 150)]
    private string $name;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $avatar = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $attempts = 0;

    public function __construct(string $name, string $email)
    {
        $this->name = trim($name);
        $this->email = mb_strtolower(trim($email));
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = mb_strtolower(trim($email));
        $this->touch();

        return $this;
    }

    public function updateEmail(string $email): void
    {
        $this->setEmail($email);
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_values(array_unique($roles));
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = array_values(array_unique($roles));
        $this->touch();

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPasswordHash(string $passwordHash): static
    {
        $this->password = $passwordHash;
        $this->touch();

        return $this;
    }

    public function changePasswordHash(string $passwordHash): void
    {
        $this->setPasswordHash($passwordHash);
    }

    public function eraseCredentials(): void
    {
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

    public function updateName(string $name): void
    {
        $this->setName($name);
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): static
    {
        $this->avatar = $avatar !== null ? trim($avatar) : null;
        $this->touch();

        return $this;
    }

    public function updateAvatar(?string $avatar): void
    {
        $this->setAvatar($avatar);
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        $this->touch();

        return $this;
    }

    public function activate(): void
    {
        $this->setIsActive(true);
    }

    public function deactivate(): void
    {
        $this->setIsActive(false);
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getAttempts(): int
    {
        return $this->attempts;
    }

    public function setAttempts(int $attempts): static
    {
        $this->attempts = $attempts;
        $this->touch();

        return $this;
    }

    public function registerFailedLoginAttempt(): void
    {
        $this->attempts++;
        $this->touch();
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}