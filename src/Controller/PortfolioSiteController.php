<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\RequiresAuth;
use App\DTO\Portfolio\CreatePortfolioSiteDTO;
use App\DTO\Portfolio\UpdatePortfolioSiteDTO;
use App\Entity\User;
use App\Service\ApiResponseService;
use App\Service\PortfolioSiteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[RequiresAuth]
#[Route('/api/portfolio-sites')]
final class PortfolioSiteController extends AbstractController
{
    public function __construct(
        private readonly PortfolioSiteService $portfolios,
        private readonly ApiResponseService $api,
    ) {
    }

    #[Route('', name: 'api_portfolio_sites_list', methods: ['GET'])]
    public function list(User $user): JsonResponse
    {
        return $this->api->fromServiceResult($this->portfolios->list($user));
    }

    #[Route('', name: 'api_portfolio_sites_create', methods: ['POST'])]
    public function create(User $user, #[MapRequestPayload] CreatePortfolioSiteDTO $dto): JsonResponse
    {
        return $this->api->fromServiceResult($this->portfolios->create($user, $dto));
    }

    #[Route('/{id<\d+>}', name: 'api_portfolio_sites_show', methods: ['GET'])]
    public function show(User $user, int $id): JsonResponse
    {
        return $this->api->fromServiceResult($this->portfolios->show($user, $id));
    }

    #[Route('/{id<\d+>}', name: 'api_portfolio_sites_update', methods: ['PUT', 'PATCH'])]
    public function update(User $user, int $id, #[MapRequestPayload] UpdatePortfolioSiteDTO $dto): JsonResponse
    {
        return $this->api->fromServiceResult($this->portfolios->update($user, $id, $dto));
    }

    #[Route('/{id<\d+>}', name: 'api_portfolio_sites_delete', methods: ['DELETE'])]
    public function delete(User $user, int $id): JsonResponse
    {
        return $this->api->fromServiceResult($this->portfolios->delete($user, $id));
    }

    #[Route('/{id<\d+>}/publish', name: 'api_portfolio_sites_publish', methods: ['POST'])]
    public function publish(User $user, int $id): JsonResponse
    {
        return $this->api->fromServiceResult($this->portfolios->publish($user, $id));
    }
}
