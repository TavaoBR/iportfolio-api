<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Entity\Education;

final class EducationMapper
{
    public function toArray(Education $education): array
    {
        return [
            'id' => $education->getId(),
            'institution' => $education->getInstitution(),
            'degree' => $education->getDegree(),
            'field_of_study' => $education->getFieldOfStudy(),
            'description' => $education->getDescription(),
            'start_date' => $education->getStartDate()?->format('Y-m-d'),
            'end_date' => $education->getEndDate()?->format('Y-m-d'),
            'is_current' => $education->isCurrent(),
            'sort_order' => $education->getSortOrder(),
            'created_at' => $education->getCreatedAt()->format(DATE_ATOM),
            'updated_at' => $education->getUpdatedAt()?->format(DATE_ATOM),
        ];
    }

    public function toArrayList(array $educations): array
    {
        return array_map(fn (Education $education): array => $this->toArray($education), $educations);
    }
}