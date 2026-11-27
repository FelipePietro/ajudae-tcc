# QA independente — Reteste de Eventos e candidaturas — 26/11/2026

## A. Resumo executivo

**REPROVADO — CORREÇÕES OBRIGATÓRIAS.** A revisão local `6a8aa17` passou nos percursos principais dos três achados anteriores. A auditoria encontrou um bloqueador na edição de Agendas. Salvar sem alterações uma Agenda gravada com segundos truncou os horários, tirou o Evento do ar e registrou isenção de desistência para a candidatura existente. A duplicação foi reproduzida também em uma condição adicional: perda da resposta, falha de escrita no armazenamento da aba e reabertura do editor.

## B. Estado do repositório

- Branch ativa e única no worktree, `feat/eventos-candidaturas`. HEAD inicial e final `6a8aa17912eccc143dc6d6d670a820b2bdcdae08`.
- `git ls-remote` confirmou `74609bfd03cfbdbfa8dbfdcaf603c5dabd91794e` em `dev` e `feat/eventos-candidaturas` remotas. O HEAD local está um commit à frente e tem 19 arquivos diferentes da base.
- O checkout já tinha `.env.example` removido, `package-lock.json` alterado e `composer.phar`, `EVIDENCIAS.json` e o relatório original sem rastreamento. O stage estava vazio. O reteste acrescentou este relatório e uma referência no relatório original.
- Revisados Controllers Web e público, módulo `managed-events.js`, formulário de criação, gestão de Agendas e candidatos, `requestJson`, política de Evento, Services de Evento, Agenda, moderação, candidatura e seleção, Resources, migration de idempotência e testes F2/F4.
- Fontes normativas no checkout: [catálogo Web](CATALOGO_WEB.md), [contrato F2](CONTRATOS_F2_EVENTOS.md), [contrato de participação](CONTRATOS_PARTICIPACAO.md), [matriz da equipe](MATRIZ_INTEGRACAO.md), [prontidão](PRONTIDAO.md), [auditoria anterior](QA_INDEPENDENTE_ENRICO_2026-11-26.md) e [relatório de desenvolvimento](DESENVOLVIMENTO_ENRICO_2026-11-26.md). O caminho do PDF oficial informado em `AGENTS.md` não existia no ambiente.

## C. Matriz de funcionalidades

| Funcionalidade | Integração e evidência desta execução | Resultado ou pendência |
| --- | --- | --- |
| Criação e escopo | Web real, sessão tipada e persistência MySQL. ONG ativa e independente criaram com 201. Suite PHP cobriu ONG inativa, designado, proprietário e campos protegidos. | Estado inicial pendente e D04/D05 confirmados no recorte. |
| QA-01 e D02 | Duas sessões concorrentes fizeram POST multipart com a mesma chave. Duas respostas 201, mesmo ID, um Evento, duas Agendas e uma intenção. Outra chave permitiu criar Evento com os mesmos dados. | Reenvio padrão corrigido. Circunstância adicional de armazenamento indisponível reproduziu QA-05. |
| Agendas | Web integrado. 422 para Evento sem Agenda. PHP cobriu 29 minutos, rollback da segunda Agenda, compensação de banner e exclusão da última Agenda. | QA-04 altera segundos ao salvar sem edições. |
| Lotação e seleção | Candidaturas por Agenda em MySQL. Duas vagas preenchidas na Agenda A, terceira aprovação 409. Agenda B de cinco vagas aceitou inscrição. Recusa manteve histórico, repetição 422. | Última vaga simultânea retornou 200/409 e deixou uma única aprovação. |
| Publicação e reanálise | HTTP real, detalhe e busca indisponíveis antes da moderação. Título manteve publicação. Mudança real de horário solicitou reanálise e preservou inscrição. | A mesma transição ocorreu sem mudança do usuário em QA-04. |
| Exploração pública | Feed, detalhe, busca e filtro de modalidade leram dados persistidos. Visitante, ONG, organizador e Pessoa pendente abriram o detalhe publicado. | As Agendas na UI do detalhe continuam disponíveis apenas ao voluntário aprovado. A projeção pública de disponibilidade permanece sem decisão específica registrada. |
| QA-02 e capas | Chromium 360, 768 e 1280 px. Capa legada relativa e gerenciada visíveis. Evento sem capa ficou com fundo. A rota binária gerenciada retornou 404 enquanto pendente e 200 publicado. | Achado anterior encerrado no recorte. |
| Candidatura | HTTP 201 com e sem carta, 403 para ONG, organizador e Pessoa pendente, 409 para duplicidade e reanálise. Chromium percorreu as quatro etapas, preservou carta ao voltar, enviou sem carta e confirmou histórico após recarga. | 503 injetado manteve revisão e exibiu erro. A tentativa real retornou 201. |
| QA-03, match e histórico | GET de inscrições retornou score 80 e estado em formação em dados sintéticos. Chromium renderizou ambos em linhas próprias, fora de match e XP. | Achado anterior encerrado. PHP cobre correspondências com IDs conhecidos, sem seleção automática. |

## D. Auditoria dos testes existentes

| Suíte | Avaliação |
| --- | --- |
| `managed-events.mjs` | Cobertura unitária efetiva de montagem de payload, duração e vagas, multipart, estado de confiabilidade e reutilização da chave com armazenamento acessível. Os objetos de formulário e armazenamento são simulados. Não detecta serialização dos horários ao abrir a edição nem reload após falha de gravação do armazenamento. |
| `Bloco9EventManagementTest` | Cobertura efetiva de status, persistência, escopo, 409 para chave com outro payload, reenvio multipart e rollback com falha induzida. Usa armazenamento fake em parte dos casos e sessão construída pelo PHPUnit. Concorrência real foi verificada à parte. |
| `EventoOperacionalTest` e `AgendaInscricaoTest` | Exercitam regras de reanálise, prazo, match, aprovação sequencial e histórico. A camada PHP recebe os dados do teste e não exercita os valores transformados pelo editor. |
| `Bloco9WebEventParticipationTest` e `AvaliacaoConfiabilidadeTest` | Exercitam projeção de confiabilidade, ator e seleção. O Chromium confirmou a passagem do campo do contrato para a tela. |
| `OngAuthAndEventoTest`, `OrganizadorWorkflowTest`, `PessoaValidationAccessTest` e `Bloco81WebEventModerationTest` | Cobrem identidade, estado e titularidade em backend. Parte dos cenários usa Bearer ou sessão montada no teste. |
| Testes JavaScript do cliente HTTP e renderizador Blade | O cliente usa resposta simulada para falhas, incluindo 401, 419 e 500. O renderizador verificou sintaxe de três scripts do modo demonstrativo em execução separada com manifesto temporário. |

A execução nova reproduziu todos os 110 casos PHP relatados no desenvolvimento. O recorte integrado foi confirmado por ambiente e rotas. Os dois novos achados exigiram ação no navegador com dados persistidos e não são detectados pelas asserções atuais.

## E. Defeitos reproduzidos

### QA-04 — Salvar Agenda sem mudanças altera horário e retira Evento do ar

**Severidade alta.** Funcionalidade de edição de Agenda e reanálise RN012/RN013/RN018. A reprodução usou uma ONG ativa, Evento aprovado, Agenda com quatro vagas e candidatura pendente.

1. Criar Agenda válida via Web com início `2026-12-01T02:10:37Z` e fim `2026-12-01T03:10:37Z`. Publicar o Evento e registrar uma candidatura sintética.
2. Fazer o controle `PATCH /gestao/agendas/10` enviando somente as quatro vagas existentes. O horário e a publicação continuam iguais.
3. Em Chromium, abrir `/ong/eventos?evento=6`, entrar na edição da Agenda e acionar **Salvar Agenda** sem mudar campo algum.
4. Conferir o resultado do PATCH, recarregar os dados de gestão e consultar o detalhe público.

| Medida | Antes de salvar | Depois de salvar |
| --- | --- | --- |
| Início e término (UTC) | 02:10:37 e 03:10:37 | 02:10:00 e 03:10:00 |
| Vagas | 4 | 4 |
| Estado do Evento | `ativo` | `aguardando a confirmação` |
| Detalhe público | HTTP 200 | HTTP 404 |
| Isenção na candidatura | Ausente | Válida até `2026-12-01T02:10:00Z` |

**Esperado:** gravação sem mudanças conserva os instantes, a publicação e os efeitos da candidatura. **Encontrado:** PATCH retornou 200 e persistiu os efeitos acima. O editor preenche os dois valores em precisão de minuto e envia ambos ao salvar. O Service interpreta a diferença como alteração real. O impacto é retirar um Evento aprovado da exploração pública e alterar o estado da inscrição a partir de uma ação que preservava os dados visíveis.

**Correção proposta:** enviar somente os campos realmente alterados e preservar instantes vindos do servidor ao editar outros dados. Cobrir salvar sem alterações e editar apenas vagas com início e término contendo segundos. [Formatação do editor](../../resources/js/modules/managed-events.js#L14) · [Envio da Agenda](../../resources/js/modules/managed-events.js#L344) · [Efeitos no Service](../../app/Services/AgendaManagementService.php#L79).

### QA-05 — Falha de escrita na sessão da aba permite duplicação após reabrir o editor

**Severidade média.** Funcionalidade de recuperação de criação. Em Chromium com sessão ONG válida, o ensaio simulou `QuotaExceededError` somente ao gravar a chave de idempotência no `sessionStorage`. Os demais recursos do navegador permaneceram ativos.

1. Criar Evento online sintético com uma Agenda futura. Interromper a entrega do HTTP 201 depois de o servidor gravar.
2. Confirmar o erro de conexão na interface e um Evento no MySQL.
3. Reabrir o editor e reenviar os mesmos dados, com a falha de armazenamento ainda ativa.
4. Conferir o sucesso do segundo envio e duas linhas com a mesma intenção no MySQL.

**Esperado:** recuperar a tentativa anterior ou deixar claro que reabrir a tela perde a recuperação da solicitação. **Encontrado:** a interface ofereceu a mesma orientação genérica de reenvio. A chave em memória se perdeu ao reabrir. O segundo POST usou outra chave e criou outro Evento. Na configuração padrão com `sessionStorage` gravável, o mesmo percurso manteve um único Evento.

**Correção proposta:** viabilizar recuperação da tentativa quando o armazenamento da aba falhar ou orientar o usuário a reconsultar a gestão antes de repetir em uma nova página. Incluir o cenário em navegador. [Persistência e fallback da chave](../../resources/js/modules/managed-events.js#L112) · [Submissão](../../resources/js/modules/managed-events.js#L204).

## F. Lacunas e divergências

- A projeção pública de datas e disponibilidade sem login ou para ator sem elegibilidade continua em aberto. O documento de requisitos pede exploração por vários atores. O catálogo atual define que a rota que entrega disponibilidade de candidatura é exclusiva do voluntário aprovado.
- A suíte JavaScript já cobre falhas simuladas de transporte. Ficaram sem percurso de ponta a ponta a expiração de sessão durante mutação, a paginação de listas extensas e a acessibilidade por teclado dos modais em todas as larguras.
- A criação sem cabeçalho permanece prevista no contrato Web anterior. A deduplicação depende de reaproveitar a chave. A API Bearer conserva o contrato já existente.
- A autorização de criação institucional pelo designado, descrita no material anterior, foi substituída por D04/D05 no catálogo atual. A criação conjunta exigida por D02 baseia a revisão de Agendas.
- Os demais fluxos F4, S3, Railway e o runtime PHP 8.4 ficaram fora deste ensaio. O ambiente local usou PHP 8.5.9.

## G. Segurança e privacidade

POST de Evento em sessão ONG com CSRF ausente respondeu 419. Os casos do backend cobriram autorização pela identidade, titularidade, escopo de designação e rejeição de campos controlados pelo servidor. A candidatura recusou os papéis inelegíveis exercitados. O histórico do titular mostrou a carta sintética, e outra Pessoa consultando seu próprio histórico não recebeu a carta. A gestão autorizada recebeu a carta. O HTTP público não serviu capa gerenciada pendente. O recorte não apontou exposição de dados pessoais.

## H. Integridade e concorrência

A execução usou schemas recém-criados em instância isolada. O cenário de criação disputou o bloqueio da ONG em dois processos PHP com sessões distintas. `performance_schema` mostrou duas transações esperando. O resultado foi 201/201 com mesmo ID, um Evento, duas Agendas, uma intenção e um banner associado. A outra chave criou um segundo Evento independente. Dois objetos gerenciados ficaram no armazenamento local de QA para os dois Eventos criados.

Na seleção, duas requisições reais competiram pela única vaga, com duas esperas observadas para o bloqueio do Evento. Os resultados foram 200/409, uma aprovada e uma pendente. A falha induzida no PHPUnit ao gravar a segunda Agenda reverteu Evento, Agendas, intenção e imagem preparada. QA-04 confirma uma perda de precisão e mudança de estado no fluxo de edição, apesar dessas garantias transacionais.

## I. Interface e usabilidade

O Chromium carregou ambas as capas em 360, 768 e 1280 px e consultou candidatos no acordeão, com os estados de confiabilidade previstos. O fluxo de candidatura passou nas quatro etapas e no histórico. A falha simulada de servidor mostrou mensagem de erro na revisão e o envio real confirmou somente após HTTP 201. A auditoria coletou DOM, HTTP e persistência, sem homologação estética ou capturas anexadas.

## J. Comandos e resultados

| Verificação | Resultado |
| --- | --- |
| Ambiente | PHP 8.5.9, Node 24.16.0, Chromium 158 e MySQL 8.4.3 em `127.0.0.1:33429`. O preflight verificou UUID `9e9f38a9-e9f9-11f1-b7ef-0250de5958d2`, datadir temporário e schemas `ajudae_qa9_indep_tests_20261126` e `ajudae_qa9_indep_web_20261126`. Sessões, assets, storage e fixtures independentes. |
| PHPUnit focal | `php vendor/bin/phpunit --do-not-cache-result --filter 'Bloco9EventManagementTest\|Bloco9WebEventParticipationTest\|EventoOperacionalTest\|AgendaInscricaoTest\|OngAuthAndEventoTest\|Bloco81WebEventModerationTest\|OrganizadorWorkflowTest\|PessoaValidationAccessTest\|AvaliacaoConfiabilidadeTest\|ManagedImage'`. **110 aprovados, 1.195 asserções, zero ignorados**. |
| JavaScript | `node --test` sobre os arquivos de `tests/Frontend` fora do renderizador Blade, **102 aprovados**. Reteste separado do renderizador com o manifesto temporário e modo demonstrativo, três scripts analisados com sucesso. |
| Build do commit | Arquivos frontend de `git archive HEAD` em temporário, `npm ci --offline` com o lockfile de HEAD e `npm run build`. Vite 8.2.1 compilou 38 módulos e gerou o manifesto usado no Chromium. |
| Build com dependências já instaladas | `npm.cmd run build -- --outDir <temporário>`, Vite 8.4.0, 38 módulos. O lockfile local preexistente permaneceu no checkout. |
| Rotas e Git | `php artisan route:list -v --except-vendor` confirmou as rotas integradas. `node --check` e `git diff --check` passaram. |

Rotas exercitadas incluem `GET /feed`, `GET /eventos/{id}`, `GET /api/v1/eventos/{id}/imagem`, `GET /ong/eventos/criar`, `GET /ong/eventos?evento={id}`, `GET /ong/candidatos?evento={id}`, `POST /gestao/eventos`, `PATCH /gestao/agendas/{agenda}`, `GET /candidatura?evento={id}`, `POST /candidatura/{evento}/enviar`, `GET /minhas-candidaturas`, `GET /gestao/agendas/{agenda}/inscricoes`, `PATCH /gestao/inscricoes/{inscricao}/selecao` e `PATCH /admin/fila-eventos/{evento}/decisao`.

## K. Riscos de integração

O consumo das novas rotas continua dependente de identidade Web e moderação. O editor F2 e os módulos das demais frentes compartilham `resources/js/identity.js` e o cliente HTTP. As referências consultadas ainda não contêm o commit auditado. A migration de idempotência deve existir no ambiente que habilitar esse consumo. A trilha de QA comprovou integração local com o lockfile do commit. S3 e deploy seguem fora do resultado.

## L. Veredito final

**REPROVADO — CORREÇÕES OBRIGATÓRIAS.** QA-02 e QA-03 passaram nas condições auditadas. QA-01 passou no reenvio padrão e na corrida com a mesma chave, com a falha adicional em QA-05. O bloqueador QA-04 é uma edição sem intenção que modifica dados e despublica um Evento. A correção deve ser validada com uma Agenda já persistida e horários com segundos, acompanhando publicação e candidatura.

O código da aplicação permaneceu em modo de auditoria. Sem commit, push, merge ou deploy. O pacote de delta de código não se aplica a esta entrega documental.

### Decisões para você responder

Nenhuma.
