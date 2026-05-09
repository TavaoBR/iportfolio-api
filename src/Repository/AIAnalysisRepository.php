<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AIAnalysis;
use App\Entity\Resume;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AIAnalysis>
 */
final class AIAnalysisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AIAnalysis::class);
    }

    public function save(AIAnalysis $entity): AIAnalysis
    {
        $em = $this->getEntityManager();
        $em->persist($entity);
        $em->flush();

        return $entity;
    }

    /**
     * @return list<AIAnalysis>
     */
    public function findByResumeOrdered(Resume $resume): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.resume = :resume')
            ->setParameter('resume', $resume)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneOnResume(Resume $resume, int $id): ?AIAnalysis
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.resume = :resume')
            ->andWhere('a.id = :id')
            ->setParameter('resume', $resume)
            ->setParameter('id', $id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
