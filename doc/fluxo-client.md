# Fluxo Completo da Pasta `client`

Este documento descreve com detalhes o papel de cada arquivo da pasta `client`, como eles se conectam com `routes/events.js`, `controllers/*` e `models/*`, e qual o fluxo geral de publicação e recebimento de eventos dentro da aplicação.

## Visão Geral

A pasta `client` funciona como a camada de integração da aplicação com o broker de eventos.

Ela cobre dois sentidos de comunicação:

1. Saída de eventos: quando a aplicação executa uma ação local e precisa avisar outros serviços.
2. Entrada de eventos: quando a aplicação recebe um webhook/evento do broker e precisa processá-lo internamente.

Na prática, essa pasta tem 3 responsabilidades principais:

- publicar eventos para o broker;
- receber e validar eventos vindos do broker;
- atualizar o status de processamento de um evento no broker.

## Arquivos da Pasta `client`

### 1. `client/index.js`

Arquivo agregador.

Exporta apenas dois módulos:

- `publishEvent`
- `heartbeat`

Ele existe para permitir importações centralizadas, mas na prática o projeto também importa arquivos da pasta `client` diretamente, sem passar por esse `index.js`.

Resumo:

- não contém regra de negócio;
- serve apenas como ponto opcional de exportação.

---

### 2. `client/publishEvent.js`

Responsável por publicar eventos para o Event Broker.

#### Objetivo

Sempre que alguma regra de negócio local termina uma ação importante, esse módulo envia um `POST` para o broker em:

- `EVENT_BROKER_BASE_URL + EVENT_BROKER_PUBLISH_PATH`
- por padrão: `http://127.0.0.1:3093/api/event`

#### Configuração usada

Ele carrega:

- `EVENT_BROKER_BASE_URL`
- `EVENT_BROKER_PUBLISH_PATH`
- `SERVICE_TOKEN`

Observação importante:

- o arquivo usa `process.env.SERVICE_TOKEN`;
- o `.env.example` documenta `EVENT_BROKER_SERVICE_TOKEN`.

Ou seja, hoje existe uma inconsistência entre o nome esperado no código e o nome documentado no exemplo de ambiente.

#### Assinatura

Recebe um objeto com:

- `eventId`: identificador do evento a publicar;
- `payload`: corpo JSON do evento;
- `userId`: enviado no header `user`;
- `priority`: enviada no header `priority`.

#### Comportamento

1. monta a URL do broker;
2. envia `POST` com headers:
   - `Content-Type: application/json`
   - `service-token`
   - `user`
   - `event`
   - `priority`
3. envia o `payload` serializado em JSON;
4. se a resposta não for `2xx`, lança erro;
5. em caso de falha de rede ou status inválido, loga e relança o erro.

#### Papel arquitetural

Esse é o principal cliente de saída da pasta `client`.

Ele é usado por vários controllers, por exemplo:

- `controllers/entradaController.js`
- `controllers/saidaController.js`
- `controllers/loteController.js`
- outros controllers que publicam eventos após alterar o estado local

#### Exemplos de eventos publicados

- `10`: finalização de entrada;
- `11`: saída de estoque;
- `87`: criação de lote de limpeza de salmão;
- `88`: exclusão/zeramento de lote;
- `89`: cancelamento de limpeza;
- `96`: devolução ao estoque.

O significado exato de cada código fica distribuído entre os controllers.

---

### 3. `client/heartbeat.js`

Responsável por expor um endpoint simples de healthcheck.

#### Objetivo

Confirmar que o serviço está online e que o banco está acessível.

#### Como funciona

1. importa `sequelize` de `../models`;
2. chama `sequelize.authenticate()`;
3. se funcionar:
   - responde `200`;
   - retorna `{ success: true, message: 'Service is online', time }`;
4. se falhar:
   - responde `500`;
   - retorna `{ success: false, message: 'Service is offline', error, time }`.

#### Onde é usado

Em `routes/events.js`:

- `GET /heartbeat`

#### Papel arquitetural

- não processa eventos;
- apenas valida saúde da aplicação e conexão com banco.

---

### 4. `client/checkEvent.js`

É o middleware de entrada mais importante da pasta.

Ele impede reprocessamento duplicado e controla o estado do evento recebido.

#### Dependências

- `EventosProcessados` de `../models`
- `marcaProcessando` de `./marcaProcessando`
- `SERVICE_TOKEN` via variável de ambiente

#### Headers esperados

Esse middleware depende de dois headers:

- `delivery-id`
- `event-type`

Se algum deles não vier:

- responde `400`;
- interrompe o fluxo.

#### Fluxo detalhado

1. Lê `delivery-id` e `event-type` do request.
2. Busca em `EventosProcessados` um registro com o mesmo `delivery_id`.
3. Se não existir registro:
   - considera o evento como novo;
   - cria um registro em `EventosProcessados` com:
     - `delivery_id`
     - `event_type`
     - `status: false`
   - chama `next()`.
4. Se o registro existir e `status == true`:
   - entende que o evento já foi concluído anteriormente;
   - chama o broker em `PUT /api/delivery/ok`;
   - responde `409` com mensagem `"Evento já processado"`.
5. Se o registro existir e `status == false`:
   - entende que já existe processamento em andamento ou pendente;
   - chama `marcaProcessando(deliveryId)`;
   - responde `409` com mensagem `"Evento em processamento"`.

#### Intenção do campo `status`

Na prática:

- `false` = evento recebido mas ainda não confirmado como concluído;
- `true` = evento finalizado e confirmado.

#### Papel arquitetural

Esse arquivo protege a aplicação contra:

- duplicação de webhook;
- concorrência acidental;
- reexecução da mesma ação de estoque.

#### Observação importante sobre fluxo

Quando o evento é novo, o middleware apenas registra o evento com `status: false` e deixa o processamento seguir.

Quem marca a conclusão depois é `confirmaProcesso.js`, mas isso só acontece se o controller chamado decidir usar essa confirmação.

Ou seja, a proteção contra duplicidade depende do controller encerrar o ciclo corretamente.

---

### 5. `client/marcaProcessando.js`

Responsável por avisar ao broker que um `delivery_id` está em processamento.

#### Como funciona

Recebe:

- `deliveryId`

Executa:

- `PUT http://127.0.0.1:3093/api/delivery/processando`

Com body:

```json
{
  "delivery_id": "..."
}
```

E header:

- `service-token`

#### Quando é chamado

É usado por `checkEvent.js` quando encontra um evento já registrado com `status == false`.

Isso representa o cenário:

- o evento já entrou antes;
- ainda não foi marcado como concluído;
- então o broker é avisado de que ele segue em processamento.

#### Papel arquitetural

- não altera o banco local;
- não decide roteamento;
- apenas sincroniza o estado intermediário com o broker.

Observação:

- o arquivo importa `EventosProcessados`, mas não usa esse model.

---

### 6. `client/confirmaProcesso.js`

Responsável por finalizar o ciclo de processamento de um evento recebido.

#### Dependências

- `EventosProcessados`
- `SERVICE_TOKEN`

#### Como funciona

Recebe:

- `deliveryId`

Executa duas ações:

1. envia `PUT http://127.0.0.1:3093/api/delivery/ok` para o broker;
2. atualiza a tabela `EventosProcessados`, definindo:
   - `status: true`
   - para o `delivery_id` correspondente.

#### Objetivo

Sincronizar duas confirmações ao mesmo tempo:

- no broker: evento concluído;
- no banco local: evento concluído.

#### Papel arquitetural

Esse arquivo fecha o ciclo iniciado por `checkEvent.js`.

Fluxo esperado:

1. `checkEvent` cria `EventosProcessados.status = false`;
2. controller executa regra de negócio;
3. controller chama `confirmaProcesso(deliveryId)`;
4. broker e banco local passam a considerar o evento como processado.

#### Onde aparece no projeto

Foi encontrado importado em:

- `controllers/saidaController.js`
- `controllers/loteController.js`

Mas o uso não é uniforme em todos os fluxos de evento recebidos.

Isso significa que alguns eventos podem entrar no sistema, serem processados, e mesmo assim não terem o `status` local atualizado para `true`, dependendo do controller executado.

---

### 7. `client/actions.js`

É o roteador manual dos eventos recebidos.

#### Objetivo

Ler o header `event-type` e decidir qual controller deve tratar o request.

#### Dependências

Importa de `../controllers`:

- `entradaController`
- `saidaController`
- `loteController`

#### Mapeamento atual

- `101` -> `entradaController.legado(req, res)`
- `102` -> `saidaController.legado(req, res)`
- `84` -> `loteController.desfazer(req, res)`
- `1` -> `entradaController.salmao(req, res)`
- `2` -> `loteController.excluirLimpeza(req, res)`
- `95` -> `saidaController.desfazer(req, res)`

#### Significado funcional

- `101`: movimentação de entrada vinda de sistema legado;
- `102`: movimentação de saída vinda de sistema legado;
- `84`: entrada cancelada, zerando/removendo lote restante;
- `1`: limpeza de salmão gerando entrada de lote;
- `2`: cancelamento da limpeza de salmão;
- `95`: cancelamento de saída, devolvendo quantidade ao estoque.

#### Observações importantes

1. O arquivo usa vários `if` independentes, não `else if`.
   Isso não quebra o fluxo atual porque cada `event-type` só deve coincidir com um código, mas estruturalmente é um roteador manual simples.

2. Não há `default`.
   Se o `event-type` não casar com nenhum caso:
   - nenhuma ação é executada;
   - o request pode ficar sem resposta, dependendo do comportamento do controller ou da rota.

3. Não há `await` nas chamadas dos controllers.
   Como os controllers são assíncronos em vários casos, o fluxo depende de cada um responder corretamente por conta própria.

#### Papel arquitetural

Esse arquivo é o dispatcher da entrada de eventos.

Ele recebe o request já validado por `checkEvent` e o encaminha para a regra de negócio correta.

## Fluxo Geral de Entrada de Eventos

O caminho completo dos eventos recebidos é este:

### Etapa 1. Entrada HTTP

Em `routes/events.js` existe:

- `POST /receive`

Essa rota encadeia:

1. `checkEvent`
2. `actions`

Ou seja:

- primeiro valida se o evento pode ser processado;
- depois executa a ação correspondente.

### Etapa 2. Validação anti-duplicidade

`checkEvent`:

- lê `delivery-id`;
- lê `event-type`;
- verifica se esse `delivery-id` já existe em `EventosProcessados`.

Possibilidades:

- novo evento -> grava `status: false` e continua;
- evento concluído -> responde conflito;
- evento ainda pendente -> avisa broker e responde conflito.

### Etapa 3. Roteamento por tipo

`actions` lê `event-type` e chama o controller correspondente.

Exemplo:

- `event-type = 95`
- chama `saidaController.desfazer`

### Etapa 4. Regra de negócio no controller

O controller altera banco local, por exemplo:

- cria lote;
- baixa estoque;
- zera lote;
- devolve quantidade;

Dependendo do fluxo, o controller também:

- publica um novo evento para outros serviços usando `publishEvent`;
- confirma conclusão do evento recebido usando `confirmaProcesso`.

### Etapa 5. Confirmação do processamento

Quando o controller usa `confirmaProcesso(deliveryId)`:

1. o broker recebe `/api/delivery/ok`;
2. `EventosProcessados.status` vira `true`.

Esse é o encerramento ideal do ciclo.

## Fluxo Geral de Saída de Eventos

Além de receber eventos, a pasta `client` também publica eventos produzidos internamente.

O fluxo de saída é este:

### Etapa 1. Controller executa regra local

Exemplos:

- `entradaController.manual` finaliza lista de entrada e cria lotes;
- `saidaController.saidaManual` aplica FIFO e baixa quantidades;
- `loteController.excluir` zera um lote;

### Etapa 2. Controller chama `publishEvent`

Depois da alteração local, o controller publica um evento com:

- código do evento;
- payload com dados relevantes;
- usuário responsável;
- prioridade.

### Etapa 3. Broker distribui para outros serviços

O broker passa a ser responsável por repassar esse evento para quem precisar reagir.

Ou seja, a aplicação de estoque:

- mantém o estado local;
- notifica o ecossistema através do broker.

## Relação Entre Arquivos `client` e Outros Pontos do Projeto

### `routes/events.js`

É a porta de entrada dos eventos recebidos.

Mapa:

- `GET /heartbeat` -> `client/heartbeat.js`
- `POST /receive` -> `client/checkEvent.js` -> `client/actions.js`

### `controllers/*`

Os controllers consomem a pasta `client` de dois jeitos:

1. para publicar eventos, usando `publishEvent`;
2. para confirmar eventos recebidos, usando `confirmaProcesso`.

### `models/EventosProcessados`

É o model central da idempotência do fluxo de entrada.

Ele guarda:

- `delivery_id`
- `event_type`
- `status`

Sem esse model, `checkEvent.js` não conseguiria distinguir:

- evento novo;
- evento repetido;
- evento ainda pendente.

## Leitura Arquivo por Arquivo em Linguagem Objetiva

Se outra IA precisar de uma descrição curta e direta, você pode usar esta:

- `index.js`: exportador simples de utilitários do client.
- `publishEvent.js`: cliente HTTP que envia eventos para o broker.
- `heartbeat.js`: endpoint de saúde que valida conexão com banco.
- `checkEvent.js`: middleware que valida headers, evita duplicidade e registra eventos recebidos.
- `marcaProcessando.js`: avisa ao broker que um delivery ainda está em processamento.
- `confirmaProcesso.js`: marca um delivery como concluído no broker e na base local.
- `actions.js`: roteador de `event-type` para controllers de negócio.

## Resumo do Ciclo Completo

### Quando a aplicação recebe um evento externo

1. o broker chama `POST /events/receive`;
2. `checkEvent` valida headers e idempotência;
3. se for novo, grava em `EventosProcessados` com `status: false`;
4. `actions` escolhe o controller com base no `event-type`;
5. o controller atualiza estoque/lote;
6. opcionalmente publica novos eventos;
7. idealmente chama `confirmaProcesso(deliveryId)`;
8. o broker e a base local passam a tratar o evento como concluído.

### Quando a aplicação gera um evento interno

1. um controller executa a regra local;
2. chama `publishEvent(...)`;
3. o broker recebe o evento e distribui para outros serviços.

## Pontos de Atenção Importantes Para Outra IA

Ao analisar essa pasta, outra IA deve considerar estes detalhes:

1. O `client` não é um frontend.
   Aqui, "client" significa cliente de integração com broker/eventos.

2. O coração do fluxo de entrada é `checkEvent -> actions -> controller -> confirmaProcesso`.

3. O coração do fluxo de saída é `controller -> publishEvent`.

4. O controle de duplicidade depende da tabela `EventosProcessados`.

5. A confirmação final não parece estar padronizada em todos os controllers que recebem eventos.

6. Existe inconsistência entre variáveis de ambiente:
   - código usa `SERVICE_TOKEN`
   - `.env.example` documenta `EVENT_BROKER_SERVICE_TOKEN`

7. `actions.js` é um roteador manual por `event-type`, sem fallback/default explícito.

8. `marcaProcessando.js` importa `EventosProcessados`, mas não utiliza esse model.

9. Em alguns controllers, `publishEvent` é chamado sem `await`, então a resposta HTTP local pode acontecer antes da publicação terminar.

## Texto Pronto Para Colar em Outra IA

Se quiser um resumo em formato de prompt/contexto para outra IA, use este:

```text
A pasta client deste projeto Node.js é a camada de integração com um Event Broker. Ela faz duas coisas principais: publica eventos para outros serviços e recebe eventos vindos do broker. O fluxo de entrada começa em routes/events.js, na rota POST /receive, que executa primeiro client/checkEvent.js e depois client/actions.js. O checkEvent valida os headers delivery-id e event-type, consulta o model EventosProcessados e garante idempotência. Se o delivery_id ainda não existe, grava um registro com status false e deixa o fluxo seguir. Se já existe com status true, informa ao broker que o delivery já está ok e retorna conflito. Se já existe com status false, chama client/marcaProcessando.js para avisar ao broker que ainda está processando e retorna conflito.

Depois disso, client/actions.js lê o header event-type e encaminha o request para o controller correto. Os mapeamentos atuais são: 101 para entradaController.legado, 102 para saidaController.legado, 84 para loteController.desfazer, 1 para entradaController.salmao, 2 para loteController.excluirLimpeza e 95 para saidaController.desfazer. Esses controllers executam as regras de negócio de estoque e lote.

O encerramento ideal do fluxo ocorre com client/confirmaProcesso.js, que avisa o broker em /api/delivery/ok e atualiza EventosProcessados.status para true. Já o fluxo de saída acontece quando controllers internos chamam client/publishEvent.js, que envia POST para o broker com headers service-token, user, event e priority, além do payload JSON.

Os arquivos da pasta têm estas funções: index.js exporta utilitários; publishEvent.js publica eventos; heartbeat.js valida saúde do serviço e do banco; checkEvent.js controla idempotência dos eventos recebidos; marcaProcessando.js sincroniza estado intermediário com o broker; confirmaProcesso.js finaliza o processamento; actions.js roteia event-type para controllers.

Pontos importantes: esta pasta não é frontend, e sim cliente de integração. O fluxo central é checkEvent -> actions -> controller -> confirmaProcesso para entrada, e controller -> publishEvent para saída. Há uma inconsistência entre o código e o .env.example porque o código usa SERVICE_TOKEN, enquanto o exemplo documenta EVENT_BROKER_SERVICE_TOKEN. Também não há fallback/default em actions.js.
```
