<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Mapper\TemplateCatalogMapper;
use App\Repository\TemplateCatalogRepository;
use App\Repository\UserTemplateUnlockRepository;
use Symfony\Component\HttpFoundation\Response;

final class TemplateCatalogService
{
    public function __construct(
        private readonly TemplateCatalogRepository $templates,
        private readonly UserTemplateUnlockRepository $unlocks,
        private readonly TemplateCatalogMapper $mapper,
    ) {
    }

    /**
     * @return array{status: int, message: string, data?: list<array<string, mixed>>, errors?: mixed}
     */
    public function listPublic(?string $type = null): array
    {
        try {
            return [
                'status' => Response::HTTP_OK,
                'message' => 'Templates encontrados',
                'data' => $this->mapper->toPublicList($this->templates->findActive($type)),
            ];
        } catch (\Exception $e) {
            return [
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Ocorreu algum erro inesperado',
                'errors' => $e->getMessage(),
            ];
        }
    }

    /**
     * Catalogo personalizado: can_use / bundle_ref apos desbloqueio.
     *
     * @return array{status: int, message: string, data?: list<array<string, mixed>>, errors?: mixed}
     */
    public function listForAuthenticatedUser(User $user, ?string $type = null): array
    {
        try {
            return [
                'status' => Response::HTTP_OK,
                'message' => 'Templates com o teu estado de desbloqueio',
                'data' => $this->mapper->toUserList($this->templates->findActive($type), $user, $this->unlocks),
            ];
        } catch (\Exception $e) {
            return [
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Ocorreu algum erro inesperado',
                'errors' => $e->getMessage(),
            ];
        }
    }
}
