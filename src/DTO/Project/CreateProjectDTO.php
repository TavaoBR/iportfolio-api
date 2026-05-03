<?php

declare(strict_types=1);

namespace App\DTO\Project;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateProjectDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Nome do projeto e obrigatorio')]
        #[Assert\Length(max: 180)]
        public readonly string $name,

        #[Assert\Length(max: 5000)]
        public readonly ?string $description = null,

        #[SerializedName('project_url')]
        #[Assert\Url(requireTld: true, message: 'URL do projeto invalida')]
        #[Assert\Length(max: 255)]
        public readonly ?string $projectUrl = null,

        #[SerializedName('repository_url')]
        #[Assert\Url(requireTld: true, message: 'URL do repositorio invalida')]
        #[Assert\Length(max: 255)]
        public readonly ?string $repositoryUrl = null,

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