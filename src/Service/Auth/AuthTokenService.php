<?php

declare(strict_types=1);

namespace App\Service\Auth;

use App\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class AuthTokenService
{
    public function __construct(
        #[Autowire('%env(AUTH_TOKEN_SALT)%')]
        private readonly string $salt,
        #[Autowire('%env(int:AUTH_TOKEN_TTL_SECONDS)%')]
        private readonly int $ttlSeconds,
    ) {
    }

    public function issue(User $user, \DateTimeImmutable $issuedAt): string
    {
        $payload = $this->base64UrlEncode(json_encode([
            'uid' => $user->getId(),
            'iat' => $issuedAt->getTimestamp(),
            'nonce' => bin2hex(random_bytes(16)),
        ], JSON_THROW_ON_ERROR));

        return $payload.'.'.$this->signature($payload);
    }

    public function expiresAt(\DateTimeImmutable $issuedAt): \DateTimeImmutable
    {
        return $issuedAt->modify('+'.$this->ttlSeconds.' seconds');
    }
    public function hash(string $token): string
    {
        return hash_hmac('sha256', $token, $this->salt);
    }

    public function metadata(string $token): ?array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 2) {
            return null;
        }

        [$payload, $signature] = $parts;

        if (!hash_equals($this->signature($payload), $signature)) {
            return null;
        }

        try {
            $metadata = json_decode($this->base64UrlDecode($payload), true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        if (!isset($metadata['uid'], $metadata['iat']) || !is_int($metadata['uid']) || !is_int($metadata['iat'])) {
            return null;
        }

        if ($metadata['iat'] + $this->ttlSeconds < time()) {
            return null;
        }

        return $metadata;
    }

    private function signature(string $payload): string
    {
        return $this->base64UrlEncode(hash_hmac('sha256', $payload, $this->salt, true));
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): string
    {
        return base64_decode(strtr($value, '-_', '+/'), true) ?: '';
    }
}