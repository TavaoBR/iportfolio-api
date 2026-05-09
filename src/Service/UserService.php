<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\User\CreateUserDTO;
use App\DTO\User\UpdateUserDTO;
use App\Entity\User;
use App\Exception\User\InvalidAvatarException;
use App\Exception\User\UserAlreadyExistsException;
use App\Exception\User\UserNotFoundException;
use App\Mapper\UserMapper;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UserMapper $userMapper,
        private readonly AvatarBase64Service $avatarBase64,
    ) {
    }

    /**
     * @return array{status: int, message: string, data?: array<string, mixed>, errors?: mixed}
     */
    public function create(CreateUserDTO $dto): array
    {
        try {
            if ($this->users->findByEmail($dto->email)) {
                throw new UserAlreadyExistsException('Usuario ja cadastrado');
            }

            $user = new User($dto->name, $dto->email);
            $user->changePasswordHash($this->passwordHasher->hashPassword($user, $dto->password));

            if ($dto->avatar !== null) {
                $this->avatarBase64->assertValid($dto->avatar);
                $user->updateAvatar(trim($dto->avatar));
            }

            $this->users->save($user);

            return [
                'status' => Response::HTTP_CREATED,
                'message' => 'Conta criada com sucesso',
                'data' => $this->userMapper->toArray($user),
            ];
        } catch (InvalidAvatarException $e) {
            return [
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => $e->getMessage(),
            ];
        } catch (UserAlreadyExistsException $e) {
            return [
                'status' => Response::HTTP_CONFLICT,
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
    public function show(int $id): array
    {
        try {
            $user = $this->users->find($id);

            if (!$user instanceof User) {
                throw new UserNotFoundException('Usuario nao encontrado');
            }

            return [
                'status' => Response::HTTP_OK,
                'message' => 'Usuario encontrado com sucesso',
                'data' => $this->userMapper->toArray($user),
            ];
        } catch (UserNotFoundException $e) {
            return [
                'status' => Response::HTTP_NOT_FOUND,
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
    public function update(int $id, UpdateUserDTO $dto): array
    {
        try {
            $user = $this->users->find($id);

            if (!$user instanceof User) {
                throw new UserNotFoundException('Usuario nao encontrado');
            }

            if ($dto->name !== null) {
                $user->updateName($dto->name);
            }

            if ($dto->email !== null) {
                $user->updateEmail($dto->email);
            }

            if ($dto->avatar !== null) {
                $this->avatarBase64->assertValid($dto->avatar);
                $user->updateAvatar(trim($dto->avatar));
            }

            $this->users->save($user);

            return [
                'status' => Response::HTTP_OK,
                'message' => 'Usuario atualizado com sucesso',
                'data' => $this->userMapper->toArray($user),
            ];
        } catch (UserNotFoundException $e) {
            return [
                'status' => Response::HTTP_NOT_FOUND,
                'message' => $e->getMessage(),
            ];
        } catch (InvalidAvatarException $e) {
            return [
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
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
    public function activate(int $id): array
    {
        return $this->changeActiveStatus($id, true);
    }

    /**
     * @return array{status: int, message: string, data?: array<string, mixed>, errors?: mixed}
     */
    public function deactivate(int $id): array
    {
        return $this->changeActiveStatus($id, false);
    }

    /**
     * @return array{status: int, message: string, data?: array<string, mixed>, errors?: mixed}
     */
    private function changeActiveStatus(int $id, bool $active): array
    {
        try {
            $user = $this->users->find($id);

            if (!$user instanceof User) {
                throw new UserNotFoundException('Usuario nao encontrado');
            }

            if ($active) {
                $user->activate();
            } else {
                $user->deactivate();
            }

            $this->users->save($user);

            return [
                'status' => Response::HTTP_OK,
                'message' => $active ? 'Usuario ativado com sucesso' : 'Usuario desativado com sucesso',
                'data' => $this->userMapper->toArray($user),
            ];
        } catch (UserNotFoundException $e) {
            return [
                'status' => Response::HTTP_NOT_FOUND,
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

