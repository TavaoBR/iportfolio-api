# Modulo User - Indice Oficial

Este diretorio substitui o guia monolitico do modulo de usuario.

O modulo User deve ser documentado e implementado por partes, seguindo esta ordem:

```md
1. 01-visao-geral-e-decisoes.md
2. 02-banco-entity-migration.md
3. 03-dtos.md
4. 04-validators.md
5. 05-mapper.md
6. 06-repository.md
7. 09-avatar-base64.md
8. 11-exceptions-e-response.md
9. 07-service.md
10. 08-controller.md
11. 12-tdd.md
12. 10-testes-e-validacao.md
```

## Ordem correta de implementacao

```md
1. Criar/ajustar Entity com Symfony CLI
2. Gerar migration com Symfony CLI
3. Revisar e rodar migration
4. Criar DTOs
5. Criar Validators
6. Criar Mapper
7. Ajustar Repository
8. Criar AvatarStorageService
9. Criar Exceptions e ApiResponseService
10. Criar UserService
11. Criar UserController
12. Seguir ciclos TDD em 12-tdd.md
13. Validar rotas, container e schema
```

## Decisoes oficiais

```md
- Avatar entra como base64 na API.
- Avatar e salvo como arquivo.
- Banco guarda caminho relativo.
- CreateUserDTO aceita avatar opcional.
- UpdateUserDTO aceita avatar opcional.
- Services retornam status/message/data/errors.
- Controllers apenas transformam retorno em JsonResponse.
```