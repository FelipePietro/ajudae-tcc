# Contratos Web de participação e seleção

Este catálogo cobre a gestão de inscritos em F2 e participação em F4. As rotas pertencem ao grupo Web com sessão do Laravel. Envie `Accept: application/json` e cookies de sessão. Nas mutações, inclua o token CSRF usado pelo cliente HTTP Web do projeto. As rotas de gestão usam a identidade verificada dos guards `web_pessoa` e `web_ong`. O histórico e a avaliação da ONG usam `web_pessoa` com jornada pessoal.

## Autorização por registro

| Ator | Inscritos, seleção, presença, monitoria, justificativa gerencial, nota obrigatória e encerramentos |
| --- | --- |
| ONG proprietária ativa | Pode executar no próprio Evento, inclusive com organizador designado. |
| Organizador designado | Pode executar quando aprovado, com vínculo ativo com a ONG ativa do Evento e designação para aquele registro. |
| Organizador sem designação, com vínculo encerrado ou designado apenas em outro Evento | Recebe 403. |
| Proprietário independente | Pode executar no próprio Evento independente conforme a policy, estando aprovado e sem vínculo ativo. |
| Pessoa comum | Pode ler monitoria e ler ou enviar justificativa da própria inscrição pelos contratos de titularidade. Seleção, presença, decisões gerenciais e encerramentos recebem 403. |
| Administrador | As capacidades de moderação administrativa continuam disponíveis em suas rotas específicas. A gestão operacional do Evento por administrador recebe 403. |

Evento aguardando aprovação ou em reanálise pode ser consultado por gestor autorizado. Seleção, presença, avaliações e encerramentos nessa fase retornam 409. Monitoria e justificativa também validam o estado do Evento e da Agenda. Cancelamentos continuam sujeitos a RN030. Depois da finalização, monitoria estudantil e justificativa ainda podem ser decididas segundo seus casos elegíveis e exigências de auditoria.

D01 passa a exigir Evento oficialmente finalizado. O registro é aceito do instante efetivo `finalizado_em` até o mesmo horário sete dias corridos depois, inclusive, para voluntário aprovado com presença confirmada em Agenda finalizada. Agendas precisam estar terminais. Uma avaliação por Pessoa e Evento, somente `nota` de 1 a 5. Evento sem carimbo verificável não recebe nova avaliação. A nota enviada continua consultável após o prazo. Encerramento, XP e certificado independem dessa avaliação facultativa.

Para RN021, a decisão D04/D05 do anexo autoriza a ONG proprietária a registrar a nota mesmo quando há organizador designado. A nota continua obrigatória por participante presente, única por Evento e com autoria persistida. A redação de RN021 que reserva a nota ao designado quando há um deve ser reconciliada na documentação normativa.

## Rotas e respostas

Os sucessos retornam `{success, message, data}`. Sessão ausente, inválida ou expirada retorna 401. Falta de autorização retorna 403. Identificador inexistente ou avaliação/justificativa ainda não enviada retorna 404 nas respectivas consultas. CSRF ausente ou inválido retorna 419. Validação do payload retorna 422 com `{message, errors}`. Conflito de estado, elegibilidade, prazo, lotação e repetição retorna 409 com `{success: false, message, errors: null}`.

| Domínio · operação · regra | Tela futura e rota | Payload | Resposta e guarda específica | Service |
| --- | --- | --- | --- | --- |
| F2 · Consultar inscrições · RN015/RN016/RN032 | Candidatos · `GET /gestao/agendas/{agenda}/inscricoes` | Query `page`, `per_page` de 1 a 100, padrão 20, e `status` opcional (`pendente`, `aprovado`, `reprovado`, `participando`, `participou`, `não participou`). | 200, `data.data` lista de `InscricaoResource` e metadados do paginador Laravel em `data`, incluindo `total`, `current_page`, `last_page`. Inclui projeção pública da Pessoa, resumo de confiabilidade, correspondências e carta no escopo de gestão privado. | `EventoParticipationService::listarInscritos` |
| F2 · Selecionar · RN015 | Candidatos · `PATCH /gestao/inscricoes/{inscricao}/selecao` | `status_inscricao` obrigatório (`aprovado`, `reprovado` ou `participando`). | 200, `InscricaoResource`. Evento/Agenda ativos, seleção humana, transições válidas e vagas da Agenda sob lock. `pendente` permite `aprovado` ou `reprovado`. `aprovado` permite `participando`. Validação de transição inicial 422, conflito concorrente ou incompatibilidade de estado 409. | `EventoParticipationService::selecionar` |
| F4 · Histórico próprio · RN017–RN019 | Minhas participações · `GET /minhas-participacoes/dados` | Query `page`. | 200, `data.data` com `titulo`, `status_evento`, `nota`, `avaliacao_url`, `avaliacao_ate` e `agendas` com `inscricao_id`, `inicio`, `situacao` e URLs condicionais para justificativa e monitoria. Paginador simples com `next_page_url` e `prev_page_url`. | `WebParticipationController::index` e `AvaliacaoService` |
| F4 · Presença/ausência · RN019 | Gestão de participações · `PATCH /gestao/inscricoes/{inscricao}/presenca` | `presenca` booleano obrigatório. `carga_horaria` e `observacao` proibidos. | 200, `InscricaoResource` com `status_inscricao`, `presenca`, `presenca_confirmada_em`, `carga_horaria` como decimal em texto ou nulo. Agenda iniciada, Evento/Agenda ativos e inscrição elegível. | `EventoParticipationService::registrarPresenca` |
| F4 · Consultar monitoria · RN020/RN029 | Gestão de participações e histórico · `GET /gestao/inscricoes/{inscricao}/monitoria` | Sem body. | 200, `inscricao_id`, `declarou_estudante`, `status` (`nao_aplicavel`, `pendente`, `validada`, `rejeitada`), `validado_em` ISO ou nulo. Titular ou gestor autorizado. | `MonitoriaService::consultar` |
| F4 · Decidir monitoria · RN020/RN029/D03 | Gestão de participações · `PATCH /gestao/inscricoes/{inscricao}/monitoria` | `confirmada` booleano obrigatório, `justificativa` até 2.000 caracteres, exigida em rejeição ou Agenda finalizada. | 200, mesma projeção da consulta. Declaração estudantil e presença confirmada. Registra autoria e justificativa sem alterar carga ou presença. | `MonitoriaService::decidir` |
| F4 · Consultar ocorrência · RN018/RN032 | Gestão de participações e histórico · `GET /gestao/inscricoes/{inscricao}/justificativa` | Sem body. | 200, `justificativa_id`, `inscricao_id`, `status`, `motivo`, `motivo_decisao`, `enviada_em`, `decidido_em`. Titular ou gestor autorizado. 404 sem protocolo. | `JustificativaParticipacaoService::consultar` |
| F4 · Enviar justificativa · RN018/RN019 | Histórico · `POST /gestao/inscricoes/{inscricao}/justificativa` | `motivo` obrigatório, até 2.000 caracteres. | 201, mesma projeção da consulta, `status: "pendente"`. Titular em ocorrência elegível de ausência ou desistência. | `JustificativaParticipacaoService::submeter` |
| F4 · Decidir justificativa · RN018/RN027/RN034 | Gestão de participações · `PATCH /gestao/inscricoes/{inscricao}/justificativa` | `deferida` booleano e `motivo` até 2.000 caracteres, obrigatórios. | 200, mesma projeção, `status: "deferida"` ou `"indeferida"`. Gestor autorizado, sem autodeferimento. Ocorrência original preservada. | `JustificativaParticipacaoService::decidir` |
| F4 · Consultar avaliações obrigatórias · RN021/RN024 | Encerramento · `GET /gestao/eventos/{evento}/avaliacoes` | Query `page` e `per_page` de 1 a 100, padrão 20. | 200, `data.pode_avaliar` e `data.participantes` com paginação simples e lista de participantes distintos com presença confirmada em Agenda finalizada. Campos por Pessoa: `pessoa_id`, `nm_pessoa`, `nota` inteira ou nula. | `EventoParticipationService::consultarAvaliacoesVoluntarios` |
| F4 · Avaliar voluntário · RN021/D04/D05 | Encerramento · `POST /gestao/eventos/{evento}/avaliacoes/{pessoa}` | `nota` inteira de 1 a 5. | 201, `avaliacao_voluntario_id`, `evento_id`, `pessoa_id`, `nota`. Evento ativo, Agendas terminais, presença confirmada em Agenda finalizada e nota única por Evento/Pessoa. | `AvaliacaoService::registrarVoluntario` |
| F4 · Consultar nota própria sobre ONG · RN022/D01 | Minhas participações · `GET /minhas-participacoes/{evento}/avaliacao-ong` | Sem body. | 200, `data.nota`. 404 sem nota própria. Consulta permanece após o prazo. | `AvaliacaoService::consultarOng` |
| F4 · Avaliar ONG · RN022/D01 | Minhas participações · `POST /minhas-participacoes/{evento}/avaliacao-ong` | `nota` inteira de 1 a 5. | 201, `data.nota`. Somente titular elegível em Evento oficialmente finalizado, no prazo. | `AvaliacaoService::registrarOng` |
| F4 · Encerrar Agenda · RN023/RN025 | Encerramento de Agenda · `PATCH /gestao/agendas/{agenda}/finalizar` | Sem body. | 200, Agenda com `status_ativo: "finalizado"`. Exige Evento/Agenda ativos, horário encerrado e presença/ausência resolvidas. Agenda vazia pode encerrar. Consolida XP de maneira idempotente na transação. | `EventoParticipationService::finalizarAgenda`, `XpService::concederAgenda` |
| F4 · Encerrar Evento · RN024/D02/D05 | Encerramento de Evento · `POST /gestao/eventos/{evento}/finalizar` | Sem body. | 200, `EventoResource` com `status_evento: "finalizado"`. Evento ativo, com pelo menos uma Agenda, todas terminais e todos os presentes avaliados. Persiste instante e ator. | `EventoParticipationService::finalizarEvento` |

Para ambas as avaliações, campos `comentario`, `texto` e `justificativa` preenchidos são rejeitados com 422. Trate cartas e motivos como dados restritos. A API `/api/v1` preserva os métodos, caminhos e projeções existentes, com a autorização e o prazo atualizados nos Services e Policies compartilhados. A seleção e o encerramento de Agenda registram a mudança e o ator na mesma transação que efetiva a operação, conforme RN034.

O histórico inclui inscrições aprovadas, em participação, com presença ou ausência registrada, desistências e Agendas canceladas do titular. `justificativa_url` é informada quando há protocolo ou a ocorrência permite envio. `enviar_justificativa_url` só é informada para ocorrência elegível sem protocolo. `monitoria_url` oferece consulta para inscrição com monitoria aplicável. Motivos e decisões são consultados no detalhe protegido. As rotas existentes continuam validando titularidade, permissão e estado a cada requisição.

Na consulta pelo gestor autorizado e na resposta da decisão Web, `data.decidido_por` é `null` enquanto pendente. Em uma decisão registrada, traz `tipo` (`pessoa` ou `ong`) e `nome` de exibição, que pode ser nulo se a conta estiver indisponível. O titular recebe situação, motivo e data da decisão. A autoria nominal fica restrita à gestão. As respostas Bearer preservam o formato anterior.

## Exemplos

`PATCH /gestao/inscricoes/42/presenca` com `{"presenca":true}` responde 200. `data` contém `status_inscricao: "participou"`, `presenca: true`, `presenca_confirmada_em` em ISO e `carga_horaria` como `"1.00"` se a Agenda declarou uma hora.

`POST /minhas-participacoes/18/avaliacao-ong` com `{"nota":5}` responde 201 com `{"success":true,"message":"Avaliação enviada.","data":{"nota":5}}`.

## Evidências

`Bloco9WebEventParticipationTest` cobre inspeção privada, seleção, matriz de autorização, presença da ONG e do designado, monitoria, justificativa, avaliação e encerramentos por sessão. `Bloco81WebParticipationTest` cobre projeção e envio D01 por sessão. `AvaliacaoConfiabilidadeTest`, `PresencaAusenciaDesistenciaTest` e `FinalizacaoEventoCertificadoTest` preservam regressões Bearer, incluindo limite inclusivo, duplicidade, definição de autoria e XP/certificado. `IdentityBoundaryTest` confirma que a ONG ativa preserva o direito de operar seus próprios Eventos durante a designação e que a ONG pendente ou sem titularidade recebe a negativa da policy.

A regressão focada dos sete arquivos Feature de avaliações, presença, finalização, operação de Evento, organizadores e Participação Web passou no MySQL descartável com 82 testes e 1.053 asserções. O reteste reutilizou somente o schema isolado com todas as migrations conferidas e dados de testes revertidos. [QA independente](QA_INDEPENDENTE.md)
