<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\TemplateCatalogItem;
use App\Entity\User;
use App\Repository\TemplateCatalogRepository;
use App\Repository\UserTemplateUnlockRepository;
use App\Entity\UserTemplateUnlock;
use Symfony\Component\HttpFoundation\Response;

/**
 * Regista desbloqueio de template premium.
 * Quando `TEMPLATE_UNLOCK_ALLOW_FREE=1`, aceita desbloqueio sem referencia de pagamento (apenas dev).
 * Caso contrario, e necessario `payment_reference` (ex.: id da sessao do gateway, preenchido pelo webhook).
 */
final class TemplateUnlockService
{
    public function __construct(
        private readonly TemplateCatalogRepository $templates,
        private readonly UserTemplateUnlockRepository $unlocks,
        private readonly bool $allowPremiumUnlockWithoutPayment,
    ) {
    }

    /**
     * @return array{status: int, message: string, data?: array<string, mixed>, errors?: mixed}
     */
    public function unlock(User $user, string $templateKey, ?string $paymentReference): array
    {
        $tpl = $this->templates->findActiveByTemplateKey($templateKey);

        if ($tpl === null) {
            return [
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Template nao encontrada ou inativa',
            ];
        }

        if (!$tpl->isPremium()) {
            return [
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'Esta template e gratuita; nao requer desbloqueio',
            ];
        }

        if ($this->unlocks->hasUnlock($user, $tpl)) {
            return [
                'status' => Response::HTTP_OK,
                'message' => 'Template ja estava desbloqueada',
                'data' => ['template_key' => $tpl->getTemplateKey(), 'already_unlocked' => true],
            ];
        }

        $ref = $paymentReference !== null ? trim($paymentReference) : null;
        $allow = $this->allowPremiumUnlockWithoutPayment && ($ref === null || $ref === '');

        if (!$allow && ($ref === null || $ref === '')) {
            return [
                'status' => Response::HTTP_PAYMENT_REQUIRED,
                'message' => 'Pagamento pendente. Envie payment_reference apos confirmacao do gateway, ou ative TEMPLATE_UNLOCK_ALLOW_FREE=1 em desenvolvimento.',
            ];
        }

        $row = new UserTemplateUnlock($user, $tpl, $allow ? 'dev_free_unlock' : $ref);
        $this->unlocks->save($row);

        return [
            'status' => Response::HTTP_CREATED,
            'message' => 'Template premium desbloqueada',
            'data' => [
                'template_key' => $tpl->getTemplateKey(),
                'unlocked_at' => $row->getUnlockedAt()->format(\DATE_ATOM),
            ],
        ];
    }

    /**
     * Registo após gateway confirmar pagamento (webhook).
     *
     * @return bool true quando criou novo desbloqueio; false quando já existia ou template inválido
     */
    public function recordPaidUnlockFromGateway(User $user, TemplateCatalogItem $template, string $gatewayPaymentReference): bool
    {
        if (!$template->isPremium() || !$template->isActive()) {
            return false;
        }

        if ($this->unlocks->hasUnlock($user, $template)) {
            return false;
        }

        $ref = mb_substr(trim($gatewayPaymentReference), 0, 191);
        if ($ref === '') {
            return false;
        }

        $this->unlocks->save(new UserTemplateUnlock($user, $template, $ref));

        return true;
    }
}
