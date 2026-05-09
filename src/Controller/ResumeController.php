<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\RequiresAuth;
use App\DTO\Resume\CreateResumeDTO;
use App\DTO\Resume\UpdateResumeDTO;
use App\Entity\User;
use App\Service\ApiResponseService;
use App\Service\ResumeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[RequiresAuth]
#[Route('/api/resumes')]
final class ResumeController extends AbstractController
{
    public function __construct(
        private readonly ResumeService $resumes,
        private readonly ApiResponseService $api,
    ) {
    }

    #[Route('', name: 'api_resumes_create', methods: ['POST'])]
    public function create(User $user, #[MapRequestPayload] CreateResumeDTO $dto): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->resumes->create($user, $dto)
        );
    }

    #[Route('/{publicId}', name: 'api_resumes_show', requirements: ['publicId' => '[0-9a-fA-F-]{36}'], methods: ['GET'])]
    public function show(User $user, string $publicId): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->resumes->show($user, $publicId)
        );
    }

    #[Route('/{publicId}', name: 'api_resumes_update', requirements: ['publicId' => '[0-9a-fA-F-]{36}'], methods: ['PUT', 'PATCH'])]
    public function update(User $user, string $publicId, #[MapRequestPayload] UpdateResumeDTO $dto): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->resumes->update($user, $publicId, $dto)
        );
    }

    #[Route('', name: 'api_resumes_list', methods: ['GET'])]
    public function list(User $user): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->resumes->list($user)
        );
    }

    #[Route('/{publicId}', name: 'api_resumes_delete', requirements: ['publicId' => '[0-9a-fA-F-]{36}'], methods: ['DELETE'])]
    public function delete(User $user, string $publicId): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->resumes->delete($user, $publicId)
        );
    }
}

