<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Entity\Skill;

final class SkillMapper
{
    public function toArray(Skill $skill): array
    {
        return [
            'id' => $skill->getId(),
            'name' => $skill->getName(),
            'category' => $skill->getCategory(),
            'level' => $skill->getLevel(),
            'sort_order' => $skill->getSortOrder(),
            'created_at' => $skill->getCreatedAt()->format(DATE_ATOM),
            'updated_at' => $skill->getUpdatedAt()?->format(DATE_ATOM),
        ];
    }

    public function toArrayList(array $skills): array
    {
        return array_map(fn (Skill $skill): array => $this->toArray($skill), $skills);
    }
}