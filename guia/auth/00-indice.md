# Modulo Auth - Indice

## Guias

```md
01-visao-geral-e-decisoes.md
02-endpoints.md
03-token-proprio.md
04-tdd.md
```

## Decisao central

Auth nao usa JWT.

O projeto usa token proprio enviado por header customizado, assinado com salt e validado pelo metadata interno.