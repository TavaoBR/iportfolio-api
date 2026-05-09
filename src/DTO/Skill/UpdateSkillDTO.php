<?php

declare(strict_types=1);

namespace App\DTO\Skill;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateSkillDTO
{
    public function __construct(
        #[Assert\Type(type: 'string', message: 'O nome deve ser um texto')]
        #[Assert\Length(max: 120, maxMessage: 'O nome deve ter no maximo {{ limit }} caracteres')]
        public ?string $name = null,

        #[Assert\Type(type: 'string', message: 'A categoria deve ser um texto')]
        #[Assert\Length(max: 120)]
        public ?string $category = null,

        #[Assert\Choice(choices: ['beginner', 'intermediate', 'advanced', 'expert'], message: 'Nivel invalido')]
        public ?string $level = null,

        #[SerializedName('sort_order')]
        #[Assert\PositiveOrZero(message: 'Ordem deve ser zero ou maior')]
        public ?int $sortOrder = null,
    ) {
    }
}
