<?php

declare(strict_types=1);

namespace App\Exception\Profile;

final class UserProfileAlreadyExistsException extends \RuntimeException
{
    public function __construct(string $message = 'Perfil ja cadastrado')
    {
        parent::__construct($message);
    }
}
