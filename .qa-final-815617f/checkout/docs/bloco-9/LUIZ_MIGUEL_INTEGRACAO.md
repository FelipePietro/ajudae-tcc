# Bloco 9 · Integração de Luiz Miguel

26/11/2026 · Branch `feat/participacao-administracao` · Base `74609bfd03cfbdbfa8dbfdcaf603c5dabd91794e`

Os resultados abaixo registram a entrega de 26/11. A [correção das pendências em 27/11](CORRECOES_QA_LUIZ_MIGUEL_2026-11-27.md) registra os contratos e as verificações posteriores.

O escopo segue a Parte 5 do manual de quatro integrantes, de 24/11/2026. A matriz local do Bloco 9 ainda descreve uma divisão com seis integrantes. A referência para autorizações, estados e payloads é o código da base, conferido com o [catálogo Web](CATALOGO_WEB.md), o [contrato de participação](CONTRATOS_PARTICIPACAO.md) e o [guia da API](../guia-integracao-api.md). O PDF oficial indicado no AGENTS.md estava indisponível. Foi usada a [referência RN V1 local](../referencia-rn-v1-extraida-2026-10-21.md).

## Checklist das nove tarefas

| # | Área e página | Ligação Web e estado desta entrega |
| --- | --- | --- |
| 1 | Histórico pessoal em `/minhas-participacoes` | `GET /minhas-participacoes/dados` agora retorna o estado real do Evento, a identificação da inscrição do titular por Agenda e URLs de ação conforme elegibilidade. A interface agrupa Eventos ativos, concluídos e os demais estados nas páginas carregadas. |
| 2 | Presença e monitoria em `/ong/eventos#gestao-participacoes` ou `/organizador/eventos#gestao-participacoes` | O painel usa a lista e o detalhe de gestão `GET /gestao/eventos[/{evento}]` e a lista `GET /gestao/agendas/{agenda}/inscricoes`. Envia somente `presenca` em `PATCH /gestao/inscricoes/{inscricao}/presenca`. Consulta e decide monitoria no `GET/PATCH` de `/gestao/inscricoes/{inscricao}/monitoria`, com `confirmada` e justificativa quando exigida. |
| 3 | Justificativas no painel de gestão e no histórico pessoal | Gestão conectada a `GET/PATCH /gestao/inscricoes/{inscricao}/justificativa`, com consulta, situação, datas, motivos e decisão explícita `deferida`. A ocorrência original permanece. O titular pode consultar e enviar a justificativa por Agenda no histórico, usando o mesmo contrato Web. O link de envio só aparece para ocorrência elegível sem protocolo. A autoria permanece registrada pelo serviço. |
| 4 | Notas e encerramento no painel de gestão e em `/minhas-participacoes` | Conectados `GET /gestao/eventos/{evento}/avaliacoes`, `POST /gestao/eventos/{evento}/avaliacoes/{pessoa}`, `PATCH /gestao/agendas/{agenda}/finalizar` e `POST /gestao/eventos/{evento}/finalizar`. A nota opcional da ONG permanece no envio Web existente e a leitura usa a nota trazida no histórico. A rota independente `GET /minhas-participacoes/{evento}/avaliacao-ong` também foi confirmada no catálogo de rotas. |
| 5 | Validação de Pessoas em `/admin/candidatos` | Ligados `GET /admin/validacoes-pessoa/dados` e `GET /admin/validacoes-pessoa/{pessoa}`. Na mesma base do detalhe, os `POST` assumem, aprovam, solicitam correção, exigem padrinho, reprovam ou reprovam definitivamente segundo estado e capacidade. Ligados `PATCH /admin/documentos/{documento}/status`, `GET /admin/antecedentes/renovacoes` e o download privado existente. A página `/admin/usuarios` é de moderação e não tem um diretório Web próprio neste contrato. |
| 6 | Aprovação institucional em `/admin/cadastros-ong` e moderação em `/admin/fila-eventos` | Fila institucional ligada a `GET /admin/validacoes-ong/dados` e decisão a `PATCH /admin/validacoes-ong/{ong}/status`. Download usa a URL retornada pelo servidor. A fila de Eventos já usa `GET /admin/fila-eventos/dados` e `PATCH /admin/fila-eventos/{evento}/decisao` e foi preservada. O motivo é obrigatório na reprovação de Evento. O contrato de decisão de ONG aceita apenas o status. |
| 7 | Promoções em `/admin/upgrades-org` e conclusões de apadrinhamento em `/admin/conclusoes-padrinho` | Ligados `GET /admin/solicitacoes-organizador/dados` e detalhe, mais os `POST` de iniciar análise, aprovar e reprovar. Apadrinhamento usa `GET /admin/padrinhos/conclusoes/dados` e os `POST` de aprovar e reprovar, com capacidade própria, suficiente para chegar ao painel e à página dedicada. A interface exige motivo ao reprovar conclusão conforme o manual, embora o controlador aceite motivo nulo. |
| 8 | Campanhas em `/admin/campanhas-xp` | Ligados `GET /admin/campanhas-xp/dados`, `POST /admin/campanhas-xp`, `PATCH /admin/campanhas-xp/{campanha}` e `PATCH /admin/campanhas-xp/{campanha}/status`. O formulário usa código da categoria e datas em UTC, conforme os campos e o fuso do servidor. A auditoria é registrada pelo backend e consultada na tela de logs quando houver a capacidade respectiva. |
| 9 | Privacidade e auditoria em `/admin/solicitacoes-lgpd`, `/admin/responder-lgpd` e `/admin/log-acoes` | Ligados `GET /admin/solicitacoes-lgpd/dados`, `PATCH /admin/solicitacoes-lgpd/{id}/status` e `GET /admin/log-acoes/dados`, com paginação e filtros do contrato. A visão de consulta respeita as reduções da projeção. A conta com permissão somente de resposta informa o número de um protocolo recebido para tratá-lo pela rota autorizada. |

A integração usa `requestJson`, sessão e CSRF. As mutações exibem o resultado do servidor e sincronizam a listagem ou o detalhe. As filas de Pessoa e ONG recebem arrays sem metadados no envelope. Nessas filas, uma página cheia oferece a próxima página e o controle de voltar permanece disponível se a consulta seguinte estiver vazia.

## Arquivos afetados

- JavaScript em `resources/js/modules/` · `admin-operations.js`, `participation-management.js`, `web-operations.js`, `event-moderation.js` e `participation.js`.
- Administração em `resources/views/admin/` · `admin.blade.php`, `campanhas-xp.blade.php`, `log-acoes.blade.php`, `solicitacoes-lgpd.blade.php`, `partials/queue.blade.php` e `partials/web-operations.blade.php`.
- Gestão e componentes · `resources/views/ong/meus-eventos.blade.php`, `resources/views/participacoes/web-gestao.blade.php` e `resources/views/components/web-operations-controls.blade.php`.

Na primeira etapa, os entrypoints, layouts, contratos do backend e modo demonstrativo foram preservados. O complemento alterou a projeção Web do histórico, reaproveitou regras dos serviços e habilitou a rota de apadrinhamento pela navegação tipada existente. A remoção local de `.env.example` já estava no worktree antes das duas etapas.

## O que falta para homologar

- [ ] **Histórico pessoal.** Testar dois titulares, lista vazia, paginação e elegibilidade da nota. Conferir Eventos ativos e concluídos, inscrição com ausência ou desistência elegível, desistência sem penalidade, protocolo já enviado e monitoria por Agenda. Validar envio `201`, motivo até 2.000 caracteres, isolamento `403` e leitura após recarregar.
- [ ] **Presença e monitoria.** Usar ONG proprietária, independente, designado ativo e designado com vínculo encerrado. Confirmar persistência por nova leitura, negativa `403`, presença antes do início `409` e rejeição de monitoria sem justificativa `422`.
- [ ] **Justificativa gerencial.** Testar ocorrência sem protocolo `404`, deferimento, indeferimento com motivo, tentativa de autodeferimento e conservação da ausência ou desistência após recarregar. Confirmar com a coordenação se a autoria deve ser exibida no retorno Web.
- [ ] **Avaliações e encerramentos.** Testar Agenda com presença incompleta, Agenda vazia, encerramento fora de ordem, nota 1 a 5, nota duplicada e Evento com Agenda cancelada. Conferir que repetir encerramento não concede XP novamente. Validar envio da nota da ONG no dia 7 inclusive e bloqueio no dia 8.
- [ ] **Pessoas e documentos.** Testar fila vazia, filtros, posse da análise, documento rejeitado sem motivo, exigência de padrinho, decisão incompatível e reprovação definitiva por conta autorizada. Validar acesso negado e download com arquivo sintético. Determinar com a coordenação se `/admin/usuarios` precisa de contrato próprio de moderação.
- [ ] **ONG e Evento.** Confirmar ONG pendente → ativa, desaparecimento da fila após decisão, documento indisponível e capacidade negada. Aprovar e reprovar Evento com conta autorizada, conferir publicação no feed e conflito ao tentar aprovar Evento sem Agenda.
- [ ] **Promoção e apadrinhamento.** Testar iniciador da análise, capacidade distinta, reprovação sem motivo e decisão repetida. Confirmar que uma conta com somente `padrinhos.validar_conclusao` chega à fila dedicada e recebe `403` nas demais filas administrativas.
- [ ] **Campanhas.** Criar, editar e ativar/desativar, confirmar categoria existente, período inválido `422` e ausência de capacidade `403`. Conferir persistência e registro de auditoria.
- [ ] **LGPD e auditoria.** Assumir protocolo e concluir/recusar com resposta. Conferir atribuição, leitura reduzida para terceiro, recusa de conta sem capacidade, validação de resposta e filtro exato do log por ação. Validar a jornada com a solicitação criada pelo titular na frente de João Pedro.
- [ ] **Ambiente integrado.** Fazer os testes autenticados no navegador com `AJUDAE_WEB_AUTH=true`, MySQL descartável validado e dados sintéticos. Conferir Network e persistência por novo GET ou recarga. Verificar 360, 768 e 1280 px, foco dos formulários, teclado e estados vazios/erro. Nenhuma alteração de banco, migração, commit, push, PR ou deploy foi executada.

## Evidências automatizadas

- `npm run build` passou com Vite e 40 módulos transformados.
- `node --test` nos arquivos de `tests/Frontend` passou com 95 testes. Foi usado o PHP do Laragon no PATH local do processo.
- O filtro PHP `VisualParticipation|VisualFour` passou com 18 testes e 309 asserções. Esses testes preservam o comportamento demonstrativo.
- O Laravel renderizou 11 páginas integradas com identidades sintéticas em memória. A serialização dos Resources confirmou o formato das filas de Pessoa e ONG. `route:list` confirmou as rotas de gestão, de administração e do histórico.
- Em ambiente de teste com sessão e cache em memória, 14 endpoints JSON responderam HTTP 401 sem sessão. O mesmo ensaio fora de APP_ENV=testing confirmou o bloqueio de sessão em memória por HTTP 503.
- A suíte Web autenticada com `RefreshDatabase` não foi executada. Não houve ensaio de persistência no MySQL, validação binária em storage nem inspeção visual no navegador.

Quando a homologação com DevTools estiver concluída, o histórico de `AGENTS.md` pode registrar a integração do Bloco 9, com referência a este relatório e aos resultados de sessão e persistência.

## Complemento de histórico pessoal e apadrinhamento

O complemento alterou `app/Http/Controllers/WebParticipationController.php`, `app/Services/JustificativaParticipacaoService.php` e `app/Services/MonitoriaService.php` para projetar apenas as opções de ação do titular. `resources/js/modules/participation.js` e `resources/views/participacoes/pessoal.blade.php` exibem os grupos de Eventos, a consulta de monitoria e o envio e acompanhamento da justificativa. As regras de avaliação de ONG continuam as mesmas.

`app/Support/WebNavigation.php` reconhece `padrinhos.validar_conclusao` como capacidade administrativa. A rota HTML `GET /admin/conclusoes-padrinho` é gerada pelo registro existente. A nova view reutiliza as operações Web já ligadas à fila. Foram removidas as listagens duplicadas do painel e da validação de Pessoas. A validação de Pessoas mantém um atalho quando o perfil também pode decidir conclusões.

A checagem com dados em memória confirmou a projeção de ausência elegível, desistência com e sem penalidade, protocolo existente, monitoria aplicável e Agenda cancelada. Confirmou também a renderização do histórico e da página de apadrinhamento, o acesso com permissão exclusiva e a negativa `403` ao tentar acessar a fila de Pessoas com essa permissão. A homologação autenticada com DevTools e o ensaio de persistência ficam para a próxima etapa.

O build Vite, a sintaxe PHP e JavaScript e git diff --check passaram. As regressões tiveram 95 testes JavaScript e 18 testes PHP com 309 asserções. Sem migração, commit, push ou deploy.

## Homologação local em MySQL descartável

A execução de 26/11/2026 usou o schema `ajudae_luiz_homologacao` da instância MySQL 8.4.3 isolada em `127.0.0.1:33429`. A porta, o diretório de dados e o UUID foram confirmados antes das operações. As 30 migrations foram aplicadas nessa instância após autorização. O Laravel de teste está em `http://127.0.0.1:8809/login`, com sessão em arquivo, armazenamento privado local e dados sintéticos. O `.env` do projeto, o servidor Laravel já aberto e o MySQL principal foram preservados.

### Resultados por fluxo

| Tarefa | Evidência desta execução |
| --- | --- |
| Histórico pessoal | Consulta autenticada com estado do Evento e inscrições do titular. O HTML e o bundle JavaScript responderam `200`. A consulta sem sessão retornou `401`. |
| Presença e monitoria | A ONG registrou presença e a confirmação apareceu na lista em um GET novo. A Agenda futura retornou `409` e o ator sem permissão recebeu `403`. A monitoria validada ficou disponível para o titular. Rejeição sem justificativa retornou `422`. |
| Justificativas | O titular enviou com `201`, consultou o protocolo após nova requisição e recebeu `409` ao tentar repetir. Outro titular recebeu `403` e motivo vazio retornou `422`. O deferimento do gestor apareceu na consulta do titular. |
| Avaliações e encerramentos | O encerramento antecipado retornou `409`. Agenda e Evento foram finalizados na ordem prevista. A nota duplicada retornou `409` e a avaliação da ONG permaneceu consultável após gravar. As regressões cobriram a concessão de XP sem duplicação. |
| Pessoas e documentos | A análise foi assumida e a correção solicitada, com o status conferido no detalhe por novo GET. A suíte cobriu validação de documentos e acesso privado em armazenamento de teste. |
| ONG e Eventos | ONG aprovada saiu da fila pendente. Evento aprovado desapareceu da fila de moderação. A suíte verificou publicação, acesso administrativo e documentação privada de cadastro institucional com dados de teste. |
| Promoção e apadrinhamento | A solicitação de promoção foi assumida e reprovada. O detalhe refletiu a mudança. O login com permissão exclusiva chegou à fila de apadrinhamento, com `403` em Validação de Pessoas. Aprovação e rejeição foram confirmadas por nova consulta. A repetição da aprovação retornou `409`. |
| Campanhas de XP | A campanha foi criada com `201`, desativada e editada. Os valores ficaram disponíveis por nova consulta. |
| LGPD e auditoria | O protocolo passou a em análise e foi concluído. Ausência de resposta no encerramento retornou `422`. O titular viu a situação concluída e o filtro de auditoria por ação retornou o registro. |

### Verificações e limites

- A suíte focada em Web, permissões, participação e administração passou com 72 testes e 800 asserções no ambiente isolado.
- A regressão demonstrativa passou com 18 testes PHP e 309 asserções. Também passaram 95 testes JavaScript e o build Vite em diretório temporário.
- Os cenários da tabela usaram HTTP com login de sessão e novas requisições ao banco após gravação. A interface de login abriu no navegador do Codex.
- As duas tentativas de conectar ao DevTools do Edge headless expiraram antes da inspeção das páginas autenticadas. A revisão visual com DevTools, inclusive nos três tamanhos de tela, permanece pendente.
- O ambiente contém uma ausência elegível e uma conclusão de apadrinhamento pendente para repetir os fluxos no navegador. A configuração e as credenciais sintéticas ficam apenas no diretório temporário da homologação. Nenhum dado real, serviço externo, commit, push ou deploy foi usado.
