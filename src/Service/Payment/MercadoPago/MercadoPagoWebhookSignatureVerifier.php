<?php

declare(strict_types=1);

namespace App\Service\Payment\MercadoPago;

use Symfony\Component\HttpFoundation\Request;

/**
 * Valida notificação com x-signature (secret gerado na aplicação do MP).
 *
 * @see https://www.mercadopago.com.br/developers/pt/docs/your-integrations/notifications/webhooks
 */
final class MercadoPagoWebhookSignatureVerifier
{
    public function verify(Request $request, string $secret): bool
    {
        $secret = trim($secret);

        if ($secret === '') {
            return true;
        }

        $xSignature = $request->headers->get('x-signature');
        $requestId = $request->headers->get('x-request-id');

        if (!\is_string($xSignature) || $xSignature === ''
            || !\is_string($requestId) || $requestId === '') {
            return false;
        }

        $parts = explode(',', $xSignature);
        $ts = '';
        $v1Hash = '';

        foreach ($parts as $part) {
            $kv = explode('=', trim($part), 2);
            if (\count($kv) !== 2) {
                continue;
            }
            $k = strtolower(trim($kv[0]));
            $v = trim($kv[1]);
            match ($k) {
                'ts' => $ts = $v,
                'v1' => $v1Hash = $v,
                default => null,
            };
        }

        if ($ts === '' || $v1Hash === '') {
            return false;
        }

        /** @var string $dataId */
        $dataId = $request->query->get('data.id', '');
        if (!\is_string($dataId)) {
            $dataId = (string) $dataId;
        }

        $manifest = sprintf('id:%s;request-id:%s;ts:%s;', $dataId, $requestId, $ts);
        $expected = hash_hmac('sha256', $manifest, $secret);

        return hash_equals(strtolower((string) $expected), strtolower($v1Hash));
    }
}
