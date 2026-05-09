<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\Payment\MercadoPago\MercadoPagoWebhookProcessor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Webhook Mercado Pago (IPN / notificações). Rota pública; validação via assinatura opcional.
 */
final class MercadoPagoWebhookController extends AbstractController
{
    public function __construct(
        private readonly MercadoPagoWebhookProcessor $processor,
    ) {
    }

    #[Route('/webhooks/mercadopago', name: 'webhook_mercadopago', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $result = $this->processor->handle($request);

        return new JsonResponse($result['body'], $result['http_status']);
    }
}
