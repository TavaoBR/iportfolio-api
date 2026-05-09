<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PortfolioSection;
use App\Entity\PortfolioSite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PortfolioSection>
 */
final class PortfolioSectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PortfolioSection::class);
    }

    public function save(PortfolioSection $entity): PortfolioSection
    {
        $em = $this->getEntityManager();
        $em->persist($entity);
        $em->flush();

        return $entity;
    }

    public function remove(PortfolioSection $entity): void
    {
        $em = $this->getEntityManager();
        $em->remove($entity);
        $em->flush();
    }

    /**
     * @return list<PortfolioSection>
     */
    public function findBySiteOrdered(PortfolioSite $site): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.portfolioSite = :site')
            ->setParameter('site', $site)
            ->orderBy('s.position', 'ASC')
            ->addOrderBy('s.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneOnSite(PortfolioSite $site, int $id): ?PortfolioSection
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.portfolioSite = :site')
            ->andWhere('s.id = :id')
            ->setParameter('site', $site)
            ->setParameter('id', $id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
