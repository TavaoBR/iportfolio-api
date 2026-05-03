# Modulo User - Mapper

## Arquivo

```md
src/Mapper/UserMapper.php
```

## Responsabilidade

```md
- Converter User em UserResponseDTO
- Converter User em array seguro
- Impedir retorno de password
- Impedir retorno de dados internos de autenticacao
```

## Codigo

```php
<?php

declare(strict_types=1);

namespace App\Mapper;

use App\DTO\User\UserResponseDTO;
use App\Entity\User;

final class UserMapper
{
    public function toResponseDTO(User $user): UserResponseDTO
    {
        return new UserResponseDTO(
            id: (int) $user->getId(),
            name: $user->getName(),
            email: $user->getEmail(),
            avatar: $user->getAvatar(),
            isActive: $user->isActive(),
            createdAt: $user->getCreatedAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $user->getUpdatedAt()?->format(\DateTimeInterface::ATOM),
        );
    }

    public function toArray(User $user): array
    {
        return $this->toResponseDTO($user)->toArray();
    }
}
```

## Decisao

O mapper fica fora do Controller.

Motivo:

```md
Controller nao deve saber como a entidade vira resposta publica.
```

