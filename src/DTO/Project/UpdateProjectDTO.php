<?php

declare(strict_types=1);

namespace App\DTO\Project;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateProjectDTO
{
    public function __construct(
        #[Assert\Type(type: 'string', message: 'O nome deve ser um texto')]
        #[Assert\Length(max: 180)]
        public ?string $name = null,

        #[Assert\Length(max: 10000)]
        public ?string $description = null,

        #[SerializedName('project_url')]
        #[Assert\Length(max: 255)]
        public ?string $projectUrl = null,

        #[SerializedName('repository_url')]
        #[Assert\Length(max: 255)]
        public ?string $repositoryUrl = null,

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
