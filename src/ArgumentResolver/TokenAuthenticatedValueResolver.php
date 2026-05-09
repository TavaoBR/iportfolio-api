<?php

declare(strict_types=1);

namespace App\ArgumentResolver;

use App\Entity\LoginSession;
use App\Entity\User;
use App\Middleware\Auth\RequiresAuthMiddleware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Injeta {@see User} e {@see LoginSession} a partir dos atributos de request definidos pelo
 * {@see RequiresAuthMiddleware}.
 */
final class TokenAuthenticatedValueResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $type = $argument->getType();

        if (User::class === $type) {
            $value = $request->attributes->get(RequiresAuthMiddleware::AUTHENTICATED_USER);

            return $value instanceof User ? [$value] : [];
        }

        if (LoginSession::class === $type) {
            $value = $request->attributes->get(RequiresAuthMiddleware::LOGIN_SESSION);

            return $value instanceof LoginSession ? [$value] : [];
        }

        return [];
    }
}
