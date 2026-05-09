<?php

declare(strict_types=1);

namespace App\DTO\Template;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UnlockPremiumTemplateDTO
{
    public function __construct(
        #[SerializedName('template_key')]
        #[Assert\NotBlank]
        #[Assert\Length(max: 64)]
        public string $templateKey,

        #[SerializedName('payment_reference')]
        #[Assert\Length(max: 191)]
        public ?string $paymentReference = null,
    ) {
    }
}
