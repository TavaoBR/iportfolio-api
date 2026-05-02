# Contexto do Projeto - Backend Smart Portfolio CV

## 1. Visão Geral

O projeto tem como objetivo criar uma plataforma onde o usuário possa montar seu currículo profissional, gerar CV em PDF, criar um portfólio online e utilizar IA para analisar e melhorar o currículo com foco em ATS.

A proposta inicial é começar pelo backend, construindo uma API sólida, organizada e preparada para evoluir.

## 2. Objetivo Principal

Criar uma API em Symfony que permita:

- Cadastro e autenticação de usuários
- Gestão do perfil profissional
- Criação e edição de currículos
- Organização de seções do currículo
- Geração de CV em PDF
- Análise de currículo com IA
- Sugestões de melhoria para ATS
- Comparação do currículo com descrição de vaga
- Criação de portfólio público
- Escolha de templates
- Organização da exibição das seções do portfólio

## 3. Stack Inicial

```md
Backend: Symfony
Banco de dados: MySQL
Frontend futuro: Vue 3 + TypeScript + Flowbite
IA: API externa para análise e geração de conteúdo
PDF: geração pelo backend
```

## 4. Escopo Inicial do Backend

Nesta primeira fase, o foco será estruturar o backend com boa separação de responsabilidades.

### Módulos iniciais

```md
1. Auth
2. User
3. UserProfile
4. Resume
5. ResumeSection
6. Experience
7. Education
8. Skill
9. Project
10. PortfolioSite
11. PortfolioSection
12. Template
13. AIAnalysis
14. PDFGenerator
```

## 5. Padrão de Projeto

O projeto deve seguir uma arquitetura em camadas, evitando concentrar regra de negócio em Controllers ou Entities.

### Estrutura recomendada

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
```

## 6. Separação de Responsabilidades

### Controller

O Controller deve ser responsável apenas por receber a requisição, validar entrada básica, chamar Services e retornar resposta HTTP.

Não deve conter regra de negócio complexa.

Exemplo de responsabilidade:

```md
- Receber request
- Ler DTO
- Chamar service
- Retornar JSON
```

### Service

Os Services devem concentrar as regras de negócio da aplicação.

Exemplo:

```md
- Criar currículo
- Atualizar perfil profissional
- Reordenar seções
- Calcular score ATS
- Gerar versão otimizada do CV
- Publicar portfólio
```

### Entity

As Entities representam as tabelas do banco e o estado dos dados.

Devem evitar regras grandes e integrações externas.

Podem ter regras simples do próprio domínio, como:

```md
- Ativar/desativar seção
- Atualizar slug
- Marcar currículo como principal
- Alterar template
```

### Repository

Os Repositories devem centralizar consultas ao banco.

Devem evitar regra de negócio e focar em buscar, salvar e consultar dados.

Exemplo:

```md
- findByUser
- findPublicBySlug
- findMainResumeByUser
- findVisibleSections
- findByResumeOrdered
```

### DTO

DTOs devem ser usados para entrada e saída de dados da API.

Exemplo:

```md
CreateResumeDTO
UpdateResumeDTO
CreateExperienceDTO
UpdatePortfolioSectionDTO
AIAnalysisRequestDTO
AIAnalysisResponseDTO
```

### Mapper

Mappers podem ser usados para transformar DTO em Entity e Entity em Response.

Exemplo:

```md
ResumeMapper
UserProfileMapper
PortfolioMapper
```

### Factory

Factories podem ser usadas para criar objetos mais complexos.

Exemplo:

```md
ResumeFactory
PortfolioSiteFactory
AIAnalysisFactory
```

## 7. Design Patterns Recomendados

### Service Layer Pattern

Usado para manter regras de negócio fora dos Controllers.

Exemplo:

```md
ResumeService
PortfolioService
AIAnalysisService
PDFGeneratorService
```

### Repository Pattern

Usado para isolar o acesso ao banco de dados.

Exemplo:

```md
ResumeRepository
ProjectRepository
PortfolioSiteRepository
```

### DTO Pattern

Usado para evitar expor diretamente as Entities na API.

Ajuda a manter segurança, padronização e controle do contrato da API.

### Factory Pattern

Usado para centralizar criação de objetos quando existe muita regra inicial.

Exemplo:

```md
Criar um Resume já com seções padrão:
- Dados pessoais
- Resumo profissional
- Experiências
- Formação
- Skills
- Projetos
```

### Strategy Pattern

Recomendado para lidar com diferentes tipos de templates, layouts e análises.

Exemplo:

```md
PDFTemplateStrategy
PortfolioTemplateStrategy
ATSAnalysisStrategy
```

### Adapter Pattern

Recomendado para integração com APIs externas de IA.

A aplicação não deve depender diretamente da API escolhida.

Exemplo:

```md
AIProviderInterface
OpenAIProvider
GeminiProvider
ClaudeProvider
```

Assim, se no futuro trocar o fornecedor de IA, a regra principal não precisa ser reescrita.

### Command Pattern

Pode ser usado futuramente para tarefas pesadas.

Exemplo:

```md
GenerateResumePdfCommand
AnalyzeResumeWithAICommand
OptimizeResumeForJobCommand
```

## 8. Boas Práticas de Programação

### Controllers pequenos

Evitar Controllers grandes com regra de negócio.

Errado:

```md
Controller criando currículo, validando regra, salvando entidades e chamando IA diretamente.
```

Certo:

```md
Controller chama ResumeService e retorna resposta.
```

### Entities não devem virar classes gigantes

Evitar colocar toda a regra de currículo, IA, PDF e portfólio dentro das Entities.

### Não retornar Entity diretamente

Sempre preferir Responses ou DTOs.

Isso evita expor campos sensíveis e melhora o contrato da API.

### Validar dados de entrada

Usar validações com Symfony Validator.

Exemplo:

```md
- NotBlank
- Email
- Length
- Url
- Choice
```

### Padronizar respostas da API

Exemplo de resposta de sucesso:

```json
{
  "success": true,
  "message": "Currículo criado com sucesso.",
  "data": {}
}
```

Exemplo de erro:

```json
{
  "success": false,
  "message": "Dados inválidos.",
  "errors": []
}
```

### Usar Enums

Evitar strings soltas no código.

Exemplo:

```md
ResumeLanguageEnum
ResumeSectionTypeEnum
PortfolioLayoutTypeEnum
TemplateTypeEnum
AIAnalysisStatusEnum
```

### Evitar N+1

Cuidado ao listar currículos, seções, experiências, skills e projetos.

Usar queries otimizadas quando necessário.

Exemplo:

```md
- JOIN FETCH
- paginação
- filtros específicos
- evitar carregar relações desnecessárias
```

### Evitar over-fetching

Não buscar dados que a tela não precisa.

Exemplo:

```md
Na listagem de currículos, não precisa retornar todas as experiências e seções completas.
```

### Usar paginação

Principalmente em:

```md
- projetos
- currículos
- análises de IA
- histórico de versões
```

### Logs estruturados

Registrar eventos importantes:

```md
- criação de usuário
- login
- geração de PDF
- análise ATS
- erro de integração com IA
- publicação de portfólio
```

### Segurança

Boas práticas iniciais:

```md
- Senha com hash seguro
- JWT ou sessão segura
- Rate limit em login
- Validação de dono do recurso
- Não permitir acessar currículo de outro usuário
- Slug público apenas para portfólio publicado
- Sanitizar HTML/textos antes de exibir
```

## 9. Entidades Iniciais

## User

Representa o usuário da plataforma.

Campos sugeridos:

```md
id
name
email
password
avatar
is_active
created_at
updated_at
```

Relacionamentos:

```md
User 1:1 UserProfile
User 1:N Resume
User 1:N Project
User 1:N PortfolioSite
```

## UserProfile

Representa informações profissionais do usuário.

Campos sugeridos:

```md
id
user_id
headline
bio
phone
city
state
country
linkedin_url
github_url
website_url
created_at
updated_at
```

## Resume

Representa um currículo criado pelo usuário.

Campos sugeridos:

```md
id
user_id
title
target_role
language
template_key
ats_score
is_main
created_at
updated_at
```

Relacionamentos:

```md
Resume 1:N ResumeSection
Resume 1:N Experience
Resume 1:N Education
Resume 1:N Skill
```

## ResumeSection

Representa seções configuráveis do currículo.

Campos sugeridos:

```md
id
resume_id
section_type
title
content
position
is_visible
created_at
updated_at
```

Tipos de seção:

```md
personal_info
professional_summary
experiences
educations
skills
languages
certifications
projects
links
custom
```

## Experience

Representa experiências profissionais.

Campos sugeridos:

```md
id
resume_id
company
role
start_date
end_date
is_current
description
created_at
updated_at
```

## Education

Representa formação acadêmica.

Campos sugeridos:

```md
id
resume_id
institution
course
degree
start_date
end_date
description
created_at
updated_at
```

## Skill

Representa habilidades técnicas ou comportamentais.

Campos sugeridos:

```md
id
resume_id
name
category
level
created_at
updated_at
```

Categorias possíveis:

```md
technical
soft_skill
language
tool
framework
```

## Project

Representa projetos do usuário para currículo e portfólio.

Campos sugeridos:

```md
id
user_id
title
description
technologies
repository_url
demo_url
image
is_visible
created_at
updated_at
```

## PortfolioSite

Representa o portfólio público do usuário.

Campos sugeridos:

```md
id
user_id
slug
template_key
title
subtitle
is_public
created_at
updated_at
```

## PortfolioSection

Representa as seções configuráveis do portfólio.

Campos sugeridos:

```md
id
portfolio_site_id
section_type
layout_type
position
is_visible
settings_json
created_at
updated_at
```

Exemplos de layout_type:

```md
grid
list
cards
carousel
timeline
tags
progress_bar
simple
```

## Template

Representa templates disponíveis para CV e portfólio.

Campos sugeridos:

```md
id
name
key
type
preview_image
is_premium
is_active
created_at
updated_at
```

Tipos:

```md
resume
portfolio
```

## AIAnalysis

Representa uma análise feita pela IA.

Campos sugeridos:

```md
id
resume_id
user_id
job_description
score_ats
status
problems_json
suggestions_json
keywords_missing_json
strengths_json
weaknesses_json
created_at
updated_at
```

## 10. Modelagem Inicial do Banco

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL
);

CREATE TABLE user_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    headline VARCHAR(180) NULL,
    bio TEXT NULL,
    phone VARCHAR(30) NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(100) NULL,
    country VARCHAR(100) NULL,
    linkedin_url VARCHAR(255) NULL,
    github_url VARCHAR(255) NULL,
    website_url VARCHAR(255) NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    CONSTRAINT fk_user_profiles_user FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE resumes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    target_role VARCHAR(150) NULL,
    language VARCHAR(20) NOT NULL DEFAULT 'pt_BR',
    template_key VARCHAR(100) NULL,
    ats_score INT NULL,
    is_main TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    CONSTRAINT fk_resumes_user FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE resume_sections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resume_id INT NOT NULL,
    section_type VARCHAR(80) NOT NULL,
    title VARCHAR(150) NULL,
    content LONGTEXT NULL,
    position INT NOT NULL DEFAULT 0,
    is_visible TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    CONSTRAINT fk_resume_sections_resume FOREIGN KEY (resume_id) REFERENCES resumes(id)
);

CREATE TABLE experiences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resume_id INT NOT NULL,
    company VARCHAR(150) NOT NULL,
    role VARCHAR(150) NOT NULL,
    start_date DATE NULL,
    end_date DATE NULL,
    is_current TINYINT(1) NOT NULL DEFAULT 0,
    description TEXT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    CONSTRAINT fk_experiences_resume FOREIGN KEY (resume_id) REFERENCES resumes(id)
);

CREATE TABLE educations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resume_id INT NOT NULL,
    institution VARCHAR(180) NOT NULL,
    course VARCHAR(180) NULL,
    degree VARCHAR(100) NULL,
    start_date DATE NULL,
    end_date DATE NULL,
    description TEXT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    CONSTRAINT fk_educations_resume FOREIGN KEY (resume_id) REFERENCES resumes(id)
);

CREATE TABLE skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resume_id INT NOT NULL,
    name VARCHAR(120) NOT NULL,
    category VARCHAR(80) NULL,
    level VARCHAR(50) NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    CONSTRAINT fk_skills_resume FOREIGN KEY (resume_id) REFERENCES resumes(id)
);

CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(180) NOT NULL,
    description TEXT NULL,
    technologies JSON NULL,
    repository_url VARCHAR(255) NULL,
    demo_url VARCHAR(255) NULL,
    image VARCHAR(255) NULL,
    is_visible TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    CONSTRAINT fk_projects_user FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE portfolio_sites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    slug VARCHAR(150) NOT NULL UNIQUE,
    template_key VARCHAR(100) NULL,
    title VARCHAR(180) NOT NULL,
    subtitle VARCHAR(255) NULL,
    is_public TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    CONSTRAINT fk_portfolio_sites_user FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE portfolio_sections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    portfolio_site_id INT NOT NULL,
    section_type VARCHAR(80) NOT NULL,
    layout_type VARCHAR(80) NOT NULL,
    position INT NOT NULL DEFAULT 0,
    is_visible TINYINT(1) NOT NULL DEFAULT 1,
    settings_json JSON NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    CONSTRAINT fk_portfolio_sections_site FOREIGN KEY (portfolio_site_id) REFERENCES portfolio_sites(id)
);

CREATE TABLE templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    template_key VARCHAR(100) NOT NULL UNIQUE,
    type VARCHAR(50) NOT NULL,
    preview_image VARCHAR(255) NULL,
    is_premium TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL
);

CREATE TABLE ai_analyses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resume_id INT NOT NULL,
    user_id INT NOT NULL,
    job_description LONGTEXT NULL,
    score_ats INT NULL,
    status VARCHAR(50) NOT NULL,
    problems_json JSON NULL,
    suggestions_json JSON NULL,
    keywords_missing_json JSON NULL,
    strengths_json JSON NULL,
    weaknesses_json JSON NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    CONSTRAINT fk_ai_analyses_resume FOREIGN KEY (resume_id) REFERENCES resumes(id),
    CONSTRAINT fk_ai_analyses_user FOREIGN KEY (user_id) REFERENCES users(id)
);
```

## 11. Índices Recomendados

```sql
CREATE INDEX idx_resumes_user_id ON resumes(user_id);
CREATE INDEX idx_resume_sections_resume_id ON resume_sections(resume_id);
CREATE INDEX idx_resume_sections_position ON resume_sections(position);
CREATE INDEX idx_experiences_resume_id ON experiences(resume_id);
CREATE INDEX idx_educations_resume_id ON educations(resume_id);
CREATE INDEX idx_skills_resume_id ON skills(resume_id);
CREATE INDEX idx_projects_user_id ON projects(user_id);
CREATE INDEX idx_portfolio_sites_user_id ON portfolio_sites(user_id);
CREATE INDEX idx_portfolio_sites_slug ON portfolio_sites(slug);
CREATE INDEX idx_portfolio_sections_site_id ON portfolio_sections(portfolio_site_id);
CREATE INDEX idx_ai_analyses_resume_id ON ai_analyses(resume_id);
CREATE INDEX idx_ai_analyses_user_id ON ai_analyses(user_id);
```

## 12. Fluxo de Criação de Currículo

```md
1. Usuário cria conta
2. Backend cria User
3. Usuário preenche UserProfile
4. Usuário cria Resume
5. Sistema cria seções padrão do Resume
6. Usuário adiciona experiências, formação, skills e projetos
7. Usuário escolhe template
8. Sistema gera PDF
9. Usuário solicita análise ATS
10. IA retorna score, problemas e sugestões
```

## 13. Fluxo de Portfólio

```md
1. Usuário cria PortfolioSite
2. Define slug público
3. Escolhe template
4. Configura seções
5. Define layout de cada seção
6. Ativa ou desativa seções
7. Publica portfólio
8. Visitante acessa página pública pelo slug
```

## 14. Integração com IA

A integração com IA deve ficar isolada em uma camada própria.

Estrutura sugerida:

```md
src/
└── Infrastructure/
    └── AI/
        ├── AIProviderInterface.php
        ├── OpenAIProvider.php
        ├── AIAnalysisPromptBuilder.php
        └── AIAnalysisResponseParser.php
```

### Exemplo de responsabilidades

```md
AIProviderInterface:
- Define contrato para enviar prompts e receber resposta

OpenAIProvider:
- Implementa chamada real para API externa

AIAnalysisPromptBuilder:
- Monta o prompt com base no currículo e na vaga

AIAnalysisResponseParser:
- Valida e converte resposta da IA em estrutura interna
```

## 15. Exemplo de Retorno da Análise ATS

```json
{
  "score_ats": 91,
  "nivel": "excelente",
  "problemas": [
    "Resumo profissional pode ser mais direto",
    "Faltam métricas em algumas experiências"
  ],
  "sugestoes": [
    "Adicionar palavras-chave da vaga",
    "Melhorar descrições com resultados mensuráveis",
    "Destacar tecnologias principais"
  ],
  "keywords_ausentes": [
    "Symfony",
    "Vue.js",
    "MySQL",
    "API REST"
  ]
}
```

## 16. Geração de PDF

A geração de PDF deve ficar isolada em um serviço próprio.

Exemplo:

```md
PDFGeneratorService
ResumePdfRenderer
ResumeTemplateResolver
```

Responsabilidades:

```md
- Buscar dados do currículo
- Aplicar template escolhido
- Renderizar HTML
- Converter HTML para PDF
- Retornar arquivo para download
```

## 17. Endpoints Iniciais

### Auth

```md
POST /api/auth/register
POST /api/auth/login
GET  /api/auth/me
POST /api/auth/logout
```

### Profile

```md
GET  /api/profile
PUT  /api/profile
```

### Resume

```md
GET    /api/resumes
POST   /api/resumes
GET    /api/resumes/{id}
PUT    /api/resumes/{id}
DELETE /api/resumes/{id}
```

### Resume Sections

```md
GET  /api/resumes/{id}/sections
PUT  /api/resumes/{id}/sections/{sectionId}
POST /api/resumes/{id}/sections/reorder
```

### Experiences

```md
POST   /api/resumes/{id}/experiences
PUT    /api/experiences/{id}
DELETE /api/experiences/{id}
```

### Skills

```md
POST   /api/resumes/{id}/skills
PUT    /api/skills/{id}
DELETE /api/skills/{id}
```

### Projects

```md
GET    /api/projects
POST   /api/projects
PUT    /api/projects/{id}
DELETE /api/projects/{id}
```

### Portfolio

```md
GET    /api/portfolio-sites
POST   /api/portfolio-sites
GET    /api/portfolio-sites/{id}
PUT    /api/portfolio-sites/{id}
DELETE /api/portfolio-sites/{id}
POST   /api/portfolio-sites/{id}/publish
```

### Public Portfolio

```md
GET /api/public/portfolio/{slug}
```

### AI

```md
POST /api/resumes/{id}/ai/analyze
POST /api/resumes/{id}/ai/optimize
POST /api/resumes/{id}/ai/compare-job
```

### PDF

```md
GET /api/resumes/{id}/pdf
```

## 18. Regras Importantes

```md
- Usuário só pode acessar dados dele
- Portfólio público só aparece se is_public = true
- Slug do portfólio deve ser único
- Currículo pode ter várias versões
- Apenas um currículo pode ser marcado como principal por usuário
- Templates inativos não podem ser usados
- Score ATS deve ser salvo no histórico
- Análise com IA deve ter status: pending, processing, completed, failed
```

## 19. Possíveis Status de AIAnalysis

```md
pending
processing
completed
failed
```

## 20. Possíveis Templates de CV

```md
classic
modern
minimalist
executive
technology
creative
```

## 21. Possíveis Templates de Portfólio

```md
dev_minimal
corporate
designer
freelancer
dark_mode
one_page
```

## 22. Cuidados Técnicos

### Performance

```md
- Usar paginação
- Evitar N+1
- Criar índices nas FKs
- Evitar carregar relações completas em listagens
- Usar DTOs específicos para listagem e detalhes
```

### Segurança

```md
- Validar ownership dos recursos
- Não retornar password
- Sanitizar textos públicos
- Limitar tamanho dos textos enviados para IA
- Rate limit em endpoints de IA
- Rate limit em login
```

### Custos com IA

```md
- Salvar histórico das análises
- Evitar chamar IA sem necessidade
- Criar limite por plano futuramente
- Cachear resultado quando currículo e vaga forem iguais
```

### Manutenção

```md
- Services pequenos e específicos
- Evitar duplicação de regra
- Criar testes para regras principais
- Padronizar nomes de métodos
- Padronizar retornos da API
```

## 23. Estrutura de Services Inicial

```md
AuthService
UserProfileService
ResumeService
ResumeSectionService
ExperienceService
EducationService
SkillService
ProjectService
PortfolioSiteService
PortfolioSectionService
TemplateService
AIAnalysisService
PDFGeneratorService
```

## 24. Estrutura de Repositories Inicial

```md
UserRepository
UserProfileRepository
ResumeRepository
ResumeSectionRepository
ExperienceRepository
EducationRepository
SkillRepository
ProjectRepository
PortfolioSiteRepository
PortfolioSectionRepository
TemplateRepository
AIAnalysisRepository
```

## 25. Próximo Passo Recomendado

A primeira entrega do backend pode ser dividida assim:

### Fase 1 - Base da API

```md
- Criar projeto Symfony
- Configurar MySQL
- Configurar Doctrine
- Criar User
- Criar autenticação
- Criar padrão de resposta JSON
- Criar tratamento global de erros
```

### Fase 2 - Currículo

```md
- Criar Resume
- Criar ResumeSection
- Criar Experience
- Criar Education
- Criar Skill
- Criar CRUDs principais
```

### Fase 3 - Portfólio

```md
- Criar Project
- Criar PortfolioSite
- Criar PortfolioSection
- Criar endpoint público por slug
```

### Fase 4 - IA

```md
- Criar AIProviderInterface
- Criar integração com provedor de IA
- Criar análise ATS
- Criar comparação com vaga
- Salvar histórico de análise
```

### Fase 5 - PDF

```md
- Criar templates HTML
- Gerar PDF do currículo
- Retornar download
```

## 26. Diretriz Principal

O backend deve nascer organizado, mas sem excesso de complexidade.

A ideia inicial é usar uma arquitetura em camadas simples, com:

```md
Controller -> DTO -> Service -> Repository -> Entity
```

E integrações externas isoladas em:

```md
Infrastructure
```

Assim o projeto começa simples, mas preparado para crescer com segurança.
