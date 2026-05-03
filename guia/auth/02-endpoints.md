# Modulo Auth - Endpoints

## POST /api/auth/login

Request:

```json
{
  "email": "gustavo@email.com",
  "password": "Senha123"
}
```

Response 200:

```json
{
  "message": "Login realizado com sucesso",
  "data": {
    "token": "token-proprio",
    "session_metadata": {
      "user_id": 1,
      "email": "gustavo@email.com",
      "ip": "127.0.0.1",
      "token_header": "X-Token-CV",
      "issued_at": "2026-05-03T12:00:00-03:00",
      "expires_at": "2026-05-04T12:00:00-03:00"
    },
    "user": {
      "id": 1,
      "name": "Gustavo Oliveira",
      "email": "gustavo@email.com"
    }
  }
}
```

Credenciais invalidas:

```md
401 Email ou senha invalidos
```

Usuario inativo ou conta bloqueada:

```md
403 Usuario inativo
403 Conta bloqueada apos varias tentativas de login invalidas.
```

## GET /api/me

Header:

```md
X-Token-CV: {token}
```

Response 200:

```json
{
  "message": "Usuario autenticado encontrado com sucesso",
  "data": {
    "id": 1,
    "name": "Gustavo Oliveira",
    "email": "gustavo@email.com"
  }
}
```

Erros:

```md
401 Token ausente
401 Token invalido
```

## POST /api/auth/logout

Header:

```md
X-Token-CV: {token}
```

Response 200:

```json
{
  "message": "Logout realizado com sucesso"
}
```

Depois do logout, o token atual deve retornar `401 Token invalido`.