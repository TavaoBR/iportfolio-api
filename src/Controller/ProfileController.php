<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\RequiresAuth;
use App\DTO\Profile\UpsertProfileDTO;
use App\Entity\User;
use App\Service\ApiResponseService;
use App\Service\UserProfileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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

    #[Route('', name: 'api_profile_create', methods: ['POST'])]
    public function create(User $user, #[MapRequestPayload] UpsertProfileDTO $dto): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->profiles->create($user, $dto)
        );
    }

    #[Route('', name: 'api_profile_update', methods: ['PUT', 'PATCH'])]
    public function update(User $user, #[MapRequestPayload] UpsertProfileDTO $dto): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->profiles->update($user, $dto)
        );
    }

    #[Route('', name: 'api_profile_show', methods: ['GET'])]
    public function show(User $user): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->profiles->show($user)
        );
    }
}
