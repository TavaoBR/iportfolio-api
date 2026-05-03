# Modulo User - Service

## Objetivo

O Service concentra regra de negocio do modulo User.

Ele retorna sempre o padrao interno:

```php
return [
    'status' => 201,
    'message' => 'Conta criada com sucesso',
    'data' => $data,
];
```

## Arquivo

```md
src/Service/UserService.php
```

## Responsabilidades

```md
- Criar usuario
- Validar email duplicado
- Gerar hash de senha
- Processar avatar base64
- Atualizar usuario
- Ativar/desativar usuario
- Converter erros conhecidos em status/message
- Converter erro inesperado em status 500
```

## Codigo completo

```php
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
        private readonly AvatarStorageService $avatarStorage,
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

            $user = new User(
                name: $dto->name,
                email: $dto->email,
            );

            $user->changePasswordHash(
                $this->passwordHasher->hashPassword($user, $dto->password)
            );

            if ($dto->avatar !== null) {
                $avatarPath = $this->avatarStorage->storeBase64($dto->avatar, $user);
                $user->updateAvatar($avatarPath);
            }

            $this->users->save($user);

            return [
                'status' => Response::HTTP_CREATED,
                'message' => 'Conta criada com sucesso',
                'data' => $this->userMapper->toArray($user),
            ];
        } catch (UserAlreadyExistsException $e) {
            return [
                'status' => Response::HTTP_CONFLICT,
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
    public function show(int $id): array
    {
        try {
            $user = $this->getById($id);

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
            $user = $this->getById($id);

            if ($dto->name !== null) {
                $user->updateName($dto->name);
            }

            if ($dto->email !== null && mb_strtolower(trim($dto->email)) !== $user->getEmail()) {
                if ($this->users->existsByEmail($dto->email, $user->getId())) {
                    throw new UserAlreadyExistsException('Usuario ja cadastrado');
                }

                $user->updateEmail($dto->email);
            }

            if ($dto->avatar !== null) {
                $oldAvatar = $user->getAvatar();
                $newAvatar = $this->avatarStorage->storeBase64($dto->avatar, $user);
                $user->updateAvatar($newAvatar);
                $this->avatarStorage->remove($oldAvatar);
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
        } catch (UserAlreadyExistsException $e) {
            return [
                'status' => Response::HTTP_CONFLICT,
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

    private function getById(int $id): User
    {
        $user = $this->users->find($id);

        if (!$user instanceof User) {
            throw new UserNotFoundException('Usuario nao encontrado');
        }

        return $user;
    }

    /**
     * @return array{status: int, message: string, data?: array<string, mixed>, errors?: mixed}
     */
    private function changeActiveStatus(int $id, bool $active): array
    {
        try {
            $user = $this->getById($id);

            if ($active) {
                $user->activate();
                $message = 'Usuario ativado com sucesso';
            } else {
                $user->deactivate();
                $message = 'Usuario desativado com sucesso';
            }

            $this->users->save($user);

            return [
                'status' => Response::HTTP_OK,
                'message' => $message,
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
```

## Decisoes

```md
- Service retorna array, nao Entity.
- Controller nao decide status HTTP.
- Email duplicado vira UserAlreadyExistsException.
- Usuario ausente vira UserNotFoundException.
- Avatar invalido vira InvalidAvatarException.
- Hash de senha fica no Service.
- Avatar base64 e delegado ao AvatarStorageService.
```