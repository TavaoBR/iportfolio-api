<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Entity\UserProfile;

final class UserProfileMapper
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(UserProfile $profile): array
    {
        return [
            'id' => $profile->getId(),
            'headline' => $profile->getHeadline(),
            'bio' => $profile->getBio(),
            'phone' => $profile->getPhone(),
            'city' => $profile->getCity(),
            'state' => $profile->getState(),
            'country' => $profile->getCountry(),
            'linkedin_url' => $profile->getLinkedinUrl(),
            'github_url' => $profile->getGithubUrl(),
            'website_url' => $profile->getWebsiteUrl(),
            'created_at' => $profile->getCreatedAt()->format(DATE_ATOM),
            'updated_at' => $profile->getUpdatedAt()?->format(DATE_ATOM),
        ];
    }
}