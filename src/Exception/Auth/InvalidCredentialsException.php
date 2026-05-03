<?php

declare(strict_types=1);

namespace App\Exception\Auth;

final class InvalidCredentialsException extends \RuntimeException
{
    public function __construct(string $message = 'Email ou senha invalidos')
    {
        parent::__construct($message);
    }
}