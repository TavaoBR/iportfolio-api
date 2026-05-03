<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Entity\User;

final class UserMapper
{
    public function toArray(User $user): array
    {
        return [
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'avatar' => $user->getAvatar(),
            'is_active' => $user->isActive(),
            'created_at' => $user->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'updated_at' => $user->getUpdatedAt()?->format(\DateTimeInterface::ATOM),
        ];
    }
}