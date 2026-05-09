<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Skill;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Skill>
 */
final class SkillRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Skill::class);
    }

    public function save(Skill $skill): Skill
    {
        $em = $this->getEntityManager();
        $em->persist($skill);
        $em->flush();

        return $skill;
    }

    /**
     * @return list<Skill>
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.user = :user')
            ->setParameter('user', $user)
            ->orderBy('s.sortOrder', 'ASC')
            ->addOrderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneOwnedByUser(User $user, int $id): ?Skill
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.id = :id')
            ->andWhere('s.user = :user')
            ->setParameter('id', $id)
            ->setParameter('user', $user)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}