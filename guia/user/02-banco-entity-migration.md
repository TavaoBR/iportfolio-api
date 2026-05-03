# Modulo User - Banco, Entity e Migration

## Objetivo

Criar a persistencia do usuario usando Symfony CLI/Maker e Doctrine.

A Entity representa estado persistido. Ela nao deve gerar token, validar request, processar upload ou montar resposta HTTP.

## Passo a passo via CLI

Criar usuario com o Maker:

```bash
php bin/console make:user
```

Respostas recomendadas:

```md
Class name: User
Store users in database: yes
Unique property: email
Security user provider: Doctrine
Password hashed: yes
```

Adicionar campos extras:

```bash
php bin/console make:entity User
```

Campos:

```md
name: string, length 150, nullable false
avatar: string, length 255, nullable true
isActive: boolean, nullable false
createdAt: datetime_immutable, nullable false
updatedAt: datetime_immutable, nullable true
```

Gerar migration:

```bash
php bin/console make:migration
```

Revisar migration gerada:

```bash
Get-Content migrations\Version*.php
```

Executar migration:

```bash
php bin/console doctrine:migrations:migrate
```

Validar schema:

```bash
php bin/console doctrine:schema:validate
```

## Tabela esperada

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT NOT NULL,
    email VARCHAR(180) NOT NULL,
    roles JSON NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(150) NOT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL,
    created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
    updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
    UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email),
    INDEX idx_users_is_active (is_active),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
```

## Decisao sobre avatar

O banco nao deve guardar o base64.

O banco guarda apenas o caminho relativo do arquivo:

```md
uploads/avatars/avatar_abc123.png
```

Motivo:

```md
- Base64 no banco aumenta o tamanho dos dados.
- Arquivos estaticos sao mais faceis de servir e cachear.
- Fica mais simples trocar para S3, CDN ou outro storage no futuro.
```

## Entity completa

Arquivo:

```md
src/Entity/User.php
```

```php
<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\Index(name: 'idx_users_is_active', fields: ['isActive'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private string $email;

    /**
     * @var list<string>
     */
    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private string $password;

    #[ORM\Column(length: 150)]
    private string $name;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $avatar = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct(string $name, string $email)
    {
        $this->name = trim($name);
        $this->email = mb_strtolower(trim($email));
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function updateEmail(string $email): void
    {
        $this->email = mb_strtolower(trim($email));
        $this->touch();
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_values(array_unique($roles));
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = array_values(array_unique($roles));
        $this->touch();
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function changePasswordHash(string $passwordHash): void
    {
        $this->password = $passwordHash;
        $this->touch();
    }

    public function eraseCredentials(): void
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function updateName(string $name): void
    {
        $this->name = trim($name);
        $this->touch();
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function updateAvatar(?string $avatar): void
    {
        $this->avatar = $avatar !== null ? trim($avatar) : null;
        $this->touch();
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function activate(): void
    {
        $this->isActive = true;
        $this->touch();
    }

    public function deactivate(): void
    {
        $this->isActive = false;
        $this->touch();
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
```

## Relacionamentos futuros

Nao criar ainda:

```md
User 1:1 UserProfile
User 1:N Resume
User 1:N Project
User 1:N PortfolioSite
User 1:N AuthToken
```

Cada relacionamento deve nascer no guia do modulo correspondente.