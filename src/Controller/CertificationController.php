<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\RequiresAuth;
use App\DTO\Certification\CreateCertificationDTO;
use App\Entity\User;
use App\Exception\Auth\InvalidAuthTokenException;
use App\Middleware\Auth\RequiresAuthMiddleware;
use App\Service\ApiResponseService;
use App\Service\CertificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[RequiresAuth]
#[Route('/api/certifications')]
final class CertificationController extends AbstractController
{
    public function __construct(
        private readonly CertificationService $certifications,
        private readonly ApiResponseService $api,
    ) {
    }

    #[Route('', name: 'api_certifications_create', methods: ['POST'])]
    public function create(Request $request, #[MapRequestPayload] CreateCertificationDTO $dto): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->certifications->create($this->authenticatedUser($request), $dto)
        );
    }

    #[Route('', name: 'api_certifications_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->certifications->list($this->authenticatedUser($request))
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