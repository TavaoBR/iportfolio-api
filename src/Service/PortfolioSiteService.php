<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Portfolio\CreatePortfolioSiteDTO;
use App\DTO\Portfolio\UpdatePortfolioSiteDTO;
use App\Entity\User;
use App\Entity\PortfolioSection;
use App\Entity\PortfolioSite;
use App\Exception\Portfolio\PortfolioSiteNotFoundException;
use App\Exception\Portfolio\PortfolioSlugTakenException;
use App\Mapper\PortfolioSectionMapper;
use App\Mapper\PortfolioSiteMapper;
use App\Repository\PortfolioSectionRepository;
use App\Repository\PortfolioSiteRepository;
use Symfony\Component\HttpFoundation\Response;

final class PortfolioSiteService
{
    public function __construct(
        private readonly PortfolioSiteRepository $sites,
        private readonly PortfolioSectionRepository $portfolioSections,
        private readonly PortfolioSiteMapper $siteMapper,
        private readonly PortfolioSectionMapper $sectionMapper,
        private readonly TemplateAssignmentValidator $templateUse,
    ) {
    }

    /**
     * @return array{status: int, message: string, data?: list<array<string, mixed>>, errors?: mixed}
     */
    public function list(User $user): array
    {
        try {
            return [
                'status' => Response::HTTP_OK,
                'message' => 'Portfolios encontrados com sucesso',
                'data' => array_map(
                    fn (PortfolioSite $p): array => $this->siteMapper->toArray($p),
                    $this->sites->findByUser($user),
                ),
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
    public function create(User $user, CreatePortfolioSiteDTO $dto): array
    {
        try {
            if ($this->sites->existsSlugForOtherSite($dto->slug, null)) {
                throw new PortfolioSlugTakenException('Este slug ja esta em uso');
            }

            $templateErr = $this->templateUse->validatePortfolioUse($user, $dto->templateKey);
            if ($templateErr !== null) {
                return $templateErr;
            }

            $site = new PortfolioSite($user, $dto->slug, $dto->title);
            $site->setSubtitle($dto->subtitle);
            $site->setTemplateKey($dto->templateKey);

            $this->sites->save($site);

            return [
                'status' => Response::HTTP_CREATED,
                'message' => 'Portfolio criado com sucesso',
                'data' => $this->siteMapper->toArray($site),
            ];
        } catch (PortfolioSlugTakenException $e) {
            return [
                'status' => Response::HTTP_CONFLICT,
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
    public function show(User $user, int $id): array
    {
        try {
            $site = $this->sites->findOneOwnedByUser($user, $id);

            if (!$site instanceof PortfolioSite) {
                throw new PortfolioSiteNotFoundException('Portfolio nao encontrado');
            }

            return [
                'status' => Response::HTTP_OK,
                'message' => 'Portfolio encontrado',
                'data' => [
                    ...$this->siteMapper->toArray($site),
                    'sections' => $this->sectionMapper->toArrayList(
                        $this->portfolioSections->findBySiteOrdered($site),
                    ),
                ],
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
    public function update(User $user, int $id, UpdatePortfolioSiteDTO $dto): array
    {
        try {
            $site = $this->sites->findOneOwnedByUser($user, $id);

            if (!$site instanceof PortfolioSite) {
                throw new PortfolioSiteNotFoundException('Portfolio nao encontrado');
            }

            if ($dto->slug !== null && $this->sites->existsSlugForOtherSite($dto->slug, $site->getId())) {
                throw new PortfolioSlugTakenException('Este slug ja esta em uso');
            }

            $nextTemplateKey = $dto->templateKey !== null ? $dto->templateKey : $site->getTemplateKey();
            $templateErr = $this->templateUse->validatePortfolioUse($user, $nextTemplateKey);
            if ($templateErr !== null) {
                return $templateErr;
            }

            $site->update(
                $dto->slug,
                $dto->title,
                $dto->subtitle,
                $dto->templateKey,
                $dto->isPublic,
            );

            $this->sites->save($site);

            return [
                'status' => Response::HTTP_OK,
                'message' => 'Portfolio atualizado com sucesso',
                'data' => $this->siteMapper->toArray($site),
            ];
        } catch (PortfolioSlugTakenException $e) {
            return [
                'status' => Response::HTTP_CONFLICT,
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
    public function delete(User $user, int $id): array
    {
        try {
            $site = $this->sites->findOneOwnedByUser($user, $id);

            if (!$site instanceof PortfolioSite) {
                throw new PortfolioSiteNotFoundException('Portfolio nao encontrado');
            }

            $this->sites->remove($site);

            return [
                'status' => Response::HTTP_OK,
                'message' => 'Portfolio removido com sucesso',
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
    public function publish(User $user, int $id): array
    {
        try {
            $site = $this->sites->findOneOwnedByUser($user, $id);

            if (!$site instanceof PortfolioSite) {
                throw new PortfolioSiteNotFoundException('Portfolio nao encontrado');
            }

            $site->publish();
            $this->sites->save($site);

            return [
                'status' => Response::HTTP_OK,
                'message' => 'Portfolio publicado',
                'data' => $this->siteMapper->toArray($site),
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
    public function showPublishedBySlug(string $slug): array
    {
        $site = $this->sites->findPublicBySlug($slug);

        if (!$site instanceof PortfolioSite) {
            return [
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Portfolio nao encontrado ou nao publico',
            ];
        }

        $visible = array_values(array_filter(
            $this->portfolioSections->findBySiteOrdered($site),
            static fn (PortfolioSection $s): bool => $s->isVisible(),
        ));

        return [
            'status' => Response::HTTP_OK,
            'message' => 'Portfolio publico carregado',
            'data' => [
                'portfolio' => $this->siteMapper->toArray($site),
                'sections' => $this->sectionMapper->toArrayList($visible),
            ],
        ];
    }
}
