<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Entity\PortfolioSite;

final class PortfolioSiteMapper
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(PortfolioSite $site): array
    {
        return [
            'id' => $site->getId(),
            'slug' => $site->getSlug(),
            'title' => $site->getTitle(),
            'subtitle' => $site->getSubtitle(),
            'template_key' => $site->getTemplateKey(),
            'is_public' => $site->isPublic(),
            'created_at' => $site->getCreatedAt()->format(DATE_ATOM),
            'updated_at' => $site->getUpdatedAt()?->format(DATE_ATOM),
        ];
    }
}
