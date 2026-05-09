<?php

declare(strict_types=1);

namespace App\DTO\ResumeSection;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateResumeSectionDTO
{
    public function __construct(
        #[SerializedName('section_type')]
        #[Assert\Choice(choices: [
            'personal_info',
            'professional_summary',
            'experiences',
            'educations',
            'skills',
            'languages',
            'certifications',
            'projects',
            'links',
            'custom',
        ], message: 'Tipo de secao invalido')]
        public ?string $sectionType = null,

        #[Assert\Length(max: 180)]
        public ?string $title = null,

        public ?string $content = null,

        #[Assert\PositiveOrZero]
        public ?int $position = null,

        #[SerializedName('is_visible')]
        public ?bool $isVisible = null,
    ) {
    }
}
