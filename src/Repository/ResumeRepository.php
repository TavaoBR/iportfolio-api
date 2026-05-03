<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Resume;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Resume>
 */
final class ResumeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Resume::class);
    }

    public function save(Resume $resume): Resume
    {
        $em = $this->getEntityManager();
        $em->persist($resume);
        $em->flush();

        return $resume;
    }

    public function unsetMainForUser(User $user): void
    {
        $resumes = $this->createQueryBuilder('r')
            ->andWhere('r.user = :user')
            ->andWhere('r.isMain = true')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        foreach ($resumes as $resume) {
            $resume->unsetMain();
        }
    }

    /**
     * @return list<Resume>
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.user = :user')
            ->setParameter('user', $user)
            ->orderBy('r.isMain', 'DESC')
            ->addOrderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByPublicIdForUser(string $publicId, User $user): ?Resume
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.publicId = :publicId')
            ->andWhere('r.user = :user')
            ->setParameter('publicId', $publicId)
            ->setParameter('user', $user)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}