<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Entity\Resume;

final class ResumeMapper
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Resume $resume): array
    {
        return [
            'id' => $resume->getId(),
            'public_id' => $resume->getPublicId(),
            'title' => $resume->getTitle(),
            'target_role' => $resume->getTargetRole(),
            'language' => $resume->getLanguage(),
            'template_key' => $resume->getTemplateKey(),
            'ats_score' => $resume->getAtsScore(),
            'is_main' => $resume->isMain(),
            'is_public' => $resume->isPublic(),
            'created_at' => $resume->getCreatedAt()->format(DATE_ATOM),
            'updated_at' => $resume->getUpdatedAt()?->format(DATE_ATOM),
        ];
    }

    /**
     * @param list<Resume> $resumes
     * @return list<array<string, mixed>>
     */
    public function toArrayList(array $resumes): array
    {
        return array_map(fn (Resume $resume): array => $this->toArray($resume), $resumes);
    }
}