<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\RequiresAuth;
use App\DTO\Certification\CreateCertificationDTO;
use App\DTO\Certification\UpdateCertificationDTO;
use App\Entity\User;
use App\Service\ApiResponseService;
use App\Service\CertificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    public function create(User $user, #[MapRequestPayload] CreateCertificationDTO $dto): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->certifications->create($user, $dto)
        );
    }

    #[Route('/{id<\d+>}', name: 'api_certifications_update', methods: ['PUT', 'PATCH'])]
    public function update(User $user, int $id, #[MapRequestPayload] UpdateCertificationDTO $dto): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->certifications->update($user, $id, $dto)
        );
    }

    #[Route('/{id<\d+>}', name: 'api_certifications_delete', methods: ['DELETE'])]
    public function delete(User $user, int $id): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->certifications->delete($user, $id)
        );
    }

    #[Route('', name: 'api_certifications_list', methods: ['GET'])]
    public function list(User $user): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->certifications->list($user)
        );
    }
}
