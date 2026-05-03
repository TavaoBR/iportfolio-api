<?php

declare(strict_types=1);

namespace App\Middleware\Auth;

use App\Attribute\RequiresAuth;
use App\Service\Auth\AuthenticatedUserService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class RequiresAuthMiddleware implements EventSubscriberInterface
{
    public const AUTHENTICATED_USER = 'authenticated_user';
    public const LOGIN_SESSION = 'login_session';

    public function __construct(
        private readonly AuthenticatedUserService $authenticatedUsers,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        if (!$this->requiresAuth($event->getController())) {
            return;
        }

        $authContext = $this->authenticatedUsers->authenticate($event->getRequest());

        $event->getRequest()->attributes->set(self::AUTHENTICATED_USER, $authContext['user']);
        $event->getRequest()->attributes->set(self::LOGIN_SESSION, $authContext['session']);
    }

    private function requiresAuth(callable|array $controller): bool
    {
        if (!is_array($controller) || !isset($controller[0], $controller[1]) || !is_object($controller[0])) {
            return false;
        }

        $class = new \ReflectionClass($controller[0]);
        $method = $class->getMethod((string) $controller[1]);

        return $class->getAttributes(RequiresAuth::class) !== []
            || $method->getAttributes(RequiresAuth::class) !== [];
    }
}