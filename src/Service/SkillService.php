<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Skill\CreateSkillDTO;
use App\Entity\Skill;
use App\Entity\User;
use App\Mapper\SkillMapper;
use App\Repository\SkillRepository;
use Symfony\Component\HttpFoundation\Response;

final class SkillService
{
    public function __construct(private readonly SkillRepository $skills, private readonly SkillMapper $mapper) {}

    public function create(User $user, CreateSkillDTO $dto): array
    {
        try {
            $skill = new Skill($user, $dto->name);
            $skill->update($dto->name, $dto->category, $dto->level, $dto->sortOrder);
            $this->skills->save($skill);
            return ['status' => Response::HTTP_CREATED, 'message' => 'Competencia criada com sucesso', 'data' => $this->mapper->toArray($skill)];
        } catch (\Exception $e) {
            return ['status' => Response::HTTP_INTERNAL_SERVER_ERROR, 'message' => 'Ocorreu algum erro inesperado', 'errors' => $e->getMessage()];
        }
    }

    public function list(User $user): array
    {
        try {
            return ['status' => Response::HTTP_OK, 'message' => 'Competencias encontradas com sucesso', 'data' => $this->mapper->toArrayList($this->skills->findByUser($user))];
        } catch (\Exception $e) {
            return ['status' => Response::HTTP_INTERNAL_SERVER_ERROR, 'message' => 'Ocorreu algum erro inesperado', 'errors' => $e->getMessage()];
        }
    }
}