<?php

declare(strict_types=1);

namespace App\DTO\Experience;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateExperienceDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Empresa e obrigatoria')]
        #[Assert\Length(max: 180)]
        public readonly string $company,

        #[Assert\NotBlank(message: 'Cargo e obrigatorio')]
        #[Assert\Length(max: 180)]
        public readonly string $role,

        #[Assert\Length(max: 5000)]
        public readonly ?string $description = null,

        #[Assert\Length(max: 180)]
        public readonly ?string $location = null,

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