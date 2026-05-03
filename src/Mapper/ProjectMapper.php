<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Entity\Project;

final class ProjectMapper
{
    public function toArray(Project $project): array
    {
        return [
            'id' => $project->getId(),
            'name' => $project->getName(),
            'description' => $project->getDescription(),
            'project_url' => $project->getProjectUrl(),
            'repository_url' => $project->getRepositoryUrl(),
            'start_date' => $project->getStartDate()?->format('Y-m-d'),
            'end_date' => $project->getEndDate()?->format('Y-m-d'),
            'is_current' => $project->isCurrent(),
            'sort_order' => $project->getSortOrder(),
            'created_at' => $project->getCreatedAt()->format(DATE_ATOM),
            'updated_at' => $project->getUpdatedAt()?->format(DATE_ATOM),
        ];
    }

    public function toArrayList(array $projects): array
    {
        return array_map(fn (Project $project): array => $this->toArray($project), $projects);
    }
}