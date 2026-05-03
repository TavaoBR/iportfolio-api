<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\LoginSession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LoginSession>
 */
final class LoginSessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LoginSession::class);
    }

    public function save(LoginSession $session): LoginSession
    {
        $em = $this->getEntityManager();
        $em->persist($session);
        $em->flush();

        return $session;
    }

    public function findActiveByTokenHash(string $tokenHash): ?LoginSession
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.tokenHash = :tokenHash')
            ->andWhere('s.revokedAt IS NULL')
            ->andWhere('s.expireDateTime > :now')
            ->setParameter('tokenHash', $tokenHash)
            ->setParameter('now', new \DateTimeImmutable())
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}