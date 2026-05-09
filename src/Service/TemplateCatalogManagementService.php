<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Template\CreateCatalogTemplateDTO;
use App\Entity\TemplateCatalogItem;
use App\Repository\TemplateCatalogRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * CRUD do catálogo (operacoes de backoffice). Protegido por ROLE_ADMIN no controller.
 */
final class TemplateCatalogManagementService
{
    public function __construct(
        private readonly TemplateCatalogRepository $templates,
    ) {
    }

    /**
     * @return array{status: int, message: string, data?: array<string, mixed>, errors?: mixed}
     */
    public function create(CreateCatalogTemplateDTO $dto): array
    {
        try {
            if ($this->templates->findOneByTemplateKey($dto->templateKey) instanceof TemplateCatalogItem) {
                return [
                    'status' => Response::HTTP_CONFLICT,
                    'message' => 'Ja existe template com esta chave',
                ];
            }

            $item = TemplateCatalogItem::createNew(
                name: $dto->name,
                templateKey: $dto->templateKey,
                type: $dto->type,
                isPremium: $dto->isPremium,
                previewImage: $dto->previewImage,
                previewUrl: $dto->previewUrl,
                bundleRef: $dto->bundleRef,
                definitionJson: $dto->definitionJson,
                premiumPrice: $dto->premiumPrice,
            );

            $this->templates->save($item);

            return [
                'status' => Response::HTTP_CREATED,
                'message' => 'Template registada no catalogo',
                'data' => [
                    'template_key' => $item->getTemplateKey(),
                    'id' => $item->getId(),
                ],
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
     * @param array<string, mixed> $patch corpo JSON (parcial)
     *
     * @return array{status: int, message: string, data?: array<string, mixed>, errors?: mixed}
     */
    public function partialUpdate(string $templateKey, array $patch): array
    {
        try {
            $item = $this->templates->findOneByTemplateKey($templateKey);

            if (!$item instanceof TemplateCatalogItem) {
                return [
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Template nao encontrada',
                ];
            }

            $replaceDefinition = \array_key_exists('definition_json', $patch);
            $definitionValue = $replaceDefinition ? $patch['definition_json'] : null;
            if ($replaceDefinition && $definitionValue !== null && !\is_array($definitionValue)) {
                return [
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'message' => 'definition_json deve ser um objeto JSON',
                ];
            }

            /** @var ?array<string, mixed> $definitionArray */
            $definitionArray = $replaceDefinition ? (\is_array($definitionValue) ? $definitionValue : null) : null;

            $replacePremium = \array_key_exists('premium_price', $patch);
            $premiumValue = null;
            if ($replacePremium) {
                $pv = $patch['premium_price'];
                if ($pv === null || $pv === '') {
                    $premiumValue = null;
                } elseif (\is_numeric($pv)) {
                    $premiumValue = (string) $pv;
                } elseif (\is_string($pv) && is_numeric(trim($pv))) {
                    $premiumValue = trim($pv);
                } else {
                    return [
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                        'message' => 'premium_price invalido',
                    ];
                }
            }

            $item->applyAdminUpdate(
                name: \array_key_exists('name', $patch) && \is_string($patch['name']) ? $patch['name'] : null,
                type: \array_key_exists('type', $patch) && \is_string($patch['type']) ? $patch['type'] : null,
                isPremium: \array_key_exists('is_premium', $patch) ? (bool) $patch['is_premium'] : null,
                isActive: \array_key_exists('is_active', $patch) ? (bool) $patch['is_active'] : null,
                previewImage: \array_key_exists('preview_image', $patch) && \is_string($patch['preview_image']) ? $patch['preview_image'] : null,
                previewUrl: \array_key_exists('preview_url', $patch) && \is_string($patch['preview_url']) ? $patch['preview_url'] : null,
                bundleRef: \array_key_exists('bundle_ref', $patch) && \is_string($patch['bundle_ref']) ? $patch['bundle_ref'] : null,
                replaceDefinitionSchema: $replaceDefinition,
                definitionJson: $definitionArray,
                replacePremiumPrice: $replacePremium,
                premiumPrice: $premiumValue,
            );

            $this->templates->save($item);

            return [
                'status' => Response::HTTP_OK,
                'message' => 'Template atualizada',
                'data' => ['template_key' => $item->getTemplateKey()],
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
