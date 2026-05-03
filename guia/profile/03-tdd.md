# Modulo Profile - TDD

## Ciclos implementados

```md
1. PUT /api/profile cria perfil para usuario autenticado.
2. GET /api/profile retorna perfil do usuario autenticado.
3. GET /api/profile sem perfil retorna 404.
4. PUT /api/profile atualiza perfil existente.
5. PUT /api/profile sem token retorna 401.
6. PUT /api/profile com URL invalida retorna 422.
```

## Regra

Todos os testes exercem a API HTTP e usam `X-Token-CV`.

Nao testar usando user_id na URL porque esse nao e o contrato do projeto.