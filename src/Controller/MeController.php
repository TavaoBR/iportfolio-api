<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\ApiResponseService;
use App\Service\Auth\AuthenticatedUserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class MeController extends AbstractController
{
    public function __construct(
        private readonly AuthenticatedUserService $authenticatedUsers,
        private readonly ApiResponseService $api,
    ) {
    }

    #[Route('/api/me', name: 'api_me_show', methods: ['GET'])]
    public function show(Request $request): JsonResponse
    {
        return $this->api->fromServiceResult($this->authenticatedUsers->me($request));
    }
}