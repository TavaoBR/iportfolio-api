# Modulo Profile - Endpoints

## GET /api/profile

Header:

```md
X-Token-CV: {token}
```

Response 200:

```json
{
  "message": "Perfil encontrado com sucesso",
  "data": {
    "id": 1,
    "headline": "Desenvolvedor Backend PHP/Symfony",
    "bio": "Resumo profissional",
    "phone": "+55 11 99999-9999",
    "city": "Sao Paulo",
    "state": "SP",
    "country": "Brasil",
    "linkedin_url": "https://www.linkedin.com/in/gustavo",
    "github_url": "https://github.com/gustavo",
    "website_url": "https://gustavo.dev"
  }
}
```

Sem perfil:

```md
404 Perfil nao encontrado
```

## PUT /api/profile

Cria ou atualiza o perfil do usuario autenticado.

Request:

```json
{
  "headline": "Desenvolvedor Backend PHP/Symfony",
  "bio": "Construo APIs limpas e testaveis.",
  "phone": "+55 11 99999-9999",
  "city": "Sao Paulo",
  "state": "SP",
  "country": "Brasil",
  "linkedin_url": "https://www.linkedin.com/in/gustavo",
  "github_url": "https://github.com/gustavo",
  "website_url": "https://gustavo.dev"
}
```

Criacao:

```md
201 Perfil criado com sucesso
```

Atualizacao:

```md
200 Perfil atualizado com sucesso
```

Erros:

```md
401 Token ausente
401 Token invalido
422 Dados invalidos
```