<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Entity\Experience;

final class ExperienceMapper
{
    public function toArray(Experience $experience): array
    {
        return [
            'id' => $experience->getId(),
            'company' => $experience->getCompany(),
            'role' => $experience->getRole(),
            'description' => $experience->getDescription(),
            'location' => $experience->getLocation(),
            'start_date' => $experience->getStartDate()?->format('Y-m-d'),
            'end_date' => $experience->getEndDate()?->format('Y-m-d'),
            'is_current' => $experience->isCurrent(),
            'sort_order' => $experience->getSortOrder(),
            'created_at' => $experience->getCreatedAt()->format(DATE_ATOM),
            'updated_at' => $experience->getUpdatedAt()?->format(DATE_ATOM),
        ];
    }

    public function toArrayList(array $experiences): array
    {
        return array_map(fn (Experience $experience): array => $this->toArray($experience), $experiences);
    }
}