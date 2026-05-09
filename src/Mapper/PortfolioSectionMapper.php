<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Entity\PortfolioSection;

final class PortfolioSectionMapper
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(PortfolioSection $section): array
    {
        return [
            'id' => $section->getId(),
            'section_type' => $section->getSectionType(),
            'layout_type' => $section->getLayoutType(),
            'position' => $section->getPosition(),
            'is_visible' => $section->isVisible(),
            'settings' => $section->getSettingsJson() ?? [],
            'created_at' => $section->getCreatedAt()->format(DATE_ATOM),
            'updated_at' => $section->getUpdatedAt()?->format(DATE_ATOM),
        ];
    }

    /**
     * @param list<PortfolioSection> $sections
     * @return list<array<string, mixed>>
     */
    public function toArrayList(array $sections): array
    {
        return array_map(fn (PortfolioSection $s): array => $this->toArray($s), $sections);
    }
}
