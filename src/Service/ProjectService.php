<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Project\CreateProjectDTO;
use App\Entity\Project;
use App\Entity\User;
use App\Mapper\ProjectMapper;
use App\Repository\ProjectRepository;
use Symfony\Component\HttpFoundation\Response;

final class ProjectService
{
    public function __construct(private readonly ProjectRepository $projects, private readonly ProjectMapper $mapper) {}

    public function create(User $user, CreateProjectDTO $dto): array
    {
        try {
            $project = new Project($user, $dto->name);
            $project->update($dto->name, $dto->description, $dto->projectUrl, $dto->repositoryUrl, $this->date($dto->startDate), $this->date($dto->endDate), $dto->isCurrent, $dto->sortOrder);
            $this->projects->save($project);
            return ['status' => Response::HTTP_CREATED, 'message' => 'Projeto criado com sucesso', 'data' => $this->mapper->toArray($project)];
        } catch (\Exception $e) {
            return ['status' => Response::HTTP_INTERNAL_SERVER_ERROR, 'message' => 'Ocorreu algum erro inesperado', 'errors' => $e->getMessage()];
        }
    }

    public function list(User $user): array
    {
        try {
            return ['status' => Response::HTTP_OK, 'message' => 'Projetos encontrados com sucesso', 'data' => $this->mapper->toArrayList($this->projects->findByUser($user))];
        } catch (\Exception $e) {
            return ['status' => Response::HTTP_INTERNAL_SERVER_ERROR, 'message' => 'Ocorreu algum erro inesperado', 'errors' => $e->getMessage()];
        }
    }

    private function date(?string $value): ?\DateTimeImmutable
    {
        return $value ? new \DateTimeImmutable($value) : null;
    }
}