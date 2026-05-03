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
- Nao criar listagem de usuarios sem uma necessidade real e autorizada pelo produto.
- Para endpoints do proprio usuario, buscar pelo id vindo do metadata de autenticacao.
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


## Decisao sobre listagem

Nao implementar `findAll`, paginacao ou endpoint de listagem de usuarios neste modulo.

Como nao existe role de admin por enquanto, o repository deve focar em consultas necessarias para:

```md
- Cadastro
- Login futuro
- Validacao de email duplicado
- Busca do usuario autenticado pelo id vindo do metadata
```

Quando o modulo Auth existir, operacoes autenticadas devem receber o id do usuario pelo contexto/token proprio, nao por parametro publico de rota.
