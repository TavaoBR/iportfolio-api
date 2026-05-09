<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\PublicRoute;
use App\Service\ApiResponseService;
use App\Service\PortfolioSiteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[PublicRoute]
#[Route('/api/public')]
final class PublicPortfolioController extends AbstractController
{
    public function __construct(
        private readonly PortfolioSiteService $portfolios,
        private readonly ApiResponseService $api,
    ) {
    }

    #[Route('/portfolio/{slug}', name: 'api_public_portfolio_show', requirements: ['slug' => '[a-z0-9]+(?:-[a-z0-9]+)*'], methods: ['GET'])]
    public function show(string $slug): JsonResponse
    {
        return $this->api->fromServiceResult($this->portfolios->showPublishedBySlug($slug));
    }
}
