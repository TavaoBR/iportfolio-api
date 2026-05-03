<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Resume\CreateResumeDTO;
use App\Entity\Resume;
use App\Entity\User;
use App\Mapper\ResumeMapper;
use App\Repository\ResumeRepository;
use Symfony\Component\HttpFoundation\Response;

final class ResumeService
{
    public function __construct(
        private readonly ResumeRepository $resumes,
        private readonly ResumeMapper $mapper,
        private readonly PublicIdGenerator $publicIds,
    ) {
    }

    /**
     * @return array{status: int, message: string, data?: array<string, mixed>, errors?: mixed}
     */
    public function create(User $user, CreateResumeDTO $dto): array
    {
        try {
            if ($dto->isMain) {
                $this->resumes->unsetMainForUser($user);
            }

            $resume = new Resume($user, $this->publicIds->uuidV4(), $dto->title);
            $resume->updateDraft(
                title: $dto->title,
                targetRole: $dto->targetRole,
                language: $dto->language,
                isMain: $dto->isMain,
            );

            $this->resumes->save($resume);

            return [
                'status' => Response::HTTP_CREATED,
                'message' => 'Curriculo criado com sucesso',
                'data' => $this->mapper->toArray($resume),
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
    public function show(User $user, string $publicId): array
    {
        try {
            $resume = $this->resumes->findByPublicIdForUser($publicId, $user);

            if (!$resume instanceof Resume) {
                return [
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Curriculo nao encontrado',
                ];
            }

            return [
                'status' => Response::HTTP_OK,
                'message' => 'Curriculo encontrado com sucesso',
                'data' => $this->mapper->toArray($resume),
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
                'message' => 'Curriculos encontrados com sucesso',
                'data' => $this->mapper->toArrayList($this->resumes->findByUser($user)),
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