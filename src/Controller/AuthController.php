<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Auth\LoginDTO;
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
    #[Route('/logout', name: 'api_auth_logout', methods: ['POST'])]
    public function logout(Request $request): JsonResponse
    {
        return $this->api->fromServiceResult($this->authenticatedUsers->logout($request));
    }
}
