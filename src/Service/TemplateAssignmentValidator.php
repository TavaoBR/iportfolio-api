<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\TemplateCatalogItem;
use App\Entity\User;
use App\Repository\TemplateCatalogRepository;
use App\Repository\UserTemplateUnlockRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * Valida se o utilizador pode aplicar uma template_key a um recurso resume ou portfolio.
 */
final class TemplateAssignmentValidator
{
    public function __construct(
        private readonly TemplateCatalogRepository $templates,
        private readonly UserTemplateUnlockRepository $unlocks,
    ) {
    }

    /**
     * @return array{status: int, message: string}|null erro HTTP ou null se OK
     */
    public function validateResumeUse(User $user, ?string $templateKey): ?array
    {
        if ($templateKey === null || trim($templateKey) === '') {
            return null;
        }

        return $this->validate($user, $templateKey, 'resume');
    }

    /**
     * @return array{status: int, message: string}|null
     */
    public function validatePortfolioUse(User $user, ?string $templateKey): ?array
    {
        if ($templateKey === null || trim($templateKey) === '') {
            return null;
        }

        return $this->validate($user, $templateKey, 'portfolio');
    }

    /**
     * @return array{status: int, message: string}|null
     */
    private function validate(User $user, string $templateKey, string $expectedType): ?array
    {
        $tpl = $this->templates->findActiveByTemplateKey($templateKey);

        if (!$tpl instanceof TemplateCatalogItem) {
            return [
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Template nao encontrada ou inativa',
            ];
        }

        if ($tpl->getType() !== $expectedType) {
            return [
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'Tipo do template invalido para este recurso',
            ];
        }

        if (!$tpl->isPremium()) {
            return null;
        }

        if ($this->unlocks->hasUnlock($user, $tpl)) {
            return null;
        }

        return [
            'status' => Response::HTTP_PAYMENT_REQUIRED,
            'message' => 'Esta template e premium. Desbloqueie-a (pagamento) antes de aplicar ao curriculo ou portfolio.',
        ];
    }
}
