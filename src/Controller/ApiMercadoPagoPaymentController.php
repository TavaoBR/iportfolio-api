<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\RequiresAuth;
use App\DTO\Payment\MercadoPagoTemplateCheckoutDTO;
use App\Entity\User;
use App\Service\ApiResponseService;
use App\Service\Payment\MercadoPago\MercadoPagoTemplateCheckoutService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[RequiresAuth]
#[Route('/api/me/payments/mercadopago')]
final class ApiMercadoPagoPaymentController extends AbstractController
{
    public function __construct(
        private readonly MercadoPagoTemplateCheckoutService $checkout,
        private readonly ApiResponseService $api,
    ) {
    }

    /**
     * Cria Checkout Pro (preference) para desbloquear uma template premium.
     *
     * O frontend deve redirecionar o utilizador para `init_point` ou `sandbox_init_point`.
     */
    #[Route('/template-checkout', name: 'api_me_payments_mercadopago_template_checkout', methods: ['POST'])]
    public function templateCheckout(User $user, #[MapRequestPayload] MercadoPagoTemplateCheckoutDTO $dto): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->checkout->beginTemplateUnlockCheckout($user, $dto->templateKey),
        );
    }
}
