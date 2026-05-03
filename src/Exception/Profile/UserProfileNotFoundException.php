<?php

declare(strict_types=1);

namespace App\Exception\Profile;

final class UserProfileNotFoundException extends \RuntimeException
{
    public function __construct(string $message = 'Perfil nao encontrado')
    {
        parent::__construct($message);
    }
}