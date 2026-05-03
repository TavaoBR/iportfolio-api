<?php

declare(strict_types=1);

namespace App\Service\Auth;

use App\DTO\Auth\LoginDTO;
use App\Entity\LoginSession;
use App\Entity\User;
use App\Exception\Auth\AccountLockedException;
use App\Exception\Auth\InactiveUserException;
use App\Exception\Auth\InvalidCredentialsException;
use App\Mapper\UserMapper;
use App\Repository\LoginSessionRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AuthService
{
    private const MAX_LOGIN_ATTEMPTS = 5;

    public function __construct(
        private readonly UserRepository $users,
        private readonly LoginSessionRepository $sessions,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly AuthTokenService $tokens,
        private readonly AuthSessionMetadataFactory $metadataFactory,
        private readonly UserMapper $userMapper,
    ) {
    }

    /**
     * @return array{status: int, message: string, data?: array<string, mixed>, errors?: mixed}
     */
    public function login(LoginDTO $dto): array
    {
        try {
            $user = $this->users->findByEmail($dto->email);

            if (!$user instanceof User) {
                throw new InvalidCredentialsException();
            }

            if (!$user->isActive()) {
                throw new InactiveUserException();
            }

            if ($user->getAttempts() >= self::MAX_LOGIN_ATTEMPTS) {
                throw new AccountLockedException();
            }

            if (!$this->passwordHasher->isPasswordValid($user, $dto->password)) {
                $user->registerFailedLoginAttempt();
                $this->users->save($user);

                throw new InvalidCredentialsException();
            }

            $issuedAt = new \DateTimeImmutable();
            $expiresAt = $this->tokens->expiresAt($issuedAt);
            $token = $this->tokens->issue($user, $issuedAt);
            $tokenHash = $this->tokens->hash($token);
            $sessionMetadata = $this->metadataFactory->create($user, $issuedAt);

            $session = new LoginSession(
                userId: $user->getId(),
                tokenHash: $tokenHash,
                loginDateTime: $issuedAt,
                expireDateTime: $expiresAt,
                ip: $sessionMetadata['ip'] ?? null,
                sessionMetadata: $sessionMetadata,
            );

            $user->setAttempts(0);
            $this->users->save($user);
            $this->sessions->save($session);

            return [
                'status' => Response::HTTP_OK,
                'message' => 'Login realizado com sucesso',
                'data' => [
                    'token' => $token,
                    'user' => $this->userMapper->toArray($user),
                    'session_metadata' => $sessionMetadata,
                ],
            ];
        } catch (InvalidCredentialsException $e) {
            return [
                'status' => Response::HTTP_UNAUTHORIZED,
                'message' => $e->getMessage(),
            ];
        } catch (InactiveUserException|AccountLockedException $e) {
            return [
                'status' => Response::HTTP_FORBIDDEN,
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
}