<?php

declare(strict_types=1);

namespace App\DTO\Payment;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class MercadoPagoTemplateCheckoutDTO
{
    public function __construct(
        #[SerializedName('template_key')]
        #[Assert\NotBlank]
        #[Assert\Length(max: 64)]
        public string $templateKey,
    ) {
    }
}
