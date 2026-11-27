# Correções pós-QA de Agenda e criação de Evento — 26/11/2026

## Escopo

A branch local `feat/eventos-candidaturas` partiu de `6a8aa17912eccc143dc6d6d670a820b2bdcdae08`. O trabalho responde a QA-04 e QA-05 do [reteste independente](QA_INDEPENDENTE_ENRICO_RETESTE_2026-11-26.md). Foram usados os contratos versionados e as decisões locais. O PDF oficial indicado em `AGENTS.md` estava indisponível.

## QA-04 — Preservar a Agenda na edição

O formulário usa o horário exibido em minutos para detectar edição e o instante completo vindo do backend para validar o intervalo quando o campo continua igual. Sem diferenças, informa que não há alterações e evita o PATCH. Na edição de vagas, envia somente `vagas_agenda`. Quando muda apenas um horário, preserva o outro com precisão de segundos. A reanálise e a isenção de desistência continuam determinadas pelo serviço conforme a mudança real.

## QA-05 — Retomar a tentativa de criação

O editor usa `sessionStorage` quando consegue gravar e reler a chave. Se o armazenamento da aba falhar, pede uma chave à sessão Laravel autenticada antes de enviar o Evento. A sessão grava somente a chave, escopada pelo tipo e ID do ator. O servidor continua usando a idempotência transacional existente para o envio de Evento e Agendas.

Ao reabrir o editor na mesma sessão, o gestor pode reenviar os mesmos dados e o mesmo banner para retomar a tentativa. A interface permite consultar a gestão ou iniciar outro Evento explicitamente. A chave é liberada quando o cliente confirma o resultado ou escolhe iniciar outro Evento. O encerramento compara a chave recebida com a atual, de forma que uma confirmação atrasada preserve outra tentativa. Se a chamada de confirmação falhar, a chave fica disponível para novo reenvio.

## Arquivos

- Edição e retomada em `resources/js/modules/managed-events.js` e `resources/views/ong/criar.blade.php`.
- Sessão e autorização em `app/Http/Controllers/WebEventManagementController.php` e `routes/web-event-management.php`.
- Regressões em `tests/Feature/Bloco9EventManagementTest.php` e `tests/Frontend/managed-events.mjs`.
- Contrato em [CONTRATOS_F2_EVENTOS.md](CONTRATOS_F2_EVENTOS.md).

## Verificações

| Verificação | Resultado desta execução |
| --- | --- |
| Chromium QA-04 | Salvar uma Agenda com `:37` sem editar produziu zero PATCH, detalhe público 200 e nenhuma isenção de desistência. Alterar só vagas enviou `{"vagas_agenda":5}`, preservou os dois horários e pediu reanálise sem gerar isenção. Alterar o término enviou só `data_fim` e registrou a isenção prevista. |
| Chromium QA-05 | Com `QuotaExceededError` simulado e a resposta 201 interrompida após a gravação, a criação retomada pela sessão retornou o mesmo ID e deixou um único Evento no MySQL. A confirmação encerrou a tentativa. A ação explícita “Iniciar outro Evento” permitiu uma segunda criação. |
| Chromium com armazenamento normal | O reenvio padrão após perder 201 também retornou o mesmo Evento. Dados diferentes com a mesma chave retornaram 409 antes da recuperação. |
| PHP | A suíte focal F2/F4 aprovou **112 testes e 1.226 asserções**. Inclui o ciclo da chave na sessão, escopo e confirmação atrasada sobre outra chave. |
| JavaScript | **104 testes passaram** na suíte sem o renderizador Blade. O recorte de gestão e cliente HTTP teve 35 aprovações. `node --check` passou. |
| HTTP e concorrência | Fluxos de candidatura, publicação, recusa e lotação verificados. A última vaga concorrente retornou 200 e 409, com duas esperas de bloqueio observadas e uma única aprovação no banco. |
| Build e estilo | Vite 8.2.1 com o lockfile do commit em snapshot temporário e os arquivos frontend atualizados. Compilou 38 módulos. `php -l`, Pint focal e `git diff --check` passaram. |

As rotas verificadas foram `GET /ong/eventos?evento={id}`, `PATCH /gestao/agendas/{agenda}`, `GET /ong/eventos/criar`, `GET|POST|DELETE /gestao/eventos/intencao`, `POST /gestao/eventos`, `GET /eventos/{id}` e os endpoints existentes de candidatura e seleção. O teste confirmou o modo Web integrado e dados sintéticos em MySQL 8.4.3 descartável na porta 33429, com schemas novos `ajudae_qa9_fix_tests_20261126` e `ajudae_qa9_fix_web_20261126`. O preflight confirmou UUID e datadir temporário. PHP local 8.5.9. As evidências de navegador são de DOM, HTTP e MySQL.

## Limites e preservação

A retomada vale para a sessão Laravel ativa. Os campos e arquivos do formulário continuam sendo informados pelo gestor. As correções reutilizam a migration de idempotência já presente e mantêm a API Bearer. O banco principal, S3 e Railway ficaram fora da execução. Os relatórios independentes, `EVIDENCIAS.json`, `.env.example`, `package-lock.json` e `composer.phar` tinham estado preexistente no checkout e foram preservados. Uma nova rodada independente permanece pendente.

Sem commit, push, merge ou deploy nesta execução.

### Decisões para você responder

Nenhuma.
