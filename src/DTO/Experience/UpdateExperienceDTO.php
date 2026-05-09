<?php

declare(strict_types=1);

namespace App\DTO\Experience;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateExperienceDTO
{
    public function __construct(
        #[Assert\Type(type: 'string', message: 'Empresa deve ser um texto')]
        #[Assert\Length(max: 180)]
        public ?string $company = null,

        #[Assert\Type(type: 'string', message: 'Cargo deve ser um texto')]
        #[Assert\Length(max: 180)]
        public ?string $role = null,

        #[Assert\Length(max: 5000)]
        public ?string $description = null,

        #[Assert\Length(max: 180)]
        public ?string $location = null,

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
