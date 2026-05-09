<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\ResumeSection\CreateResumeSectionDTO;
use App\DTO\ResumeSection\ReorderResumeSectionsDTO;
use App\DTO\ResumeSection\UpdateResumeSectionDTO;
use App\Entity\Resume;
use App\Entity\ResumeSection;
use App\Entity\User;
use App\Exception\Resume\ResumeNotFoundException;
use App\Exception\Resume\ResumeSectionNotFoundException;
use App\Mapper\ResumeSectionMapper;
use App\Repository\ResumeRepository;
use App\Repository\ResumeSectionRepository;
use Symfony\Component\HttpFoundation\Response;

final class ResumeSectionService
{
    public function __construct(
        private readonly ResumeRepository $resumes,
        private readonly ResumeSectionRepository $sections,
        private readonly ResumeSectionMapper $mapper,
    ) {
    }

    /**
     * @return array{status: int, message: string, data?: list<array<string, mixed>>, errors?: mixed}
     */
    public function list(User $user, string $publicResumeId): array
    {
        try {
            $resume = $this->requireResume($user, $publicResumeId);

            return [
                'status' => Response::HTTP_OK,
                'message' => 'Secoes listadas com sucesso',
                'data' => $this->mapper->toArrayList($this->sections->findByResumeOrdered($resume)),
            ];
        } catch (ResumeNotFoundException $e) {
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
     * @return array{status: int, message: string, data?: array<string, mixed>, errors?: mixed}
     */
    public function create(User $user, string $publicResumeId, CreateResumeSectionDTO $dto): array
    {
        try {
            $resume = $this->requireResume($user, $publicResumeId);

            $section = new ResumeSection($resume, $dto->sectionType, $dto->position);
            $section->setTitle($dto->title);
            $section->setContent($dto->content);
            $section->setIsVisible($dto->isVisible);

            $this->sections->save($section);

            return [
                'status' => Response::HTTP_CREATED,
                'message' => 'Secao criada com sucesso',
                'data' => $this->mapper->toArray($section),
            ];
        } catch (ResumeNotFoundException $e) {
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
     * @return array{status: int, message: string, data?: array<string, mixed>, errors?: mixed}
     */
    public function update(User $user, string $publicResumeId, int $sectionId, UpdateResumeSectionDTO $dto): array
    {
        try {
            $resume = $this->requireResume($user, $publicResumeId);
            $section = $this->sections->findOneOnResume($resume, $sectionId);

            if (!$section instanceof ResumeSection) {
                throw new ResumeSectionNotFoundException('Secao nao encontrada');
            }

            $section->applyUpdate(
                $dto->sectionType,
                $dto->title,
                $dto->content,
                $dto->position,
                $dto->isVisible,
            );

            $this->sections->save($section);

            return [
                'status' => Response::HTTP_OK,
                'message' => 'Secao atualizada com sucesso',
                'data' => $this->mapper->toArray($section),
            ];
        } catch (ResumeSectionNotFoundException $e) {
            return [
                'status' => Response::HTTP_NOT_FOUND,
                'message' => $e->getMessage(),
            ];
        } catch (ResumeNotFoundException $e) {
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
    public function delete(User $user, string $publicResumeId, int $sectionId): array
    {
        try {
            $resume = $this->requireResume($user, $publicResumeId);
            $section = $this->sections->findOneOnResume($resume, $sectionId);

            if (!$section instanceof ResumeSection) {
                throw new ResumeSectionNotFoundException('Secao nao encontrada');
            }

            $this->sections->remove($section);

            return [
                'status' => Response::HTTP_OK,
                'message' => 'Secao removida com sucesso',
            ];
        } catch (ResumeSectionNotFoundException $e) {
            return [
                'status' => Response::HTTP_NOT_FOUND,
                'message' => $e->getMessage(),
            ];
        } catch (ResumeNotFoundException $e) {
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
    public function reorder(User $user, string $publicResumeId, ReorderResumeSectionsDTO $dto): array
    {
        try {
            $resume = $this->requireResume($user, $publicResumeId);

            $current = $this->sections->findByResumeOrdered($resume);
            $existingIds = array_map(static fn (ResumeSection $s): int => (int) $s->getId(), $current);
            sort($existingIds);

            $ordered = $dto->orderedIds;
            $orderedSorted = $ordered;
            sort($orderedSorted);

            if ($existingIds !== $orderedSorted || \count(array_unique($ordered)) !== \count($ordered)) {
                return [
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'message' => 'Lista ordered_ids deve conter todos os ids das secoes, sem duplicados',
                ];
            }

            foreach ($dto->orderedIds as $idx => $id) {
                $section = $this->sections->findOneOnResume($resume, $id);
                if (!$section instanceof ResumeSection) {
                    return [
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                        'message' => 'Id de secao invalido',
                    ];
                }
                $section->setPosition($idx);
                $this->sections->save($section);
            }

            return [
                'status' => Response::HTTP_OK,
                'message' => 'Ordem das secoes atualizada',
                'data' => $this->mapper->toArrayList($this->sections->findByResumeOrdered($resume)),
            ];
        } catch (ResumeNotFoundException $e) {
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

    private function requireResume(User $user, string $publicResumeId): Resume
    {
        $resume = $this->resumes->findByPublicIdForUser($publicResumeId, $user);

        if (!$resume instanceof Resume) {
            throw new ResumeNotFoundException('Curriculo nao encontrado');
        }

        return $resume;
    }
}
