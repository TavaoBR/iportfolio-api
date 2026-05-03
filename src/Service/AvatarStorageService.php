<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\User\InvalidAvatarException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

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
        #[Autowire('%env(USER_AVATAR_UPLOAD_DIR)%')]
        private readonly string $uploadDir,
        #[Autowire('%env(USER_AVATAR_PUBLIC_PATH)%')]
        private readonly string $publicPath,
    ) {
    }

    public function storeBase64(string $base64Image): string
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

        $fileName = sprintf('avatar_%s.%s', bin2hex(random_bytes(16)), self::MIME_TO_EXTENSION[$mimeType]);
        $absoluteDir = $this->projectDir . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $this->uploadDir);

        if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0755, true) && !is_dir($absoluteDir)) {
            throw new \RuntimeException('Nao foi possivel criar diretorio de avatar');
        }

        $absolutePath = $absoluteDir . DIRECTORY_SEPARATOR . $fileName;

        if (file_put_contents($absolutePath, $binary) === false) {
            throw new \RuntimeException('Nao foi possivel salvar avatar');
        }

        return trim($this->publicPath, '/\\') . '/' . $fileName;
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