# QA independente — Enrico, Bloco 9 — 27/11/2026

## A. Resumo executivo

**APROVADO COM RESSALVAS.** A disponibilidade pública por Agenda foi confirmada em nova execução HTTP e no Chromium. O ensaio concorrente manteve o limite de vagas. As regressões focais passaram. Foi encontrado um defeito de interface de baixa severidade ao navegar para candidatura sem Agenda selecionável. O veredito vale para os arquivos deste checkout, incluindo correções locais ainda ausentes do commit.

## B. Estado do repositório

- Branch e HEAD inicial e final `feat/eventos-candidaturas`, `6a8aa17912eccc143dc6d6d670a820b2bdcdae08`. Um worktree, stage vazio.
- O GitHub informou `74609bfd03cfbdbfa8dbfdcaf603c5dabd91794e` para `dev` e para a branch de Enrico. Não havia referência `dev` local. Comparação com `origin/dev` deu 0 commits atrás e 1 à frente. O commit local altera 19 arquivos. O delta rastreado de trabalho afeta 13, incluindo alterações de ambiente e dependências anteriores à auditoria.
- O diff rastreado teve o mesmo identificador Git antes e depois, `4d57f2d6498d825e0e73488f1fdf0d3a731bc8ab`. A auditoria acrescentou este relatório ao checkout e preservou o [relatório de 26/11](QA_INDEPENDENTE_ENRICO_2026-11-26.md). Sem staging, commit, push, merge ou deploy.
- Revisados o catálogo Web, a matriz, a prontidão, os contratos F2 e de participação, o resumo versionado RN010–RN018, o relatório de desenvolvimento e as rodadas anteriores. O PDF oficial estava indisponível no caminho registrado no projeto.
- Artefatos auditados incluem `routes/web-event-management.php`, `routes/web-event-participation.php`, controllers Web e público, Services de Eventos, Agendas, candidaturas e seleção, `Evento`, `Agenda`, Resources, validação e políticas, as views de feed e detalhe, o módulo `managed-events.js` e os testes Feature e Frontend da tabela abaixo.

## C. Matriz de funcionalidades

| Funcionalidade | Implementada e integrada | Verificação desta rodada | Situação e pendência |
| --- | --- | --- | --- |
| Criar e editar Evento, D02/RN010–012 | Sim, sessão Web e serviço de domínio | ONG e independente criaram via HTTP. Nenhuma Agenda retornou 422. Feature cobriu titularidade, ONG inativa, designação e rollback na segunda Agenda. | Passou no recorte. Moderação depende da capacidade de administração. |
| Agendas, capacidade e publicação RN011–013 | Sim, backend e MySQL | 2 e 5 vagas no mesmo Evento. Duas aprovações em A, terceira tentativa 409, B continuou aberta. Título manteve detalhe 200. Alterar fim de B retirou o detalhe e gerou isenção para inscrição existente. | Passou no recorte. |
| Feed e detalhe | Sim, Blade lê banco | Busca e modalidade, exclusão de pendentes e reanálise. Com 13 Eventos elegíveis, 12 apareceram na primeira página e um na segunda, sem duplicação. A modalidade e a busca permaneceram no link, e o filtro sem resultado não mostrou cards. HTML público com datas, vagas, lotação, janela encerrada e Agenda cancelada. | Disponibilidade pública confirmada em cinco contextos. A ação de candidatura tem a ressalva QA-06. |
| Fluxo de candidatura RN014 | Sim, formulário e serviço | Pessoa aprovada enviou HTTP 201 com e sem carta. Duplicidade 409. Pendente, ONG e organizador 403. Na ausência de sessão e token, a escrita retornou 419. Chromium conferiu as quatro etapas e preservou a carta ao voltar. Falha 503 manteve a etapa de revisão e mostrou erro. Novo envio sem carta retornou 201, e o histórico persistiu após recarregar. | Passou nos controles de gravação e autorização executados. |
| Seleção humana RN015 | Sim, PATCH, política e transação | Consulta isolada por Agenda, aprovação, lotação, recusa e preservação do registro. Teste concorrente Web com duas sessões e dois processos PHP. | Uma aprovada e uma pendente na última vaga. |
| Match e histórico RN016–018 | Sim, projeções de gestão e titular | Match zero na candidatura pendente. Feature testa IDs compatíveis, confiabilidade separada, janela exata e desistência. HTTP conferiu carta apenas nos contextos exercitados e preservação na reanálise. | Passou no recorte. O ciclo completo de presença e encerramento pertence à frente de participação. |

## D. Auditoria dos testes existentes

| Verificação | Avaliação |
| --- | --- |
| [Criação, intenções e titularidade](../../tests/Feature/Bloco9EventManagementTest.php#L24) | Cobertura efetiva de status HTTP e persistência via MySQL. Os casos Web montam sessão em teste. Login real e CSRF foram exercitados pelo ensaio HTTP. A escrita de imagem usa disco fake em parte da suíte. |
| [Disponibilidade pública](../../tests/Feature/Bloco9EventManagementTest.php#L240) | O teste confere estados, contagem e privacidade em cinco contextos. Asserções de texto e atributos validam o HTML inicial. A resposta privada e a degradação em 503 exigem teste de navegador. |
| [Rollback na segunda gravação](../../tests/Feature/Bloco9EventManagementTest.php#L337) | Falha induzida no evento Eloquent. Evidência de atomicidade no banco e de compensação no disco fake. A duração e a capacidade usam validação e serviços reais. |
| [Regras operacionais](../../tests/Feature/EventoOperacionalTest.php#L149) e [participação Web](../../tests/Feature/Bloco9WebEventParticipationTest.php#L111) | Cobertura efetiva de janela, capacidade, decisão, match, escopo de gestão e persistência. Aprovações sequenciais em Feature deixam a concorrência para teste com processos separados. |
| [Módulo de gestão](../../tests/Frontend/managed-events.mjs#L24) e `api-client.test.mjs` | Cobrem payload sem titularidade, preservação de segundos, idempotência, imagem, confiabilidade e erros HTTP. Dependem de objetos sintéticos e mocks do cliente. Cobertura parcial de estado visual, recarga e perda de resposta. |
| Relatórios anteriores | 104 testes PHP nas duas baterias iniciais, depois 110 e 112 em retestes, e 113 no teste de implementação da projeção pública. Cada total pertence ao filtro e checkout da rodada. A corrida desta auditoria foi executada de novo e observou duas esperas no MySQL. |

## E. Defeitos reproduzidos

### QA-06 — Ação de candidatura continua ativa quando a única Agenda está lotada

**Severidade baixa.** Fluxo de detalhe público para candidatura de voluntário aprovado. O backend continuou protegendo a vaga.

1. Publique um Evento sintético com uma Agenda de uma vaga e aprove uma candidatura. No ensaio, foi o Evento **QA concorrência última vaga**.
2. Entre como outro voluntário aprovado, sem candidatura nessa Agenda, e abra o detalhe.
3. Confirme o aviso de lotação e acione **Escolher uma Agenda**. A ativação foi feita com Enter no Chromium.
4. O formulário abriu em **Escolha sua Agenda**, com uma opção desabilitada e zero opções selecionáveis.

**Esperado.** O detalhe comunica que não há Agenda disponível e apresenta a ação de escolha como indisponível neste estado.

**Encontrado e impacto.** O detalhe mostra lotação e mantém um botão ativo que conduz a uma etapa sem possibilidade de avançar. O voluntário sai do detalhe para descobrir a mesma indisponibilidade. A interface de candidatura manteve o bloqueio.

**Evidência.** `evidencias/cta-agenda-lotada.png`, `evidencias/formulario-agenda-lotada.png` e `evidencias/sold-out.json` no pacote. A view condiciona o link apenas à elegibilidade em [detalhe.blade.php](../../resources/views/eventos/detalhe.blade.php#L348). A lista desabilita corretamente a opção com impedimento em [application.js](../../resources/js/modules/application.js#L53).

**Correção recomendada.** Usar a disponibilidade já calculada no detalhe para desabilitar ou ocultar a ação de escolha quando nenhuma Agenda aceita candidatura. Se a consulta de impedimentos pessoais estiver disponível, aplicar o mesmo critério aos impedimentos do voluntário. Conservar a validação pelo servidor.

Os bloqueadores anteriores QA-01 a QA-05 tiveram nova cobertura focada de chave, sessão, horário, capa e confiabilidade. A retomada após perda de HTTP 201 com quota da aba indisponível permanece com evidência Chromium do [reteste de 26/11](QA_INDEPENDENTE_ENRICO_VALIDACAO_QA_04_05_2026-11-26.md). Nesta execução essa frente foi verificada pelos testes de chave/sessão e de horário. Nenhum defeito crítico ou alto foi reproduzido.

## F. Lacunas, riscos e divergências

- O código auditado inclui QA-04, QA-05 e a projeção pública como alterações locais. A branch remota ainda aponta à base e o commit local contém apenas parte do estado auditado.
- O contrato limita a retomada da criação à chave disponível na aba ou à sessão Laravel. A recuperação após perder as duas permanece fora do contrato atual. A suíte Web não cobre expiração real de sessão ao longo de todo o formulário.
- D04/D05 restringem criação institucional à ONG e criação independente a organizador sem vínculo vigente. A divergência com a versão resumida original de RN008/RN011 está registrada no contrato F2. Controle de vagas permanece por Agenda, com `vagas_evento` informativo.
- O modo integrado foi exercitado com PHP 8.5.9, Node 24.16.0, MySQL 8.4.3 e Chromium 158. PHP 8.4 do destino, S3/Railway e desempenho com grande volume ficaram fora do ensaio. O renderizador isolado de Blade em modo demonstrativo ficou fora dos 104 testes JavaScript. Interface administrativa e outros fluxos F4 foram avaliados no recorte de integração e por testes automatizados pertinentes.

## G. Segurança e privacidade

O backend derivou a titularidade pelas identidades de teste. As verificações de política e de papel cobriram ONG ativa, organizador independente, designação válida, conta pendente e escopo de gestão por registro. A verificação HTTP da criação com sessão válida e sem prova CSRF retornou 419. A consulta pessoal sem sessão retornou 401. Cartas sintéticas e motivo interno de cancelamento ficaram ausentes da página pública. A gestão e histórico titular mantiveram as projeções autorizadas. A revisão foi defensiva e local.

## H. Integridade e concorrência

O schema efetivo de cada teste foi conferido pelo Laravel, com porta 33429 e UUID `9e9f38a9-e9f9-11f1-b7ef-0250de5958d2`, em diretório temporário e interface de loopback. Foram usados `ajudae_qa9_audit_tests` e `ajudae_qa9_audit_web`, novos nesta rodada e com dados sintéticos. No teste concorrente, uma transação de QA reteve o lock da linha do Evento, enquanto duas sessões válidas tentavam aprovar candidatos diferentes à única vaga. O MySQL exibiu dois solicitantes simultâneos aguardando. Após liberar o lock, as respostas foram 200 e 409. A consulta independente ao banco contou um aprovado e um pendente. O HTML público após a corrida informou lotação.

## I. Interface e usabilidade

Chromium conferiu o detalhe público a 360, 768 e 1280 px. O DOM mediu 345, 753 e 1265 px e oito elementos de horário no caso de quatro Agendas. A 1280 px, ONG, organizador e Pessoa pendente mantiveram as mesmas informações. Voluntário aprovado recebeu o impedimento pessoal. Com resposta 503 simulada nessa consulta, o HTML público e os oito horários de início e fim foram preservados. As três capturas públicas foram inspecionadas, sem corte nos cartões de Agenda. A navegação por Enter no link do detalhe foi exercitada no caso QA-06. A revisão de acessibilidade foi focal.

## J. Testes executados

| Verificação | Resultado |
| --- | --- |
| `php artisan route:list -v --except-vendor` | Rotas Web e API conferidas no modo integrado. `GET /feed`, `GET /eventos/{id}`, `GET /candidatura?evento={id}`, `POST /candidatura/{evento}/enviar`, `POST /gestao/eventos`, `PATCH /gestao/agendas/{agenda}` e `PATCH /gestao/inscricoes/{inscricao}/selecao`. |
| `php vendor/bin/phpunit --do-not-cache-result --filter 'Bloco9EventManagementTest\|Bloco9WebEventParticipationTest\|EventoOperacionalTest\|AgendaInscricaoTest\|OngAuthAndEventoTest\|Bloco81WebEventModerationTest\|OrganizadorWorkflowTest\|PessoaValidationAccessTest\|AvaliacaoConfiabilidadeTest\|ManagedImage'` | 113 aprovados, 1.260 asserções e zero ignorados. |
| `node --test` nos arquivos JavaScript de `tests/Frontend`, exceto o renderizador Blade | 104 aprovados. A primeira execução falhou ao localizar `php` para a fixture de organizadores. O novo processo recebeu o diretório do executável e a suíte passou. |
| Vite 8.4.0, build com saída temporária | 38 módulos. O servidor Web respondeu 500 enquanto o manifesto ainda não existia durante a preparação. As verificações HTTP foram executadas com o manifesto gerado. |
| HTTP e Chromium em 127.0.0.1 | Publicação, filtros, vagas, autorização, reanálise, concorrência e os cenários de interface acima. O Chromium reiniciou após erro do processo GPU e foi usado com perfil de QA dedicado. |
| `git diff --check` | Aprovado. Git emitiu avisos de conversão CRLF/LF para dois arquivos preexistentes. |

## K. Integração

A aprovação administrativa depende da frente de Oliver. O significado e a autoria dos estados de seleção afetam a participação e o histórico da frente de Luiz Miguel. A sessão Web depende de identidade. Os pontos compartilhados mais sensíveis são o cliente HTTP, o inicializador de identidade, o painel da ONG e os contratos de participação. A migration de idempotência do commit local foi aplicada apenas ao MySQL descartável desta rodada. A reunião do delta com `dev` depende de uma revisão que inclua os arquivos efetivamente auditados.

## L. Veredito final

**APROVADO COM RESSALVAS.** A pendência anterior de leitura pública das Agendas está encerrada nos cenários testados. O fluxo preservou publicação, vagas, autorização e histórico sob a regressão executada. O único achado novo reproduzido foi a ação em uma tela sem opção selecionável, com o controle de capacidade funcionando. O parecer se aplica ao checkout auditado. Os commits disponíveis no GitHub ainda não contêm esse estado.

### Decisões para você responder

Nenhuma.
