<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Certification;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Certification>
 */
final class CertificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Certification::class);
    }

    public function save(Certification $certification): Certification
    {
        $em = $this->getEntityManager();
        $em->persist($certification);
        $em->flush();

        return $certification;
    }

    public function remove(Certification $certification): void
    {
        $em = $this->getEntityManager();
        $em->remove($certification);
        $em->flush();
    }

    /**
     * @return list<Certification>
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.user = :user')
            ->setParameter('user', $user)
            ->orderBy('c.sortOrder', 'ASC')
            ->addOrderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneOwnedByUser(User $user, int $id): ?Certification
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.id = :id')
            ->andWhere('c.user = :user')
            ->setParameter('id', $id)
            ->setParameter('user', $user)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}