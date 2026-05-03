# Modulo User - Exceptions e ApiResponse

## Objetivo

Padronizar erros conhecidos por Exceptions proprias e converter retornos dos Services em JSON.

O body da API nao deve ter `success`.

## Estrutura

```md
src/Exception/User/UserAlreadyExistsException.php
src/Exception/User/UserNotFoundException.php
src/Exception/User/InvalidAvatarException.php
src/Exception/User/AvatarUploadException.php
src/Exception/User/EmptyPayloadException.php
src/Service/ApiResponseService.php
```

## Exceptions

## UserAlreadyExistsException

```php
<?php

declare(strict_types=1);

namespace App\Exception\User;

final class UserAlreadyExistsException extends \RuntimeException
{
}
```

## UserNotFoundException

```php
<?php

declare(strict_types=1);

namespace App\Exception\User;

final class UserNotFoundException extends \RuntimeException
{
}
```

## InvalidAvatarException

```php
<?php

declare(strict_types=1);

namespace App\Exception\User;

final class InvalidAvatarException extends \RuntimeException
{
}
```

## AvatarUploadException

```php
<?php

declare(strict_types=1);

namespace App\Exception\User;

final class AvatarUploadException extends \RuntimeException
{
}
```

## EmptyPayloadException

```php
<?php

declare(strict_types=1);

namespace App\Exception\User;

final class EmptyPayloadException extends \RuntimeException
{
}
```

## Mapa de status

```md
UserAlreadyExistsException -> 409
UserNotFoundException -> 404
InvalidAvatarException -> 422
EmptyPayloadException -> 422
AvatarUploadException -> 500
Exception inesperada -> 500
```

## ApiResponseService

Arquivo:

```md
src/Service/ApiResponseService.php
```

```php
<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;

final class ApiResponseService
{
    /**
     * @param array{
     *     status: int,
     *     message: string,
     *     data?: mixed,
     *     errors?: mixed
     * } $result
     */
    public function fromServiceResult(array $result): JsonResponse
    {
        $status = $result['status'];

        $body = [
            'message' => $result['message'],
        ];

        if (array_key_exists('data', $result)) {
            $body['data'] = $result['data'];
        }

        if (array_key_exists('errors', $result)) {
            $body['errors'] = $result['errors'];
        }

        return new JsonResponse($body, $status);
    }
}
```

## Padrao interno do Service

Sucesso sem dados:

```php
return [
    'status' => 201,
    'message' => 'Conta criada com sucesso',
];
```

Sucesso com dados:

```php
return [
    'status' => 200,
    'message' => 'Usuario encontrado com sucesso',
    'data' => $data,
];
```

Erro conhecido:

```php
return [
    'status' => 409,
    'message' => 'Usuario ja cadastrado',
];
```

Erro com detalhes:

```php
return [
    'status' => 500,
    'message' => 'Ocorreu algum erro inesperado',
    'errors' => $e->getMessage(),
];
```

## Padrao HTTP final

Sucesso:

```json
{
  "message": "Conta criada com sucesso",
  "data": {
    "id": 1,
    "name": "Gustavo Oliveira"
  }
}
```

Erro:

```json
{
  "message": "Usuario ja cadastrado"
}
```

Erro com detalhes:

```json
{
  "message": "Ocorreu algum erro inesperado",
  "errors": "Detalhe tecnico do erro"
}
```

## Regra oficial

```md
Nao usar success true/false no body da resposta.
O sucesso ou erro e indicado pelo HTTP status code.
```