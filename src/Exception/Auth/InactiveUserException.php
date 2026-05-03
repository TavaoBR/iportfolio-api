<?php

declare(strict_types=1);

namespace App\Exception\Auth;

final class InactiveUserException extends \RuntimeException
{
    public function __construct(string $message = 'Usuario inativo')
    {
        parent::__construct($message);
    }
}