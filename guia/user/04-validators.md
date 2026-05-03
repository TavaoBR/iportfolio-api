# Modulo User - Validators

## Objetivo

Este guia define todas as validacoes do modulo User e onde cada uma deve acontecer.

A regra principal e:

```md
DTO/Validator valida formato de entrada.
Service valida regra de negocio.
Repository apenas consulta o banco.
Controller nao valida campo manualmente.
```

## Arquivos envolvidos

```md
src/DTO/User/CreateUserDTO.php
src/DTO/User/UpdateUserDTO.php
src/Validator/Base64Image.php
src/Validator/Base64ImageValidator.php
src/Exception/User/EmptyPayloadException.php
src/Exception/User/UserAlreadyExistsException.php
src/Exception/User/InvalidAvatarException.php
```

## O que fica em Validator

Validators devem validar dados que podem ser verificados sem depender da regra de negocio principal.

Exemplos:

```md
- Campo obrigatorio
- Tipo esperado
- Tamanho minimo/maximo
- Formato de email
- Forca minima de senha
- Base64 valido
- MIME type permitido
- Tamanho maximo do avatar
```

## O que nao fica em Validator

Estas regras ficam no Service, com Exceptions proprias:

```md
- Email ja cadastrado
- Usuario nao encontrado
- Usuario inativo
- Payload vazio no update
- Falha ao salvar avatar
- Regra de permissao/ownership
```

Motivo:

```md
Essas regras representam comportamento da aplicacao, dependem de banco, estado atual ou fluxo de negocio.
Elas precisam virar status HTTP especifico pelo padrao status/message/errors.
```

## Mapa de validacoes

| Campo | Create | Update | Onde validar |
| --- | --- | --- | --- |
| name | obrigatorio | opcional | DTO + Service trim |
| email | obrigatorio | opcional | DTO formato + Service duplicidade |
| password | obrigatorio | nao atualiza aqui | DTO |
| avatar | opcional | opcional | DTO custom Base64Image + AvatarStorageService |
| payload vazio | nao se aplica | invalido | Service |

## Regras por campo

## name

Create:

```md
- Obrigatorio
- Deve ser string
- Nao pode ser vazio
- Maximo 150 caracteres
- Deve ser salvo com trim
```

Update:

```md
- Opcional
- Se enviado, deve ser string
- Se enviado, nao pode ser vazio
- Maximo 150 caracteres
- Deve ser salvo com trim
```

Constraints sugeridas:

```php
#[Assert\NotBlank(message: 'O nome e obrigatorio')]
#[Assert\Type(type: 'string', message: 'O nome deve ser um texto')]
#[Assert\Length(max: 150, maxMessage: 'O nome deve ter no maximo {{ limit }} caracteres')]
public string $name,
```

Para update:

```php
#[Assert\Type(type: 'string', message: 'O nome deve ser um texto')]
#[Assert\Length(max: 150, maxMessage: 'O nome deve ter no maximo {{ limit }} caracteres')]
public ?string $name = null,
```

Observacao:

```md
NotBlank em campo opcional de update pode bloquear null.
Por isso, no update, o Service deve rejeitar string vazia quando name for enviado.
```

## email

Create:

```md
- Obrigatorio
- Deve ser string
- Deve ter formato de email valido
- Maximo 180 caracteres
- Deve ser normalizado para lowercase na Entity/Service
- Duplicidade deve ser verificada no Service
```

Update:

```md
- Opcional
- Se enviado, deve ser email valido
- Maximo 180 caracteres
- Duplicidade deve ignorar o usuario atual
```

Constraints sugeridas:

```php
#[Assert\NotBlank(message: 'O email e obrigatorio')]
#[Assert\Type(type: 'string', message: 'O email deve ser um texto')]
#[Assert\Email(message: 'Informe um email valido')]
#[Assert\Length(max: 180, maxMessage: 'O email deve ter no maximo {{ limit }} caracteres')]
public string $email,
```

Duplicidade no Service:

```php
if ($this->users->findByEmail($dto->email)) {
    throw new UserAlreadyExistsException('Usuario ja cadastrado');
}
```

Retorno esperado:

```php
return [
    'status' => 409,
    'message' => 'Usuario ja cadastrado',
];
```

## password

Create:

```md
- Obrigatoria
- Deve ser string
- Minimo 8 caracteres
- Maximo 72 caracteres
- Deve conter pelo menos uma letra
- Deve conter pelo menos um numero
```

Constraints sugeridas:

```php
#[Assert\NotBlank(message: 'A senha e obrigatoria')]
#[Assert\Type(type: 'string', message: 'A senha deve ser um texto')]
#[Assert\Length(
    min: 8,
    max: 72,
    minMessage: 'A senha deve ter pelo menos {{ limit }} caracteres',
    maxMessage: 'A senha deve ter no maximo {{ limit }} caracteres'
)]
#[Assert\Regex(
    pattern: '/^(?=.*[A-Za-z])(?=.*\d).+$/',
    message: 'A senha deve conter letras e numeros'
)]
public string $password,
```

Por que maximo 72:

```md
Algoritmos como bcrypt possuem limite pratico de 72 bytes.
Esse limite evita falsa expectativa de seguranca em senhas muito longas truncadas pelo algoritmo.
```

## avatar

Create e Update:

```md
- Opcional
- Se enviado, deve ser string
- Pode ser data URI
- Pode ser base64 puro
- Deve decodificar com base64_decode strict
- Deve ter MIME real permitido
- Tipos permitidos: image/png, image/jpeg, image/webp
- Tamanho maximo inicial: 2 MB
```

Constraint sugerida:

```php
#[Base64Image(required: false, maxSizeInMb: 2)]
public ?string $avatar = null,
```

## payload vazio no update

O DTO de update tem todos os campos opcionais, entao este payload passa no DTO:

```json
{}
```

Mas ele nao deve ser aceito pela regra de aplicacao.

Validar no Service:

```php
if ($dto->name === null && $dto->email === null && $dto->avatar === null) {
    throw new EmptyPayloadException('Nenhum dado enviado para atualizacao');
}
```

Retorno esperado:

```php
return [
    'status' => 422,
    'message' => 'Nenhum dado enviado para atualizacao',
];
```

## CreateUserDTO completo com constraints

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
        #[Assert\Type(type: 'string', message: 'O nome deve ser um texto')]
        #[Assert\Length(max: 150, maxMessage: 'O nome deve ter no maximo {{ limit }} caracteres')]
        public string $name,

        #[Assert\NotBlank(message: 'O email e obrigatorio')]
        #[Assert\Type(type: 'string', message: 'O email deve ser um texto')]
        #[Assert\Email(message: 'Informe um email valido')]
        #[Assert\Length(max: 180, maxMessage: 'O email deve ter no maximo {{ limit }} caracteres')]
        public string $email,

        #[Assert\NotBlank(message: 'A senha e obrigatoria')]
        #[Assert\Type(type: 'string', message: 'A senha deve ser um texto')]
        #[Assert\Length(
            min: 8,
            max: 72,
            minMessage: 'A senha deve ter pelo menos {{ limit }} caracteres',
            maxMessage: 'A senha deve ter no maximo {{ limit }} caracteres'
        )]
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

## UpdateUserDTO completo com constraints

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
        #[Assert\Type(type: 'string', message: 'O nome deve ser um texto')]
        #[Assert\Length(max: 150, maxMessage: 'O nome deve ter no maximo {{ limit }} caracteres')]
        public ?string $name = null,

        #[Assert\Type(type: 'string', message: 'O email deve ser um texto')]
        #[Assert\Email(message: 'Informe um email valido')]
        #[Assert\Length(max: 180, maxMessage: 'O email deve ter no maximo {{ limit }} caracteres')]
        public ?string $email = null,

        #[Base64Image(required: false, maxSizeInMb: 2)]
        public ?string $avatar = null,
    ) {
    }
}
```

## Base64Image

Arquivo:

```md
src/Validator/Base64Image.php
```

```php
<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER)]
final class Base64Image extends Constraint
{
    public function __construct(
        public bool $required = false,
        public int $maxSizeInMb = 2,
        public string $message = 'Imagem invalida',
        public string $invalidBase64Message = 'Imagem deve estar em base64 valido',
        public string $invalidMimeTypeMessage = 'Tipo de imagem nao permitido',
        public string $maxSizeMessage = 'Imagem excede o tamanho maximo permitido',
        mixed $options = null,
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct($options, $groups, $payload);
    }
}
```

## Base64ImageValidator

Arquivo:

```md
src/Validator/Base64ImageValidator.php
```

```php
<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class Base64ImageValidator extends ConstraintValidator
{
    private const ALLOWED_MIME_TYPES = [
        'image/png',
        'image/jpeg',
        'image/webp',
    ];

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof Base64Image) {
            throw new UnexpectedTypeException($constraint, Base64Image::class);
        }

        if ($value === null || $value === '') {
            if ($constraint->required) {
                $this->context->buildViolation($constraint->message)->addViolation();
            }

            return;
        }

        if (!is_string($value)) {
            $this->context->buildViolation($constraint->message)->addViolation();
            return;
        }

        [$mimeType, $base64] = $this->splitBase64Image($value);

        if ($mimeType !== null && !in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
            $this->context->buildViolation($constraint->invalidMimeTypeMessage)->addViolation();
            return;
        }

        $decoded = base64_decode($base64, true);

        if ($decoded === false) {
            $this->context->buildViolation($constraint->invalidBase64Message)->addViolation();
            return;
        }

        $maxBytes = $constraint->maxSizeInMb * 1024 * 1024;

        if (strlen($decoded) > $maxBytes) {
            $this->context->buildViolation($constraint->maxSizeMessage)->addViolation();
            return;
        }

        $detectedMimeType = $this->detectMimeType($decoded);

        if (!in_array($detectedMimeType, self::ALLOWED_MIME_TYPES, true)) {
            $this->context->buildViolation($constraint->invalidMimeTypeMessage)->addViolation();
        }
    }

    private function splitBase64Image(string $value): array
    {
        if (preg_match('/^data:(image\/[a-zA-Z0-9.+-]+);base64,(.+)$/', $value, $matches) === 1) {
            return [$matches[1], $matches[2]];
        }

        return [null, $value];
    }

    private function detectMimeType(string $binary): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);

        return (string) $finfo->buffer($binary);
    }
}
```

## Formato de erro de validacao

Quando o Symfony Validator barrar o payload, a API deve responder sem `success`:

```json
{
  "message": "Dados invalidos",
  "errors": [
    {
      "field": "email",
      "message": "Informe um email valido"
    }
  ]
}
```

Status HTTP:

```md
422 Unprocessable Entity
```

## Exemplos de payloads invalidos

Nome vazio:

```json
{
  "name": "",
  "email": "gustavo@email.com",
  "password": "Senha123"
}
```

Email invalido:

```json
{
  "name": "Gustavo",
  "email": "email-invalido",
  "password": "Senha123"
}
```

Senha fraca:

```json
{
  "name": "Gustavo",
  "email": "gustavo@email.com",
  "password": "abcdefgh"
}
```

Avatar invalido:

```json
{
  "name": "Gustavo",
  "email": "gustavo@email.com",
  "password": "Senha123",
  "avatar": "isso-nao-e-base64"
}
```

Update vazio:

```json
{}
```

## Checklist

```md
- CreateUserDTO valida name, email, password e avatar.
- UpdateUserDTO valida name, email e avatar quando enviados.
- Password tem minimo 8, maximo 72, letras e numeros.
- Avatar aceita data URI e base64 puro.
- Avatar limita tamanho maximo.
- Avatar valida MIME type real.
- Email duplicado fica no Service, nao no Validator.
- Payload vazio no update fica no Service.
- Controller nao valida campos manualmente.
- Resposta de validacao nao usa success true/false.
```