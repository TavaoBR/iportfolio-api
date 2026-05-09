<?php

declare(strict_types=1);

namespace App\DTO\Portfolio;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreatePortfolioSiteDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Slug e obrigatorio')]
        #[Assert\Regex(pattern: '/^[a-z0-9]+(?:-[a-z0-9]+)*$/', message: 'Slug invalido (use letras minusculas, numeros e hifens)')]
        #[Assert\Length(max: 120)]
        public string $slug,

        #[Assert\NotBlank(message: 'Titulo e obrigatorio')]
        #[Assert\Length(max: 180)]
        public string $title,

        #[Assert\Length(max: 255)]
        public ?string $subtitle = null,

        #[SerializedName('template_key')]
        #[Assert\Length(max: 120)]
        public ?string $templateKey = null,
    ) {
    }
}
