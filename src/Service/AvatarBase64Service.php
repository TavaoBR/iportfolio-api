<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\User\InvalidAvatarException;

final class AvatarBase64Service
{
    private const ALLOWED_MIME_TYPES = [
        'image/png' => true,
        'image/jpeg' => true,
        'image/webp' => true,
    ];

    public function assertValid(string $base64Image): void
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

        if (!isset(self::ALLOWED_MIME_TYPES[$mimeType])) {
            throw new InvalidAvatarException('Tipo de avatar nao permitido');
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
