# Guia 01 - Modulo de Usuario

Este documento e o padrao oficial de implementacao dos modulos do projeto Smart Portfolio CV.

A partir deste guia, todos os proximos modulos devem seguir a mesma organizacao:

```md
Entity/Migration -> Repository -> DTO -> Validator -> Mapper -> Service -> Controller -> Testes/Validacao
```

O objetivo e manter o backend simples de evoluir, sem jogar regra de negocio em controllers e sem expor entidades diretamente na API.

## 1. Decisoes arquiteturais oficiais

## Padrao de camadas

O modulo deve seguir uma arquitetura em camadas:

```md
Controller -> DTO -> Service -> Repository -> Entity
                    -> Mapper
                    -> Validator
```

Responsabilidades:

```md
Controller:
- Recebe HTTP request
- Chama DTO/Validator
- Chama Service
- Retorna JSON padronizado

DTO:
- Define o contrato de entrada ou saida da API
- Recebe validacoes de formato
- Evita expor Entity diretamente

Validator:
- Valida regra de entrada reutilizavel
- Exemplo: email unico

Mapper:
- Converte Entity para DTO ou array de resposta
- Impede vazamento de campos sensiveis

Service:
- Executa regra de negocio
- Orquestra repository, password hasher e validacoes de dominio

Repository:
- Centraliza consultas de banco
- Evita queries espalhadas em services/controllers

Entity:
- Representa estado persistido
- Pode ter regras simples do proprio dominio
```

Por que assim:

```md
- Controllers ficam pequenos e previsiveis.
- Services ficam testaveis.
- Repositories concentram acesso ao banco.
- DTOs protegem o contrato publico da API.
- Mappers evitam over-fetching e vazamento de dados.
- A Entity nao vira uma classe gigante com regras de aplicacao.
```

## Uso obrigatorio do Symfony CLI/Maker

Para este projeto:

```md
- Entities devem ser criadas com Symfony CLI/Maker.
- Migrations devem ser geradas com Symfony CLI/Maker.
- Migrations devem ser revisadas antes de executar.
- Ajustes manuais em Entity/Migration so entram como refinamento apos a geracao.
```

## Sequencia ideal dos modulos do projeto

Ordem recomendada:

```md
1. Base tecnica da API
2. User
3. Auth com token proprio via header
4. UserProfile
5. Template
6. Resume
7. ResumeSection
8. Experience
9. Education
10. Skill
11. Project
12. PortfolioSite
13. PortfolioSection
14. Public Portfolio
15. AIAnalysis
16. PDFGenerator
```

Motivo:

```md
- User e dono dos recursos principais.
- Auth depende de User.
- UserProfile depende de User autenticado.
- Resume e PortfolioSite dependem de User e Template.
- AI e PDF dependem do curriculo ja modelado.
```

## 2. Objetivo do modulo User

O modulo User representa a conta do usuario dentro da plataforma.

Ele deve resolver:

```md
- Persistir dados basicos do usuario
- Guardar senha com hash seguro
- Validar email unico
- Ativar e desativar usuario
- Retornar usuario sem campos sensiveis
- Servir como base para Auth, Profile, Resume, Project e Portfolio
```

Ele nao deve resolver:

```md
- Login
- Logout
- Geracao de token
- Validacao do header X-Portfolio-Token
- UserProfile profissional
- Regras de curriculo ou portfolio
```

Essas responsabilidades ficam em modulos proprios.

## 3. Estrutura de diretorios

Criar ou manter:

```md
src/
├── Controller/
│   └── UserController.php
├── DTO/
│   └── User/
│       ├── CreateUserDTO.php
│       ├── UpdateUserDTO.php
│       └── UserResponseDTO.php
├── Entity/
│   └── User.php
├── Exception/
│   ├── ConflictException.php
│   └── ResourceNotFoundException.php
├── Mapper/
│   └── UserMapper.php
├── Repository/
│   └── UserRepository.php
├── Service/
│   ├── ApiResponseService.php
│   └── UserService.php
└── Validator/
    ├── UniqueUserEmail.php
    └── UniqueUserEmailValidator.php
```

Arquivos gerados pelo Symfony:

```md
migrations/VersionYYYYMMDDHHMMSS.php
```

## 4. Passo a passo via CLI

## 4.1 Conferir pacotes

```bash
composer show symfony/security-bundle symfony/validator doctrine/orm symfony/maker-bundle
```

Se faltar algum:

```bash
composer require symfony/security-bundle symfony/validator doctrine/orm doctrine/doctrine-bundle
composer require symfony/maker-bundle --dev
```

## 4.2 Criar o User com Maker

Use o Maker para gerar a base do usuario:

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

Depois, adicione os campos extras com:

```bash
php bin/console make:entity User
```

Campos a adicionar:

```md
name: string, length 150, nullable false
avatar: string, length 255, nullable true
isActive: boolean, nullable false
createdAt: datetime_immutable, nullable false
updatedAt: datetime_immutable, nullable true
```

Observacao:

```md
O Maker pode gerar nomes como isActive, createdAt e updatedAt em camelCase.
A tabela no banco deve ficar com snake_case por configuracao padrao do Doctrine.
```

## 4.3 Gerar migration

```bash
php bin/console make:migration
```

Antes de executar, revisar:

```bash
Get-Content migrations\Version*.php
```

Executar:

```bash
php bin/console doctrine:migrations:migrate
```

Validar:

```bash
php bin/console doctrine:schema:validate
php bin/console debug:router
```

## 4.4 Criar pastas de apoio

```bash
mkdir src\DTO\User
mkdir src\Validator
mkdir src\Mapper
mkdir src\Service
mkdir src\Exception
```

## 5. Banco de dados

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

Por que `roles` existe:

```md
O make:user do Symfony usa roles para integracao com o Security Bundle.
Mesmo com token proprio via header, roles continuam uteis para autorizacao futura.
```

## Migration esperada

A migration sera gerada automaticamente. Ela deve ficar parecida com:

```php
<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionYYYYMMDDHHMMSS extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create users table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE users (
            id INT AUTO_INCREMENT NOT NULL,
            email VARCHAR(180) NOT NULL,
            roles JSON NOT NULL,
            password VARCHAR(255) NOT NULL,
            name VARCHAR(150) NOT NULL,
            avatar VARCHAR(255) DEFAULT NULL,
            is_active TINYINT(1) NOT NULL,
            created_at DATETIME NOT NULL COMMENT ''(DC2Type:datetime_immutable)'',
            updated_at DATETIME DEFAULT NULL COMMENT ''(DC2Type:datetime_immutable)'',
            UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE INDEX idx_users_is_active ON users (is_active)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE users');
    }
}
```

Boa pratica:

```md
Nunca escrever migration do zero quando o Maker consegue gerar.
Revisar SQL gerado e ajustar apenas indice, nome ou detalhe especifico.
```

## 6. Entity

Arquivo:

```md
src/Entity/User.php
```

Codigo completo recomendado:

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

    public function __construct(
        string $name,
        string $email,
    ) {
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

Relacionamentos futuros:

```md
User 1:1 UserProfile
User 1:N Resume
User 1:N Project
User 1:N PortfolioSite
User 1:N AuthToken
```

Decisao:

```md
Nao adicionar relacionamentos agora se o modulo relacionado ainda nao existe.
Isso reduz migrations prematuras e evita acoplamento cedo demais.
```

## 7. Exceptions

## ConflictException

Arquivo:

```md
src/Exception/ConflictException.php
```

```php
<?php

declare(strict_types=1);

namespace App\Exception;

final class ConflictException extends \RuntimeException
{
}
```

## ResourceNotFoundException

Arquivo:

```md
src/Exception/ResourceNotFoundException.php
```

```php
<?php

declare(strict_types=1);

namespace App\Exception;

final class ResourceNotFoundException extends \RuntimeException
{
}
```

Por que criar exceptions:

```md
Services nao devem conhecer detalhes HTTP.
Uma exception de dominio/aplicacao pode ser convertida para JSON pelo ExceptionSubscriber.
```

## 8. Repository

Arquivo:

```md
src/Repository/UserRepository.php
```

Codigo completo recomendado:

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

    public function findOneByEmail(string $email): ?User
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
            ->andWhere('u.isActive = :isActive')
            ->setParameter('email', mb_strtolower(trim($email)))
            ->setParameter('isActive', true)
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

    /**
     * @return list<array{
     *     id: int,
     *     name: string,
     *     email: string,
     *     avatar: string|null,
     *     isActive: bool,
     *     createdAt: \DateTimeImmutable
     * }>
     */
    public function findActiveSummaries(int $limit = 20, int $offset = 0): array
    {
        return $this->createQueryBuilder('u')
            ->select('u.id', 'u.name', 'u.email', 'u.avatar', 'u.isActive', 'u.createdAt')
            ->andWhere('u.isActive = :isActive')
            ->setParameter('isActive', true)
            ->orderBy('u.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getArrayResult();
    }
}
```

Boas praticas:

```md
- Repository nao valida regra de negocio.
- Repository nao retorna password em queries de listagem.
- Usar select parcial em listagens para evitar over-fetching.
- Usar paginacao em qualquer listagem.
- Nao criar JOIN FETCH sem necessidade.
- Para User sozinho nao ha N+1, mas cuidado quando UserProfile, Resume e Portfolio forem relacionados.
```

## 9. DTOs

Criar pasta:

```md
src/DTO/User/
```

## CreateUserDTO

Arquivo:

```md
src/DTO/User/CreateUserDTO.php
```

```php
<?php

declare(strict_types=1);

namespace App\DTO\User;

use App\Validator\UniqueUserEmail;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateUserDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'O nome e obrigatorio.')]
        #[Assert\Length(max: 150, maxMessage: 'O nome deve ter no maximo {{ limit }} caracteres.')]
        public string $name,

        #[Assert\NotBlank(message: 'O email e obrigatorio.')]
        #[Assert\Email(message: 'Informe um email valido.')]
        #[Assert\Length(max: 180, maxMessage: 'O email deve ter no maximo {{ limit }} caracteres.')]
        #[UniqueUserEmail]
        public string $email,

        #[Assert\NotBlank(message: 'A senha e obrigatoria.')]
        #[Assert\Length(min: 8, max: 72, minMessage: 'A senha deve ter pelo menos {{ limit }} caracteres.')]
        #[Assert\Regex(
            pattern: '/^(?=.*[A-Za-z])(?=.*\d).+$/',
            message: 'A senha deve conter letras e numeros.'
        )]
        public string $password,
    ) {
    }
}
```

## UpdateUserDTO

Arquivo:

```md
src/DTO/User/UpdateUserDTO.php
```

```php
<?php

declare(strict_types=1);

namespace App\DTO\User;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateUserDTO
{
    public function __construct(
        #[Assert\Length(max: 150, maxMessage: 'O nome deve ter no maximo {{ limit }} caracteres.')]
        public ?string $name = null,

        #[Assert\Email(message: 'Informe um email valido.')]
        #[Assert\Length(max: 180, maxMessage: 'O email deve ter no maximo {{ limit }} caracteres.')]
        public ?string $email = null,

        #[Assert\Url(message: 'Informe uma URL valida para o avatar.')]
        #[Assert\Length(max: 255, maxMessage: 'O avatar deve ter no maximo {{ limit }} caracteres.')]
        public ?string $avatar = null,
    ) {
    }
}
```

Observacao:

```md
Validacao de email unico no update precisa ignorar o proprio usuario.
Por isso ela fica no UserService, nao diretamente no atributo do DTO.
```

## UserResponseDTO

Arquivo:

```md
src/DTO/User/UserResponseDTO.php
```

```php
<?php

declare(strict_types=1);

namespace App\DTO\User;

final readonly class UserResponseDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public ?string $avatar,
        public bool $isActive,
        public string $createdAt,
        public ?string $updatedAt,
    ) {
    }

    /**
     * @return array{
     *     id: int,
     *     name: string,
     *     email: string,
     *     avatar: string|null,
     *     is_active: bool,
     *     created_at: string,
     *     updated_at: string|null
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
```

## 10. Validator

## UniqueUserEmail

Arquivo:

```md
src/Validator/UniqueUserEmail.php
```

```php
<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER)]
final class UniqueUserEmail extends Constraint
{
    public string $message = 'Este email ja esta em uso.';
}
```

## UniqueUserEmailValidator

Arquivo:

```md
src/Validator/UniqueUserEmailValidator.php
```

```php
<?php

declare(strict_types=1);

namespace App\Validator;

use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class UniqueUserEmailValidator extends ConstraintValidator
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueUserEmail) {
            throw new UnexpectedTypeException($constraint, UniqueUserEmail::class);
        }

        if ($value === null || $value === '') {
            return;
        }

        if (!is_string($value)) {
            return;
        }

        if ($this->userRepository->existsByEmail($value)) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
```

Por que usar validator:

```md
- Regras de entrada reutilizaveis ficam fora do controller.
- O controller recebe DTO ja validado.
- A regra de unicidade fica testavel.
```

## 11. Mapper

Arquivo:

```md
src/Mapper/UserMapper.php
```

```php
<?php

declare(strict_types=1);

namespace App\Mapper;

use App\DTO\User\UserResponseDTO;
use App\Entity\User;

final class UserMapper
{
    public function toResponseDTO(User $user): UserResponseDTO
    {
        return new UserResponseDTO(
            id: (int) $user->getId(),
            name: $user->getName(),
            email: $user->getEmail(),
            avatar: $user->getAvatar(),
            isActive: $user->isActive(),
            createdAt: $user->getCreatedAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $user->getUpdatedAt()?->format(\DateTimeInterface::ATOM),
        );
    }

    /**
     * @return array{
     *     id: int,
     *     name: string,
     *     email: string,
     *     avatar: string|null,
     *     is_active: bool,
     *     created_at: string,
     *     updated_at: string|null
     * }
     */
    public function toArray(User $user): array
    {
        return $this->toResponseDTO($user)->toArray();
    }
}
```

Decisao:

```md
Mapper centraliza o formato publico.
Se amanha o frontend precisar mudar o nome de algum campo, a mudanca fica aqui.
```

## 12. ApiResponseService

Se ainda nao existir, criar:

```md
src/Service/ApiResponseService.php
```

```php
<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;

final class ApiResponseService
{
    /**
     * @param array<string, mixed>|list<mixed>|null $data
     */
    public function success(
        string $message,
        array|null $data = null,
        int $status = JsonResponse::HTTP_OK,
    ): JsonResponse {
        return new JsonResponse([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * @param array<string, mixed>|list<mixed> $errors
     */
    public function error(
        string $message,
        array $errors = [],
        int $status = JsonResponse::HTTP_BAD_REQUEST,
    ): JsonResponse {
        return new JsonResponse([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}
```

## 13. Service

Arquivo:

```md
src/Service/UserService.php
```

Codigo completo recomendado:

```php
<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\User\CreateUserDTO;
use App\DTO\User\UpdateUserDTO;
use App\Entity\User;
use App\Exception\ConflictException;
use App\Exception\ResourceNotFoundException;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function create(CreateUserDTO $dto): User
    {
        $this->ensureEmailIsAvailable($dto->email);

        $user = new User(
            name: $dto->name,
            email: $dto->email,
        );

        $passwordHash = $this->passwordHasher->hashPassword($user, $dto->password);
        $user->changePasswordHash($passwordHash);

        $this->userRepository->save($user);

        return $user;
    }

    public function update(User $user, UpdateUserDTO $dto): User
    {
        if ($dto->name !== null) {
            $user->updateName($dto->name);
        }

        if ($dto->email !== null && mb_strtolower($dto->email) !== $user->getEmail()) {
            $this->ensureEmailIsAvailable($dto->email, $user);
            $user->updateEmail($dto->email);
        }

        if ($dto->avatar !== null) {
            $user->updateAvatar($dto->avatar);
        }

        $this->userRepository->save($user);

        return $user;
    }

    public function activate(User $user): User
    {
        $user->activate();
        $this->userRepository->save($user);

        return $user;
    }

    public function deactivate(User $user): User
    {
        $user->deactivate();
        $this->userRepository->save($user);

        return $user;
    }

    public function getById(int $id): User
    {
        $user = $this->userRepository->find($id);

        if (!$user instanceof User) {
            throw new ResourceNotFoundException('Usuario nao encontrado.');
        }

        return $user;
    }

    public function ensureEmailIsAvailable(string $email, ?User $currentUser = null): void
    {
        $ignoreUserId = $currentUser?->getId();

        if ($this->userRepository->existsByEmail($email, $ignoreUserId)) {
            throw new ConflictException('Este email ja esta em uso.');
        }
    }
}
```

Decisoes:

```md
- Hash de senha fica no Service, nao na Entity.
- Entity nao conhece PasswordHasher.
- Unicidade de email e garantida em duas camadas: validator e banco.
- Service retorna Entity porque ainda esta dentro da aplicacao.
- Controller usa Mapper para resposta publica.
```

## 14. Controller

Arquivo:

```md
src/Controller/UserController.php
```

Codigo completo recomendado:

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\User\CreateUserDTO;
use App\DTO\User\UpdateUserDTO;
use App\Mapper\UserMapper;
use App\Service\ApiResponseService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/users')]
final class UserController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService,
        private readonly UserMapper $userMapper,
        private readonly ApiResponseService $apiResponse,
    ) {
    }

    #[Route('', name: 'api_users_create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] CreateUserDTO $dto,
    ): JsonResponse {
        $user = $this->userService->create($dto);

        return $this->apiResponse->success(
            message: 'Usuario criado com sucesso.',
            data: $this->userMapper->toArray($user),
            status: JsonResponse::HTTP_CREATED,
        );
    }

    #[Route('/{id<\d+>}', name: 'api_users_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $user = $this->userService->getById($id);

        return $this->apiResponse->success(
            message: 'Usuario encontrado com sucesso.',
            data: $this->userMapper->toArray($user),
        );
    }

    #[Route('/{id<\d+>}', name: 'api_users_update', methods: ['PUT', 'PATCH'])]
    public function update(
        int $id,
        #[MapRequestPayload] UpdateUserDTO $dto,
    ): JsonResponse {
        $user = $this->userService->getById($id);
        $user = $this->userService->update($user, $dto);

        return $this->apiResponse->success(
            message: 'Usuario atualizado com sucesso.',
            data: $this->userMapper->toArray($user),
        );
    }

    #[Route('/{id<\d+>}/activate', name: 'api_users_activate', methods: ['PATCH'])]
    public function activate(int $id): JsonResponse
    {
        $user = $this->userService->getById($id);
        $user = $this->userService->activate($user);

        return $this->apiResponse->success(
            message: 'Usuario ativado com sucesso.',
            data: $this->userMapper->toArray($user),
        );
    }

    #[Route('/{id<\d+>}/deactivate', name: 'api_users_deactivate', methods: ['PATCH'])]
    public function deactivate(int $id): JsonResponse
    {
        $user = $this->userService->getById($id);
        $user = $this->userService->deactivate($user);

        return $this->apiResponse->success(
            message: 'Usuario desativado com sucesso.',
            data: $this->userMapper->toArray($user),
        );
    }
}
```

Decisoes:

```md
- Controller nao chama Repository.
- Controller nao chama PasswordHasher.
- Controller nao monta array manualmente.
- Controller nao decide regra de email unico.
- Endpoints administrativos podem ser protegidos depois com Auth e roles.
```

Observacao importante:

```md
No futuro, registro publico deve migrar para POST /api/auth/register.
O POST /api/users pode ficar restrito a administradores ou ser removido da API publica.
```

## 15. Tratamento de erros esperado

Este modulo depende de um ExceptionSubscriber global.

Exemplo esperado:

```md
src/EventSubscriber/ExceptionSubscriber.php
```

Comportamento:

```md
ResourceNotFoundException -> HTTP 404
ConflictException -> HTTP 409
ValidationFailedException -> HTTP 422
AccessDeniedException -> HTTP 403
Demais excecoes -> HTTP 500 em producao sem stack trace
```

Exemplo de resposta de conflito:

```json
{
  "success": false,
  "message": "Este email ja esta em uso.",
  "errors": []
}
```

## 16. Endpoints do modulo

Endpoints iniciais:

```md
POST  /api/users
GET   /api/users/{id}
PUT   /api/users/{id}
PATCH /api/users/{id}
PATCH /api/users/{id}/activate
PATCH /api/users/{id}/deactivate
```

Endpoints futuros, apos Auth:

```md
GET /api/auth/me
PUT /api/me
```

Nao criar ainda:

```md
GET /api/users
DELETE /api/users/{id}
```

Motivo:

```md
Listagem geral de usuarios e exclusao fisica exigem regra administrativa clara.
Por enquanto, desativar usuario e mais seguro que deletar.
```

## 17. Exemplos reais de uso

## Criar usuario

Request:

```bash
curl -X POST http://127.0.0.1:8000/api/users ^
  -H "Content-Type: application/json" ^
  -d "{\"name\":\"Gustavo Oliveira\",\"email\":\"gustavo@email.com\",\"password\":\"Senha123\"}"
```

Response:

```json
{
  "success": true,
  "message": "Usuario criado com sucesso.",
  "data": {
    "id": 1,
    "name": "Gustavo Oliveira",
    "email": "gustavo@email.com",
    "avatar": null,
    "is_active": true,
    "created_at": "2026-05-02T20:10:00-03:00",
    "updated_at": null
  }
}
```

## Buscar usuario

```bash
curl http://127.0.0.1:8000/api/users/1
```

## Atualizar usuario

```bash
curl -X PATCH http://127.0.0.1:8000/api/users/1 ^
  -H "Content-Type: application/json" ^
  -d "{\"name\":\"Gustavo Fagundes\",\"avatar\":\"https://example.com/avatar.png\"}"
```

## Desativar usuario

```bash
curl -X PATCH http://127.0.0.1:8000/api/users/1/deactivate
```

## 18. Boas praticas obrigatorias

## SOLID

```md
Single Responsibility:
- UserService cuida de usuario.
- UserMapper cuida de resposta.
- UserRepository cuida de query.

Open/Closed:
- Novas regras de usuario devem entrar em novos metodos/classes sem quebrar controllers.

Liskov:
- Evitar herancas desnecessarias no dominio.

Interface Segregation:
- Criar interfaces apenas quando houver mais de uma implementacao real ou fronteira externa.

Dependency Inversion:
- Services dependem de abstracoes do Symfony/Doctrine injetadas por DI, nao de instanciacao manual.
```

## Performance

```md
- Nao retornar entidades diretamente.
- Nao retornar password.
- Nao buscar relacoes futuras em listagens simples.
- Usar paginacao em listagens.
- Usar select parcial para summaries.
- Evitar N+1 ao adicionar UserProfile/Resume no futuro.
```

## Seguranca

```md
- Senha sempre com UserPasswordHasherInterface.
- Nunca logar senha ou token.
- Nunca retornar password.
- Validar email unico no app e no banco.
- Usuario desativado nao deve autenticar.
- Endpoints de usuario devem ser protegidos quando Auth estiver pronto.
```

## Banco

```md
- Email deve ter unique index.
- Campos de busca devem ter indice quando necessario.
- Nao usar soft delete improvisado. Para este modulo, isActive resolve desativacao.
- Evitar campos JSON sem necessidade no User.
```

## API

```md
- Todas as respostas seguem success/message/data ou success/message/errors.
- DTOs definem entrada.
- Mapper define saida.
- Erros sao padronizados globalmente.
```

## 19. Checklist de pronto

O modulo User esta pronto quando:

```md
- User foi criado com php bin/console make:user.
- Campos extras foram criados com php bin/console make:entity User.
- Migration foi gerada com php bin/console make:migration.
- Migration foi revisada.
- Migration foi executada.
- Entity User tem dados basicos e metodos de dominio simples.
- UserRepository tem save, remove, findOneByEmail, findActiveByEmail e existsByEmail.
- DTOs existem e possuem validacoes.
- UniqueUserEmailValidator existe.
- UserMapper nao retorna password.
- UserService aplica hash de senha e regra de email unico.
- UserController nao contem regra de negocio.
- Respostas seguem o padrao JSON do projeto.
- Usuario pode ser criado, buscado, atualizado, ativado e desativado.
- Password nao aparece em nenhuma response.
- doctrine:schema:validate passa.
- debug:router mostra as rotas esperadas.
```

## 20. Comandos finais de validacao

```bash
php bin/console lint:container
php bin/console doctrine:schema:validate
php bin/console debug:router
```

Se houver testes:

```bash
php bin/phpunit
```

## 21. Padrao para proximos guias

Todos os proximos arquivos da pasta `guia/` devem seguir esta estrutura:

```md
1. Objetivo do modulo
2. Decisoes arquiteturais
3. Estrutura de diretorios
4. Passo a passo via CLI
5. Banco e migration
6. Entity
7. Repository
8. DTOs
9. Validators
10. Mappers
11. Services
12. Controllers
13. Tratamento de erros
14. Endpoints
15. Exemplos reais de uso
16. Boas praticas
17. Checklist de pronto
18. Comandos de validacao
```

Este documento passa a ser a referencia oficial de consistencia para os proximos modulos.

