# Bloco 8.1 — Contratos compartilhados

O modo integrado usa `AJUDAE_WEB_AUTH=true`. As rotas Blade e as novas operações JSON Web usam as guards de Pessoa e ONG resolvidas por `ResolveWebAccount`, a sessão e o CSRF do Laravel. Os endpoints privados em `/api/v1` continuam Bearer, com abilities, autorização por ator e titularidade. A projeção é específica do recurso. Controllers devem usar o mesmo domínio, sem chamadas HTTP internas.

O cliente `resources/js/core/http.js` faz requisições da mesma origem com `Accept: application/json`, cookies de sessão e `X-CSRF-TOKEN` nas escritas. Retorna o JSON e lança `HttpError` com `status`, `message` e `errors` para que a tela trate o estado. Respostas de sucesso usam `success/message/data`. Coleções paginadas mantêm metadados do paginador. Os erros de entrada são 422 por campo. Ausência de sessão JSON retorna 401. Falta de autorização retorna 403, recurso indisponível 404, transição incompatível 409, CSRF 419 e limite de requisições 429. As páginas HTML privadas continuam redirecionando para login quando a sessão termina.

## Propriedade dos arquivos

| Superfície | Responsável |
| --- | --- |
| Rotas centrais, bootstrap, middleware de sessão, cliente HTTP e entrada Vite integrada | Coordenador |
| Cadastro Pessoa, revisão documental, regras de apadrinhamento e guarda da aprovação | F1 |
| Eventos, Agendas, capacidade, candidatura e seleção | F2. Edições de controllers compartilhados sequenciadas após lote F4 |
| Controllers e projeções administrativas, moderação, organização e atribuição | F3 |
| Presença, justificativas, monitoria, avaliações e finalização | F4 |
| XP, confiança, ranking e certificados | F5 |
| Privacidade, notificações, auditoria e efeitos de cancelamento | F6 |

## Interfaces de domínio

- F1 registra exigência de padrinho em análise humana. A aprovação administrativa de F3 usa a guarda transacional do mesmo serviço. Aceite do vínculo não aprova a Pessoa.
- F2 disponibiliza o estado da inscrição e a declaração estudantil. F4 decide presença, justificativa, monitoria e encerramento. F5 consome apenas fatos persistidos. Monitoria pendente bloqueia emissão estudantil mesmo após Agenda encerrada.
- F4 aplica D01 a partir da finalização registrada pelo servidor. O envio após encerramento inclui o instante de sete dias corridos. A consulta da própria avaliação segue disponível depois do limite.
- F6 recebe fatos privados e metadados mínimos. As notificações são por destinatário. Mudanças de estado e seus efeitos devem confirmar na mesma transação ou usar chaves que permitam reprocessamento sem duplicar efeitos.
