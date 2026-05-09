<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Experience;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Experience>
 */
final class ExperienceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Experience::class);
    }

    public function save(Experience $experience): Experience
    {
        $em = $this->getEntityManager();
        $em->persist($experience);
        $em->flush();

        return $experience;
    }

    /**
     * @return list<Experience>
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.user = :user')
            ->setParameter('user', $user)
            ->orderBy('e.sortOrder', 'ASC')
            ->addOrderBy('e.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneOwnedByUser(User $user, int $id): ?Experience
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.id = :id')
            ->andWhere('e.user = :user')
            ->setParameter('id', $id)
            ->setParameter('user', $user)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}