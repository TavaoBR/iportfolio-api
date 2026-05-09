<?php

declare(strict_types=1);

namespace App\Service\Payment\MercadoPago;

use App\Entity\PaymentTransaction;
use App\Entity\User;
use App\Repository\PaymentTransactionRepository;
use App\Repository\TemplateCatalogRepository;
use App\Repository\UserTemplateUnlockRepository;
use App\Service\PublicIdGenerator;
use Symfony\Component\HttpFoundation\Response;

final class MercadoPagoTemplateCheckoutService
{
    public function __construct(
        private readonly MercadoPagoApiClient $mercadoPago,
        private readonly PaymentTransactionRepository $payments,
        private readonly TemplateCatalogRepository $templates,
        private readonly UserTemplateUnlockRepository $unlocks,
        private readonly PublicIdGenerator $publicIds,
        private readonly string $defaultUri,
        private readonly string $premiumFallbackAmount,
    ) {
    }

    /**
     * @return array{status: int, message: string, data?: array<string, mixed>, errors?: mixed}
     */
    public function beginTemplateUnlockCheckout(User $user, string $templateKey): array
    {
        if (!$this->mercadoPago->isConfigured()) {
            return [
                'status' => Response::HTTP_SERVICE_UNAVAILABLE,
                'message' => 'Mercado Pago nao configurado (MERCADOPAGO_ACCESS_TOKEN)',
            ];
        }

        $key = mb_strtolower(trim($templateKey));
        $tpl = $this->templates->findActiveByTemplateKey($key);

        if ($tpl === null) {
            return [
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Template nao encontrada ou inativa',
            ];
        }

        if (!$tpl->isPremium()) {
            return [
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'Esta template e gratuita; nao precisa de checkout',
            ];
        }

        if ($this->unlocks->hasUnlock($user, $tpl)) {
            return [
                'status' => Response::HTTP_OK,
                'message' => 'Template ja desbloqueada',
                'data' => [
                    'already_unlocked' => true,
                    'template_key' => $tpl->getTemplateKey(),
                ],
            ];
        }

        $amountStr = $this->resolveAmount($tpl->getPremiumPrice(), $this->premiumFallbackAmount);
        if ($amountStr === null || (float) $amountStr <= 0) {
            return [
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'Preco da template premium indefinido. Defina premium_price no catalogo ou MERCADOPAGO_PREMIUM_FALLBACK_AMOUNT.',
            ];
        }

        $publicId = $this->publicIds->uuidV4();
        $transaction = PaymentTransaction::beginTemplateUnlock(
            publicId: $publicId,
            user: $user,
            template: $tpl,
            amount: $amountStr,
            currency: 'BRL',
        );

        $base = rtrim($this->defaultUri, '/');

        $payload = [
            'items' => [
                [
                    'title' => 'Template '.$tpl->getName(),
                    'quantity' => 1,
                    'unit_price' => (float) $amountStr,
                    'currency_id' => 'BRL',
                ],
            ],
            'payer' => [
                'email' => $user->getEmail(),
            ],
            'external_reference' => $publicId,
            'notification_url' => $base.'/webhooks/mercadopago',
            'back_urls' => [
                'success' => $base.'/',
                'failure' => $base.'/',
                'pending' => $base.'/',
            ],
            'statement_descriptor' => 'IPORTFOLIO',
            'binary_mode' => false,
        ];

        $preference = $this->mercadoPago->createCheckoutPreference($payload);

        if ($preference === null) {
            return [
                'status' => Response::HTTP_BAD_GATEWAY,
                'message' => 'Nao foi possivel criar o checkout no Mercado Pago',
            ];
        }

        $prefId = isset($preference['id']) && \is_string($preference['id']) ? $preference['id'] : null;
        if ($prefId === null || $prefId === '') {
            return [
                'status' => Response::HTTP_BAD_GATEWAY,
                'message' => 'Resposta do Mercado Pago sem id da preference',
            ];
        }

        $transaction->mergeMetadata([
            'mercado_pago' => [
                'preference_request' => $payload,
                'preference_response' => $preference,
            ],
        ]);
        $transaction->setGatewayPreferenceId($prefId);
        $this->payments->save($transaction);

        $initPoint = \is_string($preference['init_point'] ?? null) ? $preference['init_point'] : null;
        $sandboxInitPoint = \is_string($preference['sandbox_init_point'] ?? null) ? $preference['sandbox_init_point'] : null;

        return [
            'status' => Response::HTTP_CREATED,
            'message' => 'Checkout Mercado Pago criado',
            'data' => [
                'transaction_public_id' => $publicId,
                'preference_id' => $prefId,
                'init_point' => $initPoint,
                'sandbox_init_point' => $sandboxInitPoint,
                'amount' => $amountStr,
                'currency' => 'BRL',
            ],
        ];
    }

    private function resolveAmount(?string $templatePrice, string $fallbackRaw): ?string
    {
        $fromTemplate = $this->normalizeMoney($templatePrice);
        if ($fromTemplate !== null) {
            return $fromTemplate;
        }

        return $this->normalizeMoney($fallbackRaw);
    }

    private function normalizeMoney(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $v = trim($value);
        if ($v === '' || !is_numeric($v)) {
            return null;
        }

        return number_format((float) $v, 2, '.', '');
    }
}
