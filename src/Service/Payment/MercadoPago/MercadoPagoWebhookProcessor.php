<?php

declare(strict_types=1);

namespace App\Service\Payment\MercadoPago;

use App\Entity\PaymentTransaction;
use App\Enum\PaymentPurpose;
use App\Enum\PaymentTransactionStatus;
use App\Repository\PaymentTransactionRepository;
use App\Service\TemplateUnlockService;
use Symfony\Component\HttpFoundation\Request;

/**
 * Processa notificações de pagamento (Checkout Pro) e atualiza {@see PaymentTransaction} + desbloqueio.
 */
final class MercadoPagoWebhookProcessor
{
    public function __construct(
        private readonly MercadoPagoApiClient $mercadoPago,
        private readonly PaymentTransactionRepository $payments,
        private readonly TemplateUnlockService $templateUnlock,
        private readonly MercadoPagoWebhookSignatureVerifier $signatureVerifier,
        private readonly string $webhookSecret,
    ) {
    }

    /**
     * @return array{http_status: int, body: array<string, mixed>}
     */
    public function handle(Request $request): array
    {
        if (!$this->mercadoPago->isConfigured()) {
            return [
                'http_status' => 503,
                'body' => ['message' => 'Mercado Pago nao configurado'],
            ];
        }

        if (!$this->signatureVerifier->verify($request, $this->webhookSecret)) {
            return [
                'http_status' => 403,
                'body' => ['message' => 'Assinatura invalida'],
            ];
        }

        $payload = $this->decodeJsonBody($request);
        $topic = $this->resolveTopic($request, $payload);

        if ($topic !== null && strcasecmp($topic, 'payment') !== 0) {
            return [
                'http_status' => 200,
                'body' => ['ignored' => true, 'topic' => $topic],
            ];
        }

        $paymentId = $this->resolvePaymentResourceId($request, $payload);
        if ($paymentId === null || $paymentId === '') {
            return [
                'http_status' => 400,
                'body' => ['message' => 'Notificacao sem id do pagamento'],
            ];
        }

        $payment = $this->mercadoPago->fetchPayment($paymentId);
        if ($payment === null) {
            return [
                'http_status' => 502,
                'body' => ['message' => 'Nao foi possivel obter dados do pagamento no Mercado Pago'],
            ];
        }

        $externalRefRaw = $payment['external_reference'] ?? null;
        $externalReference = \is_scalar($externalRefRaw) ? (string) $externalRefRaw : '';
        $externalReference = trim($externalReference);

        $transaction = $externalReference !== ''
            ? $this->payments->findByPublicId($externalReference)
            : null;

        if (!$transaction instanceof PaymentTransaction && isset($payment['preference_id'])) {
            $pref = \is_scalar($payment['preference_id']) ? (string) $payment['preference_id'] : '';
            if ($pref !== '') {
                $transaction = $this->payments->findByMercadoPagoPreferenceId($pref);
            }
        }

        if (!$transaction instanceof PaymentTransaction) {
            return [
                'http_status' => 200,
                'body' => ['status' => 'no_local_transaction'],
            ];
        }

        $mpStatus = \is_scalar($payment['status'] ?? null) ? strtolower((string) $payment['status']) : '';
        $newStatus = $this->mapMercadoPaymentStatus($mpStatus);

        if ($transaction->getStatus() === PaymentTransactionStatus::Paid) {
            return [
                'http_status' => 200,
                'body' => [
                    'status' => 'already_finalized',
                    'transaction_public_id' => $transaction->getPublicId(),
                ],
            ];
        }

        $txnAmount = $payment['transaction_amount'] ?? null;
        if (\is_numeric($txnAmount)) {
            $expected = round((float) $transaction->getAmount(), 2);
            $reported = round((float) $txnAmount, 2);
            if (abs($expected - $reported) >= 0.02) {
                $transaction->mergeMetadata([
                    'mercado_pago_amount_mismatch' => [
                        'expected' => $transaction->getAmount(),
                        'reported_transaction_amount' => (string) $txnAmount,
                    ],
                ]);
            }
        }

        $transaction->mergeMetadata(['last_mercado_pago_payment' => $payment]);

        if ($newStatus === PaymentTransactionStatus::Paid) {
            $transaction->markPaid($paymentId);
        } elseif ($newStatus === PaymentTransactionStatus::Processing) {
            $transaction->markProcessing();
        } elseif ($newStatus === PaymentTransactionStatus::Failed) {
            $transaction->markFailed($mpStatus !== '' ? $mpStatus : 'failed');
        } elseif ($newStatus === PaymentTransactionStatus::Cancelled) {
            $transaction->markCancelled();
        } elseif ($newStatus === PaymentTransactionStatus::Refunded) {
            $transaction->markRefunded();
        }

        $this->payments->save($transaction);

        if ($newStatus === PaymentTransactionStatus::Paid
            && $transaction->getPurpose() === PaymentPurpose::TemplatePremiumUnlock) {
            $template = $transaction->getRelatedTemplate();
            if ($template !== null) {
                $this->templateUnlock->recordPaidUnlockFromGateway(
                    $transaction->getUser(),
                    $template,
                    $paymentId,
                );
            }
        }

        return [
            'http_status' => 200,
            'body' => [
                'ok' => true,
                'transaction_public_id' => $transaction->getPublicId(),
                'payment_status' => $newStatus->value,
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function decodeJsonBody(Request $request): array
    {
        $content = trim((string) $request->getContent());
        if ($content === '') {
            return [];
        }

        try {
            $decoded = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return [];
        }

        return \is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function resolveTopic(Request $request, array $payload): ?string
    {
        $q = $request->query->get('topic');
        if (\is_string($q) && $q !== '') {
            return $q;
        }
        foreach (['type', 'topic'] as $field) {
            $v = $payload[$field] ?? null;
            if (\is_string($v) && $v !== '') {
                return $v;
            }
        }

        $action = $payload['action'] ?? null;
        if (\is_string($action) && str_starts_with(mb_strtolower($action), 'payment')) {
            return 'payment';
        }

        return null;
    }

    /** @param array<string, mixed> $payload */
    private function resolvePaymentResourceId(Request $request, array $payload): ?string
    {
        $candidates = [
            $request->query->get('id'),
            $request->query->get('data.id'),
        ];

        if (isset($payload['data']) && \is_array($payload['data']) && isset($payload['data']['id'])) {
            $candidates[] = $payload['data']['id'];
        }
        if (isset($payload['id'])) {
            $candidates[] = $payload['id'];
        }

        foreach ($candidates as $c) {
            if (\is_scalar($c)) {
                $s = trim((string) $c);
                if ($s !== '') {
                    return $s;
                }
            }
        }

        return null;
    }

    private function mapMercadoPaymentStatus(string $mpStatus): PaymentTransactionStatus
    {
        return match ($mpStatus) {
            'approved' => PaymentTransactionStatus::Paid,
            'refunded', 'charged_back' => PaymentTransactionStatus::Refunded,
            'cancelled', 'voided' => PaymentTransactionStatus::Cancelled,
            'rejected' => PaymentTransactionStatus::Failed,
            'pending',
            'in_process',
            'in_mediation',
            'authorized' => PaymentTransactionStatus::Processing,
            default => str_contains($mpStatus, 'rejected')
                ? PaymentTransactionStatus::Failed
                : PaymentTransactionStatus::Processing,
        };
    }
}
