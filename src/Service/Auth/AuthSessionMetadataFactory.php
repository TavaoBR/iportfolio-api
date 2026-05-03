<?php

declare(strict_types=1);

namespace App\Service\Auth;

use App\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;

final class AuthSessionMetadataFactory
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly AuthTokenService $tokens,
        #[Autowire('%env(AUTH_TOKEN_HEADER)%')]
        private readonly string $tokenHeader,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function create(User $user, \DateTimeImmutable $issuedAt): array
    {
        $request = $this->requestStack->getCurrentRequest();
        $expiresAt = $this->tokens->expiresAt($issuedAt);

        return [
            'user_id' => $user->getId(),
            'email' => $user->getEmail(),
            'ip' => $request?->getClientIp(),
            'token_header' => $this->tokenHeader,
            'issued_at' => $issuedAt->format(DATE_ATOM),
            'expires_at' => $expiresAt->format(DATE_ATOM),
        ];
    }
}