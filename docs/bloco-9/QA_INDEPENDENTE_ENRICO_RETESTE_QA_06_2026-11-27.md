# QA independente — Reteste de QA-06 e regressão do Bloco 9 — 27/11/2026

## A. Resumo executivo

**APROVADO COM RESSALVAS.** O QA-06 foi confirmado como resolvido. A regressão de criação, publicação, Agendas, candidatura e seleção passou nos cenários executados. Nenhum novo defeito foi reproduzido. O parecer se aplica ao checkout local com a correção de QA-06, a disponibilidade pública por Agenda e outras correções ainda fora de commit.

## B. Estado do repositório

- Branch `feat/eventos-candidaturas`, com um único worktree e HEAD `6a8aa17912eccc143dc6d6d670a820b2bdcdae08`.
- O GitHub respondeu `74609bfd03cfbdbfa8dbfdcaf603c5dabd91794e` para `dev` e para `feat/eventos-candidaturas`. A comparação com `origin/dev` encontrou 0 commits atrás e 1 à frente. O commit local afeta 19 arquivos.
- Foram auditados o commit local e as alterações rastreadas já presentes em 13 arquivos. O identificador do diff rastreado antes e depois foi `eeb1d6565c2f31e27d15eca3e65c5949026367c2`. O stage permaneceu vazio.
- A auditoria preservou o relatório pedido pelo anexo, [QA de 26/11](QA_INDEPENDENTE_ENRICO_2026-11-26.md), e a [auditoria de 27/11 antes da correção](QA_INDEPENDENTE_ENRICO_2026-11-27.md). Criou somente este relatório no checkout. Sem staging, commit, push, merge ou deploy.
- Fontes consultadas: [catálogo Web](CATALOGO_WEB.md), [matriz de integração](MATRIZ_INTEGRACAO.md), [checklist](PRONTIDAO.md), [gestão F2](CONTRATOS_F2_EVENTOS.md), [participação](CONTRATOS_PARTICIPACAO.md), [relatório da correção QA-06](CORRECAO_QA_06_ENRICO_2026-11-27.md) e resumo versionado RN010–RN018. O PDF oficial estava indisponível no caminho indicado.
- Revisados os controllers de gestão, público, candidatura e seleção, os Services correspondentes, as rotas Web, políticas e testes focais. Para QA-06, foram conferidos a [view](../../resources/views/eventos/detalhe.blade.php#L347), o [módulo JS](../../resources/js/modules/managed-events.js#L542) e a [regressão Feature](../../tests/Feature/Bloco9EventManagementTest.php#L240).

| Arquivo da correção QA-06 | SHA-256 auditado |
| --- | --- |
| `resources/js/modules/managed-events.js` | `2ad412b68968a6da5ed7261cafa829d39e0236d67fcf98f2f9b699c54066acd5` |
| `resources/views/eventos/detalhe.blade.php` | `4b0a8f515a4da392bb3e2fb242c90af96bec93efc1460eced609fa9358f791fb` |
| `tests/Feature/Bloco9EventManagementTest.php` | `6bbcb4e28408dc8b11a7600be70bbaa5d9ef864e6fcb93381bdd3fc3baebbbfd` |

## C. Matriz de funcionalidades

| Funcionalidade | Integração e cenários desta rodada | Situação |
| --- | --- | --- |
| Gestão de Eventos RN010–012 e D02 | HTTP e PHPUnit. ONG ativa e organizador independente, obrigatoriedade da Agenda, estado criado, escopo institucional, reversão na segunda inserção. | Passou. A corrida de criação com a mesma chave devolveu o mesmo Evento, com duas Agendas. O banner gerenciado recebeu 404 antes da publicação e 200 após moderação. |
| Agendas RN013 | Capacidade individual de 2 e 5. A terceira aprovação em A recebeu 409, e B continuou operando. Suite focal de edição, cronologia e limites. | Passou nos cenários exercitados. A lotação de A não impediu candidatura em B. |
| Publicação e exploração pública | Busca persistida e filtro por modalidade. Detalhe pendente 404 e publicado 200. Título manteve publicação, horário disparou reanálise e preservou cadastro e isenção. | Passou nos cenários exercitados. Agendas públicas em cinco contextos, com carta e motivo interno ausentes no detalhe. |
| Candidatura RN014 e QA-06 | HTML inicial, estados públicos, conta e impedimento pessoal, janela, lotação, perda da consulta pessoal e navegação para opção possível. Candidatura com e sem carta, 403 por papel, 409 em duplicidade. | QA-06 resolvido. Ação desabilitada sem opção elegível, abertura por Enter quando disponível e persistência no histórico após resposta 201. |
| Seleção RN015 | Aprovação, recusa, repetição e corrida real por uma vaga, em duas sessões e dois processos Web. | Passou. HTTP 200 e 409, um aprovado e um pendente no banco. |
| Match e histórico RN016–018 | Correlacionou match informativo, carta reservada, estados e histórico. Cobertura da base para contagens de IDs, confiabilidade, janela exata e desistência. | Passou no recorte. Match zero permaneceu pendente até decisão humana; recusa manteve o registro. |

## D. Auditoria dos testes

- **HTML de QA-06.** O Feature exercita o detalhe publicado com dados e consulta persistida. Asserções de atributos mostram que o link está oculto quando a disponibilidade pública termina. O teste isolado de HTML não mede foco, CSS nem a reação a dados pessoais. O ensaio de navegador verifica esses efeitos. [Bloco9EventManagementTest](../../tests/Feature/Bloco9EventManagementTest.php#L303).
- **Propriedade e criação.** Os casos Feature constroem sessões e exercitam rotas, validação, banco e compensação. Parte das imagens usa disco fake e parte do rollback usa falha Eloquent induzida. O cenário HTTP usa login, CSRF e processos Web separados.
- **Vagas e privacidade.** A bateria de regras testa limite, prazos, segregação por Agenda, projeções de gestão e histórico do titular. Os casos sequenciais foram complementados pela corrida independente contra MySQL real.
- **Frontend.** A suíte de funções cobre normalização, imagem, intenção, precisão de horários, confiabilidade e cliente HTTP. Asserções unitárias de objetos e requisições simuladas têm cobertura parcial de DOM. O fluxo de candidatura no Chromium cobre transições, erros e recarga.
- **Evidência histórica.** QA-01–05 têm ensaios dedicados nos relatórios anteriores. A nova execução repetiu a bateria focal, os percursos HTTP, a seleção concorrente e o QA-06. Os números de cada rodada correspondem ao próprio checkout e conjunto de testes.

## E. Achados

**QA-06 resolvido no reteste.** Um voluntário sem candidatura abriu um Evento cuja única vaga estava preenchida. O HTML exibiu **Nenhuma Agenda disponível para candidatura**. O controle visível estava desabilitado e o link ficou fora do foco. Com o módulo de identidade bloqueado, o HTML manteve o bloqueio no caso lotado e ofereceu a ação no Evento com vaga. Com a consulta pessoal ativa, o voluntário já inscrito em todas as opções disponíveis teve a ação desabilitada. O caso misto abriu o formulário via Enter com uma opção elegível. A correção foi conferida em `GET /eventos/{id}`.

Nenhum novo defeito foi reproduzido no recorte executado. As lacunas conhecidas de portabilidade e integração constam nas seções seguintes.

## F. Lacunas e divergências

- A recuperação de criação depende da chave disponível na aba ou na sessão Laravel. S3, Railway, PHP 8.4 do destino, expiração real de sessão em cada etapa e carga de produção ficaram fora do ensaio.
- Se a consulta pessoal falhar, o detalhe usa a disponibilidade pública. Nesse estado, a interface pode oferecer o link a quem já se inscreveu se houver vaga pública. A tela de candidatura consulta o estado de novo e o servidor aplica a regra pessoal na gravação.
- D04/D05 restringem a criação institucional à ONG. A divergência com a formulação histórica de RN008/RN011 consta no contrato F2. O limite continua por Agenda, com `vagas_evento` informativo.
- Renderer Blade dos testes Frontend ficou fora da bateria JavaScript. Encerramento, monitoria e homologação administrativa pertencem às frentes correspondentes. O escopo desta rodada cobriu a fronteira de Eventos, candidaturas e seleção.

## G. Segurança e privacidade

O ensaio HTTP confirmou CSRF 419 na criação sem token válido, leitura privada 401 para visitante e 403 para os tipos de conta inelegíveis exercitados. A titularidade e a decisão de seleção são verificadas nos serviços e políticas. Carta sintética e motivo interno de cancelamento ficaram ausentes do HTML público. As asserções de histórico confirmaram a fronteira de titular. Foram usados dados sintéticos e operações normais de QA local.

## H. Integridade e concorrência

Na seleção da última vaga, uma transação de QA reteve o lock da linha do Evento e houve duas transações de aplicação simultaneamente em espera no MySQL. Após liberar o lock, as respostas foram 200 e 409. O estado persistido foi um aprovado e um pendente. O detalhe informou lotação. No Evento com duas Agendas, a aplicação preservou a capacidade independente de 2 e 5, recusou a terceira aprovação da primeira e permitiu candidatura na segunda.

Em uma segunda corrida real, dois envios multipart com a mesma chave aguardaram o lock de gestão. Ambos retornaram 201 com o mesmo ID. O banco registrou um Evento, duas Agendas e uma intenção. Alterar o payload com a mesma chave retornou 409. Uma nova chave permitiu um novo Evento. As evidências estruturadas incluem `http-evidence.json` e `posqa-race.json`.

## I. Interface

Cinco capturas foram inspecionadas. O Evento lotado mostrou o controle desabilitado, sem transbordamento a 360, 768 e 1280 px. Os estados com vaga e com impedimento pessoal foram conferidos a 1280 px. Respostas 503 simuladas na consulta pessoal preservaram tanto a ação pública com vaga quanto o bloqueio público sem vaga. O visitante manteve o controle desabilitado.

No formulário de candidatura, o Chromium percorreu as quatro etapas, confirmou a preservação da carta ao voltar e revisou o envio sem carta. Uma resposta 503 no envio deixou a interface na revisão. A repetição retornou 201 e a candidatura permaneceu no histórico depois da recarga. A verificação de teclado e acessibilidade foi focal.

## J. Comandos, resultados e ambiente

- `php artisan route:list -v --except-vendor` conferiu `GET /feed`, `GET /eventos/{id}`, `GET /candidatura?evento={id}`, `GET /candidatura/{evento}/agendas`, `POST /candidatura/{evento}/enviar`, gestão de Eventos/Agendas e `PATCH /gestao/inscricoes/{inscricao}/selecao`.
- `php vendor/bin/phpunit --do-not-cache-result --filter 'Bloco9EventManagementTest\|Bloco9WebEventParticipationTest\|EventoOperacionalTest\|AgendaInscricaoTest\|OngAuthAndEventoTest\|Bloco81WebEventModerationTest\|OrganizadorWorkflowTest\|PessoaValidationAccessTest\|AvaliacaoConfiabilidadeTest\|ManagedImage'`. 113 testes aprovados, 1.267 asserções e zero ignorados.
- `node --test` nos arquivos `.mjs` de `tests/Frontend`, com exceção do renderizador Blade. 104 aprovados, zero ignorados.
- Build Vite 8.4.0, 38 módulos e saída temporária. A instância HTTP de QA foi conferida contra o manifesto recém-gerado, `assets/identity-bwR4pFVH.js`. O primeiro timeout de prontidão HTTP ocorreu enquanto o processo local respondia à primeira requisição. Foi repetido com a aplicação pronta.
- PHP 8.5.9, Node 24.16.0, MySQL 8.4.3 em loopback, Chromium 158.0.8127.88. O UUID `9e9f38a9-e9f9-11f1-b7ef-0250de5958d2`, a porta 33429, o diretório temporário e os schemas novos `ajudae_qa9_retest06_tests` e `ajudae_qa9_retest06_web` foram verificados antes dos testes com escrita. Instâncias HTTP em 33182 e 33183.
- `git diff --check` passou. Permanecem avisos de normalização CRLF/LF para os dois arquivos já alterados.

## K. Integração

O delta apresentado para revisão precisa incluir a versão local efetivamente auditada. A gestão e a moderação compartilham transições com Oliver, e o histórico da seleção alimenta os fluxos de Luiz Miguel. Sessões tipadas e estados da Pessoa dependem da frente de identidade. O ensaio da migration de idempotência foi feito apenas no banco descartável.

## L. Veredito final

**APROVADO COM RESSALVAS.** A disponibilidade pública e o bloqueio do QA-06 passaram no checkout auditado. A integração em `dev` depende de versionar o delta e revisar essa revisão. A validação foi local e o Railway ficou fora desta rodada.

### Decisões para você responder

Nenhuma.
