# Modulo User - DTOs

## Objetivo

DTOs definem o contrato de entrada e saida da API.

Eles evitam expor `Entity` diretamente e deixam claro quais campos cada endpoint aceita.

## Estrutura

```md
src/DTO/User/CreateUserDTO.php
src/DTO/User/UpdateUserDTO.php
src/DTO/User/UserResponseDTO.php
```

## CreateUserDTO

O avatar deve existir no cadastro como opcional.

Motivo:

```md
O usuario pode criar conta sem avatar.
Se enviar avatar no cadastro, o backend ja valida e salva o arquivo.
```

Arquivo:

```md
src/DTO/User/CreateUserDTO.php
```

```php
<?php

declare(strict_types=1);

namespace App\DTO\User;

use App\Validator\Base64Image;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateUserDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'O nome e obrigatorio')]
        #[Assert\Length(max: 150, maxMessage: 'O nome deve ter no maximo {{ limit }} caracteres')]
        public string $name,

        #[Assert\NotBlank(message: 'O email e obrigatorio')]
        #[Assert\Email(message: 'Informe um email valido')]
        #[Assert\Length(max: 180, maxMessage: 'O email deve ter no maximo {{ limit }} caracteres')]
        public string $email,

        #[Assert\NotBlank(message: 'A senha e obrigatoria')]
        #[Assert\Length(min: 8, max: 72, minMessage: 'A senha deve ter pelo menos {{ limit }} caracteres')]
        #[Assert\Regex(
            pattern: '/^(?=.*[A-Za-z])(?=.*\d).+$/',
            message: 'A senha deve conter letras e numeros'
        )]
        public string $password,

        #[Base64Image(required: false, maxSizeInMb: 2)]
        public ?string $avatar = null,
    ) {
    }
}
```

## UpdateUserDTO

Arquivo:

```md
src/DTO/User/UpdateUserDTO.php
```

```php
<?php

declare(strict_types=1);

namespace App\DTO\User;

use App\Validator\Base64Image;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateUserDTO
{
    public function __construct(
        #[Assert\Length(max: 150, maxMessage: 'O nome deve ter no maximo {{ limit }} caracteres')]
        public ?string $name = null,

        #[Assert\Email(message: 'Informe um email valido')]
        #[Assert\Length(max: 180, maxMessage: 'O email deve ter no maximo {{ limit }} caracteres')]
        public ?string $email = null,

        #[Base64Image(required: false, maxSizeInMb: 2)]
        public ?string $avatar = null,
    ) {
    }
}
```

## UserResponseDTO

Arquivo:

```md
src/DTO/User/UserResponseDTO.php
```

```php
<?php

declare(strict_types=1);

namespace App\DTO\User;

final readonly class UserResponseDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public ?string $avatar,
        public bool $isActive,
        public string $createdAt,
        public ?string $updatedAt,
    ) {
    }

    /**
     * @return array{
     *     id: int,
     *     name: string,
     *     email: string,
     *     avatar: string|null,
     *     is_active: bool,
     *     created_at: string,
     *     updated_at: string|null
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
```

## Decisoes

```md
- DTO de entrada recebe avatar em base64.
- Entity guarda caminho do arquivo.
- Response retorna caminho do avatar.
- Password nunca aparece em DTO de response.
- Email unico no create pode ser validado no Service para manter o padrao de Exceptions.
```

## Exemplo de payload

```json
{
  "name": "Gustavo Oliveira",
  "email": "gustavo@email.com",
  "password": "Senha123",
  "avatar": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUg..."
}
```