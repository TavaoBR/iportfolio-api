<?php

declare(strict_types=1);

namespace App\Exception\Auth;

final class InvalidAuthTokenException extends \RuntimeException
{
    public function __construct(string $message = 'Token invalido')
    {
        parent::__construct($message);
    }
}