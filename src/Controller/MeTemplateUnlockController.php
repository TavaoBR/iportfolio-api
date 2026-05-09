<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\RequiresAuth;
use App\DTO\Template\UnlockPremiumTemplateDTO;
use App\Entity\User;
use App\Service\ApiResponseService;
use App\Service\TemplateUnlockService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[RequiresAuth]
#[Route('/api/me/template-unlocks')]
final class MeTemplateUnlockController extends AbstractController
{
    public function __construct(
        private readonly TemplateUnlockService $unlocks,
        private readonly ApiResponseService $api,
    ) {
    }

    #[Route('', name: 'api_me_template_unlock', methods: ['POST'])]
    public function unlock(User $user, #[MapRequestPayload] UnlockPremiumTemplateDTO $dto): JsonResponse
    {
        return $this->api->fromServiceResult(
            $this->unlocks->unlock($user, $dto->templateKey, $dto->paymentReference),
        );
    }
}
