<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Entity\TemplateCatalogItem;
use App\Entity\User;
use App\Repository\UserTemplateUnlockRepository;

final class TemplateCatalogMapper
{
    /**
     * Lista pública: preview para escolha; nao expoe bundle_ref (ficheiro interno).
     *
     * @return array<string, mixed>
     */
    public function toPublicArray(TemplateCatalogItem $tpl): array
    {
        return [
            'id' => $tpl->getId(),
            'name' => $tpl->getName(),
            'template_key' => $tpl->getTemplateKey(),
            'type' => $tpl->getType(),
            'preview_image' => $tpl->getPreviewImage(),
            'preview_url' => $tpl->getPreviewUrl(),
            'is_premium' => $tpl->isPremium(),
            /** Preço sugerido para checkout Mercado Pago (pode falhar sobre fallback configurado na API). */
            'premium_price' => $tpl->getPremiumPrice(),
            /** Esquema opcional (secções / slots) para autocomplete no editor */
            'section_schema' => $tpl->getDefinitionJson(),
        ];
    }

    /**
     * Lista autenticada: indica se o utilizador pode aplicar a template (gratuita ou desbloqueada).
     *
     * @return array<string, mixed>
     */
    public function toUserCatalogRow(
        TemplateCatalogItem $tpl,
        User $user,
        UserTemplateUnlockRepository $unlocks,
    ): array {
        $canUse = !$tpl->isPremium() || $unlocks->hasUnlock($user, $tpl);
        $row = $this->toPublicArray($tpl);
        $row['is_unlocked'] = $tpl->isPremium() && $unlocks->hasUnlock($user, $tpl);
        $row['can_use'] = $canUse;
        if ($canUse) {
            $row['bundle_ref'] = $tpl->getBundleRef();
        }

        return $row;
    }

    /**
     * @param list<TemplateCatalogItem> $templates
     * @return list<array<string, mixed>>
     */
    public function toPublicList(array $templates): array
    {
        return array_map(fn (TemplateCatalogItem $t): array => $this->toPublicArray($t), $templates);
    }

    /**
     * @param list<TemplateCatalogItem> $templates
     * @return list<array<string, mixed>>
     */
    public function toUserList(array $templates, User $user, UserTemplateUnlockRepository $unlocks): array
    {
        return array_map(
            fn (TemplateCatalogItem $t): array => $this->toUserCatalogRow($t, $user, $unlocks),
            $templates,
        );
    }
}
