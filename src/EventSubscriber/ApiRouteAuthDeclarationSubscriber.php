<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Attribute\PublicRoute;
use App\Attribute\RequiresAuth;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Rotas nomeadas como `api_*` devem declarar obrigatoriamente acesso público ou autenticação,
 * para evitar criar endpoints acidentalmente não protegidos e sem marcação documental.
 *
 * Prioridade menor que RequiresAuthMiddleware: corre depois da autenticação opcional já ter corrido,
 * apenas para falhar rápido em configuração inválida.
 */
final class ApiRouteAuthDeclarationSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => ['onKernelController', -64],
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $routeName = $event->getRequest()->attributes->get('_route');
        if (!\is_string($routeName) || !str_starts_with($routeName, 'api_')) {
            return;
        }

        $controllerRef = $event->getController();
        if (!\is_array($controllerRef)
            || !isset($controllerRef[0], $controllerRef[1])
            || !\is_object($controllerRef[0])) {
            return;
        }

        $classRef = new \ReflectionClass($controllerRef[0]);
        $methodRef = $classRef->getMethod((string) $controllerRef[1]);

        if ([] !== $classRef->getAttributes(RequiresAuth::class) && [] !== $classRef->getAttributes(PublicRoute::class)) {
            throw new \LogicException(\sprintf(
                'O controller "%s" não pode declarar #[RequiresAuth] e #[PublicRoute] ao mesmo tempo na classe.',
                $classRef->getName(),
            ));
        }

        if ([] !== $methodRef->getAttributes(RequiresAuth::class) && [] !== $methodRef->getAttributes(PublicRoute::class)) {
            throw new \LogicException(\sprintf(
                'O método "%s::%s()" não pode declarar #[RequiresAuth] e #[PublicRoute] ao mesmo tempo.',
                $classRef->getName(),
                $methodRef->getName(),
            ));
        }

        $requiresAuth = [] !== $methodRef->getAttributes(RequiresAuth::class)
            || ([] !== $classRef->getAttributes(RequiresAuth::class)
                && [] === $methodRef->getAttributes(PublicRoute::class));

        $isPublic = [] !== $methodRef->getAttributes(PublicRoute::class)
            || ([] !== $classRef->getAttributes(PublicRoute::class)
                && [] === $methodRef->getAttributes(RequiresAuth::class));

        if ($requiresAuth xor $isPublic) {
            return;
        }

        throw new \LogicException(\sprintf(
            'Toda acção HTTP com nome de rota "api_*" deve declarar autenticação ou acesso público. '
                . 'Use #[RequiresAuth] ou #[PublicRoute] em "%s::%s()" ou na própria classe.',
            $classRef->getName(),
            $methodRef->getName(),
        ));
    }
}
