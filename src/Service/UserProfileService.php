<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Profile\UpsertProfileDTO;
use App\Entity\User;
use App\Entity\UserProfile;
use App\Exception\Profile\UserProfileNotFoundException;
use App\Mapper\UserProfileMapper;
use App\Repository\UserProfileRepository;
use Symfony\Component\HttpFoundation\Response;

final class UserProfileService
{
    public function __construct(
        private readonly UserProfileRepository $profiles,
        private readonly UserProfileMapper $mapper,
    ) {
    }

    /**
     * @return array{status: int, message: string, data?: array<string, mixed>, errors?: mixed}
     */
    public function upsert(User $user, UpsertProfileDTO $dto): array
    {
        try {
            $profile = $this->profiles->findByUser($user);
            $created = false;

            if (!$profile instanceof UserProfile) {
                $profile = new UserProfile($user);
                $created = true;
            }

            $profile->update(
                headline: $dto->headline,
                bio: $dto->bio,
                phone: $dto->phone,
                city: $dto->city,
                state: $dto->state,
                country: $dto->country,
                linkedinUrl: $dto->linkedinUrl,
                githubUrl: $dto->githubUrl,
                websiteUrl: $dto->websiteUrl,
            );

            $this->profiles->save($profile);

            return [
                'status' => $created ? Response::HTTP_CREATED : Response::HTTP_OK,
                'message' => $created ? 'Perfil criado com sucesso' : 'Perfil atualizado com sucesso',
                'data' => $this->mapper->toArray($profile),
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
    public function show(User $user): array
    {
        try {
            $profile = $this->profiles->findByUser($user);

            if (!$profile instanceof UserProfile) {
                throw new UserProfileNotFoundException();
            }

            return [
                'status' => Response::HTTP_OK,
                'message' => 'Perfil encontrado com sucesso',
                'data' => $this->mapper->toArray($profile),
            ];
        } catch (UserProfileNotFoundException $e) {
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
