<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Resume;
use App\Entity\User;
use App\Exception\Resume\ResumeNotFoundException;
use App\Repository\ResumeRepository;
use Dompdf\Dompdf;

final class ResumePdfService
{
    public function __construct(
        private readonly ResumeRepository $resumes,
    ) {
    }

    /**
     * @throws ResumeNotFoundException
     */
    public function buildPdfBinary(User $user, string $publicResumeId): string
    {
        $resume = $this->resumes->findByPublicIdForUser($publicResumeId, $user);

        if (!$resume instanceof Resume) {
            throw new ResumeNotFoundException();
        }

        $dompdf = new Dompdf();
        $html = $this->renderHtml($resume);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $output = $dompdf->output();

        return \is_string($output) ? $output : '';
    }

    private function renderHtml(Resume $resume): string
    {
        $title = htmlspecialchars($resume->getTitle(), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $role = $resume->getTargetRole() !== null
            ? htmlspecialchars($resume->getTargetRole(), ENT_QUOTES | ENT_HTML5, 'UTF-8')
            : '';
        $lang = htmlspecialchars($resume->getLanguage(), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $roleBlock = $role !== '' ? "<p><strong>Cargo alvo:</strong> {$role}</p>" : '';

        return <<<HTML
<!DOCTYPE html>
<html lang="{$lang}">
<head>
    <meta charset="UTF-8"/>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12pt; margin: 24px; }
        h1 { font-size: 18pt; margin-bottom: 8px; }
        p { margin: 4px 0; }
    </style>
</head>
<body>
    <h1>{$title}</h1>
    {$roleBlock}
    <p><em>Gerado por iportfolio-api (versao simples). Personalize o layout no servico de PDF.</em></p>
</body>
</html>
HTML;
    }
}
