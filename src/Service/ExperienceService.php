<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Experience\CreateExperienceDTO;
use App\DTO\Experience\UpdateExperienceDTO;
use App\Entity\Experience;
use App\Entity\User;
use App\Exception\Experience\ExperienceNotFoundException;
use App\Mapper\ExperienceMapper;
use App\Repository\ExperienceRepository;
use Symfony\Component\HttpFoundation\Response;

final class ExperienceService
{
    public function __construct(
        private readonly ExperienceRepository $experiences,
        private readonly ExperienceMapper $mapper,
    ) {
    }

    /**
     * @return array{status: int, message: string, data?: array<string, mixed>, errors?: mixed}
     */
    public function create(User $user, CreateExperienceDTO $dto): array
    {
        try {
            $experience = new Experience($user, $dto->company, $dto->role);
            $experience->update(
                $dto->company,
                $dto->role,
                $dto->description,
                $dto->location,
                $this->date($dto->startDate),
                $this->date($dto->endDate),
                $dto->isCurrent,
                $dto->sortOrder,
            );

            $this->experiences->save($experience);

            return [
                'status' => Response::HTTP_CREATED,
                'message' => 'Experiencia criada com sucesso',
                'data' => $this->mapper->toArray($experience),
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
    public function update(User $user, int $id, UpdateExperienceDTO $dto): array
    {
        try {
            $experience = $this->experiences->findOneOwnedByUser($user, $id);

            if (!$experience instanceof Experience) {
                throw new ExperienceNotFoundException();
            }

            $company = $dto->company !== null ? $dto->company : $experience->getCompany();
            $role = $dto->role !== null ? $dto->role : $experience->getRole();
            $description = $dto->description !== null ? $dto->description : $experience->getDescription();
            $location = $dto->location !== null ? $dto->location : $experience->getLocation();
            $startDate = $dto->startDate !== null ? $this->date($dto->startDate) : $experience->getStartDate();
            $endDate = $dto->endDate !== null ? $this->date($dto->endDate) : $experience->getEndDate();
            $isCurrent = $dto->isCurrent !== null ? $dto->isCurrent : $experience->isCurrent();
            $sortOrder = $dto->sortOrder !== null ? $dto->sortOrder : $experience->getSortOrder();

            $experience->update($company, $role, $description, $location, $startDate, $endDate, $isCurrent, $sortOrder);

            $this->experiences->save($experience);

            return [
                'status' => Response::HTTP_OK,
                'message' => 'Experiencia atualizada com sucesso',
                'data' => $this->mapper->toArray($experience),
            ];
        } catch (ExperienceNotFoundException $e) {
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
     * @return array{status: int, message: string, errors?: mixed}
     */
    public function delete(User $user, int $id): array
    {
        try {
            $row = $this->experiences->findOneOwnedByUser($user, $id);

            if (!$row instanceof Experience) {
                throw new ExperienceNotFoundException();
            }

            $this->experiences->remove($row);

            return [
                'status' => Response::HTTP_OK,
                'message' => 'Experiencia removida com sucesso',
            ];
        } catch (ExperienceNotFoundException $e) {
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
                'message' => 'Experiencias encontradas com sucesso',
                'data' => $this->mapper->toArrayList($this->experiences->findByUser($user)),
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
