# Modulo Auth - TDD

## Ciclos implementados

```md
1. POST /api/auth/login com credenciais validas retorna token e user.
2. POST /api/auth/login com senha invalida retorna 401.
3. GET /api/me com token valido retorna usuario pelo metadata.
4. GET /api/me sem token retorna 401 Token ausente.
5. GET /api/me com token invalido retorna 401 Token invalido.
6. POST /api/auth/logout invalida o token atual.
```

## Regra para proximas fatias

Toda rota autenticada nova deve receber o usuario pelo contexto/token, nao por `{id}` na URL.

Fluxo:

```md
Request -> X-Token-CV -> AuthenticatedUserService -> UserService do modulo especifico
```