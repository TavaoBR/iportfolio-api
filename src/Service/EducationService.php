<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Education\CreateEducationDTO;
use App\DTO\Education\UpdateEducationDTO;
use App\Entity\Education;
use App\Entity\User;
use App\Exception\Education\EducationNotFoundException;
use App\Mapper\EducationMapper;
use App\Repository\EducationRepository;
use Symfony\Component\HttpFoundation\Response;

final class EducationService
{
    public function __construct(
        private readonly EducationRepository $educations,
        private readonly EducationMapper $mapper,
    ) {
    }

    /**
     * @return array{status: int, message: string, data?: array<string, mixed>, errors?: mixed}
     */
    public function create(User $user, CreateEducationDTO $dto): array
    {
        try {
            $education = new Education($user, $dto->institution);
            $education->update(
                $dto->institution,
                $dto->degree,
                $dto->fieldOfStudy,
                $dto->description,
                $this->date($dto->startDate),
                $this->date($dto->endDate),
                $dto->isCurrent,
                $dto->sortOrder,
            );

            $this->educations->save($education);

            return [
                'status' => Response::HTTP_CREATED,
                'message' => 'Formacao criada com sucesso',
                'data' => $this->mapper->toArray($education),
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
    public function update(User $user, int $id, UpdateEducationDTO $dto): array
    {
        try {
            $education = $this->educations->findOneOwnedByUser($user, $id);

            if (!$education instanceof Education) {
                throw new EducationNotFoundException();
            }

            $institution = $dto->institution !== null ? $dto->institution : $education->getInstitution();
            $degree = $dto->degree !== null ? $dto->degree : $education->getDegree();
            $fieldOfStudy = $dto->fieldOfStudy !== null ? $dto->fieldOfStudy : $education->getFieldOfStudy();
            $description = $dto->description !== null ? $dto->description : $education->getDescription();
            $startDate = $dto->startDate !== null ? $this->date($dto->startDate) : $education->getStartDate();
            $endDate = $dto->endDate !== null ? $this->date($dto->endDate) : $education->getEndDate();
            $isCurrent = $dto->isCurrent !== null ? $dto->isCurrent : $education->isCurrent();
            $sortOrder = $dto->sortOrder !== null ? $dto->sortOrder : $education->getSortOrder();

            $education->update(
                $institution,
                $degree,
                $fieldOfStudy,
                $description,
                $startDate,
                $endDate,
                $isCurrent,
                $sortOrder,
            );

            $this->educations->save($education);

            return [
                'status' => Response::HTTP_OK,
                'message' => 'Formacao atualizada com sucesso',
                'data' => $this->mapper->toArray($education),
            ];
        } catch (EducationNotFoundException $e) {
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
            $row = $this->educations->findOneOwnedByUser($user, $id);

            if (!$row instanceof Education) {
                throw new EducationNotFoundException();
            }

            $this->educations->remove($row);

            return [
                'status' => Response::HTTP_OK,
                'message' => 'Formacao removida com sucesso',
            ];
        } catch (EducationNotFoundException $e) {
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
                'message' => 'Formacoes encontradas com sucesso',
                'data' => $this->mapper->toArrayList($this->educations->findByUser($user)),
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
