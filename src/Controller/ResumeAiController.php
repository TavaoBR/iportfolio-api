<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\RequiresAuth;
use App\DTO\AI\AiCompareJobDTO;
use App\Entity\User;
use App\Service\ApiResponseService;
use App\Service\ResumeAiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[RequiresAuth]
#[Route('/api/resumes/{publicId}/ai', requirements: ['publicId' => '[0-9a-fA-F-]{36}'])]
final class ResumeAiController extends AbstractController
{
    public function __construct(
        private readonly ResumeAiService $ai,
        private readonly ApiResponseService $api,
    ) {
    }

    #[Route('/analyze', name: 'api_resume_ai_analyze', methods: ['POST'])]
    public function analyze(User $user, string $publicId): JsonResponse
    {
        return $this->api->fromServiceResult($this->ai->enqueueAnalyze($user, $publicId));
    }

    #[Route('/optimize', name: 'api_resume_ai_optimize', methods: ['POST'])]
    public function optimize(User $user, string $publicId): JsonResponse
    {
        return $this->api->fromServiceResult($this->ai->enqueueOptimize($user, $publicId));
    }

    #[Route('/compare-job', name: 'api_resume_ai_compare_job', methods: ['POST'])]
    public function compareJob(User $user, string $publicId, #[MapRequestPayload] AiCompareJobDTO $dto): JsonResponse
    {
        return $this->api->fromServiceResult($this->ai->enqueueCompareJob($user, $publicId, $dto));
    }

    #[Route('/analyses', name: 'api_resume_ai_analyses_list', methods: ['GET'])]
    public function list(User $user, string $publicId): JsonResponse
    {
        return $this->api->fromServiceResult($this->ai->list($user, $publicId));
    }

    #[Route('/analyses/{analysisId<\d+>}', name: 'api_resume_ai_analyses_show', methods: ['GET'])]
    public function show(User $user, string $publicId, int $analysisId): JsonResponse
    {
        return $this->api->fromServiceResult($this->ai->show($user, $publicId, $analysisId));
    }
}
