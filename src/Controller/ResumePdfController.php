<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\RequiresAuth;
use App\Entity\User;
use App\Exception\Resume\ResumeNotFoundException;
use App\Service\ApiResponseService;
use App\Service\ResumePdfService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[RequiresAuth]
#[Route('/api/resumes')]
final class ResumePdfController extends AbstractController
{
    public function __construct(
        private readonly ResumePdfService $pdf,
        private readonly ApiResponseService $api,
    ) {
    }

    #[Route('/{publicId}/pdf', name: 'api_resumes_pdf', requirements: ['publicId' => '[0-9a-fA-F-]{36}'], methods: ['GET'])]
    public function download(User $user, string $publicId): Response
    {
        try {
            $binary = $this->pdf->buildPdfBinary($user, $publicId);

            return new Response($binary, Response::HTTP_OK, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="curriculo-' . $publicId . '.pdf"',
            ]);
        } catch (ResumeNotFoundException $e) {
            return $this->api->fromServiceResult([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
