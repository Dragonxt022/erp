# Documentação da API de Integração de Contas a Pagar

Este documento detalha a implementação e o uso da API externa desenvolvida para permitir a criação de registros na tabela `contas_a_pagares` por aplicações terceiras, utilizando autenticação JWT via SSO.

## 1. Visão Geral

A API permite que sistemas externos enviem um payload JSON contendo os dados de uma conta a pagar. O sistema valida o token de autenticação do usuário junto ao serviço de SSO (`login.taiksu.com.br`) e, se válido, cria o registro no banco de dados.

## 2. Detalhes da Implementação

### Arquivos Criados/Modificados

*   **Controller**: `app/Http/Controllers/Api/ContaAPagarApiController.php`
    *   Responsável por receber a requisição, validar o token JWT, validar os dados de entrada e criar o registro.
*   **Rotas**: `routes/api.php`
    *   Adicionada a rota `POST /contas-a-pagar`.

### Fluxo de Autenticação

1.  A aplicação cliente envia o token JWT no cabeçalho `Authorization: Bearer {token}`.
2.  A API extrai este token.
3.  A API faz uma requisição `GET` para `https://login.taiksu.com.br/api/user/me` repassando o token.
4.  Se o SSO responder com sucesso (200), a API prossegue. Caso contrário, retorna erro 401.

## 3. Manual de Uso da API

### Endpoint

**URL**: `https://admin.taiksu.com.br/api/contas-a-pagar`
**Método**: `POST`

### Cabeçalhos (Headers) Obrigatórios

| Header | Valor | Descrição |
| :--- | :--- | :--- |
| `Content-Type` | `application/json` | Indica que o corpo da requisição é JSON. |
| `Authorization` | `Bearer <SEU_TOKEN_JWT>` | Token de autenticação válido do usuário. |

### Corpo da Requisição (JSON Body)

Todos os campos marcados como **Obrigatório** devem estar presentes.

| Campo | Tipo | Obrigatório? | Descrição | Regras |
| :--- | :--- | :--- | :--- | :--- |
| `nome` | String | Sim | Título da conta a pagar. | Máx. 255 caracteres. |
| `valor` | Decimal | Sim | Valor monetário da conta. | Numérico, >= 0. |
| `emitida_em` | Data | Sim | Data de emissão da conta. | Formato `YYYY-MM-DD`. |
| `vencimento` | Data | Sim | Data de vencimento. | Formato `YYYY-MM-DD`, deve ser >= `emitida_em`. |
| `dias_lembrete` | Inteiro | Sim | Dias antes para lembrar. | Inteiro >= 0. |
| `unidade_id` | Inteiro | Sim | ID da unidade vinculada. | Deve existir na tabela `infor_unidade`. |
| `categoria_id` | Inteiro | Sim | ID da categoria da conta. | Deve existir na tabela `categorias`. |
| `descricao` | String | Não | Descrição detalhada. | Texto livre. |
| `arquivo` | String | Não | Caminho ou nome de arquivo. | String. |
| `status` | String | Não | Status inicial da conta. | `pendente` (padrão), `pago` ou `atrasado`. |

#### Exemplo de JSON Válido

```json
{
  "nome": "Manutenção de Ar Condicionado",
  "valor": 450.00,
  "emitida_em": "2026-01-05",
  "vencimento": "2026-01-20",
  "dias_lembrete": 2,
  "unidade_id": 1,
  "categoria_id": 3,
  "descricao": "Serviço realizado pela empresa XYZ Climatização.",
  "status": "pendente"
}
```

### Respostas

#### Sucesso (201 Created)

Retornado quando a conta é criada com sucesso.

```json
{
  "status": "success",
  "message": "Conta a pagar criada com sucesso.",
  "data": {
    "id": 150,
    "nome": "Manutenção de Ar Condicionado",
    "valor": "450.00",
    "emitida_em": "2026-01-05",
    "vencimento": "2026-01-20",
    "status": "pendente",
    "unidade_id": 1,
    "categoria_id": 3,
    "created_at": "2026-01-05T14:30:00.000000Z"
  }
}
```

#### Erro de Autenticação (401 Unauthorized)

Retornado quando o token não é enviado ou é inválido/expirado conforme o SSO.

```json
{
  "status": "error",
  "message": "Token de autenticação inválido.",
  "error": "Não foi possível validar o token JWT fornecido."
}
```

#### Erro de Validação (422 Unprocessable Entity)

Retornado quando algum dado enviado não atende aos requisitos (ex: campo obrigatório faltando, data inválida).

```json
{
  "status": "error",
  "message": "Dados inválidos.",
  "errors": {
    "vencimento": [
      "A data de vencimento deve ser igual ou posterior à data de emissão."
    ]
  }
}
```

#### Erro Interno (500 Internal Server Error)

Retornado em caso de falha inesperada no servidor ou conexão com banco de dados.

```json
{
  "status": "error",
  "message": "Erro ao criar conta a pagar.",
  "error": "Ocorreu um erro interno ao processar a solicitação."
}
```

## 4. Testes

Um script de teste (bash) foi criado na raiz do projeto (`test-api-contas-pagar.sh`) contendo exemplos de requisições `curl` para validar todos os cenários acima.
