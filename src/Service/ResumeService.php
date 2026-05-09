<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Resume\CreateResumeDTO;
use App\DTO\Resume\UpdateResumeDTO;
use App\Entity\Resume;
use App\Entity\User;
use App\Exception\Resume\ResumeNotFoundException;
use App\Mapper\ResumeMapper;
use App\Repository\ResumeRepository;
use Symfony\Component\HttpFoundation\Response;

final class ResumeService
{
    public function __construct(
        private readonly ResumeRepository $resumes,
        private readonly ResumeMapper $mapper,
        private readonly PublicIdGenerator $publicIds,
        private readonly TemplateAssignmentValidator $templateUse,
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

            $templateErr = $this->templateUse->validateResumeUse($user, $dto->templateKey);
            if ($templateErr !== null) {
                return $templateErr;
            }

            $resume = new Resume($user, $this->publicIds->uuidV4(), $dto->title);
            $resume->updateDraft(
                title: $dto->title,
                targetRole: $dto->targetRole,
                language: $dto->language,
                isMain: $dto->isMain,
                isPublic: false,
                templateKey: $dto->templateKey,
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
                throw new ResumeNotFoundException();
            }

            return [
                'status' => Response::HTTP_OK,
                'message' => 'Curriculo encontrado com sucesso',
                'data' => $this->mapper->toArray($resume),
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
    public function update(User $user, string $publicId, UpdateResumeDTO $dto): array
    {
        try {
            $resume = $this->resumes->findByPublicIdForUser($publicId, $user);

            if (!$resume instanceof Resume) {
                throw new ResumeNotFoundException();
            }

            if ($dto->isMain === true) {
                $this->resumes->unsetMainForUser($user);
            }

            $title = $dto->title !== null ? $dto->title : $resume->getTitle();
            $targetRole = $dto->targetRole !== null ? $dto->targetRole : $resume->getTargetRole();
            $language = $dto->language !== null ? $dto->language : $resume->getLanguage();
            $isMain = $dto->isMain !== null ? $dto->isMain : $resume->isMain();
            $isPublic = $dto->isPublic !== null ? $dto->isPublic : $resume->isPublic();
            $templateKey = $dto->templateKey !== null ? $dto->templateKey : $resume->getTemplateKey();

            $templateErr = $this->templateUse->validateResumeUse($user, $templateKey);
            if ($templateErr !== null) {
                return $templateErr;
            }

            $resume->updateDraft($title, $targetRole, $language, $isMain, $isPublic, $templateKey);

            $this->resumes->save($resume);

            return [
                'status' => Response::HTTP_OK,
                'message' => 'Curriculo atualizado com sucesso',
                'data' => $this->mapper->toArray($resume),
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

    /**
     * @return array{status: int, message: string, errors?: mixed}
     */
    public function delete(User $user, string $publicId): array
    {
        try {
            $resume = $this->resumes->findByPublicIdForUser($publicId, $user);

            if (!$resume instanceof Resume) {
                throw new ResumeNotFoundException();
            }

            $this->resumes->remove($resume);

            return [
                'status' => Response::HTTP_OK,
                'message' => 'Curriculo removido com sucesso',
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
}
