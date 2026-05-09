<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\TemplateCatalogItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TemplateCatalogItem>
 */
final class TemplateCatalogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TemplateCatalogItem::class);
    }

    public function save(TemplateCatalogItem $entity): TemplateCatalogItem
    {
        $em = $this->getEntityManager();
        $em->persist($entity);
        $em->flush();

        return $entity;
    }

    public function findActiveByTemplateKey(string $templateKey): ?TemplateCatalogItem
    {
        $templateKey = mb_strtolower(trim($templateKey));

        return $this->createQueryBuilder('t')
            ->andWhere('t.templateKey = :key')
            ->andWhere('t.isActive = true')
            ->setParameter('key', $templateKey)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByTemplateKey(string $templateKey): ?TemplateCatalogItem
    {
        $templateKey = mb_strtolower(trim($templateKey));

        return $this->createQueryBuilder('t')
            ->andWhere('t.templateKey = :key')
            ->setParameter('key', $templateKey)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return list<TemplateCatalogItem>
     */
    public function findActive(?string $type = null): array
    {
        $qb = $this->createQueryBuilder('t')
            ->andWhere('t.isActive = true')
            ->orderBy('t.type', 'ASC')
            ->addOrderBy('t.name', 'ASC');

        if ($type !== null) {
            $qb->andWhere('t.type = :type')->setParameter('type', $type);
        }

        return $qb->getQuery()->getResult();
    }
}
