<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\RequiresAuth;
use App\DTO\ResumeSection\CreateResumeSectionDTO;
use App\DTO\ResumeSection\ReorderResumeSectionsDTO;
use App\DTO\ResumeSection\UpdateResumeSectionDTO;
use App\Entity\User;
use App\Service\ApiResponseService;
use App\Service\ResumeSectionService;
use App\Service\ResumeSectionSuggestionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[RequiresAuth]
#[Route('/api/resumes/{publicId}/sections', requirements: ['publicId' => '[0-9a-fA-F-]{36}'])]
final class ResumeSectionController extends AbstractController
{
    public function __construct(
        private readonly ResumeSectionService $sections,
        private readonly ResumeSectionSuggestionService $sectionSuggestions,
        private readonly ApiResponseService $api,
    ) {
    }

    #[Route('/suggestions', name: 'api_resume_sections_suggestions', methods: ['GET'])]
    public function suggestions(User $user, string $publicId): JsonResponse
    {
        return $this->api->fromServiceResult($this->sectionSuggestions->suggest($user, $publicId));
    }

    #[Route('', name: 'api_resume_sections_list', methods: ['GET'])]
    public function list(User $user, string $publicId): JsonResponse
    {
        return $this->api->fromServiceResult($this->sections->list($user, $publicId));
    }

    #[Route('', name: 'api_resume_sections_create', methods: ['POST'])]
    public function create(User $user, string $publicId, #[MapRequestPayload] CreateResumeSectionDTO $dto): JsonResponse
    {
        return $this->api->fromServiceResult($this->sections->create($user, $publicId, $dto));
    }

    #[Route('/reorder', name: 'api_resume_sections_reorder', methods: ['POST'])]
    public function reorder(User $user, string $publicId, #[MapRequestPayload] ReorderResumeSectionsDTO $dto): JsonResponse
    {
        return $this->api->fromServiceResult($this->sections->reorder($user, $publicId, $dto));
    }

    #[Route('/{sectionId<\d+>}', name: 'api_resume_sections_update', methods: ['PUT', 'PATCH'])]
    public function update(
        User $user,
        string $publicId,
        int $sectionId,
        #[MapRequestPayload] UpdateResumeSectionDTO $dto,
    ): JsonResponse {
        return $this->api->fromServiceResult($this->sections->update($user, $publicId, $sectionId, $dto));
    }

    #[Route('/{sectionId<\d+>}', name: 'api_resume_sections_delete', methods: ['DELETE'])]
    public function delete(User $user, string $publicId, int $sectionId): JsonResponse
    {
        return $this->api->fromServiceResult($this->sections->delete($user, $publicId, $sectionId));
    }
}
