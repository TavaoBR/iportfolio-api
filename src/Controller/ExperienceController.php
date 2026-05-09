<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\RequiresAuth;
use App\DTO\Experience\CreateExperienceDTO;
use App\DTO\Experience\UpdateExperienceDTO;
use App\Entity\User;
use App\Service\ApiResponseService;
use App\Service\ExperienceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[RequiresAuth]
#[Route('/api/experiences')]
final class ExperienceController extends AbstractController
{
    public function __construct(
        private readonly ExperienceService $experiences,
        private readonly ApiResponseService $api,
    ) {
    }

    #[Route('', name: 'api_experiences_create', methods: ['POST'])]
    public function create(User $user, #[MapRequestPayload] CreateExperienceDTO $dto): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->experiences->create($user, $dto)
        );
    }

    #[Route('/{id<\d+>}', name: 'api_experiences_update', methods: ['PUT', 'PATCH'])]
    public function update(User $user, int $id, #[MapRequestPayload] UpdateExperienceDTO $dto): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->experiences->update($user, $id, $dto)
        );
    }

    #[Route('', name: 'api_experiences_list', methods: ['GET'])]
    public function list(User $user): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->experiences->list($user)
        );
    }
}
