<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\RequiresAuth;
use App\DTO\Project\CreateProjectDTO;
use App\DTO\Project\UpdateProjectDTO;
use App\Entity\User;
use App\Service\ApiResponseService;
use App\Service\ProjectService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[RequiresAuth]
#[Route('/api/projects')]
final class ProjectController extends AbstractController
{
    public function __construct(
        private readonly ProjectService $projects,
        private readonly ApiResponseService $api,
    ) {
    }

    #[Route('', name: 'api_projects_create', methods: ['POST'])]
    public function create(User $user, #[MapRequestPayload] CreateProjectDTO $dto): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->projects->create($user, $dto)
        );
    }

    #[Route('/{id<\d+>}', name: 'api_projects_update', methods: ['PUT', 'PATCH'])]
    public function update(User $user, int $id, #[MapRequestPayload] UpdateProjectDTO $dto): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->projects->update($user, $id, $dto)
        );
    }

    #[Route('/{id<\d+>}', name: 'api_projects_delete', methods: ['DELETE'])]
    public function delete(User $user, int $id): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->projects->delete($user, $id)
        );
    }

    #[Route('', name: 'api_projects_list', methods: ['GET'])]
    public function list(User $user): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->projects->list($user)
        );
    }
}
