<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Experience\CreateExperienceDTO;
use App\Entity\Experience;
use App\Entity\User;
use App\Mapper\ExperienceMapper;
use App\Repository\ExperienceRepository;
use Symfony\Component\HttpFoundation\Response;

final class ExperienceService
{
    public function __construct(private readonly ExperienceRepository $experiences, private readonly ExperienceMapper $mapper) {}

    public function create(User $user, CreateExperienceDTO $dto): array
    {
        try {
            $experience = new Experience($user, $dto->company, $dto->role);
            $experience->update($dto->company, $dto->role, $dto->description, $dto->location, $this->date($dto->startDate), $this->date($dto->endDate), $dto->isCurrent, $dto->sortOrder);
            $this->experiences->save($experience);
            return ['status' => Response::HTTP_CREATED, 'message' => 'Experiencia criada com sucesso', 'data' => $this->mapper->toArray($experience)];
        } catch (\Exception $e) {
            return ['status' => Response::HTTP_INTERNAL_SERVER_ERROR, 'message' => 'Ocorreu algum erro inesperado', 'errors' => $e->getMessage()];
        }
    }

    public function list(User $user): array
    {
        try {
            return ['status' => Response::HTTP_OK, 'message' => 'Experiencias encontradas com sucesso', 'data' => $this->mapper->toArrayList($this->experiences->findByUser($user))];
        } catch (\Exception $e) {
            return ['status' => Response::HTTP_INTERNAL_SERVER_ERROR, 'message' => 'Ocorreu algum erro inesperado', 'errors' => $e->getMessage()];
        }
    }

    private function date(?string $value): ?\DateTimeImmutable
    {
        return $value ? new \DateTimeImmutable($value) : null;
    }
}