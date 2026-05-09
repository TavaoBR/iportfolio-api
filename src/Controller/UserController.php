<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\PublicRoute;
use App\DTO\User\CreateUserDTO;
use App\DTO\User\UpdateUserDTO;
use App\Service\ApiResponseService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[PublicRoute]
#[Route('/api/users')]
final class UserController extends AbstractController
{
    public function __construct(
        private readonly UserService $users,
        private readonly ApiResponseService $api,
    ) {
    }

    #[Route('', name: 'api_users_create', methods: ['POST'])]
    public function create(#[MapRequestPayload] CreateUserDTO $dto): JsonResponse
    {
        return $this->api->fromServiceResult($this->users->create($dto));
    }

    #[Route('/{id<\d+>}', name: 'api_users_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        return $this->api->fromServiceResult($this->users->show($id));
    }
    #[Route('/{id<\d+>}', name: 'api_users_update', methods: ['PUT', 'PATCH'])]
    public function update(int $id, #[MapRequestPayload] UpdateUserDTO $dto): JsonResponse
    {
        return $this->api->fromServiceResult($this->users->update($id, $dto));
    }
    #[Route('/{id<\d+>}/activate', name: 'api_users_activate', methods: ['PATCH'])]
    public function activate(int $id): JsonResponse
    {
        return $this->api->fromServiceResult($this->users->activate($id));
    }

    #[Route('/{id<\d+>}/deactivate', name: 'api_users_deactivate', methods: ['PATCH'])]
    public function deactivate(int $id): JsonResponse
    {
        return $this->api->fromServiceResult($this->users->deactivate($id));
    }
}

