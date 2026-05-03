# Modulo Profile - Visao Geral e Decisoes

## Objetivo

UserProfile guarda os dados profissionais do usuario autenticado.

Ele nao guarda:

```md
- Email de login
- Senha
- Token
- Sessoes
- Curriculos
- Experiencias
- Educacao
- Certificacoes
```

## Campos

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

## Relacionamento

```md
User 1:1 UserProfile
```

## Decisao de acesso

Endpoints de Profile sempre usam o usuario autenticado pelo token.

Nao criar:

```md
/api/users/{id}/profile
/api/profile/{id}
```

Motivo:

```md
- Evita IDOR.
- Mantem ownership no contexto de autenticacao.
- O frontend nao precisa conhecer/enviar user_id.
```