<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Education;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Education>
 */
final class EducationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Education::class);
    }

    public function save(Education $education): Education
    {
        $em = $this->getEntityManager();
        $em->persist($education);
        $em->flush();

        return $education;
    }

    public function remove(Education $education): void
    {
        $em = $this->getEntityManager();
        $em->remove($education);
        $em->flush();
    }

    /**
     * @return list<Education>
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('ed')
            ->andWhere('ed.user = :user')
            ->setParameter('user', $user)
            ->orderBy('ed.sortOrder', 'ASC')
            ->addOrderBy('ed.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneOwnedByUser(User $user, int $id): ?Education
    {
        return $this->createQueryBuilder('ed')
            ->andWhere('ed.id = :id')
            ->andWhere('ed.user = :user')
            ->setParameter('id', $id)
            ->setParameter('user', $user)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}