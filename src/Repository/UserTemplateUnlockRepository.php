<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\TemplateCatalogItem;
use App\Entity\User;
use App\Entity\UserTemplateUnlock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserTemplateUnlock>
 */
final class UserTemplateUnlockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserTemplateUnlock::class);
    }

    public function save(UserTemplateUnlock $row): UserTemplateUnlock
    {
        $em = $this->getEntityManager();
        $em->persist($row);
        $em->flush();

        return $row;
    }

    public function hasUnlock(User $user, TemplateCatalogItem $template): bool
    {
        $count = (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->andWhere('u.user = :user')
            ->andWhere('u.template = :template')
            ->setParameter('user', $user)
            ->setParameter('template', $template)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }
}
