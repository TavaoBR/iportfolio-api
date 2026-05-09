<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Portfolio\CreatePortfolioSectionDTO;
use App\DTO\Portfolio\ReorderPortfolioSectionsDTO;
use App\DTO\Portfolio\UpdatePortfolioSectionDTO;
use App\Entity\PortfolioSection;
use App\Entity\PortfolioSite;
use App\Entity\User;
use App\Exception\Portfolio\PortfolioSectionNotFoundException;
use App\Exception\Portfolio\PortfolioSiteNotFoundException;
use App\Mapper\PortfolioSectionMapper;
use App\Repository\PortfolioSectionRepository;
use App\Repository\PortfolioSiteRepository;
use Symfony\Component\HttpFoundation\Response;

final class PortfolioSectionService
{
    public function __construct(
        private readonly PortfolioSiteRepository $sites,
        private readonly PortfolioSectionRepository $sections,
        private readonly PortfolioSectionMapper $mapper,
    ) {
    }

    /**
     * @return array{status: int, message: string, data?: list<array<string, mixed>>, errors?: mixed}
     */
    public function list(User $user, int $portfolioId): array
    {
        try {
            $site = $this->requireSite($user, $portfolioId);

            return [
                'status' => Response::HTTP_OK,
                'message' => 'Secoes listadas com sucesso',
                'data' => $this->mapper->toArrayList($this->sections->findBySiteOrdered($site)),
            ];
        } catch (PortfolioSiteNotFoundException $e) {
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
    public function create(User $user, int $portfolioId, CreatePortfolioSectionDTO $dto): array
    {
        try {
            $site = $this->requireSite($user, $portfolioId);

            $section = new PortfolioSection($site, $dto->sectionType, $dto->layoutType, $dto->position);
            $section->setIsVisible($dto->isVisible);
            if ($dto->settings !== null) {
                $section->setSettingsJson($dto->settings);
            }

            $this->sections->save($section);

            return [
                'status' => Response::HTTP_CREATED,
                'message' => 'Secao criada com sucesso',
                'data' => $this->mapper->toArray($section),
            ];
        } catch (PortfolioSiteNotFoundException $e) {
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
    public function update(User $user, int $portfolioId, int $sectionId, UpdatePortfolioSectionDTO $dto): array
    {
        try {
            $site = $this->requireSite($user, $portfolioId);
            $section = $this->sections->findOneOnSite($site, $sectionId);

            if (!$section instanceof PortfolioSection) {
                throw new PortfolioSectionNotFoundException('Secao nao encontrada');
            }

            $section->applyUpdate(
                $dto->sectionType,
                $dto->layoutType,
                $dto->position,
                $dto->isVisible,
                $dto->settings,
            );

            $this->sections->save($section);

            return [
                'status' => Response::HTTP_OK,
                'message' => 'Secao atualizada com sucesso',
                'data' => $this->mapper->toArray($section),
            ];
        } catch (PortfolioSectionNotFoundException $e) {
            return [
                'status' => Response::HTTP_NOT_FOUND,
                'message' => $e->getMessage(),
            ];
        } catch (PortfolioSiteNotFoundException $e) {
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
    public function delete(User $user, int $portfolioId, int $sectionId): array
    {
        try {
            $site = $this->requireSite($user, $portfolioId);
            $section = $this->sections->findOneOnSite($site, $sectionId);

            if (!$section instanceof PortfolioSection) {
                throw new PortfolioSectionNotFoundException('Secao nao encontrada');
            }

            $this->sections->remove($section);

            return [
                'status' => Response::HTTP_OK,
                'message' => 'Secao removida com sucesso',
            ];
        } catch (PortfolioSectionNotFoundException $e) {
            return [
                'status' => Response::HTTP_NOT_FOUND,
                'message' => $e->getMessage(),
            ];
        } catch (PortfolioSiteNotFoundException $e) {
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
    public function reorder(User $user, int $portfolioId, ReorderPortfolioSectionsDTO $dto): array
    {
        try {
            $site = $this->requireSite($user, $portfolioId);

            $current = $this->sections->findBySiteOrdered($site);
            $existingIds = array_map(static fn (PortfolioSection $s): int => (int) $s->getId(), $current);
            sort($existingIds);

            $orderedSorted = $dto->orderedIds;
            sort($orderedSorted);

            if ($existingIds !== $orderedSorted || \count(array_unique($dto->orderedIds)) !== \count($dto->orderedIds)) {
                return [
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'message' => 'Lista ordered_ids deve conter todos os ids das secoes, sem duplicados',
                ];
            }

            foreach ($dto->orderedIds as $idx => $id) {
                $section = $this->sections->findOneOnSite($site, $id);
                if (!$section instanceof PortfolioSection) {
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
                'data' => $this->mapper->toArrayList($this->sections->findBySiteOrdered($site)),
            ];
        } catch (PortfolioSiteNotFoundException $e) {
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

    private function requireSite(User $user, int $portfolioId): PortfolioSite
    {
        $site = $this->sites->findOneOwnedByUser($user, $portfolioId);

        if (!$site instanceof PortfolioSite) {
            throw new PortfolioSiteNotFoundException('Portfolio nao encontrado');
        }

        return $site;
    }
}
