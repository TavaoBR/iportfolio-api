<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\RequiresAuth;
use App\Entity\LoginSession;
use App\Entity\User;
use App\Middleware\Auth\RequiresAuthMiddleware;
use App\Exception\Auth\InvalidAuthTokenException;
use App\Service\ApiResponseService;
use App\Service\Auth\AuthenticatedUserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[RequiresAuth]
final class MeController extends AbstractController
{
    public function __construct(
        private readonly AuthenticatedUserService $authenticatedUsers,
        private readonly ApiResponseService $api,
    ) {
    }

    #[Route('/api/me', name: 'api_me_show', methods: ['GET'])]
    public function show(Request $request): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->authenticatedUsers->meFromAuthenticatedContext(
                $this->authenticatedUser($request),
                $this->loginSession($request),
            )
        );
    }

    private function authenticatedUser(Request $request): User
    {
        $user = $request->attributes->get(RequiresAuthMiddleware::AUTHENTICATED_USER);

        if (!$user instanceof User) {
            throw new InvalidAuthTokenException();
        }

        return $user;
    }

    private function loginSession(Request $request): LoginSession
    {
        $session = $request->attributes->get(RequiresAuthMiddleware::LOGIN_SESSION);

        if (!$session instanceof LoginSession) {
            throw new InvalidAuthTokenException();
        }

        return $session;
    }
}