<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\RequiresAuth;
use App\DTO\Portfolio\CreatePortfolioSectionDTO;
use App\DTO\Portfolio\ReorderPortfolioSectionsDTO;
use App\DTO\Portfolio\UpdatePortfolioSectionDTO;
use App\Entity\User;
use App\Service\ApiResponseService;
use App\Service\PortfolioSectionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[RequiresAuth]
#[Route('/api/portfolio-sites/{siteId<\d+>}/sections')]
final class PortfolioSectionController extends AbstractController
{
    public function __construct(
        private readonly PortfolioSectionService $sections,
        private readonly ApiResponseService $api,
    ) {
    }

    #[Route('', name: 'api_portfolio_sections_list', methods: ['GET'])]
    public function list(User $user, int $siteId): JsonResponse
    {
        return $this->api->fromServiceResult($this->sections->list($user, $siteId));
    }

    #[Route('', name: 'api_portfolio_sections_create', methods: ['POST'])]
    public function create(User $user, int $siteId, #[MapRequestPayload] CreatePortfolioSectionDTO $dto): JsonResponse
    {
        return $this->api->fromServiceResult($this->sections->create($user, $siteId, $dto));
    }

    #[Route('/reorder', name: 'api_portfolio_sections_reorder', methods: ['POST'])]
    public function reorder(User $user, int $siteId, #[MapRequestPayload] ReorderPortfolioSectionsDTO $dto): JsonResponse
    {
        return $this->api->fromServiceResult($this->sections->reorder($user, $siteId, $dto));
    }

    #[Route('/{sectionId<\d+>}', name: 'api_portfolio_sections_update', methods: ['PUT', 'PATCH'])]
    public function update(
        User $user,
        int $siteId,
        int $sectionId,
        #[MapRequestPayload] UpdatePortfolioSectionDTO $dto,
    ): JsonResponse {
        return $this->api->fromServiceResult($this->sections->update($user, $siteId, $sectionId, $dto));
    }

    #[Route('/{sectionId<\d+>}', name: 'api_portfolio_sections_delete', methods: ['DELETE'])]
    public function delete(User $user, int $siteId, int $sectionId): JsonResponse
    {
        return $this->api->fromServiceResult($this->sections->delete($user, $siteId, $sectionId));
    }
}
