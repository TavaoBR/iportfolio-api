# Guia 01 - Modulo User

Este arquivo agora funciona apenas como indice do modulo User.

O guia detalhado foi separado por camada dentro de:

```md
guia/user/
```

## Ordem oficial

Leia e implemente nesta sequencia:

```md
1. guia/user/00-indice.md
2. guia/user/01-visao-geral-e-decisoes.md
3. guia/user/02-banco-entity-migration.md
4. guia/user/03-dtos.md
5. guia/user/04-validators.md
6. guia/user/05-mapper.md
7. guia/user/06-repository.md
8. guia/user/09-avatar-base64.md
9. guia/user/11-exceptions-e-response.md
10. guia/user/07-service.md
11. guia/user/08-controller.md
12. guia/user/10-testes-e-validacao.md
```

## Decisoes corrigidas

Avatar:

```md
- Nao sera URL externa.
- A API recebe avatar em base64.
- O backend salva como arquivo.
- O banco salva apenas o caminho do arquivo.
- CreateUserDTO e UpdateUserDTO aceitam avatar opcional.
```

Response:

```php
return [
    'status' => 201,
    'message' => 'Conta criada com sucesso',
    'data' => $data,
];
```

Controller:

```md
Controller apenas recebe request, chama Service e transforma o retorno em JsonResponse.
```

Service:

```md
Service concentra regra de negocio e retorna status/message/data/errors.
```

## Padrao para proximos modulos

Todo modulo deve ser documentado em varios arquivos dentro de uma pasta propria.

Exemplo:

```md
guia/resume/00-indice.md
guia/resume/01-visao-geral-e-decisoes.md
guia/resume/02-banco-entity-migration.md
guia/resume/03-dtos.md
guia/resume/04-validators.md
guia/resume/05-mapper.md
guia/resume/06-repository.md
guia/resume/07-service.md
guia/resume/08-controller.md
guia/resume/09-testes-e-validacao.md
```