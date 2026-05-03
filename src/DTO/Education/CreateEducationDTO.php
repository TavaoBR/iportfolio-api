<?php

declare(strict_types=1);

namespace App\DTO\Education;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateEducationDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Instituicao e obrigatoria')]
        #[Assert\Length(max: 180)]
        public readonly string $institution,

        #[Assert\Length(max: 180)]
        public readonly ?string $degree = null,

        #[SerializedName('field_of_study')]
        #[Assert\Length(max: 180)]
        public readonly ?string $fieldOfStudy = null,

        #[Assert\Length(max: 5000)]
        public readonly ?string $description = null,

        #[SerializedName('start_date')]
        #[Assert\Date(message: 'Data de inicio invalida')]
        public readonly ?string $startDate = null,

        #[SerializedName('end_date')]
        #[Assert\Date(message: 'Data de fim invalida')]
        public readonly ?string $endDate = null,

        #[SerializedName('is_current')]
        public readonly bool $isCurrent = false,

        #[SerializedName('sort_order')]
        #[Assert\PositiveOrZero(message: 'Ordem deve ser zero ou maior')]
        public readonly int $sortOrder = 0,
    ) {
    }
}