<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Profile\UpsertProfileDTO;
use App\Entity\User;
use App\Entity\UserProfile;
use App\Exception\Profile\UserProfileAlreadyExistsException;
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
    public function create(User $user, UpsertProfileDTO $dto): array
    {
        try {
            if ($this->profiles->findByUser($user) instanceof UserProfile) {
                throw new UserProfileAlreadyExistsException();
            }

            $profile = new UserProfile($user);
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
                'status' => Response::HTTP_CREATED,
                'message' => 'Perfil criado com sucesso',
                'data' => $this->mapper->toArray($profile),
            ];
        } catch (UserProfileAlreadyExistsException $e) {
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
    public function update(User $user, UpsertProfileDTO $dto): array
    {
        try {
            $profile = $this->profiles->findByUser($user);

            if (!$profile instanceof UserProfile) {
                throw new UserProfileNotFoundException();
            }

            $profile->update(
                headline: $dto->headline !== null ? $dto->headline : $profile->getHeadline(),
                bio: $dto->bio !== null ? $dto->bio : $profile->getBio(),
                phone: $dto->phone !== null ? $dto->phone : $profile->getPhone(),
                city: $dto->city !== null ? $dto->city : $profile->getCity(),
                state: $dto->state !== null ? $dto->state : $profile->getState(),
                country: $dto->country !== null ? $dto->country : $profile->getCountry(),
                linkedinUrl: $dto->linkedinUrl !== null ? $dto->linkedinUrl : $profile->getLinkedinUrl(),
                githubUrl: $dto->githubUrl !== null ? $dto->githubUrl : $profile->getGithubUrl(),
                websiteUrl: $dto->websiteUrl !== null ? $dto->websiteUrl : $profile->getWebsiteUrl(),
            );

            $this->profiles->save($profile);

            return [
                'status' => Response::HTTP_OK,
                'message' => 'Perfil atualizado com sucesso',
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
