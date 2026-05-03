<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Exception\Auth\InvalidAuthTokenException;
use App\Exception\Auth\MissingAuthTokenException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

final class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof MissingAuthTokenException || $exception instanceof InvalidAuthTokenException) {
            $event->setResponse(new JsonResponse([
                'message' => $exception->getMessage(),
            ], JsonResponse::HTTP_UNAUTHORIZED));

            return;
        }

        $validationException = $this->findValidationException($exception);

        if (!$validationException instanceof ValidationFailedException) {
            return;
        }

        $errors = [];

        foreach ($validationException->getViolations() as $violation) {
            $errors[] = $this->formatViolation($violation);
        }

        $event->setResponse(new JsonResponse([
            'message' => 'Dados invalidos',
            'errors' => $errors,
        ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
    }

    private function findValidationException(\Throwable $exception): ?ValidationFailedException
    {
        while ($exception instanceof \Throwable) {
            if ($exception instanceof ValidationFailedException) {
                return $exception;
            }

            $exception = $exception->getPrevious();
        }

        return null;
    }

    private function formatViolation(ConstraintViolationInterface $violation): array
    {
        return [
            'field' => $violation->getPropertyPath(),
            'message' => $violation->getMessage(),
        ];
    }
}