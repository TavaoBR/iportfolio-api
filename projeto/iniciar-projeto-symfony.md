# Guia para iniciar o backend Smart Portfolio CV

Este guia mostra o passo a passo para criar a base do backend em Symfony para o projeto Smart Portfolio CV.

## 1. Requisitos iniciais

Antes de criar o projeto, instale e confira se estes itens estao disponiveis na maquina:

```bash
php -v
composer -V
symfony -v
mysql --version
git --version
```

Versoes recomendadas:

```md
PHP: 8.2 ou superior
Composer: versao atual
Symfony CLI: versao atual
MySQL: 8 ou superior
Git: versao atual
```

Se o Symfony CLI nao estiver instalado, baixe pelo site oficial:

```md
https://symfony.com/download
```

## 2. Criar o projeto Symfony

Dentro da pasta onde o backend sera criado, execute:

```bash
composer create-project symfony/skeleton .
```

Depois instale os pacotes essenciais para uma API:

```bash
composer require symfony/runtime
composer require symfony/orm-pack
composer require symfony/maker-bundle --dev
composer require symfony/validator
composer require symfony/serializer
composer require symfony/property-access
composer require symfony/property-info
composer require symfony/security-bundle
composer require symfony/mailer
composer require symfony/mime
composer require symfony/monolog-bundle
composer require symfony/http-client
composer require doctrine/doctrine-migrations-bundle
```

## 3. Instalar autenticação JWT

Para autenticação da API, use JWT:

```bash
composer require lexik/jwt-authentication-bundle
```

Gerar as chaves JWT:

```bash
php bin/console lexik:jwt:generate-keypair
```

Depois, confira se o `.env` recebeu as variaveis:

```env
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=
```

## 4. Configurar banco MySQL

No arquivo `.env`, configure a conexao com o banco:

```env
DATABASE_URL="mysql://usuario:senha@127.0.0.1:3306/iportfolio_api?serverVersion=8.0&charset=utf8mb4"
```

Exemplo local:

```env
DATABASE_URL="mysql://root:root@127.0.0.1:3306/iportfolio_api?serverVersion=8.0&charset=utf8mb4"
```

Criar o banco:

```bash
php bin/console doctrine:database:create
```

## 5. Instalar documentação da API

Para documentar endpoints com Swagger/OpenAPI:

```bash
composer require nelmio/api-doc-bundle
```

Depois, a documentacao geralmente fica disponivel em:

```md
/api/doc
```

## 6. Instalar gerador de PDF

Para gerar curriculos em PDF pelo backend, uma opcao simples para inicio e o Dompdf:

```bash
composer require dompdf/dompdf
```

Estrutura sugerida futuramente:

```md
src/Service/PDFGeneratorService.php
src/Infrastructure/PDF/ResumePdfRenderer.php
src/Infrastructure/PDF/ResumeTemplateResolver.php
templates/pdf/resume/
```

## 7. Estrutura inicial recomendada

Crie as pastas principais:

```bash
mkdir src/DTO
mkdir src/Enum
mkdir src/Exception
mkdir src/Factory
mkdir src/Mapper
mkdir src/Service
mkdir src/Validator
mkdir src/Infrastructure
mkdir src/Infrastructure/AI
mkdir src/Infrastructure/PDF
```

A estrutura base esperada:

```md
src/
├── Controller/
├── Entity/
├── Repository/
├── Service/
├── DTO/
├── Enum/
├── Validator/
├── Security/
├── Exception/
├── Factory/
├── Mapper/
└── Infrastructure/
    ├── AI/
    └── PDF/
```

## 8. Criar a entidade User

Use o Maker Bundle:

```bash
php bin/console make:user
```

Sugestao de respostas:

```md
Class name: User
Store users in database: yes
Unique property: email
Security user provider: Doctrine
Password hashed: yes
```

Depois adicione campos extras na entidade:

```md
name
avatar
isActive
createdAt
updatedAt
```

Criar migration:

```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

## 9. Criar autenticação

Criar controller de autenticação:

```bash
php bin/console make:controller AuthController
```

Endpoints iniciais:

```md
POST /api/auth/register
POST /api/auth/login
GET  /api/auth/me
POST /api/auth/logout
```

Servicos sugeridos:

```md
src/Service/AuthService.php
src/DTO/RegisterUserDTO.php
src/DTO/LoginDTO.php
src/Mapper/UserMapper.php
```

## 10. Padronizar respostas JSON

Crie uma classe para respostas da API:

```md
src/Service/ApiResponseService.php
```

Formato de sucesso:

```json
{
  "success": true,
  "message": "Operacao realizada com sucesso.",
  "data": {}
}
```

Formato de erro:

```json
{
  "success": false,
  "message": "Dados invalidos.",
  "errors": []
}
```

## 11. Criar tratamento global de erros

Crie um subscriber/listener para padronizar erros:

```bash
php bin/console make:subscriber ExceptionSubscriber
```

Responsabilidades:

```md
- Converter excecoes em JSON
- Tratar erro de validacao
- Tratar recurso nao encontrado
- Tratar acesso negado
- Evitar expor detalhes internos em producao
```

## 12. Criar entidades principais

Depois da base de autenticação, crie as entidades do dominio:

```bash
php bin/console make:entity UserProfile
php bin/console make:entity Resume
php bin/console make:entity ResumeSection
php bin/console make:entity Experience
php bin/console make:entity Education
php bin/console make:entity Skill
php bin/console make:entity Project
php bin/console make:entity PortfolioSite
php bin/console make:entity PortfolioSection
php bin/console make:entity Template
php bin/console make:entity AIAnalysis
```

Depois gere e rode as migrations:

```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

## 13. Criar enums importantes

Sugestao de enums iniciais:

```md
src/Enum/ResumeLanguageEnum.php
src/Enum/ResumeSectionTypeEnum.php
src/Enum/PortfolioLayoutTypeEnum.php
src/Enum/TemplateTypeEnum.php
src/Enum/AIAnalysisStatusEnum.php
```

Valores importantes:

```md
AIAnalysisStatusEnum:
- pending
- processing
- completed
- failed

TemplateTypeEnum:
- resume
- portfolio
```

## 14. Criar services iniciais

Services recomendados:

```md
src/Service/AuthService.php
src/Service/UserProfileService.php
src/Service/ResumeService.php
src/Service/ResumeSectionService.php
src/Service/ExperienceService.php
src/Service/EducationService.php
src/Service/SkillService.php
src/Service/ProjectService.php
src/Service/PortfolioSiteService.php
src/Service/PortfolioSectionService.php
src/Service/TemplateService.php
src/Service/AIAnalysisService.php
src/Service/PDFGeneratorService.php
```

Regra principal:

```md
Controller recebe request.
DTO representa entrada.
Service executa regra de negocio.
Repository consulta o banco.
Mapper monta resposta.
```

## 15. Criar camada de IA

Estrutura sugerida:

```md
src/Infrastructure/AI/AIProviderInterface.php
src/Infrastructure/AI/OpenAIProvider.php
src/Infrastructure/AI/AIAnalysisPromptBuilder.php
src/Infrastructure/AI/AIAnalysisResponseParser.php
```

Instale o cliente HTTP, se ainda nao tiver instalado:

```bash
composer require symfony/http-client
```

No `.env`, reserve uma variavel para a chave da IA:

```env
AI_PROVIDER=openai
OPENAI_API_KEY=
```

## 16. Configurar CORS

Como o frontend sera Vue no futuro, configure CORS:

```bash
composer require nelmio/cors-bundle
```

Depois ajuste o arquivo:

```md
config/packages/nelmio_cors.yaml
```

Exemplo inicial:

```yaml
nelmio_cors:
  defaults:
    allow_origin: ['http://localhost:5173']
    allow_methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS']
    allow_headers: ['Content-Type', 'Authorization']
    expose_headers: ['Link']
    max_age: 3600
  paths:
    '^/api/':
      origin_regex: false
      allow_origin: ['http://localhost:5173']
      allow_methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS']
      allow_headers: ['Content-Type', 'Authorization']
```

## 17. Rodar o projeto localmente

Iniciar servidor Symfony:

```bash
symfony server:start
```

Ou usando PHP diretamente:

```bash
php -S 127.0.0.1:8000 -t public
```

Testar no navegador:

```md
http://127.0.0.1:8000
```

## 18. Comandos uteis

Validar container:

```bash
php bin/console lint:container
```

Listar rotas:

```bash
php bin/console debug:router
```

Limpar cache:

```bash
php bin/console cache:clear
```

Gerar migration:

```bash
php bin/console make:migration
```

Rodar migrations:

```bash
php bin/console doctrine:migrations:migrate
```

Verificar schema:

```bash
php bin/console doctrine:schema:validate
```

## 19. Ordem recomendada de desenvolvimento

Fase 1:

```md
1. Criar projeto Symfony
2. Instalar pacotes essenciais
3. Configurar banco MySQL
4. Criar User
5. Criar Auth com JWT
6. Criar resposta JSON padronizada
7. Criar tratamento global de erros
```

Fase 2:

```md
1. Criar UserProfile
2. Criar Resume
3. Criar ResumeSection
4. Criar Experience
5. Criar Education
6. Criar Skill
7. Criar CRUDs principais
```

Fase 3:

```md
1. Criar Project
2. Criar PortfolioSite
3. Criar PortfolioSection
4. Criar endpoint publico por slug
```

Fase 4:

```md
1. Criar AIProviderInterface
2. Criar provider de IA
3. Criar analise ATS
4. Criar comparacao com vaga
5. Salvar historico da analise
```

Fase 5:

```md
1. Criar templates HTML de curriculo
2. Criar renderizador PDF
3. Gerar PDF por Resume
4. Retornar arquivo para download
```

## 20. Checklist final da base

Antes de avancar para os CRUDs, confirme:

```md
- Projeto Symfony roda localmente
- Banco MySQL conecta corretamente
- Migrations executam sem erro
- User existe no banco
- Registro funciona
- Login retorna JWT
- Endpoint /api/auth/me funciona autenticado
- Respostas JSON seguem o padrao do projeto
- Erros sao retornados em JSON
- Rotas estao sob /api
- CORS esta preparado para o frontend futuro
```

