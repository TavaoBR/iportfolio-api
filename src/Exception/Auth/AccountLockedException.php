<?php

declare(strict_types=1);

namespace App\Exception\Auth;

final class AccountLockedException extends \RuntimeException
{
    public function __construct(string $message = 'Conta bloqueada apos varias tentativas de login invalidas.')
    {
        parent::__construct($message);
    }
}