<?php

declare(strict_types=1);

namespace App\DTO\Template;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateCatalogTemplateDTO
{
    /**
     * @param array<string, mixed>|null $definitionJson
     */
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 120)]
        public string $name,

        #[SerializedName('template_key')]
        #[Assert\NotBlank]
        #[Assert\Length(max: 64)]
        #[Assert\Regex(pattern: '/^[a-z0-9]+(?:[-_][a-z0-9]+)*$/', message: 'Chave invalida')]
        public string $templateKey,

        #[Assert\Choice(choices: ['resume', 'portfolio'], message: 'Tipo invalido')]
        public string $type,

        #[SerializedName('is_premium')]
        public bool $isPremium = false,

        #[SerializedName('preview_image')]
        #[Assert\Length(max: 500)]
        public ?string $previewImage = null,

        #[SerializedName('preview_url')]
        #[Assert\Length(max: 2048)]
        public ?string $previewUrl = null,

        #[SerializedName('bundle_ref')]
        #[Assert\Length(max: 500)]
        public ?string $bundleRef = null,

        #[SerializedName('definition_json')]
        public ?array $definitionJson = null,

        /**
         * Valor opcional por template premium (decimal string, ex.: "29.90").
         */
        #[SerializedName('premium_price')]
        #[Assert\Length(max: 12)]
        public ?string $premiumPrice = null,
    ) {
    }
}
