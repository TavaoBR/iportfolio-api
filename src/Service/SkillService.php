<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Skill\CreateSkillDTO;
use App\DTO\Skill\UpdateSkillDTO;
use App\Entity\Skill;
use App\Entity\User;
use App\Exception\Skill\SkillNotFoundException;
use App\Mapper\SkillMapper;
use App\Repository\SkillRepository;
use Symfony\Component\HttpFoundation\Response;

final class SkillService
{
    public function __construct(
        private readonly SkillRepository $skills,
        private readonly SkillMapper $mapper,
    ) {
    }

    /**
     * @return array{status: int, message: string, data?: array<string, mixed>, errors?: mixed}
     */
    public function create(User $user, CreateSkillDTO $dto): array
    {
        try {
            $skill = new Skill($user, $dto->name);
            $skill->update($dto->name, $dto->category, $dto->level, $dto->sortOrder);

            $this->skills->save($skill);

            return [
                'status' => Response::HTTP_CREATED,
                'message' => 'Competencia criada com sucesso',
                'data' => $this->mapper->toArray($skill),
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
    public function update(User $user, int $id, UpdateSkillDTO $dto): array
    {
        try {
            $skill = $this->skills->findOneOwnedByUser($user, $id);

            if (!$skill instanceof Skill) {
                throw new SkillNotFoundException();
            }

            $name = $dto->name !== null ? $dto->name : $skill->getName();
            $category = $dto->category !== null ? $dto->category : $skill->getCategory();
            $level = $dto->level !== null ? $dto->level : $skill->getLevel();
            $sortOrder = $dto->sortOrder !== null ? $dto->sortOrder : $skill->getSortOrder();

            $skill->update($name, $category, $level, $sortOrder);

            $this->skills->save($skill);

            return [
                'status' => Response::HTTP_OK,
                'message' => 'Competencia atualizada com sucesso',
                'data' => $this->mapper->toArray($skill),
            ];
        } catch (SkillNotFoundException $e) {
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
            $skill = $this->skills->findOneOwnedByUser($user, $id);

            if (!$skill instanceof Skill) {
                throw new SkillNotFoundException();
            }

            $this->skills->remove($skill);

            return [
                'status' => Response::HTTP_OK,
                'message' => 'Competencia removida com sucesso',
            ];
        } catch (SkillNotFoundException $e) {
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
                'message' => 'Competencias encontradas com sucesso',
                'data' => $this->mapper->toArrayList($this->skills->findByUser($user)),
            ];
        } catch (\Exception $e) {
            return [
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Ocorreu algum erro inesperado',
                'errors' => $e->getMessage(),
            ];
        }
    }
}
