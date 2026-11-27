# QA independente — Reteste de QA-04 e QA-05 — 26/11/2026

## A. Resumo executivo

**APROVADO COM RESSALVAS.** O reteste confirmou QA-04 e QA-05 no checkout local. As regressões de criação, Agendas, publicação, capas, candidaturas e seleção passaram nos cenários executados. As ressalvas são o delta ainda sem commit, o escopo da retomada por sessão e a projeção pública de Agendas.

## B. Estado do repositório

A auditoria usou a branch `feat/eventos-candidaturas`, HEAD `6a8aa17912eccc143dc6d6d670a820b2bdcdae08`, com o delta de QA-04 e QA-05 ainda local. `git ls-remote` confirmou `74609bfd03cfbdbfa8dbfdcaf603c5dabd91794e` nas referências remotas de `dev` e Eventos. O único worktree e o stage vazio foram preservados. O checkout já tinha `.env.example` removido, `package-lock.json` modificado e artefatos locais não rastreados, incluindo os relatórios anteriores, `composer.phar` e `EVIDENCIAS.json`.

O conteúdo anterior à auditoria foi conferido em Controller, rotas, módulo de gestão, formulário Blade, contrato F2 e testes Feature e JavaScript. Os sete arquivos do delta conferem byte a byte com o ZIP de correções, SHA-256 `285375c13471b546fb7089c7c7880688cefee2fccbb0781f8cb9404c87b92e73`. O reteste também revisou os Services de Agenda, Evento, candidatura e seleção na base. A única intervenção no repositório nesta execução foi documental.

Referências usadas: [catálogo Web](CATALOGO_WEB.md), [contrato F2](CONTRATOS_F2_EVENTOS.md), [contrato de participação](CONTRATOS_PARTICIPACAO.md), [matriz de integração](MATRIZ_INTEGRACAO.md), [prontidão](PRONTIDAO.md), [último reteste](QA_INDEPENDENTE_ENRICO_RETESTE_2026-11-26.md) e [relatório das correções](CORRECOES_QA_04_05_ENRICO_2026-11-26.md). O PDF oficial indicado em `AGENTS.md` estava ausente.

### SHA-256 dos arquivos do delta auditado

| Arquivo | SHA-256 |
| --- | --- |
| `app/Http/Controllers/WebEventManagementController.php` | `c211603666c03d3cf299fa5c7441c6d72e86f6fab905ae5726f0544087e17874` |
| `docs/bloco-9/CONTRATOS_F2_EVENTOS.md` | `db88aea57c981c78494781618f87666535cb710d7de05aeff193ce66a35f8ad6` |
| `resources/js/modules/managed-events.js` | `49b3e8e122635f82a65f0175826c81317855344ec863b041c823b78f978e6c9a` |
| `resources/views/ong/criar.blade.php` | `3c3fedff71a980e0976e82f0ec162ea52647bc0dd32fc267bbfb3305067f6109` |
| `routes/web-event-management.php` | `1ea5e6791f6ec7615b15217c076bb6a83603b57b00d7e58ff6d403d28b00b6a5` |
| `tests/Feature/Bloco9EventManagementTest.php` | `89951a378fcc3880c90ee3a9d88ed98212f618539218251976e831179f3881a3` |
| `tests/Frontend/managed-events.mjs` | `c385c3ad4f3a8e89623ad1043d973c8f8b874825e276696f51f390d65b380fb6` |

## C. Matriz de funcionalidades

| Funcionalidade | Evidência independente | Situação |
| --- | --- | --- |
| Criação Web e D02 | ONG e organizador independente criaram Eventos com Agendas no MySQL. Evento sem Agenda retornou 422. Suite PHP verificou rollback da segunda Agenda e do objeto de imagem. | Validado. |
| QA-01 e QA-05 | Reenvio com chave igual devolveu o mesmo ID com armazenamento normal e com quota bloqueada. Payload diferente devolveu 409. Reserva e confirmação perdidas foram recuperadas. | Validado dentro da mesma sessão e da chave recuperada. |
| QA-04 e RN012/RN013/RN018 | Chromium salvou uma Agenda com segundos sem PATCH. Alterar vagas enviou só a capacidade e preservou os horários. Alterar o término enviou apenas o horário editado e registrou a isenção aplicável. | Validado. |
| Publicação e descoberta | Evento pendente fora da busca e detalhe 404. Moderação liberou o Evento, busca e modalidade funcionaram. Título preservou publicação, mudança sensível retirou o detalhe e bloqueou nova candidatura. | Validado. Abrangência da projeção pública continua em aberto. |
| QA-02 | Capa legada relativa e capa gerenciada foram exibidas em 360, 768 e 1280 px. Sem capa, o detalhe manteve o fundo. | Validado. |
| Candidatura | HTTP 201 com e sem carta. Duplicidade e reanálise 409. Chromium percorreu as quatro etapas, mostrou erro de 503 e gravou na tentativa real. O histórico persistiu após recarga. | Validado. |
| Seleção e lotação | Aprovação e recusa persistiram. Duas vagas na Agenda A foram preenchidas e uma terceira aprovação recebeu 409. Agenda B continuou aceitando candidatura. | Validado. Última vaga concorrente preservou uma aprovação. |
| QA-03, match e histórico | Chromium mostrou score 80 e estado em formação sem score, fora da linha de match. A carta apareceu para os atores previstos. | Validado no recorte. |

## D. Auditoria dos testes existentes

A suíte Feature verifica autorização de criação, reenvio com a mesma chave, conflito com payload diferente, rollback de Agenda e imagem, escopo da nova rota e limpeza da chave somente quando corresponde à tentativa atual. O PHP usa sessões montadas e armazenamento fake em cenários de imagem. As regras de reanálise, horários, ocupação, compatibilidade e histórico são exercitadas diretamente pelos testes de serviço e endpoint.

Os testes JavaScript de formulário usam objetos artificiais. Eles confirmam o payload parcial com segundos preservados e a detecção de falha do armazenamento. O cliente HTTP usa respostas simuladas para erros. Os percursos do Chromium e as conexões PHP independentes exercitam aspectos que passam fora dessa cobertura, incluindo UI montada, perda de respostas reais e concorrência.

## E. Defeitos e verificações de correção

**QA-04 confirmado corrigido.** A fixture tinha início `2026-12-01T02:52:37Z`, fim `2026-12-01T03:52:37Z`, Evento ativo e uma candidatura existente. Salvar sem tocar nos campos emitiu zero PATCH. O detalhe público continuou em HTTP 200 e a isenção permaneceu nula. Atualizar somente as vagas para 5 enviou `{"vagas_agenda":5}`. O Evento entrou em reanálise por essa mudança real, os dois instantes continuaram íntegros e a isenção permaneceu nula. Ao editar apenas o término, o frontend enviou somente `data_fim` e o serviço registrou a isenção usando o início original, com segundos preservados. [Módulo de gestão](../../resources/js/modules/managed-events.js#L67).

**QA-05 confirmado corrigido no cenário reproduzido.** Chromium simulou `QuotaExceededError` ao gravar a chave da tentativa e descartou a resposta 201 após o commit. A sessão Laravel conservou a chave ao reabrir o editor. Reenviar os mesmos dados devolveu o mesmo ID e deixou um único Evento. O clique explícito em **Iniciar outro Evento** permitiu nova intenção e outro ID.

**Dois limites adicionais de rede foram exercitados.** Ao perder a resposta 200 da reserva, o banco ficou com zero Eventos. Reabrir recuperou a chave já reservada e permitiu concluir o cadastro, chegando a um Evento. Ao interromper a requisição de confirmação após o 201, a sessão preservou a tentativa. Reabrir e reenviar retornou o mesmo ID, fechou a tentativa após a confirmação e manteve um Evento no banco. [Reserva e confirmação](../../app/Http/Controllers/WebEventManagementController.php#L46).

Os controles de QA-01, QA-02 e QA-03 passaram. Nenhum novo defeito crítico ou alto foi reproduzido nos cenários exercitados.

## F. Lacunas e divergências

- O detalhe público ainda mostra a disponibilidade de Agenda apenas ao voluntário aprovado. O contrato vigente reserva a ele o endpoint de elegibilidade. A projeção pública por outros atores segue sem decisão de produto específica.
- A chave nominal vive na aba e a retomada alternativa depende de sessão Laravel ativa. A abertura de uma nova sessão de login fica fora dessa garantia. Os campos e o banner são reenviados pelo gestor na retomada.
- Paginação de listas extensas, navegação completa por teclado em modais, expiração por tempo de sessão, PHP 8.4 e armazenamento remoto não foram homologados nesta rodada.
- D02 exige Agenda na criação. D04/D05 se sobrepõe à descrição histórica sobre criação institucional pelo designado. As restrições das vagas por Agenda e os efeitos da reanálise no contrato atual guiaram os testes.

## G. Segurança e privacidade

Os testes cobriram a fronteira Web autenticada, CSRF de escrita, papéis de criação e seleção, titularidade e projeção reduzida. O caso HTTP sem CSRF recebeu 419. A candidatura recusou ONG, organizador e Pessoa pendente. A carta sintética apareceu na consulta do titular e na gestão autorizada, e ficou ausente no histórico de outra Pessoa. A rota binária de imagem gerenciada devolveu 404 antes da publicação. Nenhuma exposição de dados pessoais foi confirmada no recorte.

## H. Integridade e concorrência

Duas sessões Web em processos PHP distintos enviaram multipart com a mesma intenção. MySQL observou duas transações esperando o bloqueio da ONG. As respostas foram 201 e 201 com mesmo ID. A consulta persistida encontrou um Evento, duas Agendas e uma intenção. Outra chave para os mesmos dados criou outro Evento. Os dois Eventos ficaram associados a dois objetos de imagem no storage local de QA.

Na seleção, o bloqueio da linha de Evento permitiu observar duas transações aguardando a última vaga. A liberação produziu 200 e 409, com uma candidatura aprovada e uma pendente no banco. O rollback induzido do PHPUnit preservou atomicidade de Evento, Agendas, intenção e compensação da imagem. A alteração parcial das vagas preservou os instantes gravados e os efeitos previstos sobre a candidatura.

## I. Interface e usabilidade

O Chromium verificou DOM, rede e persistência após recarga. As capas apareceram em 360, 768 e 1280 px. A confiabilidade formada e em formação teve apresentação distinta da compatibilidade. No stepper de candidatura, voltar preservou a carta digitada, o envio sem carta foi aceito e o 503 simulado manteve o usuário na revisão. A confirmação apareceu após o 201 real e persistiu no histórico. A execução não produziu capturas nem homologação visual.

## J. Ambiente e comandos

- PHP 8.5.9, Node 24.16.0, Chromium 158, Vite 8.2.1 e MySQL 8.4.3. A instância descartável usou `127.0.0.1:33429`, UUID `9e9f38a9-e9f9-11f1-b7ef-0250de5958d2`, datadir temporário e schemas novos `ajudae_qa9_final_tests` e `ajudae_qa9_final_web`. A conexão Laravel confirmou schema, porta e UUID antes da execução. As fixtures eram sintéticas.
- `php vendor/bin/phpunit --do-not-cache-result --filter 'Bloco9EventManagementTest|Bloco9WebEventParticipationTest|EventoOperacionalTest|AgendaInscricaoTest|OngAuthAndEventoTest|Bloco81WebEventModerationTest|OrganizadorWorkflowTest|PessoaValidationAccessTest|AvaliacaoConfiabilidadeTest|ManagedImage'` aprovou **112 testes e 1.226 asserções**, sem ignorados.
- `node --test` no frontend, excluindo o renderizador Blade, aprovou **104 testes**. O renderizador recebeu o manifesto temporário em ensaio separado e analisou três scripts com sucesso.
- `npm run build -- --outDir <temporário>` na cópia com o lockfile do commit e as alterações locais de frontend compilou **38 módulos**. O Chromium usou esse build.
- `php artisan route:list -v --path=gestao` confirmou 27 entradas de gestão. `git diff --check` passou. A primeira subida Web retornou 500 porque o diretório de sessões temporário estava ausente. Recriei o diretório de QA e repeti os testes com HTTP 200 no formulário de login.

Percursos Web exercitados incluem `GET /feed`, `GET /eventos/{id}`, `GET /api/v1/eventos/{id}/imagem`, `GET /ong/eventos/criar`, `GET /ong/eventos?evento={id}`, `GET /ong/candidatos?evento={id}`, `POST /gestao/eventos`, `GET|POST|DELETE /gestao/eventos/intencao`, `PATCH /gestao/agendas/{agenda}`, `GET /candidatura?evento={id}`, `POST /candidatura/{evento}/enviar`, `GET /minhas-candidaturas/dados`, `GET /gestao/agendas/{agenda}/inscricoes`, `PATCH /gestao/inscricoes/{inscricao}/selecao` e `PATCH /admin/fila-eventos/{evento}/decisao`.

## K. Integração

O delta ainda precisa estar em uma revisão versionada para que a integração com `dev` possa usar a mesma versão auditada. Identidade Web e moderação continuam sendo dependências. As rotas de intenção compartilham sessão, autorização e CSRF existentes. A API Bearer mantém seu contrato. A validação usou armazenamento local e banco sintético, com S3 e Railway fora da execução.

## L. Veredito

**APROVADO COM RESSALVAS.** Os dois defeitos anteriormente abertos passaram no reteste independente, junto dos controles QA-01 a QA-03. A aprovação técnica se aplica aos bytes locais identificados neste relatório. A inclusão em `dev` depende de versionar esse delta. Permanecem as lacunas de cobertura declaradas e a questão de projeção pública da disponibilidade por Agenda.

Sem alteração no código da aplicação, commit, push, merge ou deploy nesta auditoria. A entrega é documental.

### Decisões para você responder

Nenhuma.
