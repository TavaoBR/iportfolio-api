<?php

declare(strict_types=1);

namespace App\Exception\Auth;

final class MissingAuthTokenException extends \RuntimeException
{
    public function __construct(string $message = 'Token ausente')
    {
        parent::__construct($message);
    }
}