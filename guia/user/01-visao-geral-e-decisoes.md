# Modulo User - Visao Geral e Decisoes

## Objetivo

O modulo User representa a conta do usuario na plataforma.

Ele deve cuidar de:

```md
- Dados basicos da conta
- Email unico
- Senha com hash seguro
- Usuario ativo/inativo
- Avatar recebido em base64 e salvo como arquivo
- Retorno seguro sem password
```

Ele nao deve cuidar de:

```md
- Login
- Logout
- Token proprio
- UserProfile profissional
- Curriculo
- Portfolio
```

## Decisao sobre avatar

O avatar nao sera tratado como URL externa.

Padrao oficial:

```md
- A API recebe avatar em base64.
- O backend valida o base64.
- O backend salva o arquivo fisico em uma pasta controlada.
- O banco salva apenas o caminho relativo ou nome do arquivo.
- A response retorna o caminho publico/relativo do avatar.
```

Motivo:

```md
- Evita depender de hospedagem externa.
- Facilita controlar tamanho, extensao e tipo MIME.
- Permite trocar storage depois sem mudar o contrato principal.
```

## CreateUserDTO deve ter avatar?

Sim, mas opcional.

Motivo:

```md
O usuario pode criar conta sem avatar.
Se enviar avatar no cadastro, o backend ja processa e salva.
```

## Padrao de camadas

```md
Controller -> DTO -> Service -> Repository -> Entity
                    -> Mapper
                    -> Validator
```

## Padrao de resposta

Service retorna:

```php
return [
    'status' => 201,
    'message' => 'Conta criada com sucesso',
    'data' => $data,
];
```

Erro conhecido:

```php
return [
    'status' => 409,
    'message' => 'Usuario ja cadastrado',
];
```

Erro inesperado:

```php
return [
    'status' => 500,
    'message' => 'Ocorreu algum erro inesperado',
    'errors' => $e->getMessage(),
];
```

Controller transforma em JSON:

```json
{  "message": "Usuario ja cadastrado",
  "errors": []
}
```

## Exceptions esperadas

```md
UserAlreadyExistsException -> 409
UserNotFoundException -> 404
InvalidAvatarException -> 422
AvatarUploadException -> 500
```

