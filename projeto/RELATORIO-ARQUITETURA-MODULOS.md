# Relatório de arquitetura — alinhamento aos módulos (referência: **User**)

**Projeto:** `iportfolio-api` (Symfony / Doctrine)  
**Referência:** módulo **User** (`UserController`, `UserService`, `User` + `UserRepository`, DTOs em `DTO/User/`, `UserMapper`, exceções em `Exception/User/`, `ApiResponseService`).  
**Data do levantamento:** 2026-05-09  

---

## 1. Resumo executivo

| Dimensão | User (referência) | Demais módulos |
|----------|-------------------|----------------|
| Controller fino + `ApiResponseService` | Sim | Parcial (Auth/Me ok; conteúdo repetem `authenticatedUser`) |
| Service retorna `array{status, message, data?, errors?}` | Sim, com PHPDoc | Mistura: alguns sem PHPDoc; Resume usa 404 sem exceção |
| Exceções de domínio capturadas no Service | Sim (`UserNotFound`, etc.) | Profile sim; Auth sim; conteúdo não modela “não encontrado”; Resume inline |
| Repository com `save()` dedicado | Sim | Sim (padrão igual) |
| Mapper dedicado | Sim | Sim (padrão igual) |
| DTO + `MapRequestPayload` + Asserts | Sim | Sim (varia `readonly` / `SerializedName`) |
| **Factories** (`src/Factory`) | Não usado no User | Inexistente no projeto |
| **Validators** custom (`src/Validator`) | Guia prevê; validação de avatar em **Service** auxiliar | Inexistente pasta; regras espalhadas |
| **Interfaces** de serviço/repositório | Não | Nenhuma em `src/` |
| CRUD completo | Create, read, update, toggles | Conteúdo: só **create + list**; Resume + show |

**Conclusão:** a base (Controller → Service → Repository + Entity + DTO + Mapper + exceções por domínio) está **parcialmente replicada**. O maior desvio é o **“módulo de conteúdo”** (Skills, Experiences, Educations, Certifications, Projects): **estruturas enxutas demais**, **código espelho** entre Services e Controllers, e **ausência do mesmo rigor de exceções/PHPDoc** do `UserService`.  

---

## 2. Referência — módulo User (padrão esperado)

### 2.1 Estrutura

- **Controller:** `App\Controller\UserController` — apenas rotas + `fromServiceResult`.
- **Service:** `App\Service\UserService` — regra de negócio, `@return array{status, ...}` nas assinaturas documentadas.
- **Entity:** `App\Entity\User` + `App\Repository\UserRepository`.
- **DTOs:** `App\DTO\User\CreateUserDTO`, `UpdateUserDTO`.
- **Mapper:** `App\Mapper\UserMapper`.
- **Exceções:** `Exception\User/*`.
- **Auxiliar:** `AvatarBase64Service` (validação antes de persistir; não há Factory dedicada).

### 2.2 Princípios observados no User

1. Controller **sem** lógica de negócio.  
2. Erros previstos → **exceções** + `catch` com HTTP adequado (404, 409, 422).  
3. Persistência apenas via **Repository**.  
4. Resposta única através de **`ApiResponseService::fromServiceResult`**.  

Este é o contrato informal que o relatório usa para comparar os outros módulos.

---

## 3. Inventário por módulo

### 3.1 User — **referência**

| Artefato | Status |
|----------|--------|
| Pastas (`Controller`, `Service`, `Entity`, `Repository`, `DTO/User`, `Mapper`, `Exception/User`) | OK |
| Controllers apenas HTTP | OK |
| Regras no Service | OK |
| Exceções + mapeamento HTTP | OK |
| Migrations vs entidade (`avatar` TEXT/MEDIUMTEXT) | Requer migração MySQL aplicada (`Version20260509120000`) |

**Riscos de manutenção atuais**

- Avatar em **base64 na coluna**: listagens/`/api/me` que serializam o utilizador inteiro podem gerar **payloads enormes** e pressão em memória/DB (não é bug de N+1; é **over-fetching** por desenho).

---

### 3.2 Profile (`UserProfile`)

| Artefato | Status |
|----------|--------|
| `ProfileController` + `UserProfileService` + `UserProfile` + `UserProfileRepository` | OK |
| DTO `UpsertProfileDTO` | OK |
| `UserProfileMapper` | OK |
| `UserProfileNotFoundException` → 404 | OK (alinha ao User) |

**Ajustes sugeridos**

- Alinhar formato de código do constructor com `UserService` (quebra de linhas / PHPDoc em todos os métodos públicos que retornam `array`).
- Opcional: exceções mais específicas para validação de URLs (se regra de negócio crescer).

---

### 3.3 Auth + sessão (`AuthController`, `MeController`, `AuthService`, …)

| Artefato | Status |
|----------|--------|
| `AuthController` — login público + logout autenticado | OK fluxo HTTP |
| `RequiresAuthMiddleware` + atributo `RequiresAuth` | Boa separação (cross-cutting) |
| `AuthService`, `AuthenticatedUserService`, `AuthTokenService`, `LoginSessionRepository` | Coeso |
| Exceções `Exception/Auth/*` + `ApiExceptionSubscriber` para token | Parcialmente duplicado (ver §5) |

**Fora do padrão User**

- **Dois caminhos** para erros de autenticação: (1) retorno `array` com 401 nos services onde aplicável; (2) **`InvalidAuthTokenException`/`MissingAuthTokenException`** tratadas no **`ApiExceptionSubscriber`** e **não** passando por `ApiResponseService`. Funciona, mas **não é um único canal** de erro como no User (sempre `fromServiceResult`).

**Melhorias**

- Unificar: ou todas as falhas Auth viram array + `fromServiceResult`, ou todas viram exceções HTTP tratadas por um subscriber que replique **exatamente** o formato `{ message, errors? }`.

---

### 3.4 Resume

| Artefato | Status |
|----------|--------|
| `ResumeController`, `ResumeService`, `Resume`, `ResumeRepository`, `ResumeMapper`, `CreateResumeDTO` | OK estrutura |
| `PublicIdGenerator` como serviço | OK (papel de “factory” técnico, não pasta Factory) |

**Fora do padrão User**

- `ResumeService::show()`: recurso não encontrado retorna **`return ['status' => 404, ...]`** em vez de **`throw ResumeNotFoundException`** + `catch` (como `UserNotFoundException` no User).  
- Inconsistência de **mensagem/nomenclatura** (“Curriculo” vs “Usuario”) — aceitável, mas o **mecanismo** de erro difere.

**Repository**

- `unsetMainForUser()` altera entidades sem `flush` próprio — o `flush()` do `save()` seguinte persistiu as mudanças; **documentar** esse contrato ou chamar `$this->getEntityManager()->flush()` no fim do método para ficar explícito e evitar regressões futuras.

---

### 3.5 Blocos de conteúdo do utilizador — **Skills, Experiences, Educations, Certifications, Projects**

**Padrão atual (idêntico entre os cinco):**

- `#[RequiresAuth]` + `/api/{recurso}`  
- POST create + GET list apenas.  
- `*Service`: `create` + `list` com `try/catch (\Exception)` genérico.  
- `*Repository::findByUser` + `save`.  
- `*Mapper::toArray` / `toArrayList`.

| Versus User | Gap |
|-------------|-----|
| Operações | Falta **GET por id**, **PUT/PATCH**, **DELETE** (User tem ciclo completo). |
| Exceções | Não há `SkillNotFoundException`, etc. — hoje não há “show”, então impacto baixo. |
| PHPDoc `@return array{...}` | Frequentemente **ausente** (ex.: `SkillService`). |
| Formatação | Constructors numa linha nos Services — diverge do User. |

**Código duplicado (alto acoplamento de cópia)**

- Método privado **`date(?string): ?DateTimeImmutable`** repetido em `EducationService` e `ExperienceService`.  
- Método **`authenticatedUser(Request)`** **copiado** em vários controllers (Profile, Skill, Experience, Education, Certification, Project, Resume, Me parcialmente).

---

## 4. Auditoria por tópico solicitado

### 4.1 Estrutura de pastas e responsabilidades

- **Organização clássica por camada** (`Controller/`, `Service/`, `Entity/`, …): **boa** para Symfony de porte médio.  
- **Pastas ausentes vs guia/projeto inicial:**  
  - `src/Validator/` — não existe.  
  - `src/Factory/` — não existe (`PublicIdGenerator` compensa só parcialmente).  
- **`Service/Auth/`** — subpacote coerente para autenticação; User mantém auxiliar (`AvatarBase64Service`) ao nível raiz — **aceitável**, mas poderia virar `Service/User/` por simetria.

### 4.2 Controllers

- **Padronizado:** uso de `ApiResponseService`, `MapRequestPayload`, `JsonResponse`.  
- **Desvio:** repetição de `authenticatedUser()` em massa viola **DRY**; risco é mudar comportamento só em alguns controllers.  
- **`UserController`** não precisa disso porque não usa `[RequiresAuth]` na classe inteira — padrão intencionalmente diferente (**OK** desde que documentado).

### 4.3 Services

- **User, Profile, Auth, Resume**: concentram regra e orquestração repository + mapper + exceções.  
- **Conteúdo (5 módulos)**: regra mínima; **maior peso está na Entity (`update(...)`)** — isso é válido em DDD-lite, desde que aggregates não fiquem anêmicos; aqui há **rico `update`** nas entidades, o que ajuda.

### 4.4 Entities

- Todas ligadas ao `User` com `ManyToOne` + `JoinColumn(onDelete: 'CASCADE')` nas migrações analisadas — **consistente**.

### 4.5 Repositories

- Padrão **ServiceEntityRepository** + método **`save`** explícito: **consistente**.  
- Queries listam por **`user`** — **sem JOIN desnecessário** para serializar coleção atual (mappers não acessam `User` nos DTOs de listagem típicos).  
- **`UserRepository`** expõe `findByEmail` — específico de domínio; repositórios de conteúdo expõem `findByUser` — **coerente**.

### 4.6 DTOs

- Mistura de **`final readonly class`** e classes com propriedades `public readonly`/normais — **consistente dentro de cada DTO**.  
- `CreateResumeDTO` usa **`SerializedName`** — bom para snake_case JSON; outros DTOs podem ganhar o mesmo onde a API externa usar snake_case.

### 4.7 Validators (Symfony Constraints / custom)

- Validações majoritariamente nos **DTOs com `Assert`** — alinhado ao guia de User para campos simples.  
- Avatar: validação MIME/decode em **`AvatarBase64Service`** (não validator dedicado). **Trade-off**: menos declarativo; mais fácil de testar isolado em service.

### 4.8 Factories

- **Não há** factories nomeadas como no guia (`src/Factory`).  
- `new Entity(...)` aparece nos **Services** — aceitável em projeto pequeno; escala pode pedir **`UserFactory` / `ResumeFactory`** apenas se construções ficarem repetidas/complexas.

### 4.9 Mappers

- Padrão estável: `toArray`, `toArrayList`.  
- **UserMapper** devolve timestamps em `\DateTimeInterface::ATOM`; outros usam **`DATE_ATOM`** — **equivalentes**; padronizar import constante apenas por estilo.

### 4.10 Exceptions

- **Namespaces por domínio** (`User`, `Profile`, `Auth`) — bom.  
- **Resume**: falta tipo dedicado para “não encontrado”.  
- **Subscriber** trata apenas auth token + validation — **outros erros Symfony** continuam página HTML ou handler padrão (verificar `framework.yaml` em produção).

### 4.11 Interfaces e contratos

- **Nenhuma `interface` em `src/`** — injeção depende de classes concretas final.  
- **SOLID (D):** aceitável em app monolítico; testes duplos/mocks podem pedir interfaces depois.

### 4.12 Injeção de dependência

- **Construtores promoted + `readonly`** — bem alinhado a Symfony atual.  
- `#[Autowire('%env(...)')]` em `AuthenticatedUserService` — explícito e claro.

### 4.13 Nomenclatura

- Inglês em código (`Education`, `Skill`), mensagens PT — **mistura habitual** neste codebase.  
- Rotas pluralizadas `/api/skills`, `/api/users/{id}` — OK.  
- `public_id` vs `id` em Resume — decisão API clara no mapper.

### 4.14 Separação de responsabilidades

| Área | Avaliação |
|------|-----------|
| Controller só HTTP | Boa |
| Service orquestra | Boa |
| Repository só persistência/consultas | Boa |
| Middleware auth | Boa |

### 4.15 Padronização de retornos e erros

- **Sucesso:** `ApiResponseService` uniformiza `message` + `data` opcional.  
- **Erro validação:** 422 via `ApiExceptionSubscriber` → `errors` estruturado — **ótimo**.  
- **Erro servidor:** vários Services expõem `'errors' => $e->getMessage()` — **risco de vazar detalhes internos** em produção; preferir **log + mensagem genérica** ao cliente.

### 4.16 Clean Architecture / SOLID / DDD

- **Camadas pragmáticas Symfony** — não há “casos de uso” explícitos nem bounded contexts isolados como pacotes.  
- Entidades com **`update`** rico — ganho DDD-lite.  
- **Acoplamento** principal: controllers ↔ `RequiresAuthMiddleware` constantes repetidas.

### 4.17 Performance / N+1 / over-fetching / memória

- **N+1:** cenário atual de listagens de conteúdo **moderado**: uma query lista filhos do user; mappers não forçam lazy `User`. **Monitorizar** quando adicionarem relações nested (ex.: resume com coleções eager).  
- **Over-fetching:** **User** com `avatar` grande em **`UserMapper`** em qualquer endpoint que serialize user completo — **principal alerta atual**. Mitigações: campo “summary” sem avatar em listagens, ou URL/chave externa se voltarem storage.  
- **Memory leaks (PHP-FPM):** não típico por request cycle; vigilância só em consumers long-lived ou comandos batelados grandes.

### 4.18 Segurança

- **Senha:** não aparece no `UserMapper` — bom.  
- **Token:** opaco armazenado como hash na sessão — coerente.  
- **401 vs 422:** bem separados entre auth fail e validation.

---

## 5. Itens consolidados — fora do padrão User

1. **`authenticatedUser` duplicado** em múltiplos controllers.  
2. **Ausência de exceção + catch** para “Resume não encontrado” (contrasta com User/Profile).  
3. **`date()` privado duplicado** em Services de Experience/Education.  
4. **Módulos de conteúdo** sem atualização/leitura individual/remoção (menor simetria com User).  
5. **`ApiExceptionSubscriber` + array return** coexistem como estratégias de erro Auth.  
6. **Ausência total de interfaces** — não urgente; documentar decisão (“YAGNI”) ou introduzir para ports se houver segunda implementação.  
7. **Pastas Validator/Factory vazias** vs expectativa dos guiás Markdown do repositório (documentação pode estar adiantada em relação ao código).

---

## 6. Estrutura ideal alvo (mantendo Symfony)

Árvore-alvo **evolutiva** (não obrigatório renomear tudo de uma vez):

```text
src/
  Controller/
  Middleware/
  Attribute/
  EventSubscriber/
  Entity/
  Repository/
  Service/
    Auth/
    Resume/           # opcional: agrupar por contexto
    Content/          # opcional: SkillService → Content/SkillService ou manter flat
  DTO/
    User/
    Profile/
    Resume/
    Content/          # ou Skill/, Experience/, ...
  Mapper/
  Exception/
    User/
    Profile/
    Auth/
    Resume/           # ResumeNotFoundException
    Content/          # opcional quando CRUD existir
  Validator/          # constraints custom reutilizáveis (opcional)
  Factory/            # opcional quando new Entity(...) repetir demais
```

**Convenções desejadas (espelhar User)**

- Todo método público de serviço exposto à API deve ter PHPDoc:

  `@return array{status: int, message: string, data?: mixed, errors?: mixed}`  

- Todo “recurso não encontrado” em operações futuras deve ter **exceção de domínio** + mesmo padrão de `catch` do `UserService`.  
- Controllers protegidos: **um trait** ou **classe base** interna única (`AbstractAuthenticatedApiController`) com `authenticatedUser()` e eventualmente `loginSession()` quando aplicável.

---

## 7. Exemplos práticos de correção

### 7.1 DRY nos controllers — `AuthenticatedUserTrait` (conceito)

```php
// exemplo: src/Controller/Concerns/AuthenticatedUserTrait.php
trait AuthenticatedUserTrait
{
    private function authenticatedUser(Request $request): User
    {
        $user = $request->attributes->get(RequiresAuthMiddleware::AUTHENTICATED_USER);
        if (!$user instanceof User) {
            throw new InvalidAuthTokenException();
        }

        return $user;
    }
}
```

Um controller passaria a usar `use AuthenticatedUserTrait;`. **Equivalente válido:** `AbstractAuthenticatedApiController extends AbstractController` com o mesmo método `protected`.

### 7.2 Alinhar `ResumeService::show()` ao User

```php
// ideia
throw new ResumeNotFoundException(); // nova em Exception/Resume/

// catch no ResumeService similar a UserNotFoundException
} catch (ResumeNotFoundException $e) {
    return ['status' => Response::HTTP_NOT_FOUND, 'message' => $e->getMessage()];
}
```

Ou retornar 404 mas **centralizar mensagem** na exceção para consistência.

### 7.3 Eliminar duplicação de parsing de datas

Extrair **`App\Service\DateInputParser`** ou `App\Service\DtoDateConverter`:

```php
final class IsoDateConverter
{
    public function toImmutableOrNull(?string $value): ?\DateTimeImmutable
    {
        return $value !== null ? new \DateTimeImmutable($value) : null;
    }
}
```

Injetar em `EducationService` e `ExperienceService` (e futuros).

### 7.4 Tratamento de erro 500 uniforme / sem vazamento

```php
} catch (\Exception $e) {
    // $this->logger->error(...)
    return [
        'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
        'message' => 'Ocorreu algum erro inesperado',
        'errors' => $_ENV['APP_DEBUG'] ?? false ? $e->getMessage() : null,
    ];
}
```

(Ajustar à forma como o projeto lê debug — exemplo ilustrativo.)

---

## 8. Matriz rápida de status por módulo

| Módulo | Estrutura User-like | Exceptions HTTP | CRUD parity | Observação principal |
|--------|---------------------|-----------------|-------------|----------------------|
| User | ✅ | ✅ | ✅ | Avatar grande → over-fetch |
| Profile | ✅ | ✅ | ⚠ só upsert/read usuário atual | Equivale “meu perfil” |
| Auth | ⚠ dois canais erro | ⚠ Subscriber + arrays | ✅ login/logout | Unificar erro auth |
| Me | ✅ | ⚠ igual Auth | ⚠ só read | Combina dados user+session |
| Resume | ✅ | ⚠ 404 inline | ⚠ falta update/delete públicos | ResumeNotFound sugerido |
| Skills | ⚠ formato Service | ⚠ só catch genérico | ❌ só C+L | Alta duplicação com pares |
| Experiences | idem Skills | idem | idem | `date()` duplicado |
| Educations | idem Skills | idem | idem | `date()` duplicado |
| Certifications | idem Skills | idem | idem | — |
| Projects | idem Skills | idem | idem | — |

Legenda: **C+L** = create + list apenas.

---

## 9. Riscos de manutenção (priorização)

| Prioridade | Risco | Mitigação |
|------------|-------|-----------|
| Alta | Avatar base64 pesado nas respostas | DTO de resposta “leve” / omitir avatar em listas |
| Alta | `authenticatedUser` copiado | Trait ou base controller |
| Média | Services de conteúdo quase idênticos | Classe abstrata `AbstractUserOwnedContentService` ou gerador interno (cuidado com magia) |
| Média | Expor `$e->getMessage()` em 500 | Log + resposta genérica em prod |
| Baixa | Falta interfaces | Introduzir só quando houver segundo adapter |

---

## 10. Conclusão

O projeto **já segue um núcleo arquitetural sólido** inspirado no módulo **User**: camadas claras, DTOs validados, mappers, repositórios explícitos e um **formato de resposta HTTP centralizado** via `ApiResponseService`.  

Os principais desvios **não são “anti-padrão”**, mas **inconsistências de maturidade**: módulos de conteúdo **menos completos**, **menos documentados no PHPDoc**, **mais código duplicado** nos controllers e pequenas **heterogeneidades na modelagem de erros** (Resume 404 vs exceções; Auth subscriber vs arrays).  

Aplicando as correções das **§6–§7**, o codebase aproxima-se de forma **uniforme** do padrão estabelecido pelo **User**, com ganhos diretos em **manutenibilidade** e menor risco de **divergência entre controllers**.

---

*Este documento foi gerado com base na análise estática do código-fonte sob `src/` e nas migrações em `migrations/` na data indicada.*
