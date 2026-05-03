<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Profile\UpsertProfileDTO;
use App\Service\ApiResponseService;
use App\Service\Auth\AuthenticatedUserService;
use App\Service\UserProfileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/profile')]
final class ProfileController extends AbstractController
{
    public function __construct(
        private readonly AuthenticatedUserService $authenticatedUsers,
        private readonly UserProfileService $profiles,
        private readonly ApiResponseService $api,
    ) {
    }

    #[Route('', name: 'api_profile_upsert', methods: ['PUT'])]
    public function upsert(Request $request, #[MapRequestPayload] UpsertProfileDTO $dto): JsonResponse
    {
        $user = $this->authenticatedUsers->userFromRequest($request);

        return $this->api->fromServiceResult($this->profiles->upsert($user, $dto));
    }
    #[Route('', name: 'api_profile_show', methods: ['GET'])]
    public function show(Request $request): JsonResponse
    {
        $user = $this->authenticatedUsers->userFromRequest($request);

        return $this->api->fromServiceResult($this->profiles->show($user));
    }
}
