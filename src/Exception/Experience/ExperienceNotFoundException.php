<?php

declare(strict_types=1);

namespace App\Exception\Experience;

final class ExperienceNotFoundException extends \RuntimeException
{
    public function __construct(string $message = 'Experiencia nao encontrada')
    {
        parent::__construct($message);
    }
}
