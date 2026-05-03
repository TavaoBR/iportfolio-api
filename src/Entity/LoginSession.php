<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\LoginSessionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LoginSessionRepository::class)]
#[ORM\Table(name: 'login_sessions')]
#[ORM\Index(name: 'idx_login_sessions_token_hash', fields: ['tokenHash'])]
#[ORM\Index(name: 'idx_login_sessions_user_id', fields: ['userId'])]
#[ORM\Index(name: 'idx_login_sessions_expire_date_time', fields: ['expireDateTime'])]
final class LoginSession
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private int $userId;

    #[ORM\Column]
    private \DateTimeImmutable $loginDateTime;

    #[ORM\Column]
    private \DateTimeImmutable $expireDateTime;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $ip = null;

    #[ORM\Column(length: 64)]
    private string $tokenHash;

    /**
     * @var array<string, mixed>
     */
    #[ORM\Column(type: 'json')]
    private array $sessionMetadata = [];

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $revokedAt = null;

    /**
     * @param array<string, mixed> $sessionMetadata
     */
    public function __construct(
        int $userId,
        string $tokenHash,
        \DateTimeImmutable $loginDateTime,
        \DateTimeImmutable $expireDateTime,
        ?string $ip,
        array $sessionMetadata,
    ) {
        $this->userId = $userId;
        $this->tokenHash = $tokenHash;
        $this->loginDateTime = $loginDateTime;
        $this->expireDateTime = $expireDateTime;
        $this->ip = $ip;
        $this->sessionMetadata = $sessionMetadata;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getLoginDateTime(): \DateTimeImmutable
    {
        return $this->loginDateTime;
    }

    public function getExpireDateTime(): \DateTimeImmutable
    {
        return $this->expireDateTime;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function getTokenHash(): string
    {
        return $this->tokenHash;
    }

    /**
     * @return array<string, mixed>
     */
    public function getSessionMetadata(): array
    {
        return $this->sessionMetadata;
    }

    public function getRevokedAt(): ?\DateTimeImmutable
    {
        return $this->revokedAt;
    }

    public function setUserId(int $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function setLoginDateTime(\DateTimeImmutable $loginDateTime): static
    {
        $this->loginDateTime = $loginDateTime;

        return $this;
    }

    public function setExpireDateTime(\DateTimeImmutable $expireDateTime): static
    {
        $this->expireDateTime = $expireDateTime;

        return $this;
    }

    public function setIp(?string $ip): static
    {
        $this->ip = $ip !== null ? trim($ip) : null;

        return $this;
    }

    public function setTokenHash(string $tokenHash): static
    {
        $this->tokenHash = $tokenHash;

        return $this;
    }

    /**
     * @param array<string, mixed> $sessionMetadata
     */
    public function setSessionMetadata(array $sessionMetadata): static
    {
        $this->sessionMetadata = $sessionMetadata;

        return $this;
    }

    public function setRevokedAt(?\DateTimeImmutable $revokedAt): static
    {
        $this->revokedAt = $revokedAt;

        return $this;
    }
    public function revoke(\DateTimeImmutable $revokedAt): void
    {
        $this->revokedAt = $revokedAt;
    }

    public function isActive(\DateTimeImmutable $now): bool
    {
        return $this->revokedAt === null && $this->expireDateTime > $now;
    }
}