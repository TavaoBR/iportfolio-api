<?php

declare(strict_types=1);

namespace App\DTO\Certification;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateCertificationDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Nome da certificacao e obrigatorio')]
        #[Assert\Length(max: 180)]
        public readonly string $name,

        #[Assert\Length(max: 180)]
        public readonly ?string $issuer = null,

        #[SerializedName('credential_url')]
        #[Assert\Url(requireTld: true, message: 'URL da credencial invalida')]
        #[Assert\Length(max: 255)]
        public readonly ?string $credentialUrl = null,

        #[SerializedName('issued_at')]
        #[Assert\Date(message: 'Data de emissao invalida')]
        public readonly ?string $issuedAt = null,

        #[SerializedName('expires_at')]
        #[Assert\Date(message: 'Data de expiracao invalida')]
        public readonly ?string $expiresAt = null,

        #[SerializedName('sort_order')]
        #[Assert\PositiveOrZero(message: 'Ordem deve ser zero ou maior')]
        public readonly int $sortOrder = 0,
    ) {
    }
}