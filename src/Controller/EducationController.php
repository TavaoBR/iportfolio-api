<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\RequiresAuth;
use App\DTO\Education\CreateEducationDTO;
use App\DTO\Education\UpdateEducationDTO;
use App\Entity\User;
use App\Service\ApiResponseService;
use App\Service\EducationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[RequiresAuth]
#[Route('/api/educations')]
final class EducationController extends AbstractController
{
    public function __construct(
        private readonly EducationService $educations,
        private readonly ApiResponseService $api,
    ) {
    }

    #[Route('', name: 'api_educations_create', methods: ['POST'])]
    public function create(User $user, #[MapRequestPayload] CreateEducationDTO $dto): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->educations->create($user, $dto)
        );
    }

    #[Route('/{id<\d+>}', name: 'api_educations_update', methods: ['PUT', 'PATCH'])]
    public function update(User $user, int $id, #[MapRequestPayload] UpdateEducationDTO $dto): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->educations->update($user, $id, $dto)
        );
    }

    #[Route('/{id<\d+>}', name: 'api_educations_delete', methods: ['DELETE'])]
    public function delete(User $user, int $id): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->educations->delete($user, $id)
        );
    }

    #[Route('', name: 'api_educations_list', methods: ['GET'])]
    public function list(User $user): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->educations->list($user)
        );
    }
}
