<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Entity\AIAnalysis;

final class AIAnalysisMapper
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(AIAnalysis $analysis): array
    {
        return [
            'id' => $analysis->getId(),
            'analysis_type' => $analysis->getAnalysisType(),
            'status' => $analysis->getStatus(),
            'request_payload' => $analysis->getRequestPayload(),
            'result' => $analysis->getResult(),
            'error_message' => $analysis->getErrorMessage(),
            'created_at' => $analysis->getCreatedAt()->format(DATE_ATOM),
            'updated_at' => $analysis->getUpdatedAt()?->format(DATE_ATOM),
        ];
    }

    /**
     * @param list<AIAnalysis> $list
     * @return list<array<string, mixed>>
     */
    public function toArrayList(array $list): array
    {
        return array_map(fn (AIAnalysis $a): array => $this->toArray($a), $list);
    }
}
