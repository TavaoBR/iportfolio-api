# Modulo User - TDD

## Decisao oficial

O modulo User deve ser implementado usando TDD.

Fluxo obrigatorio:

```md
1. RED: escrever um teste para um comportamento especifico
2. Confirmar que o teste falha
3. GREEN: implementar o minimo para passar
4. Confirmar que o teste passa
5. REFACTOR: melhorar o codigo mantendo os testes verdes
6. Repetir para o proximo comportamento
```

## Regra principal

Nao escrever todos os testes primeiro.

Nao implementar todo o modulo antes dos testes.

A abordagem correta e por fatias verticais:

```md
Um comportamento -> um teste -> implementacao minima -> refatoracao
```

## Interface publica a testar

Antes do modulo Auth, os testes podem usar endpoints temporarios com `{id}` para validar comportamento do User:

```md
POST /api/users
GET /api/users/{id}
PATCH /api/users/{id}
PATCH /api/users/{id}/activate
PATCH /api/users/{id}/deactivate
```

Depois do modulo Auth, os novos testes devem validar o contrato autenticado baseado no metadata:

```md
GET /api/me
PATCH /api/me
PATCH /api/me/avatar
PATCH /api/me/deactivate
```

Nao criar teste para `GET /api/users` listando todos os usuarios, porque nao existe role admin por enquanto.

Quando um comportamento for interno e importante, usar teste de Service.

Exemplos:

```md
- AvatarStorageService salva base64 como arquivo
- UserService rejeita email duplicado
- UserService rejeita update vazio
- AuthContext extrai userId do metadata do token proprio
```

## Ordem sugerida dos ciclos

## Ciclo 1 - Criar usuario sem avatar

RED:

```md
POST /api/users com name, email e password validos deve retornar 201.
Response deve conter message e data.
Response nao deve conter password.
```

GREEN:

```md
Criar Entity, Repository, DTO minimo, Service e Controller suficientes para passar.
```

REFACTOR:

```md
Extrair mapper/response se ainda estiverem inline.
```

## Ciclo 2 - Email duplicado

RED:

```md
POST /api/users com email ja cadastrado deve retornar 409.
Response deve conter message = Usuario ja cadastrado.
```

GREEN:

```md
Criar UserAlreadyExistsException e regra no Service.
```

## Ciclo 3 - Payload invalido

RED:

```md
POST /api/users sem name/email/password deve retornar 422.
Response deve conter errors por campo.
```

GREEN:

```md
Aplicar constraints nos DTOs e padronizar erro de validacao.
```

## Ciclo 4 - Criar usuario com avatar base64

RED:

```md
POST /api/users com avatar base64 valido deve retornar 201.
Response deve conter caminho do avatar salvo.
Arquivo deve existir no storage configurado.
```

GREEN:

```md
Criar Base64ImageValidator e AvatarStorageService.
```

## Ciclo 5 - Avatar invalido

RED:

```md
POST /api/users com avatar invalido deve retornar 422.
```

GREEN:

```md
Completar validacao de base64, mime type e tamanho.
```

## Ciclo 6 - Buscar usuario temporariamente por id

RED:

```md
GET /api/users/{id} existente deve retornar 200.
GET /api/users/{id} inexistente deve retornar 404.
```

GREEN:

```md
Criar show no Service e Controller.
```

## Ciclo 7 - Atualizar usuario temporariamente por id

RED:

```md
PATCH /api/users/{id} com name/email/avatar deve retornar 200.
PATCH vazio deve retornar 422.
Email duplicado no update deve retornar 409.
```

GREEN:

```md
Criar UpdateUserDTO e regra EmptyPayloadException.
```

## Ciclo 8 - Ativar/desativar usuario temporariamente por id

RED:

```md
PATCH /api/users/{id}/deactivate deve marcar usuario como inativo.
PATCH /api/users/{id}/activate deve marcar usuario como ativo.
```

GREEN:

```md
Criar metodos activate/deactivate no Service e Entity.
```

## Ciclos apos Auth

Quando o modulo Auth estiver pronto, criar uma nova sequencia TDD para substituir o acesso por id externo:

```md
GET /api/me deve retornar o usuario autenticado pelo metadata.
PATCH /api/me deve atualizar somente o usuario autenticado.
PATCH /api/me/deactivate deve desativar somente o usuario autenticado.
Requisicao sem token proprio valido deve retornar 401.
Token valido para outro usuario nao deve permitir alterar ids por URL, porque a URL nao tera id.
```

## Padrao de teste

Testes devem verificar comportamento observavel.

Evitar:

```md
- Testar metodo privado
- Mockar Repository sem necessidade
- Testar detalhes internos da Entity quando a API ja cobre o comportamento
- Criar testes em massa antes da implementacao
```

Preferir:

```md
- Testes HTTP para fluxos da API
- Testes de Service para regras que nao precisam de HTTP
- Testes de storage para avatar base64
- Assertions no status code e body final
```

## Padrao de response nos testes

Sucesso:

```json
{
  "message": "Conta criada com sucesso",
  "data": {
    "id": 1,
    "name": "Gustavo Oliveira",
    "email": "gustavo@email.com"
  }
}
```

Erro:

```json
{
  "message": "Usuario ja cadastrado"
}
```

Nao usar:

```json
{
  "success": true
}
```

## Checklist por ciclo

```md
- O teste descreve comportamento, nao implementacao.
- O teste falha antes da implementacao.
- A implementacao e minima para passar.
- Todos os testes passam antes de refatorar.
- Refatoracao nao muda comportamento.
- Nenhuma feature especulativa foi adicionada.
```
