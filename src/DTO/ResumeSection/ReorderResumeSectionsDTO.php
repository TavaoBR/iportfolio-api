<?php

declare(strict_types=1);

namespace App\DTO\ResumeSection;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ReorderResumeSectionsDTO
{
    /**
     * @param list<int> $orderedIds
     */
    public function __construct(
        #[SerializedName('ordered_ids')]
        #[Assert\Count(min: 1, minMessage: 'Informe pelo menos um id')]
        #[Assert\All([
            new Assert\Type('int'),
        ])]
        public array $orderedIds,
    ) {
    }
}
