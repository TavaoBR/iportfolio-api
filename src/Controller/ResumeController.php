<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\RequiresAuth;
use App\DTO\Resume\CreateResumeDTO;
use App\Entity\User;
use App\Exception\Auth\InvalidAuthTokenException;
use App\Middleware\Auth\RequiresAuthMiddleware;
use App\Service\ApiResponseService;
use App\Service\ResumeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
    public function create(Request $request, #[MapRequestPayload] CreateResumeDTO $dto): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->resumes->create($this->authenticatedUser($request), $dto)
        );
    }

    #[Route('/{publicId}', name: 'api_resumes_show', requirements: ['publicId' => '[0-9a-fA-F-]{36}'], methods: ['GET'])]
    public function show(Request $request, string $publicId): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->resumes->show($this->authenticatedUser($request), $publicId)
        );
    }

    #[Route('', name: 'api_resumes_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->resumes->list($this->authenticatedUser($request))
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