<?php

declare(strict_types=1);

namespace App\Service\Auth;

use App\Entity\LoginSession;
use App\Entity\User;
use App\Exception\Auth\InvalidAuthTokenException;
use App\Exception\Auth\MissingAuthTokenException;
use App\Mapper\UserMapper;
use App\Repository\LoginSessionRepository;
use App\Repository\UserRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class AuthenticatedUserService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly LoginSessionRepository $sessions,
        private readonly AuthTokenService $tokens,
        private readonly UserMapper $userMapper,
        #[Autowire('%env(AUTH_TOKEN_HEADER)%')]
        private readonly string $tokenHeader,
    ) {
    }

    /**
     * @return array{status: int, message: string, data?: array<string, mixed>, errors?: mixed}
     */
    public function me(Request $request): array
    {
        try {
            $session = $this->sessionFromRequest($request);
            $user = $this->userFromSession($session);
            $userData = $this->userMapper->toArray($user);
            $userData['session_metadata'] = $session->getSessionMetadata();

            return [
                'status' => Response::HTTP_OK,
                'message' => 'Usuario autenticado encontrado com sucesso',
                'data' => $userData,
            ];
        } catch (MissingAuthTokenException|InvalidAuthTokenException $e) {
            return [
                'status' => Response::HTTP_UNAUTHORIZED,
                'message' => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Ocorreu algum erro inesperado',
                'errors' => $e->getMessage(),
            ];
        }
    }

    /**
     * @return array{status: int, message: string, data?: array<string, mixed>, errors?: mixed}
     */
    public function logout(Request $request): array
    {
        try {
            $session = $this->sessionFromRequest($request);
            $session->revoke(new \DateTimeImmutable());

            $this->sessions->save($session);

            return [
                'status' => Response::HTTP_OK,
                'message' => 'Logout realizado com sucesso',
            ];
        } catch (MissingAuthTokenException|InvalidAuthTokenException $e) {
            return [
                'status' => Response::HTTP_UNAUTHORIZED,
                'message' => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Ocorreu algum erro inesperado',
                'errors' => $e->getMessage(),
            ];
        }
    }

    public function userFromRequest(Request $request): User
    {
        return $this->userFromSession($this->sessionFromRequest($request));
    }

    private function sessionFromRequest(Request $request): LoginSession
    {
        $token = $request->headers->get($this->tokenHeader);

        if ($token === null || trim($token) === '') {
            throw new MissingAuthTokenException();
        }

        $metadata = $this->tokens->metadata($token);

        if ($metadata === null) {
            throw new InvalidAuthTokenException();
        }

        $session = $this->sessions->findActiveByTokenHash($this->tokens->hash($token));

        if (!$session instanceof LoginSession || $session->getUserId() !== $metadata['uid']) {
            throw new InvalidAuthTokenException();
        }

        return $session;
    }

    private function userFromSession(LoginSession $session): User
    {
        $user = $this->users->find($session->getUserId());

        if (!$user instanceof User || !$user->isActive()) {
            throw new InvalidAuthTokenException();
        }

        return $user;
    }
}