<?php

declare(strict_types=1);

namespace App\Exception\Project;

final class ProjectNotFoundException extends \RuntimeException
{
    public function __construct(string $message = 'Projeto nao encontrado')
    {
        parent::__construct($message);
    }
}
