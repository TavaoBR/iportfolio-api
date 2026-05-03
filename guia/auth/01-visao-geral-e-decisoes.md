# Modulo Auth - Visao Geral e Decisoes

## Objetivo

O modulo Auth autentica o usuario e fornece o contexto do usuario autenticado para endpoints protegidos.

Ele cuida de:

```md
- Login por email e senha
- Geracao de token proprio
- Validacao de token pelo header customizado
- Extracao do userId pelo metadata do token
- Logout invalidando o token atual
```

Ele nao cuida de:

```md
- Roles/admin
- JWT
- Listagem de usuarios
- Autorizacao granular de recursos futuros
```

## Contrato oficial

```md
POST /api/auth/login
POST /api/auth/logout
GET /api/me
```

## Header oficial

```md
X-Token-CV: {token}
```

## Padrao de resposta

Nao usar `success`.

Sucesso:

```json
{
  "message": "Login realizado com sucesso",
  "data": {
    "token": "token-proprio",
    "user": {}
  }
}
```

Erro:

```json
{
  "message": "Token invalido"
}
```
## LoginSession

O projeto usa uma tabela propria para sessoes de login.

Motivo:

```md
- Guardar duracao real da sessao.
- Permitir logout revogando apenas a sessao atual.
- Separar dados permanentes do usuario de dados temporarios de autenticacao.
- Permitir multiplas sessoes por usuario no futuro.
```