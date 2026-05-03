# Modulo User - Repository

## Arquivo

```md
src/Repository/UserRepository.php
```

## Responsabilidade

```md
- Centralizar consultas de User
- Persistir e remover User
- Buscar por email
- Verificar duplicidade de email
- Criar consultas otimizadas para listagens futuras
```

Repository nao deve:

```md
- Criar hash de senha
- Salvar avatar em arquivo
- Montar response da API
- Decidir status HTTP
- Capturar exception de regra de negocio
```

## Codigo recomendado

```php
<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
final class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function save(User $user, bool $flush = true): void
    {
        $this->getEntityManager()->persist($user);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $user, bool $flush = true): void
    {
        $this->getEntityManager()->remove($user);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByEmail(string $email): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('LOWER(u.email) = :email')
            ->setParameter('email', mb_strtolower(trim($email)))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findActiveByEmail(string $email): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('LOWER(u.email) = :email')
            ->andWhere('u.isActive = :active')
            ->setParameter('email', mb_strtolower(trim($email)))
            ->setParameter('active', true)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function existsByEmail(string $email, ?int $ignoreUserId = null): bool
    {
        $qb = $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->andWhere('LOWER(u.email) = :email')
            ->setParameter('email', mb_strtolower(trim($email)));

        if ($ignoreUserId !== null) {
            $qb
                ->andWhere('u.id != :ignoreUserId')
                ->setParameter('ignoreUserId', $ignoreUserId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }
}
```

## Boas praticas

```md
- Use QueryBuilder para consultas que podem evoluir.
- Use find() apenas para busca simples por id.
- Nao retorne password em listagens futuras.
- Em listagens, use paginacao.
- Em listagens, use select parcial para evitar over-fetching.
- Nao faca JOIN FETCH sem necessidade.
```

## Sobre N+1

No User puro nao existe N+1 relevante.

Quando surgirem relacionamentos:

```md
UserProfile
Resume
Project
PortfolioSite
```

as queries devem ser pensadas para cada tela. Nem toda response precisa carregar tudo.

