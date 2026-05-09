<?php

declare(strict_types=1);

namespace App\Exception\Resume;

final class ResumeNotFoundException extends \RuntimeException
{
    public function __construct(string $message = 'Curriculo nao encontrado')
    {
        parent::__construct($message);
    }
}
