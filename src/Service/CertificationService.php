<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Certification\CreateCertificationDTO;
use App\DTO\Certification\UpdateCertificationDTO;
use App\Entity\Certification;
use App\Entity\User;
use App\Exception\Certification\CertificationNotFoundException;
use App\Mapper\CertificationMapper;
use App\Repository\CertificationRepository;
use Symfony\Component\HttpFoundation\Response;

final class CertificationService
{
    public function __construct(
        private readonly CertificationRepository $certifications,
        private readonly CertificationMapper $mapper,
    ) {
    }

    /**
     * @return array{status: int, message: string, data?: array<string, mixed>, errors?: mixed}
     */
    public function create(User $user, CreateCertificationDTO $dto): array
    {
        try {
            $certification = new Certification($user, $dto->name);
            $certification->update(
                $dto->name,
                $dto->issuer,
                $dto->credentialUrl,
                $this->date($dto->issuedAt),
                $this->date($dto->expiresAt),
                $dto->sortOrder,
            );

            $this->certifications->save($certification);

            return [
                'status' => Response::HTTP_CREATED,
                'message' => 'Certificacao criada com sucesso',
                'data' => $this->mapper->toArray($certification),
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
    public function update(User $user, int $id, UpdateCertificationDTO $dto): array
    {
        try {
            $certification = $this->certifications->findOneOwnedByUser($user, $id);

            if (!$certification instanceof Certification) {
                throw new CertificationNotFoundException();
            }

            $name = $dto->name !== null ? $dto->name : $certification->getName();
            $issuer = $dto->issuer !== null ? $dto->issuer : $certification->getIssuer();
            $credentialUrl = $dto->credentialUrl !== null ? $dto->credentialUrl : $certification->getCredentialUrl();
            $issuedAt = $dto->issuedAt !== null ? $this->date($dto->issuedAt) : $certification->getIssuedAt();
            $expiresAt = $dto->expiresAt !== null ? $this->date($dto->expiresAt) : $certification->getExpiresAt();
            $sortOrder = $dto->sortOrder !== null ? $dto->sortOrder : $certification->getSortOrder();

            $certification->update($name, $issuer, $credentialUrl, $issuedAt, $expiresAt, $sortOrder);

            $this->certifications->save($certification);

            return [
                'status' => Response::HTTP_OK,
                'message' => 'Certificacao atualizada com sucesso',
                'data' => $this->mapper->toArray($certification),
            ];
        } catch (CertificationNotFoundException $e) {
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
     * @return array{status: int, message: string, errors?: mixed}
     */
    public function delete(User $user, int $id): array
    {
        try {
            $row = $this->certifications->findOneOwnedByUser($user, $id);

            if (!$row instanceof Certification) {
                throw new CertificationNotFoundException();
            }

            $this->certifications->remove($row);

            return [
                'status' => Response::HTTP_OK,
                'message' => 'Certificacao removida com sucesso',
            ];
        } catch (CertificationNotFoundException $e) {
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
     * @return array{status: int, message: string, data?: list<array<string, mixed>>, errors?: mixed}
     */
    public function list(User $user): array
    {
        try {
            return [
                'status' => Response::HTTP_OK,
                'message' => 'Certificacoes encontradas com sucesso',
                'data' => $this->mapper->toArrayList($this->certifications->findByUser($user)),
            ];
        } catch (\Exception $e) {
            return [
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Ocorreu algum erro inesperado',
                'errors' => $e->getMessage(),
            ];
        }
    }

    private function date(?string $value): ?\DateTimeImmutable
    {
        return $value ? new \DateTimeImmutable($value) : null;
    }
}
