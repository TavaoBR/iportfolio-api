<?php

declare(strict_types=1);

namespace App\Exception\Certification;

final class CertificationNotFoundException extends \RuntimeException
{
    public function __construct(string $message = 'Certificacao nao encontrada')
    {
        parent::__construct($message);
    }
}
