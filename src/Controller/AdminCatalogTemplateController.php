<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\RequiresAuth;
use App\DTO\Template\CreateCatalogTemplateDTO;
use App\Entity\User;
use App\Service\ApiResponseService;
use App\Service\TemplateCatalogManagementService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[RequiresAuth]
#[Route('/api/admin/catalog/templates', requirements: ['templateKey' => '[a-z0-9_-]+'])]
final class AdminCatalogTemplateController extends AbstractController
{
    public function __construct(
        private readonly TemplateCatalogManagementService $catalog,
        private readonly ApiResponseService $api,
    ) {
    }

    #[Route('', name: 'api_admin_templates_create', methods: ['POST'])]
    public function create(User $user, #[MapRequestPayload] CreateCatalogTemplateDTO $dto): JsonResponse
    {
        if (!$this->isAdmin($user)) {
            return $this->forbidden();
        }

        return $this->api->fromServiceResult($this->catalog->create($dto));
    }

    #[Route('/{templateKey}', name: 'api_admin_templates_patch', methods: ['PATCH'])]
    public function patch(User $user, string $templateKey, Request $request): JsonResponse
    {
        if (!$this->isAdmin($user)) {
            return $this->forbidden();
        }

        $raw = json_decode((string) $request->getContent(), true);

        if (!\is_array($raw)) {
            return new JsonResponse(['message' => 'Corpo JSON invalido ou vazio'], Response::HTTP_BAD_REQUEST);
        }

        return $this->api->fromServiceResult($this->catalog->partialUpdate($templateKey, $raw));
    }

    private function isAdmin(User $user): bool
    {
        return \in_array('ROLE_ADMIN', $user->getRoles(), true);
    }

    private function forbidden(): JsonResponse
    {
        return $this->api->fromServiceResult([
            'status' => Response::HTTP_FORBIDDEN,
            'message' => 'Acesso reservado a administradores',
        ]);
    }
}
