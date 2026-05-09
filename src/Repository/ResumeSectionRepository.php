<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Resume;
use App\Entity\ResumeSection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ResumeSection>
 */
final class ResumeSectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResumeSection::class);
    }

    public function save(ResumeSection $entity): ResumeSection
    {
        $em = $this->getEntityManager();
        $em->persist($entity);
        $em->flush();

        return $entity;
    }

    public function remove(ResumeSection $entity): void
    {
        $em = $this->getEntityManager();
        $em->remove($entity);
        $em->flush();
    }

    /**
     * @return list<ResumeSection>
     */
    public function findByResumeOrdered(Resume $resume): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.resume = :resume')
            ->setParameter('resume', $resume)
            ->orderBy('s.position', 'ASC')
            ->addOrderBy('s.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneOnResume(Resume $resume, int $id): ?ResumeSection
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.resume = :resume')
            ->andWhere('s.id = :id')
            ->setParameter('resume', $resume)
            ->setParameter('id', $id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
