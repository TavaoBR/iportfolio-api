<?php

declare(strict_types=1);

namespace App\Exception\Education;

final class EducationNotFoundException extends \RuntimeException
{
    public function __construct(string $message = 'Formacao nao encontrada')
    {
        parent::__construct($message);
    }
}
