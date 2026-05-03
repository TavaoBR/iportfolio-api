<?php

declare(strict_types=1);

namespace App\DTO\Skill;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateSkillDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Nome da competencia e obrigatorio')]
        #[Assert\Length(max: 120)]
        public readonly string $name,

        #[Assert\Length(max: 120)]
        public readonly ?string $category = null,

        #[Assert\Choice(choices: ['beginner', 'intermediate', 'advanced', 'expert'], message: 'Nivel invalido')]
        public readonly ?string $level = null,

        #[SerializedName('sort_order')]
        #[Assert\PositiveOrZero(message: 'Ordem deve ser zero ou maior')]
        public readonly int $sortOrder = 0,
    ) {
    }
}