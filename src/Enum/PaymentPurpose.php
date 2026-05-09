<?php

declare(strict_types=1);

namespace App\Enum;

enum PaymentPurpose: string
{
    /** Desbloqueio de template premium (Checkout Pro preference). */
    case TemplatePremiumUnlock = 'template_premium_unlock';

    /** Reserva para futuros fluxos cobrados. */
    case Other = 'other';
}
