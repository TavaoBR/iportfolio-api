# Modulo User - Testes e Validacao

## Comandos de validacao

```bash
php bin/console lint:container
php bin/console doctrine:schema:validate
php bin/console debug:router
```

## Testes manuais

Criar usuario sem avatar:

```bash
curl -X POST http://127.0.0.1:8000/api/users ^
  -H "Content-Type: application/json" ^
  -d "{\"name\":\"Gustavo Oliveira\",\"email\":\"gustavo@email.com\",\"password\":\"Senha123\"}"
```

Criar usuario com avatar:

```bash
curl -X POST http://127.0.0.1:8000/api/users ^
  -H "Content-Type: application/json" ^
  -d "{\"name\":\"Gustavo Oliveira\",\"email\":\"gustavo2@email.com\",\"password\":\"Senha123\",\"avatar\":\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUg\"}"
```

Email duplicado deve retornar:

```json
{
  "message": "Usuario ja cadastrado",
  "errors": []
}
```

## Checklist

```md
- User criado pelo Symfony CLI.
- Migration criada pelo Symfony CLI.
- Campo avatar existe como string nullable.
- CreateUserDTO aceita avatar opcional em base64.
- UpdateUserDTO aceita avatar opcional em base64.
- Validator de base64 existe.
- AvatarStorageService salva arquivo.
- Banco salva caminho do avatar.
- Service retorna status/message/data/errors.
- Controller so transforma retorno em JsonResponse.
- Password nao aparece em response.
- Email duplicado retorna 409.
- Avatar invalido retorna 422.
```

## Padrao para proximos modulos

Todo modulo deve ter guias separados por camada.

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

