<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\RequiresAuth;
use App\DTO\Profile\UpsertProfileDTO;
use App\Entity\User;
use App\Middleware\Auth\RequiresAuthMiddleware;
use App\Exception\Auth\InvalidAuthTokenException;
use App\Service\ApiResponseService;
use App\Service\UserProfileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[RequiresAuth]
#[Route('/api/profile')]
final class ProfileController extends AbstractController
{
    public function __construct(
        private readonly UserProfileService $profiles,
        private readonly ApiResponseService $api,
    ) {
    }

    #[Route('', name: 'api_profile_upsert', methods: ['PUT'])]
    public function upsert(Request $request, #[MapRequestPayload] UpsertProfileDTO $dto): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->profiles->upsert($this->authenticatedUser($request), $dto)
        );
    }

    #[Route('', name: 'api_profile_show', methods: ['GET'])]
    public function show(Request $request): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->profiles->show($this->authenticatedUser($request))
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
}