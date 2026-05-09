# iPortfolio API

API REST para a plataforma **iPortfolio**: utilizadores autenticam-se com token opaco, gerem perfil e blocos de CV (experiências, formação, competências, projetos, certificações), montam **currículos** com secções ordenadas e **sites de portfólio** publicáveis, escolhem **templates** do catálogo (incluindo **premium** com desbloqueio via **Mercado Pago**), e exportam PDF. O núcleo é **Symfony 7** com **Doctrine ORM** e **MySQL**.

---

## Capacidades

| Domínio | Descrição |
|---------|-----------|
| Identidade | Registo, sessão com token, perfil de utilizador |
| Conteúdo reutilizável | Perfil profissional + CRUD de blocos (experiência, educação, skills, projetos, certificações) |
| Currículo | Instâncias de CV com `template_key`, secções, sugestões de texto, PDF |
| Portfólio | Sites por slug, secções, publicação; leitura pública por slug |
| Catálogo | Templates `resume` / `portfolio`, preview, schema opcional, premium e preço |
| Pagamentos | Checkout Pro (Mercado Pago), webhook, transações persistidas |
| Extensibilidade | Endpoints de análise/IA sobre currículo (evolução contínua) |

---

## Arquitetura

### Visão em camadas

A aplicação segue um desenho **em camadas**, com dependências orientadas para o domínio e pouca lógica nos controladores.

```
HTTP Request
    → Controller (roteamento, DTO via Serializer/Validator)
    → Service (regras de negócio, orquestração)
    → Repository / Gateway HTTP (persistência, APIs externas)
    → Entity / resposta via ApiResponseService (JSON)
```

- **Controllers** (`src/Controller/`): apenas mapeiam rotas, injetam serviços e devolvem `JsonResponse` a partir de resultados estruturados.
- **Services** (`src/Service/`): contêm a lógica de negócio; muitos devolvem arrays com `status`, `message`, `data` / `errors` para uniformizar respostas HTTP.
- **Repositories** (`src/Repository/`): acesso a dados Doctrine; operações de escrita explícitas onde necessário.
- **Entities** (`src/Entity/`): modelo relacional (utilizadores, resumes, templates, pagamentos, etc.).
- **DTOs** (`src/DTO/`): entrada validada (Symfony Validator + `MapRequestPayload`).
- **Mappers** (`src/Mapper/`): transformação entidade ↔ array de API.
- **Exceptions** (`src/Exception/`): erros de domínio; tratamento centralizado via subscribers onde aplicável.

### Autenticação e autorização

- **Token opaco** armazenado em sessão de login (`LoginSession`); o cliente envia o valor no header configurável (`AUTH_TOKEN_HEADER`, por defeito `X-Token-CV`).
- **`RequiresAuthMiddleware`** resolve o utilizador para rotas marcadas com `#[RequiresAuth]`.
- **`TokenAuthenticatedValueResolver`** injeta `User` (e `LoginSession` quando declarados) nos métodos dos controllers.
- Rotas **`api_*`** devem declarar explicitamente `#[RequiresAuth]` ou `#[PublicRoute]` (validação em `ApiRouteAuthDeclarationSubscriber`).
- Operações de **admin** no catálogo verificam `ROLE_ADMIN` no utilizador.

### Integrações externas

- **Mercado Pago**: cliente HTTP em `src/Service/Payment/MercadoPago/` (preferences, consulta de pagamento, verificação opcional de assinatura do webhook). O endpoint de notificação é público: `GET|POST /webhooks/mercadopago`.

### Persistência e migrações

- **MySQL 8** em desenvolvimento e produção (alinhado às migrações em `migrations/`).
- **SQLite** típico em testes automatizados (`APP_ENV=test`, `DATABASE_URL` em `.env.test`).

### Documentação OpenAPI

- **Nelmio ApiDoc Bundle** está presente no projeto; a documentação interativa pode ser exposta conforme configuração em `config/packages/`.

---

## Stack tecnológica

| Componente | Tecnologia |
|------------|------------|
| Linguagem | PHP 8.2+ |
| Framework | Symfony 7.4 |
| ORM / DB | Doctrine ORM 3, MySQL 8 |
| Validação / serialização | Symfony Validator, Serializer |
| HTTP cliente | Symfony HttpClient (Mercado Pago) |
| CORS | Nelmio CORS Bundle |
| PDF | Dompdf |
| Testes | PHPUnit 11 |

---

## Estrutura do repositório (resumo)

```
config/           # serviços, bundles, segurança, rotas
migrations/       # schema versionado
public/           # front controller
src/
  ArgumentResolver/
  Attribute/      # RequiresAuth, PublicRoute
  Controller/
  DTO/
  Entity/
  Enum/
  EventSubscriber/
  Exception/
  Mapper/
  Middleware/
  Repository/
  Service/        # domínio + Payment/MercadoPago
tests/            # testes funcionais e unitários
```

---

## Requisitos

- PHP **8.2+** (`ctype`, `iconv`, PDO MySQL)
- **Composer**
- **MySQL 8** (ou compatível com as migrações)

---

## Instalação

```bash
composer install
cp .env .env.local   # opcional: DATABASE_URL, secrets locais
```

Variáveis principais (`.env` ou `.env.local`):

| Variável | Função |
|----------|--------|
| `DATABASE_URL` | MySQL |
| `APP_SECRET` | Segredo Symfony |
| `AUTH_TOKEN_HEADER`, `AUTH_TOKEN_SALT`, `AUTH_TOKEN_TTL_SECONDS` | Sessão por token |
| `DEFAULT_URI` | URL base (links, `notification_url` Mercado Pago) |
| `CORS_ALLOW_ORIGIN` | Regex de origens CORS |
| `TEMPLATE_UNLOCK_ALLOW_FREE` | `0` em produção (premium só com pagamento) |
| `MERCADOPAGO_ACCESS_TOKEN`, `MERCADOPAGO_WEBHOOK_SECRET`, `MERCADOPAGO_PREMIUM_FALLBACK_AMOUNT` | Checkout e webhook |

Aplicar migrações:

```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

Servidor local:

```bash
symfony server:start
# ou
php -S 127.0.0.1:8000 -t public
```

---

## Uso da API

### Autenticação

1. `POST /api/users` — registo (público).
2. `POST /api/auth/login` — obtém `data.token`.
3. Pedidos autenticados: header `AUTH_TOKEN_HEADER: <token>` (ex.: `X-Token-CV`).

Rotas públicas incluem login, registo, catálogo de templates, portfólio público e webhook Mercado Pago.

### Mapa de rotas (resumo)

Lista completa: `php bin/console debug:router`.

| Área | Rotas / notas |
|------|----------------|
| Utilizadores | `POST /api/users`, `GET` / `PATCH /api/users/{id}`, ativação |
| Auth | `POST /api/auth/login`, `POST /api/auth/logout` |
| Sessão | `GET /api/me` |
| Perfil e conteúdo | `/api/profile`, `/api/experiences`, `/api/educations`, `/api/skills`, `/api/projects`, `/api/certifications` |
| Currículos | `/api/resumes`, `/api/resumes/{publicId}/sections`, `.../sections/suggestions`, `.../pdf` |
| IA | `/api/resumes/{publicId}/ai/...` |
| Portfólio | `/api/portfolio-sites`, secções por site |
| Público | `GET /api/public/portfolio/{slug}` |
| Templates | `GET /api/templates`, `GET /api/me/templates`, `POST /api/me/template-unlocks` |
| Admin | `POST` / `PATCH /api/admin/catalog/templates` (`ROLE_ADMIN`) |
| Pagamentos | `POST /api/me/payments/mercadopago/template-checkout` |
| Webhook | `GET` ou `POST /webhooks/mercadopago` |

---

## Testes

```bash
php vendor/bin/phpunit
```

O ambiente `test` usa SQLite; configure `.env.test` localmente (o ficheiro pode estar no `.gitignore` — replicar variáveis necessárias para CI ou documentação interna).

---

## Segurança (produção)

- `APP_ENV=prod`, `APP_DEBUG=0`, `APP_SECRET` forte.
- `TEMPLATE_UNLOCK_ALLOW_FREE=0`.
- Tokens Mercado Pago e `MERCADOPAGO_WEBHOOK_SECRET` definidos; webhook apenas sobre HTTPS.
- Revisar `CORS_ALLOW_ORIGIN` para o domínio real do frontend.

---

## Licença

Proprietário — uso conforme acordo do projeto.
