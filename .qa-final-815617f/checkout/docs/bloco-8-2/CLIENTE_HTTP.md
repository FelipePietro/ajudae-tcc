# Cliente HTTP para a integração futura

O módulo `resources/js/core/api-client.js` exporta `createApiClient` e `ApiClientError`. A preparação do 8.2 mantém o módulo isolado. O cliente `resources/js/core/http.js` continua atendendo às páginas já ligadas no 8.1 com o contrato anterior.

## Interface

`createApiClient(options)` cria uma instância com `request(path, options)`. A URL base aceita endereço HTTP(S) absoluto ou caminho relativo à origem da página. Ao configurar `/api/v1` como base, passe `eventos`, `inscricoes/1` ou um caminho que permaneça sob `/api/v1/`. A instância usa a origem e o prefixo declarados e bloqueia redirecionamentos. Em produção, configure a origem HTTPS aprovada para a integração.

| Opção da instância | Comportamento |
| --- | --- |
| `baseUrl` | Origem e prefixo permitidos. |
| `mode` | `public` por padrão, `session` para Web ou `bearer` para API autenticada. |
| `getBearerToken` | Função síncrona que devolve o token correspondente ao contexto atual. É consultada em cada pedido. |
| `getCsrfToken` | Função síncrona para a sessão Web. O padrão lê `meta[name="csrf-token"]` ao enviar a primeira escrita e em cada escrita posterior. |
| `timeoutMs` | Tempo limite de 15.000 ms por padrão. A espera abrange o envio e a leitura do corpo. |
| `fetchImpl`, `pageOrigin` | Entradas de teste. O módulo usa `fetch` nativo e a origem real do navegador quando disponíveis. |

O pedido aceita `method` (`GET`, `HEAD`, `POST`, `PUT`, `PATCH`, `DELETE`), `body`, `headers`, `signal`, `timeoutMs` e `dedupeKey`. O cliente serializa JSON, preserva `FormData` e deixa o browser gerar o `Content-Type` e seu delimitador no multipart. Use `URLSearchParams` ao construir consultas.

```js
const publicApi = createApiClient({ baseUrl: '/api/v1' });
const result = await publicApi.request('eventos?page=1', { signal });
const events = result.data;
```

Para uso futuro autenticado, crie uma instância `bearer` com o provedor de token em memória do fluxo de Pessoa ou ONG. Em `public` e `bearer`, o Fetch usa `credentials: 'omit'`. O modo `session` exige que a origem da base e a da página coincidam, usa `credentials: 'same-origin'` e inclui `X-CSRF-TOKEN` nas escritas. Cabeçalhos arbitrários de autenticação, cookie ou CSRF são recusados por configuração. O módulo não armazena tokens, tenta renová-los ou altera a configuração de Sanctum. Guards, abilities e autorização de recursos continuam sob responsabilidade do servidor.

## Respostas e erros

O cliente devolve o JSON recebido, incluindo eventuais envelopes e metadados de paginação. Em `204`, `205`, `HEAD` bem-sucedido ou corpo vazio, retorna `null`. Resposta bem-sucedida com corpo inválido ou tipo que não seja JSON produz `invalid-response`. Downloads privados de PDF e documentos usam contratos binários específicos, fora deste cliente JSON.

| `kind` | Efeito |
| --- | --- |
| `http` | Preserva `status`. Em `422`, expõe o mapa `errors`. Expõe `retryAfter` quando o cabeçalho está acessível. |
| `invalid-response` | A resposta 2xx com corpo não pôde ser interpretada como JSON. |
| `timeout` | A operação excedeu o prazo e teve o `AbortSignal` interno cancelado. |
| `cancelled` | O `AbortSignal` do chamador cancelou a operação. |
| `network` | A chamada ou leitura do corpo falhou no transporte. Inclui falhas de CORS e redirecionamento rejeitado. |
| `duplicate` | Uma escrita com mesma chave, método e URL da mesma instância ainda está em andamento. |
| `configuration` | O pedido não pode ser montado com o modo, URL, corpo ou credenciais fornecidos. |

`400`, `409` e `422` podem aproveitar a mensagem do servidor. Exiba mensagens e erros de campo como texto. Ações de `401`, `403`, `419` e `429` dependem da interface consumidora. Não há repetição automática. O `dedupeKey` é opcional para operações de escrita e é liberado no fim da tentativa, inclusive em falha ou cancelamento. A proteção está restrita à instância e à operação em curso. A integridade em abas, sessões, reenvios e concorrência permanece no backend.

## Teste isolado

Execute `node --test tests/Frontend/api-client.test.mjs`. Os testes injetam `fetch` e usam objetos `Response`, `FormData` e `AbortController` do runtime. Cobre métodos, JSON, `FormData`, respostas vazias, erro inválido, códigos HTTP, mensagem por campo, rede, prazo durante o envio e a leitura, cancelamento, origens, credenciais e chave concorrente. Nenhuma rota real é chamada por esse teste.

Execução local no Node v22.14.0 em 23/11/2026. **25 testes aprovados**, sem falhas ou casos ignorados. `node --check resources/js/core/api-client.js` passou. A busca por imports encontrou uso do novo módulo somente no próprio teste.
