<?php

declare(strict_types=1);

namespace App\DTO\Certification;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateCertificationDTO
{
    public function __construct(
        #[Assert\Type(type: 'string', message: 'O nome deve ser um texto')]
        #[Assert\Length(max: 180)]
        public ?string $name = null,

        #[Assert\Type(type: 'string', message: 'O emissor deve ser um texto')]
        #[Assert\Length(max: 180)]
        public ?string $issuer = null,

        #[SerializedName('credential_url')]
        #[Assert\Type(type: 'string', message: 'A URL deve ser um texto')]
        #[Assert\Length(max: 255)]
        public ?string $credentialUrl = null,

        #[SerializedName('issued_at')]
        #[Assert\Date(message: 'Data de emissao invalida')]
        public ?string $issuedAt = null,

        #[SerializedName('expires_at')]
        #[Assert\Date(message: 'Data de expiracao invalida')]
        public ?string $expiresAt = null,

        #[SerializedName('sort_order')]
        #[Assert\PositiveOrZero(message: 'Ordem deve ser zero ou maior')]
        public ?int $sortOrder = null,
    ) {
    }
}
