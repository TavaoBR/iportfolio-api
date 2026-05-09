<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\PublicRoute;
use App\Service\ApiResponseService;
use App\Service\TemplateCatalogService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[PublicRoute]
#[Route('/api/templates')]
final class TemplateCatalogController extends AbstractController
{
    public function __construct(
        private readonly TemplateCatalogService $templates,
        private readonly ApiResponseService $api,
    ) {
    }

    #[Route('', name: 'api_templates_list', methods: ['GET'])]
    public function list(#[MapQueryParameter] ?string $type = null): JsonResponse
    {
        return $this->api->fromServiceResult($this->templates->listPublic($type));
    }
}
