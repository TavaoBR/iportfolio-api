<?php

declare(strict_types=1);

namespace App\Exception\Skill;

final class SkillNotFoundException extends \RuntimeException
{
    public function __construct(string $message = 'Competencia nao encontrada')
    {
        parent::__construct($message);
    }
}
