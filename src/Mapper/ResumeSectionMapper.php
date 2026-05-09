<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Entity\ResumeSection;

final class ResumeSectionMapper
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(ResumeSection $section): array
    {
        return [
            'id' => $section->getId(),
            'section_type' => $section->getSectionType(),
            'title' => $section->getTitle(),
            'content' => $section->getContent(),
            'position' => $section->getPosition(),
            'is_visible' => $section->isVisible(),
            'created_at' => $section->getCreatedAt()->format(DATE_ATOM),
            'updated_at' => $section->getUpdatedAt()?->format(DATE_ATOM),
        ];
    }

    /**
     * @param list<ResumeSection> $sections
     * @return list<array<string, mixed>>
     */
    public function toArrayList(array $sections): array
    {
        return array_map(fn (ResumeSection $s): array => $this->toArray($s), $sections);
    }
}
