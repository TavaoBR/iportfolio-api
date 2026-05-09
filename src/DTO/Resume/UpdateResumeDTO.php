<?php

declare(strict_types=1);

namespace App\DTO\Resume;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateResumeDTO
{
    public function __construct(
        #[Assert\Type(type: 'string', message: 'O titulo deve ser um texto')]
        #[Assert\Length(max: 180)]
        public ?string $title = null,

        #[SerializedName('target_role')]
        #[Assert\Length(max: 180)]
        public ?string $targetRole = null,

        #[Assert\Choice(choices: ['pt_BR', 'en_US', 'es_ES'], message: 'Idioma invalido')]
        public ?string $language = null,

        #[SerializedName('is_main')]
        public ?bool $isMain = null,

        #[SerializedName('is_public')]
        public ?bool $isPublic = null,

        #[SerializedName('template_key')]
        #[Assert\Length(max: 120)]
        public ?string $templateKey = null,
    ) {
    }
}
