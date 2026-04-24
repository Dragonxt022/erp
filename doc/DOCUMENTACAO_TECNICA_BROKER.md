# 📡 Taiksu Event Broker — Documentação Técnica

> **Versão:** 1.0  
> **Data:** 13 de Fevereiro de 2026  
> **Domínio:** `events.taiksu.com.br`

---

## Sumário

1. [Visão Geral](#1-visão-geral)
2. [Arquitetura do Sistema](#2-arquitetura-do-sistema)
3. [Stack Tecnológica](#3-stack-tecnológica)
4. [Modelo de Dados](#4-modelo-de-dados)
5. [Fluxo de Comunicação entre Microsserviços](#5-fluxo-de-comunicação-entre-microsserviços)
6. [Autenticação e Autorização](#6-autenticação-e-autorização)
7. [API — Contrato de Publicação de Eventos](#7-api--contrato-de-publicação-de-eventos)
8. [Sistema de Heartbeat (Monitoramento)](#8-sistema-de-heartbeat-monitoramento)
9. [Cron Jobs e Prioridades](#9-cron-jobs-e-prioridades)
10. [Integração com o Ecossistema Taiksu](#10-integração-com-o-ecossistema-taiksu)
11. [Painel Web (Dashboard)](#11-painel-web-dashboard)
12. [Estrutura de Diretórios](#12-estrutura-de-diretórios)

---

## 1. Visão Geral

O **Taiksu Event Broker** é um sistema proprietário de barramento de eventos construído para orquestrar a comunicação entre os microsserviços do ecossistema **Taiksu Office**. Ele implementa o padrão **Publish/Subscribe (Pub/Sub)**, onde:

- **Serviços publicadores** emitem eventos contendo payloads de dados.
- **Serviços consumidores** se inscrevem (listeners) nos eventos de interesse.
- O **Event Broker** registra cada publicação e cria entregas (deliveries) individuais para cada consumidor inscrito.

### Objetivos

| Objetivo | Descrição |
|---|---|
| **Desacoplamento** | Microsserviços não precisam se conhecer diretamente — comunicam-se via eventos |
| **Rastreabilidade** | Toda publicação e entrega é registrada no banco com status e tentativas |
| **Monitoramento** | Heartbeat verifica periodicamente se os serviços estão online |
| **Segurança** | Autenticação via token proprietário (Verona Token) para APIs M2M |

---

## 2. Arquitetura do Sistema

```
┌─────────────────────────────────────────────────────────────────────┐
│                      ECOSSISTEMA TAIKSU OFFICE                      │
│                                                                     │
│  ┌──────────┐   ┌──────────┐   ┌──────────┐   ┌──────────────────┐ │
│  │  Caixa   │   │  Email   │   │  Alertas │   │  Outros serviços │ │
│  │  .taiksu │   │  .taiksu │   │  .taiksu │   │    .taiksu       │ │
│  └────┬─────┘   └────┬─────┘   └────┬─────┘   └────────┬─────────┘ │
│       │              │              │                   │           │
│       │    POST /api/event          │                   │           │
│       │    (Verona Token)           │                   │           │
│       ▼              ▼              ▼                   ▼           │
│  ┌──────────────────────────────────────────────────────────────┐   │
│  │                    🔀 EVENT BROKER                           │   │
│  │                 events.taiksu.com.br                         │   │
│  │                                                              │   │
│  │  ┌─────────────┐  ┌──────────────┐  ┌────────────────────┐  │   │
│  │  │  Registrar  │  │  Publicar    │  │  Criar Deliveries  │  │   │
│  │  │  Serviços   │──│  Eventos     │──│  para Listeners    │  │   │
│  │  └─────────────┘  └──────────────┘  └────────────────────┘  │   │
│  │                                                              │   │
│  │  ┌──────────────────┐  ┌────────────────────────────────┐   │   │
│  │  │  Heartbeat       │  │  Dashboard (Painel Web)        │   │   │
│  │  │  Monitoramento   │  │  SSO via login.taiksu.com.br   │   │   │
│  │  └──────────────────┘  └────────────────────────────────┘   │   │
│  └──────────────────────────────────────────────────────────────┘   │
│                                                                     │
│  ┌─────────────────┐   ┌─────────────────┐                         │
│  │  login.taiksu   │   │  alertas.taiksu  │                        │
│  │  (SSO Central)  │   │  (Notificações)  │                        │
│  └─────────────────┘   └─────────────────┘                         │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 3. Stack Tecnológica

| Componente | Tecnologia |
|---|---|
| **Runtime** | Node.js |
| **Framework** | Express.js 4.x |
| **ORM** | Sequelize 6.x |
| **Banco de Dados** | MySQL (`EventBrokerDB`) |
| **Template Engine** | EJS + express-ejs-layouts |
| **Sessões** | express-session + connect-session-sequelize |
| **Agendamento** | node-cron |
| **CSS** | TailwindCSS 4.x |
| **Process Manager** | PM2 |

---

## 4. Modelo de Dados

### Diagrama de Entidade-Relacionamento

```
┌─────────────────┐       ┌─────────────────────┐       ┌──────────────────────┐
│     Service      │       │       Event          │       │    EventListener     │
├─────────────────┤       ├─────────────────────┤       ├──────────────────────┤
│ id (PK, INT)    │◄──┐   │ id (PK, INT)        │◄──┐   │ id (PK, UUID)        │
│ nome (UNIQUE)   │   │   │ name (UNIQUE)       │   │   │ event_id (FK→Event)  │
│ description     │   ├───│ service_owner (FK)  │   ├───│ consumer_service     │
│ status (BOOL)   │   │   │ description         │   │   │   (FK→Service)       │
│ verona_token    │   │   │ payload_contract    │   │   │ status (BOOL)        │
│   (UUID, UNIQUE)│   │   │   (JSON)            │   │   │ description          │
│ paranoid: true  │   │   │ paranoid: true      │   │   │ paranoid: true       │
└─────────────────┘   │   └─────────────────────┘   │   └──────────────────────┘
                      │                              │
                      │   ┌─────────────────────┐   │   ┌──────────────────────┐
                      │   │  PublishedEvent      │   │   │   EventDelivery      │
                      │   ├─────────────────────┤   │   ├──────────────────────┤
                      │   │ id (PK, UUID)       │   │   │ id (PK, UUID)        │
                      │   │ event_id (FK→Event) │───┘   │ published_event_id   │
                      ├───│ published_by        │       │   (FK→PublishedEvent) │
                      │   │   (FK→Service)      │───────│ consumer_service     │
                      │   │ payload (JSON)      │       │   (FK→Service)       │
                      │   │ user_id (INT)       │       │ status (ENUM)        │
                      │   │ issue_date (DATE)   │       │   pending|success|   │
                      │   │ paranoid: true      │       │   error|retrying     │
                      │   │ ⛔ Imutável         │       │ priority (ENUM)      │
                      │   │   (hook beforeDest.) │       │   low|medium|high|   │
                      │   └─────────────────────┘       │   urgent             │
                      │                                 │ attempts (INT)       │
                      └─────────────────────────────────│ paranoid: true       │
                                                        └──────────────────────┘
```

### Detalhamento dos Modelos

#### `Service` — Registro de Microsserviços

O modelo central que representa cada microsserviço no ecossistema Taiksu. Cada serviço possui um **Verona Token** (UUID único) utilizado para autenticação Machine-to-Machine (M2M).

| Campo | Tipo | Descrição |
|---|---|---|
| `id` | INTEGER (PK, auto-increment) | Identificador único |
| `nome` | STRING (UNIQUE) | Nome do serviço (ex: `caixa`, `email`, `alertas`) |
| `description` | STRING | Descrição do microsserviço |
| `status` | BOOLEAN | Status operacional (`online`, `offline`, `deactivated`) |
| `verona_token` | UUID (UNIQUE) | Token de autenticação para API — **não deve ser usado como FK** |

**Associações:**
- `hasMany → Event` (um serviço publica muitos tipos de evento)
- `hasMany → EventListener` (um serviço pode escutar muitos eventos)

---

#### `Event` — Eventos Matriz (Definições de Tipo)

Representa a **definição/template** de um tipo de evento. Não é um evento publicado, mas o "contrato" do que um evento significa.

| Campo | Tipo | Descrição |
|---|---|---|
| `id` | INTEGER (PK) | Identificador único |
| `name` | STRING (UNIQUE) | Nome canônico do evento (ex: `venda.criada`, `caixa.fechado`) |
| `service_owner` | INTEGER (FK→Service) | Serviço dono/publicador deste tipo de evento |
| `description` | STRING | Descrição do evento |
| `payload_contract` | JSON | Schema/contrato esperado do payload |

**Regra de negócio:** Apenas o serviço dono (`service_owner`) pode publicar instâncias deste evento.

---

#### `EventListener` — Inscrições (Subscriptions)

Registra que um serviço consumidor está **inscrito** para receber determinado tipo de evento.

| Campo | Tipo | Descrição |
|---|---|---|
| `id` | UUID (PK) | Identificador único |
| `event_id` | FK→Event | Evento no qual o serviço está inscrito |
| `consumer_service` | FK→Service | Microserviço consumidor inscrito |
| `status` | BOOLEAN | Se o listener está ativo |
| `description` | STRING | Descrição do listener |

**Cascata:** ON DELETE CASCADE, ON UPDATE CASCADE nas FKs.

---

#### `PublishedEvent` — Eventos Publicados (Instâncias)

Cada registro representa uma **ocorrência real** de um evento publicado. É **imutável** — protegido por hook Sequelize que impede exclusão.

| Campo | Tipo | Descrição |
|---|---|---|
| `id` | UUID (PK) | Identificador único da publicação |
| `event_id` | FK→Event | Referência ao tipo de evento |
| `payload` | JSON | Dados reais enviados pelo publicador |
| `user_id` | INTEGER | ID do usuário que originou a ação |
| `published_by` | FK→Service | Serviço que publicou |
| `issue_date` | DATE | Data/hora da ocorrência |

**Proteção de imutabilidade:**
```javascript
hooks: {
  beforeDestroy: (instance, options) => {
    throw new Error('Eventos publicados não podem ser excluídos');
  }
}
```

---

#### `EventDelivery` — Entregas de Eventos

Rastreia a **entrega individual** de um evento publicado para cada serviço consumidor inscrito. Permite monitorar status, tentativas e prioridade.

| Campo | Tipo | Descrição |
|---|---|---|
| `id` | UUID (PK) | Identificador único da entrega |
| `published_event_id` | FK→PublishedEvent | Evento publicado que gerou esta entrega |
| `consumer_service` | FK→Service | Serviço consumidor de destino |
| `status` | ENUM(`pending`, `success`, `error`, `retrying`) | Status atual da entrega |
| `priority` | ENUM(`low`, `medium`, `high`, `urgent`) | Prioridade da entrega |
| `attempts` | INTEGER | Número de tentativas de entrega |

---

## 5. Fluxo de Comunicação entre Microsserviços

### 5.1 Fluxo de Publicação de Evento (Publish)

O fluxo completo de publicação de evento ocorre em **uma transação atômica**:

```
   MICROSSERVIÇO PUBLICADOR                    EVENT BROKER
   ━━━━━━━━━━━━━━━━━━━━━━━                    ━━━━━━━━━━━━━

   1. Prepara payload JSON        ──────►
      POST /api/event
      Headers:
        service-token: <verona_token>
        event: <event_id>
        user: <user_id>
        priority: medium

                                            2. serviceTokenCheck middleware
                                               ├── Valida presença do header service-token
                                               ├── Busca Service por verona_token
                                               └── Injeta req.service_id

                                            3. validateServiceOwner middleware
                                               ├── Busca Event pelo header event
                                               ├── Verifica se service_owner == req.service_id
                                               ├── Injeta req.event_id, req.user_id, req.priority
                                               └── Valida body não vazio

                                            4. eventController.publish
                                               ├── TRANSACTION BEGIN
                                               │
                                               ├── 4a. Cria PublishedEvent
                                               │       ├── event_id
                                               │       ├── payload: req.body
                                               │       ├── user_id
                                               │       ├── published_by: service_id
                                               │       └── issue_date: now()
                                               │
                                               ├── 4b. Consulta EventListeners
                                               │       └── Todos inscritos no event_id
                                               │
                                               ├── 4c. Cria EventDelivery para cada listener
                                               │       ├── published_event_id
                                               │       ├── consumer_service
                                               │       ├── status: 'pending'
                                               │       └── priority: req.priority
                                               │
                                               └── TRANSACTION COMMIT

   ◄────── 5. Resposta JSON
            { success: true,
              message: "Evento publicado com sucesso" }
```

### 5.2 Pipeline de Middlewares (API)

A requisição `POST /api/event` passa por um pipeline sequencial de middlewares:

```
Request ──► serviceTokenCheck ──► validateServiceOwner ──► eventController.publish ──► Response
              │                       │                         │
              ▼                       ▼                         ▼
         Autentica              Autoriza o                 Publica evento
         serviço via            serviço como              e cria entregas
         verona_token           dono do evento            em transação
```

### 5.3 Exemplo Prático de Comunicação

**Cenário:** O serviço `caixa` realiza um fechamento de caixa e notifica os serviços interessados.

```
                    ┌──────────┐
                    │  caixa   │  Publica evento "caixa.fechado"
                    │  .taiksu │  com payload { gaveta_id, valor_total }
                    └────┬─────┘
                         │ POST /api/event
                         ▼
                ┌────────────────┐
                │  EVENT BROKER  │  Recebe, valida, persiste
                │  events.taiksu │
                └───────┬────────┘
                   ┌────┴────┐
                   ▼         ▼
            ┌──────────┐ ┌──────────┐
            │ alertas  │ │  email   │  Listeners inscritos em "caixa.fechado"
            │ .taiksu  │ │ .taiksu  │  Recebem EventDelivery com status 'pending'
            └──────────┘ └──────────┘
```

---

## 6. Autenticação e Autorização

O sistema utiliza **dois mecanismos de autenticação** independentes para contextos diferentes:

### 6.1 Autenticação M2M (Machine-to-Machine) — Verona Token

Utilizado para comunicação **entre microsserviços** via API.

| Aspecto | Detalhe |
|---|---|
| **Header** | `service-token` |
| **Formato** | UUID v4 |
| **Armazenamento** | Coluna `verona_token` no modelo `Service` |
| **Validação** | Middleware `serviceTokenCheck.js` |
| **Endpoint de teste** | `GET /api/verona` — retorna info do serviço se token válido |

**Fluxo:**
1. Serviço envia `service-token: <uuid>` no header HTTP
2. Middleware busca `Service` com `verona_token == uuid`
3. Se não encontrado → `401 Invalid service token`
4. Se serviço desativado → `401 Service is deactivated`
5. Se válido → injeta `req.service_id` e continua

### 6.2 Autenticação Humana — SSO Taiksu

Utilizado para acesso ao **painel web** (Dashboard) por usuários humanos.

| Aspecto | Detalhe |
|---|---|
| **Provider** | `login.taiksu.com.br` (SSO Central) |
| **Fluxo** | Redirect + callback com token |
| **Sessão** | Cookie `EventsTaiksuCookie` (secure, httpOnly) |
| **TTL** | ~2.5 horas (9.200.000 ms) |
| **Storage** | MySQL via connect-session-sequelize |

**Fluxo:**
1. Usuário sem sessão é redirecionado para `https://login.taiksu.com.br`
2. SSO autentica e redireciona para `/callback?token=<jwt>`
3. Callback valida o token via `GET https://login.taiksu.com.br/api/user/me`
4. Dados do usuário são salvos na sessão:
   - `id_user`, `name`, `foto`, `unidade_id`, `grupo_id`, `token`
5. Middleware `sessaoData` injeta dados da sessão em `res.locals` para as views

### 6.3 Autorização de Publicação — Ownership Validation

O middleware `validateServiceOwner` garante que apenas o **serviço dono** de um tipo de evento pode publicar instâncias dele:

```
Serviço A (id: 1) ──► tenta publicar Evento X (service_owner: 2) ──► ❌ 401 Rejeitado
Serviço B (id: 2) ──► tenta publicar Evento X (service_owner: 2) ──► ✅ Autorizado
```

---

## 7. API — Contrato de Publicação de Eventos

### `POST /api/event`

Endpoint único de publicação de eventos. Recebe o payload do evento e distribui para todos os listeners inscritos.

#### Headers Obrigatórios

| Header | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `service-token` | UUID | ✅ | Verona Token do serviço publicador |
| `event` | INTEGER | ✅ | ID do evento matriz a ser publicado |
| `user` | INTEGER | ❌ | ID do usuário que originou a ação |
| `priority` | STRING | ❌ | Prioridade (`low`, `medium`, `high`, `urgent`). Default: `medium` |

#### Body

Corpo JSON livre, determinado pelo `payload_contract` do Event correspondente. Exemplo:

```json
{
  "gaveta_id": 42,
  "valor_total": 1580.50,
  "operador": "João Silva",
  "data_fechamento": "2026-02-13T22:00:00Z"
}
```

#### Resposta de Sucesso — `200 OK`

```json
{
  "success": true,
  "message": "Evento publicado com sucesso"
}
```

#### Respostas de Erro

| HTTP | Condição | Corpo |
|---|---|---|
| `401` | Token ausente | `{ "error": "Service token is required" }` |
| `401` | Token inválido | `{ "error": "Invalid service token" }` |
| `401` | Serviço desativado | `{ "error": "Service is deactivated" }` |
| `401` | Sem body | `{ "error": "O corpo da requisição não foi fornecido" }` |
| `401` | Sem header event | `{ "error": "O ID do evento não foi fornecido no header" }` |
| `401` | Evento não existe | `{ "error": "O evento não foi encontrado" }` |
| `401` | Serviço não é dono | `{ "error": "O evento não pertence ao token de serviço fornecido" }` |
| `500` | Erro interno | `{ "success": false, "message": "Erro ao publicar evento", "error": "..." }` |

### `GET /api/verona`

Endpoint de **validação de token** (health check de autenticação).

#### Headers

| Header | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `service-token` | UUID | ✅ | Verona Token a ser validado |

#### Resposta — `200 OK`

```json
{
  "message": "Service token is valid",
  "service": "caixa",
  "id": 1
}
```

---

## 8. Sistema de Heartbeat (Monitoramento)

O Event Broker monitora a **disponibilidade** de todos os microsserviços registrados através de verificações periódicas de heartbeat.

### Funcionamento

```
EVENT BROKER                         MICROSSERVIÇO
━━━━━━━━━━━━━                        ━━━━━━━━━━━━━

  Cron: a cada 1 minuto
  ┌──────────────────┐
  │ checkHeartbeat() │
  └────────┬─────────┘
           │
           │  GET https://{nome}.taiksu.com.br/events/heartbeat
           │  Timeout: 8 segundos
           ├──────────────────────────────────────────────►
           │
           │  HTTP 200?
           │  ├── SIM ──► Service.status = 'online'
           │  └── NÃO ──► Service.status = 'offline'
           │
           │  (repete para cada serviço ativo)
```

### Regras

- Serviços com status `deactivated` são **ignorados**
- O serviço com nome `admin` é **ignorado** (skip)
- A URL é montada dinamicamente: `https://{service.nome}.taiksu.com.br/events/heartbeat`
- **Timeout de 8 segundos** — se não responder, considera offline
- Erros de rede também resultam em status `offline`
- Cada serviço é verificado **sequencialmente** (não em paralelo)

### Convenção de Endpoint

Cada microsserviço do ecossistema deve expor o endpoint:

```
GET /events/heartbeat → HTTP 200
```

Este endpoint deve retornar `200 OK` para indicar que o serviço está operacional.

---

## 9. Cron Jobs e Prioridades

O sistema de cron jobs (`cronjobs/index.js`) define 4 níveis de prioridade com frequências distintas:

| Prioridade | Frequência | Uso Atual |
|---|---|---|
| 🔴 **Urgente** | A cada 5 segundos | Placeholder (sem uso ativo) |
| 🟠 **Alta** | A cada 1 minuto | `checkHeartbeat()` — monitoramento de serviços |
| 🟡 **Média** | A cada 10 minutos | Placeholder (sem uso ativo) |
| 🟢 **Baixa** | A cada 20 minutos | Placeholder (sem uso ativo) |

Os slots de prioridade foram projetados para futuras expansões como:
- **Urgente:** Processamento de deliveries com prioridade `urgent`
- **Alta:** Processamento de deliveries com prioridade `high`
- **Média:** Retry de deliveries com status `error`
- **Baixa:** Limpeza de dados antigos, relatórios

---

## 10. Integração com o Ecossistema Taiksu

### 10.1 Serviços Conhecidos

O Event Broker comunica-se com os seguintes serviços do ecossistema:

| Serviço | Domínio | Função |
|---|---|---|
| **SSO** | `login.taiksu.com.br` | Autenticação centralizada de usuários |
| **Alertas** | `alertas.taiksu.com.br` | Sistema de notificações push |
| **Caixa** | `caixa.taiksu.com.br` | Operações de ponto de venda |
| **Email** | `email.taiksu.com.br` | Envio de e-mails transacionais |

### 10.2 Integração com Alertas (`postaAlerta`)

O Event Broker possui uma função integrada para enviar alertas ao sistema de notificações:

```javascript
// POST https://alertas.taiksu.com.br/api/alerta
{
  "user_id": 1,       // Usuário destinatário
  "app_id": "events", // App de origem
  "message": "..."    // Mensagem do alerta
}
```

### 10.3 Convenção de Nomes

Todos os microsserviços seguem o padrão de domínio:

```
{nome_do_servico}.taiksu.com.br
```

Onde `nome_do_servico` corresponde ao campo `nome` do modelo `Service`. Isso permite que o heartbeat construa URLs automaticamente.

---

## 11. Painel Web (Dashboard)

O Event Broker inclui um painel web com as seguintes views:

| Rota | View | Descrição |
|---|---|---|
| `GET /` | `index.ejs` | Dashboard principal com métricas da semana |
| `GET /events` | `events/index.ejs` | Lista de todos os eventos agrupados por serviço |
| `GET /events/info/:id` | `events/info.ejs` | Detalhes de um evento + listeners inscritos |
| `GET /events/published/:id` | `events/published.ejs` | Detalhes de um evento publicado + deliveries |
| `GET /events/service/:id` | `events/byservice.ejs` | Eventos de um serviço específico |
| `GET /events/create/:id` | `events/create.ejs` | Formulário de criação de evento |
| `GET /service/:id` | `service.ejs` | Detalhes de um serviço + eventos recentes |
| `GET /status` | `status.ejs` | Página pública de status dos serviços |

### Métricas do Dashboard

O dashboard exibe métricas **semanais**:
- Total de eventos publicados na semana
- Total de entregas
- Entregas pendentes (`pending`)
- Entregas com falha (`error`)

---

## 12. Estrutura de Diretórios

```
events.taiksu.com.br/
├── app.js                          # Configuração Express, sessão, rotas
├── package.json                    # Dependências e scripts
├── bin/
│   └── www                         # Entrypoint do servidor HTTP
├── config/
│   └── config.json                 # Credenciais Sequelize (dev/test/prod)
├── controllers/
│   ├── index.js                    # Barrel export
│   ├── eventController.js          # CRUD de eventos + publicação
│   └── serviceController.js        # CRUD de serviços
├── cronjobs/
│   └── index.js                    # Scheduler node-cron (4 níveis de prioridade)
├── functions/
│   ├── index.js                    # Barrel export
│   ├── postaAlerta.js              # Integração com alertas.taiksu.com.br
│   └── testeCron.js                # Função de teste para cron
├── heartbeat/
│   └── index.js                    # Verificação de status dos microsserviços
├── middlewares/
│   ├── serviceTokenCheck.js        # Autenticação Verona Token (M2M)
│   ├── sessaoData.js               # Injeção de dados de sessão + guard SSO
│   └── validateServiceOwner.js     # Autorização de publicação por ownership
├── migrations/                     # Histórico de migrações Sequelize
├── models/
│   ├── index.js                    # Auto-loader de modelos Sequelize
│   ├── service.js                  # Model Service (microsserviços)
│   ├── event.js                    # Model Event (tipos de evento)
│   ├── eventlistener.js            # Model EventListener (inscrições)
│   ├── publishedevent.js           # Model PublishedEvent (instâncias)
│   └── eventdelivery.js            # Model EventDelivery (entregas)
├── public/                         # Assets estáticos (CSS, imagens)
├── routes/
│   ├── api.js                      # API M2M (publicação de eventos)
│   ├── callback.js                 # Callback SSO (login.taiksu.com.br)
│   ├── events.js                   # Rotas web de eventos
│   ├── index.js                    # Dashboard principal
│   ├── service.js                  # Rotas web de serviços
│   └── status.js                   # Página de status pública
├── seeders/                        # Seeds Sequelize
└── views/                          # Templates EJS
    ├── layout.ejs                  # Layout principal
    ├── index.ejs                   # Dashboard
    ├── status.ejs                  # Página de status
    ├── events/                     # Views de eventos
    ├── components/                 # Componentes reutilizáveis
    ├── partials/                   # Partials (header, footer, sidebar)
    └── layouts/                    # Layouts alternativos
```

---

## Glossário

| Termo | Definição |
|---|---|
| **Evento Matriz** | Definição/template de um tipo de evento (modelo `Event`) |
| **Evento Publicado** | Instância registrada de um evento (`PublishedEvent`) — imutável |
| **Listener** | Inscrição de um serviço consumidor em um tipo de evento (`EventListener`) |
| **Delivery** | Registro de entrega de um evento publicado para um consumidor (`EventDelivery`) |
| **Verona Token** | Token UUID de autenticação M2M único por serviço |
| **Service Owner** | Microsserviço dono/publicador de um tipo de evento |
| **Heartbeat** | Verificação periódica de disponibilidade dos microsserviços |
| **SSO** | Single Sign-On via `login.taiksu.com.br` |
| **Pub/Sub** | Padrão Publish/Subscribe de comunicação assíncrona |
