<?php

declare(strict_types=1);

namespace App\DTO\Profile;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final class UpsertProfileDTO
{
    public function __construct(
        #[Assert\Length(max: 180, maxMessage: 'Headline deve ter no maximo 180 caracteres')]
        public readonly ?string $headline = null,

        #[Assert\Length(max: 3000, maxMessage: 'Bio deve ter no maximo 3000 caracteres')]
        public readonly ?string $bio = null,

        #[Assert\Length(max: 30, maxMessage: 'Telefone deve ter no maximo 30 caracteres')]
        public readonly ?string $phone = null,

        #[Assert\Length(max: 120, maxMessage: 'Cidade deve ter no maximo 120 caracteres')]
        public readonly ?string $city = null,

        #[Assert\Length(max: 80, maxMessage: 'Estado deve ter no maximo 80 caracteres')]
        public readonly ?string $state = null,

        #[Assert\Length(max: 80, maxMessage: 'Pais deve ter no maximo 80 caracteres')]
        public readonly ?string $country = null,

        #[SerializedName('linkedin_url')]
        #[Assert\Url(requireTld: true, message: 'LinkedIn deve ser uma URL valida')]
        #[Assert\Length(max: 255, maxMessage: 'LinkedIn deve ter no maximo 255 caracteres')]
        public readonly ?string $linkedinUrl = null,

        #[SerializedName('github_url')]
        #[Assert\Url(requireTld: true, message: 'GitHub deve ser uma URL valida')]
        #[Assert\Length(max: 255, maxMessage: 'GitHub deve ter no maximo 255 caracteres')]
        public readonly ?string $githubUrl = null,

        #[SerializedName('website_url')]
        #[Assert\Url(requireTld: true, message: 'Website deve ser uma URL valida')]
        #[Assert\Length(max: 255, maxMessage: 'Website deve ter no maximo 255 caracteres')]
        public readonly ?string $websiteUrl = null,
    ) {
    }
}