<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Certification\CreateCertificationDTO;
use App\Entity\Certification;
use App\Entity\User;
use App\Mapper\CertificationMapper;
use App\Repository\CertificationRepository;
use Symfony\Component\HttpFoundation\Response;

final class CertificationService
{
    public function __construct(private readonly CertificationRepository $certifications, private readonly CertificationMapper $mapper) {}

    public function create(User $user, CreateCertificationDTO $dto): array
    {
        try {
            $certification = new Certification($user, $dto->name);
            $certification->update($dto->name, $dto->issuer, $dto->credentialUrl, $this->date($dto->issuedAt), $this->date($dto->expiresAt), $dto->sortOrder);
            $this->certifications->save($certification);
            return ['status' => Response::HTTP_CREATED, 'message' => 'Certificacao criada com sucesso', 'data' => $this->mapper->toArray($certification)];
        } catch (\Exception $e) {
            return ['status' => Response::HTTP_INTERNAL_SERVER_ERROR, 'message' => 'Ocorreu algum erro inesperado', 'errors' => $e->getMessage()];
        }
    }

    public function list(User $user): array
    {
        try {
            return ['status' => Response::HTTP_OK, 'message' => 'Certificacoes encontradas com sucesso', 'data' => $this->mapper->toArrayList($this->certifications->findByUser($user))];
        } catch (\Exception $e) {
            return ['status' => Response::HTTP_INTERNAL_SERVER_ERROR, 'message' => 'Ocorreu algum erro inesperado', 'errors' => $e->getMessage()];
        }
    }

    private function date(?string $value): ?\DateTimeImmutable
    {
        return $value ? new \DateTimeImmutable($value) : null;
    }
}