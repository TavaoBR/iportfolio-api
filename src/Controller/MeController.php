<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\RequiresAuth;
use App\Entity\LoginSession;
use App\Entity\User;
use App\Service\ApiResponseService;
use App\Service\Auth\AuthenticatedUserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    public function show(User $user, LoginSession $session): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->authenticatedUsers->meFromAuthenticatedContext($user, $session),
        );
    }
}
