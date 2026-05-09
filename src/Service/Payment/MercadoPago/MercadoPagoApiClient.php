<?php

declare(strict_types=1);

namespace App\Service\Payment\MercadoPago;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Chamadas REST ao Mercado Pago (Checkout Pro preferences + recurso Payment).
 *
 * Documentação: https://www.mercadopago.com.br/developers/pt/reference
 */
final class MercadoPagoApiClient
{
    private const PREFERENCES_URL = 'https://api.mercadopago.com/checkout/preferences';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
        private readonly string $accessToken,
    ) {
    }

    public function isConfigured(): bool
    {
        return trim($this->accessToken) !== '';
    }

    /**
     * Cria Checkout Pro preference.
     *
     * @param array<string, mixed> $body
     *
     * @return array<string, mixed>|null
     */
    public function createCheckoutPreference(array $body): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->httpClient->request('POST', self::PREFERENCES_URL, [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $body,
                'timeout' => 30,
            ]);

            $status = $response->getStatusCode();
            $data = $response->toArray(false);

            if ($status >= 400) {
                $this->logger->warning('Mercado Pago preference rejeitada', [
                    'status' => $status,
                    'body' => $data,
                ]);

                return null;
            }

            return $data;
        } catch (\Throwable $e) {
            $this->logger->error('Falha HTTP Mercado Pago (preference)', ['exception' => $e]);

            return null;
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetchPayment(string $paymentId): ?array
    {
        if (!$this->isConfigured() || trim($paymentId) === '') {
            return null;
        }

        $url = sprintf('https://api.mercadopago.com/v1/payments/%s', rawurlencode($paymentId));

        try {
            $response = $this->httpClient->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->accessToken,
                ],
                'timeout' => 30,
            ]);

            $status = $response->getStatusCode();
            $data = $response->toArray(false);

            if ($status >= 400) {
                $this->logger->warning('Mercado Pago fetch payment falhou', [
                    'status' => $status,
                    'payment_id' => $paymentId,
                    'body' => $data,
                ]);

                return null;
            }

            return $data;
        } catch (\Throwable $e) {
            $this->logger->error('Falha HTTP Mercado Pago (payment)', [
                'payment_id' => $paymentId,
                'exception' => $e,
            ]);

            return null;
        }
    }
}
