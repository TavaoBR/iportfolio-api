<?php

declare(strict_types=1);

namespace App\DTO\Education;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateEducationDTO
{
    public function __construct(
        #[Assert\Type(type: 'string', message: 'Instituicao deve ser um texto')]
        #[Assert\Length(max: 180)]
        public ?string $institution = null,

        #[Assert\Length(max: 180)]
        public ?string $degree = null,

        #[SerializedName('field_of_study')]
        #[Assert\Length(max: 180)]
        public ?string $fieldOfStudy = null,

        #[Assert\Length(max: 5000)]
        public ?string $description = null,

        #[SerializedName('start_date')]
        #[Assert\Date(message: 'Data de inicio invalida')]
        public ?string $startDate = null,

        #[SerializedName('end_date')]
        #[Assert\Date(message: 'Data de fim invalida')]
        public ?string $endDate = null,

        #[SerializedName('is_current')]
        public ?bool $isCurrent = null,

        #[SerializedName('sort_order')]
        #[Assert\PositiveOrZero(message: 'Ordem deve ser zero ou maior')]
        public ?int $sortOrder = null,
    ) {
    }
}
