<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Project\CreateProjectDTO;
use App\DTO\Project\UpdateProjectDTO;
use App\Entity\Project;
use App\Entity\User;
use App\Exception\Project\ProjectNotFoundException;
use App\Mapper\ProjectMapper;
use App\Repository\ProjectRepository;
use Symfony\Component\HttpFoundation\Response;

final class ProjectService
{
    public function __construct(
        private readonly ProjectRepository $projects,
        private readonly ProjectMapper $mapper,
    ) {
    }

    /**
     * @return array{status: int, message: string, data?: array<string, mixed>, errors?: mixed}
     */
    public function create(User $user, CreateProjectDTO $dto): array
    {
        try {
            $project = new Project($user, $dto->name);
            $project->update(
                $dto->name,
                $dto->description,
                $dto->projectUrl,
                $dto->repositoryUrl,
                $this->date($dto->startDate),
                $this->date($dto->endDate),
                $dto->isCurrent,
                $dto->sortOrder,
            );

            $this->projects->save($project);

            return [
                'status' => Response::HTTP_CREATED,
                'message' => 'Projeto criado com sucesso',
                'data' => $this->mapper->toArray($project),
            ];
        } catch (\Exception $e) {
            return [
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Ocorreu algum erro inesperado',
                'errors' => $e->getMessage(),
            ];
        }
    }

    /**
     * @return array{status: int, message: string, data?: array<string, mixed>, errors?: mixed}
     */
    public function update(User $user, int $id, UpdateProjectDTO $dto): array
    {
        try {
            $project = $this->projects->findOneOwnedByUser($user, $id);

            if (!$project instanceof Project) {
                throw new ProjectNotFoundException();
            }

            $name = $dto->name !== null ? $dto->name : $project->getName();
            $description = $dto->description !== null ? $dto->description : $project->getDescription();
            $projectUrl = $dto->projectUrl !== null ? $dto->projectUrl : $project->getProjectUrl();
            $repositoryUrl = $dto->repositoryUrl !== null ? $dto->repositoryUrl : $project->getRepositoryUrl();
            $startDate = $dto->startDate !== null ? $this->date($dto->startDate) : $project->getStartDate();
            $endDate = $dto->endDate !== null ? $this->date($dto->endDate) : $project->getEndDate();
            $isCurrent = $dto->isCurrent !== null ? $dto->isCurrent : $project->isCurrent();
            $sortOrder = $dto->sortOrder !== null ? $dto->sortOrder : $project->getSortOrder();

            $project->update(
                $name,
                $description,
                $projectUrl,
                $repositoryUrl,
                $startDate,
                $endDate,
                $isCurrent,
                $sortOrder,
            );

            $this->projects->save($project);

            return [
                'status' => Response::HTTP_OK,
                'message' => 'Projeto atualizado com sucesso',
                'data' => $this->mapper->toArray($project),
            ];
        } catch (ProjectNotFoundException $e) {
            return [
                'status' => Response::HTTP_NOT_FOUND,
                'message' => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Ocorreu algum erro inesperado',
                'errors' => $e->getMessage(),
            ];
        }
    }

    /**
     * @return array{status: int, message: string, data?: list<array<string, mixed>>, errors?: mixed}
     */
    public function list(User $user): array
    {
        try {
            return [
                'status' => Response::HTTP_OK,
                'message' => 'Projetos encontrados com sucesso',
                'data' => $this->mapper->toArrayList($this->projects->findByUser($user)),
            ];
        } catch (\Exception $e) {
            return [
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Ocorreu algum erro inesperado',
                'errors' => $e->getMessage(),
            ];
        }
    }

    private function date(?string $value): ?\DateTimeImmutable
    {
        return $value ? new \DateTimeImmutable($value) : null;
    }
}
