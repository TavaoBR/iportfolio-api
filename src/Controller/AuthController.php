<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\RequiresAuth;
use App\DTO\Auth\LoginDTO;
use App\Entity\LoginSession;
use App\Middleware\Auth\RequiresAuthMiddleware;
use App\Exception\Auth\InvalidAuthTokenException;
use App\Service\ApiResponseService;
use App\Service\Auth\AuthService;
use App\Service\Auth\AuthenticatedUserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/auth')]
final class AuthController extends AbstractController
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly AuthenticatedUserService $authenticatedUsers,
        private readonly ApiResponseService $api,
    ) {
    }

    #[Route('/login', name: 'api_auth_login', methods: ['POST'])]
    public function login(#[MapRequestPayload] LoginDTO $dto): JsonResponse
    {
        return $this->api->fromServiceResult($this->auth->login($dto));
    }

    #[RequiresAuth]
    #[Route('/logout', name: 'api_auth_logout', methods: ['POST'])]
    public function logout(Request $request): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->authenticatedUsers->logoutFromAuthenticatedSession($this->loginSession($request))
        );
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