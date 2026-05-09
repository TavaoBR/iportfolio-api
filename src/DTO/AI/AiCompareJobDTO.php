<?php

declare(strict_types=1);

namespace App\DTO\AI;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class AiCompareJobDTO
{
    public function __construct(
        #[SerializedName('job_description')]
        #[Assert\NotBlank(message: 'Descricao da vaga e obrigatoria')]
        public string $jobDescription,
    ) {
    }
}
