# Modulo User - Avatar Base64

## Decisao oficial

Avatar entra na API como base64 e e salvo como arquivo.

Banco salva somente o caminho.

```md
Entrada: data:image/png;base64,iVBORw0KGgo...
Arquivo: public/uploads/avatars/avatar_abc123.png
Banco: uploads/avatars/avatar_abc123.png
Response: uploads/avatars/avatar_abc123.png
```

## Por que nao salvar base64 no banco?

```md
- Base64 aumenta o tamanho do dado.
- Banco fica pesado para leitura.
- Cache HTTP fica pior.
- Backup cresce sem necessidade.
- Servir arquivo estatico e mais simples.
```

## Configuracao sugerida

`.env`:

```env
USER_AVATAR_UPLOAD_DIR=public/uploads/avatars
USER_AVATAR_PUBLIC_PATH=uploads/avatars
USER_AVATAR_MAX_SIZE_MB=2
```

## AvatarStorageService

Arquivo:

```md
src/Service/AvatarStorageService.php
```

```php
<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Exception\User\AvatarUploadException;
use App\Exception\User\InvalidAvatarException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Uid\Uuid;

final class AvatarStorageService
{
    private const MIME_TO_EXTENSION = [
        'image/png' => 'png',
        'image/jpeg' => 'jpg',
        'image/webp' => 'webp',
    ];

    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
        #[Autowire('%env(default:defaultAvatarUploadDir:USER_AVATAR_UPLOAD_DIR)%')]
        private readonly string $uploadDir,
        #[Autowire('%env(default:defaultAvatarPublicPath:USER_AVATAR_PUBLIC_PATH)%')]
        private readonly string $publicPath,
    ) {
    }

    public function storeBase64(string $base64Image, ?User $user = null): string
    {
        [$declaredMimeType, $base64] = $this->splitBase64Image($base64Image);

        $binary = base64_decode($base64, true);

        if ($binary === false) {
            throw new InvalidAvatarException('Avatar deve ser uma imagem em base64 valida');
        }

        $mimeType = $this->detectMimeType($binary);

        if ($declaredMimeType !== null && $declaredMimeType !== $mimeType) {
            throw new InvalidAvatarException('Tipo do avatar nao corresponde ao conteudo enviado');
        }

        if (!array_key_exists($mimeType, self::MIME_TO_EXTENSION)) {
            throw new InvalidAvatarException('Tipo de avatar nao permitido');
        }

        $extension = self::MIME_TO_EXTENSION[$mimeType];
        $fileName = sprintf('avatar_%s.%s', Uuid::v7()->toRfc4122(), $extension);
        $absoluteDir = $this->projectDir . DIRECTORY_SEPARATOR . $this->uploadDir;

        if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0755, true) && !is_dir($absoluteDir)) {
            throw new AvatarUploadException('Nao foi possivel criar diretorio de avatar');
        }

        $absolutePath = $absoluteDir . DIRECTORY_SEPARATOR . $fileName;

        if (file_put_contents($absolutePath, $binary) === false) {
            throw new AvatarUploadException('Nao foi possivel salvar avatar');
        }

        return trim($this->publicPath, '/\\') . '/' . $fileName;
    }

    public function remove(?string $avatarPath): void
    {
        if ($avatarPath === null || $avatarPath === '') {
            return;
        }

        $absolutePath = $this->projectDir . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $avatarPath);

        if (is_file($absolutePath)) {
            unlink($absolutePath);
        }
    }

    /**
     * @return array{0: string|null, 1: string}
     */
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

## Regras

```md
- Aceitar png, jpeg, jpg e webp.
- Limitar tamanho no Validator.
- Nao confiar em nome enviado pelo cliente.
- Gerar nome seguro com UUID.
- Banco guarda caminho relativo.
- Se upload novo substituir antigo, remover antigo somente depois do novo salvar com sucesso.
```

## Exemplo de request

```json
{
  "name": "Gustavo Oliveira",
  "email": "gustavo@email.com",
  "password": "Senha123",
  "avatar": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUg..."
}
```