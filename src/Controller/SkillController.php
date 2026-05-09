<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\RequiresAuth;
use App\DTO\Skill\CreateSkillDTO;
use App\DTO\Skill\UpdateSkillDTO;
use App\Entity\User;
use App\Service\ApiResponseService;
use App\Service\SkillService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[RequiresAuth]
#[Route('/api/skills')]
final class SkillController extends AbstractController
{
    public function __construct(
        private readonly SkillService $skills,
        private readonly ApiResponseService $api,
    ) {
    }

    #[Route('', name: 'api_skills_create', methods: ['POST'])]
    public function create(User $user, #[MapRequestPayload] CreateSkillDTO $dto): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->skills->create($user, $dto)
        );
    }

    #[Route('/{id<\d+>}', name: 'api_skills_update', methods: ['PUT', 'PATCH'])]
    public function update(User $user, int $id, #[MapRequestPayload] UpdateSkillDTO $dto): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->skills->update($user, $id, $dto)
        );
    }

    #[Route('', name: 'api_skills_list', methods: ['GET'])]
    public function list(User $user): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->skills->list($user)
        );
    }
}

