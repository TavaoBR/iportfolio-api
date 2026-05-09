<?php

declare(strict_types=1);

namespace App\DTO\Resume;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateResumeDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Titulo e obrigatorio')]
        #[Assert\Length(max: 180, maxMessage: 'Titulo deve ter no maximo 180 caracteres')]
        public readonly string $title,

        #[SerializedName('target_role')]
        #[Assert\Length(max: 180, maxMessage: 'Cargo alvo deve ter no maximo 180 caracteres')]
        public readonly ?string $targetRole = null,

        #[Assert\Choice(choices: ['pt_BR', 'en_US', 'es_ES'], message: 'Idioma invalido')]
        public readonly string $language = 'pt_BR',

        #[SerializedName('is_main')]
        public readonly bool $isMain = false,

        #[SerializedName('template_key')]
        #[Assert\Length(max: 120)]
        public readonly ?string $templateKey = null,
    ) {
    }
}