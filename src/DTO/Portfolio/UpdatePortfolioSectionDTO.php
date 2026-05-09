<?php

declare(strict_types=1);

namespace App\DTO\Portfolio;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdatePortfolioSectionDTO
{
    /**
     * @param array<string, mixed>|null $settings
     */
    public function __construct(
        #[SerializedName('section_type')]
        #[Assert\Length(max: 32)]
        public ?string $sectionType = null,

        #[SerializedName('layout_type')]
        #[Assert\Choice(choices: ['grid', 'list', 'cards', 'carousel', 'timeline', 'tags', 'progress_bar', 'simple'], message: 'Layout invalido')]
        public ?string $layoutType = null,

        #[Assert\PositiveOrZero]
        public ?int $position = null,

        #[SerializedName('is_visible')]
        public ?bool $isVisible = null,

        public ?array $settings = null,
    ) {
    }
}
