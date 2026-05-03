# Modulo Auth - Token Proprio

## Decisao

Nao usar JWT.

O token e opaco para o cliente, mas contem metadata assinada para o backend validar integridade.

Formato atual:

```md
base64url(metadata).base64url(signature)
```

Metadata:

```json
{
  "uid": 1,
  "iat": 1777822607,
  "nonce": "valor-aleatorio"
}
```

Assinatura:

```md
HMAC-SHA256(payload, AUTH_TOKEN_SALT)
```

Persistencia:

```md
- O token puro nao e salvo no banco.
- A tabela `login_sessions` salva `token_hash = HMAC-SHA256(token, AUTH_TOKEN_SALT)`.
- A busca autenticada usa o hash do token na tabela `login_sessions`.
```

Variaveis:

```md
AUTH_TOKEN_HEADER='X-Token-CV'
AUTH_TOKEN_SALT='trocar-em-producao'
AUTH_TOKEN_TTL_SECONDS=86400
```

## Motivo

```md
- Mantem o contrato simples.
- Evita JWT padrao conforme decisao do projeto.
- Permite invalidar token no logout limpando o hash salvo.
- O id do usuario vem do metadata/contexto, nao da URL.
```
## Metadata de sessao

Inspirado no projeto Bode, o login tambem gera uma metadata de sessao.

Essa metadata e salva na sessao de login e retornada no login:

```json
{
  "user_id": 1,
  "email": "usuario@email.com",
  "ip": "127.0.0.1",
  "token_header": "X-Token-CV",
  "issued_at": "2026-05-03T12:00:00-03:00",
  "expires_at": "2026-05-04T12:00:00-03:00"
}
```

Regras:

```md
- Nao salvar senha na metadata.
- Nao salvar token puro na metadata.
- Nao criar role/admin neste momento.
- Usar user_id da metadata/contexto para endpoints autenticados.
```
## Tabela de sessoes

A sessao fica em tabela propria, inspirada no projeto Bode:

```md
login_sessions
- id
- user_id
- login_date_time
- expire_date_time
- ip
- token_hash
- session_metadata
- revoked_at
```

Regras:

```md
- Login cria uma nova sessao.
- A duracao da sessao vem de AUTH_TOKEN_TTL_SECONDS.
- Logout preenche revoked_at.
- /api/me so aceita sessao nao expirada e nao revogada.
- users nao deve guardar token ou metadata de sessao.
```