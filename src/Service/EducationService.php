<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Education\CreateEducationDTO;
use App\Entity\Education;
use App\Entity\User;
use App\Mapper\EducationMapper;
use App\Repository\EducationRepository;
use Symfony\Component\HttpFoundation\Response;

final class EducationService
{
    public function __construct(private readonly EducationRepository $educations, private readonly EducationMapper $mapper) {}

    public function create(User $user, CreateEducationDTO $dto): array
    {
        try {
            $education = new Education($user, $dto->institution);
            $education->update($dto->institution, $dto->degree, $dto->fieldOfStudy, $dto->description, $this->date($dto->startDate), $this->date($dto->endDate), $dto->isCurrent, $dto->sortOrder);
            $this->educations->save($education);
            return ['status' => Response::HTTP_CREATED, 'message' => 'Formacao criada com sucesso', 'data' => $this->mapper->toArray($education)];
        } catch (\Exception $e) {
            return ['status' => Response::HTTP_INTERNAL_SERVER_ERROR, 'message' => 'Ocorreu algum erro inesperado', 'errors' => $e->getMessage()];
        }
    }

    public function list(User $user): array
    {
        try {
            return ['status' => Response::HTTP_OK, 'message' => 'Formacoes encontradas com sucesso', 'data' => $this->mapper->toArrayList($this->educations->findByUser($user))];
        } catch (\Exception $e) {
            return ['status' => Response::HTTP_INTERNAL_SERVER_ERROR, 'message' => 'Ocorreu algum erro inesperado', 'errors' => $e->getMessage()];
        }
    }

    private function date(?string $value): ?\DateTimeImmutable
    {
        return $value ? new \DateTimeImmutable($value) : null;
    }
}