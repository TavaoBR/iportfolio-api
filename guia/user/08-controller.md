# Modulo User - Controller

## Objetivo

Controller expõe endpoints HTTP e deve ser fino.

Ele nao contem regra de negocio.

## Arquivo

```md
src/Controller/UserController.php
```

## Responsabilidades

```md
- Receber request
- Receber DTO validado
- Chamar UserService
- Retornar JsonResponse padronizado
```

Controller nao deve:

```md
- Consultar repository
- Fazer hash de senha
- Salvar arquivo
- Montar regra de negocio
- Fazer try/catch de dominio
- Decidir status code
```

## Codigo completo

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\User\CreateUserDTO;
use App\DTO\User\UpdateUserDTO;
use App\Service\ApiResponseService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

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
        return $this->api->fromServiceResult(
            $this->users->create($dto)
        );
    }

    #[Route('/{id<\d+>}', name: 'api_users_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->users->show($id)
        );
    }

    #[Route('/{id<\d+>}', name: 'api_users_update', methods: ['PUT', 'PATCH'])]
    public function update(int $id, #[MapRequestPayload] UpdateUserDTO $dto): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->users->update($id, $dto)
        );
    }

    #[Route('/{id<\d+>}/activate', name: 'api_users_activate', methods: ['PATCH'])]
    public function activate(int $id): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->users->activate($id)
        );
    }

    #[Route('/{id<\d+>}/deactivate', name: 'api_users_deactivate', methods: ['PATCH'])]
    public function deactivate(int $id): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->users->deactivate($id)
        );
    }
}
```

## Endpoints iniciais

```md
POST /api/users
GET /api/users/{id}
PUT /api/users/{id}
PATCH /api/users/{id}
PATCH /api/users/{id}/activate
PATCH /api/users/{id}/deactivate
```

## Endpoints futuros

Depois do modulo Auth:

```md
POST /api/auth/register
GET /api/auth/me
PUT /api/me
```

## Decisao

O endpoint `POST /api/users` pode existir no inicio para desenvolvimento.

Quando Auth estiver pronto, cadastro publico deve ficar em:

```md
POST /api/auth/register
```

## Exemplo de response

```json
{
  "message": "Conta criada com sucesso",
  "data": {
    "id": 1,
    "name": "Gustavo Oliveira",
    "email": "gustavo@email.com",
    "avatar": "uploads/avatars/avatar_abc123.png",
    "is_active": true,
    "created_at": "2026-05-03T08:00:00-03:00",
    "updated_at": null
  }
}
```