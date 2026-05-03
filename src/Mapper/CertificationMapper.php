<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Entity\Certification;

final class CertificationMapper
{
    public function toArray(Certification $certification): array
    {
        return [
            'id' => $certification->getId(),
            'name' => $certification->getName(),
            'issuer' => $certification->getIssuer(),
            'credential_url' => $certification->getCredentialUrl(),
            'issued_at' => $certification->getIssuedAt()?->format('Y-m-d'),
            'expires_at' => $certification->getExpiresAt()?->format('Y-m-d'),
            'sort_order' => $certification->getSortOrder(),
            'created_at' => $certification->getCreatedAt()->format(DATE_ATOM),
            'updated_at' => $certification->getUpdatedAt()?->format(DATE_ATOM),
        ];
    }

    public function toArrayList(array $certifications): array
    {
        return array_map(fn (Certification $certification): array => $this->toArray($certification), $certifications);
    }
}