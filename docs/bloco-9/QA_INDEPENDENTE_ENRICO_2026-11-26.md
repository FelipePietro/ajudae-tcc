# QA independente — Enrico, Bloco 9 — 26/11/2026

[Reteste independente do commit das correções](QA_INDEPENDENTE_ENRICO_RETESTE_2026-11-26.md).

[Validação independente de QA-04 e QA-05](QA_INDEPENDENTE_ENRICO_VALIDACAO_QA_04_05_2026-11-26.md).

## A. Resumo executivo

**REPROVADO — CORREÇÕES OBRIGATÓRIAS.** A integração local permite criar um Evento em análise com Agendas, moderá-lo, receber candidaturas e decidir vagas. A concorrência real preservou o limite da última vaga. Dois defeitos foram reproduzidos. Repetir o envio após perda da resposta gerou dois Eventos. A nova capa do detalhe público ignora imagens legadas válidas. A gestão também omite a confiabilidade que o contrato já fornece. A avaliação recai sobre o delta local disponível para QA. O remoto ainda aponta para a mesma revisão da `dev`.

## B. Estado do repositório

| Item | Registro |
| --- | --- |
| Checkout e worktree | `D:/laragon/www/ajudae-tcc`. Único worktree listado. Branch ativa `feat/eventos-candidaturas`. |
| Commit inicial e final | `74609bfd03cfbdbfa8dbfdcaf603c5dabd91794e` (`feat(backend): prepara contratos web e regras para integracao`). |
| Comparação com `dev` | `origin/dev...HEAD` vazio e nenhum commit em `origin/dev..HEAD`. `git ls-remote` confirmou o mesmo SHA nas duas branches remotas. Houve uma tentativa sem rede e uma consulta bem-sucedida com acesso de leitura. |
| Delta local de aplicação | `resources/js/identity.js`, `resources/views/components/agenda-editor.blade.php`, `resources/views/eventos/detalhe.blade.php` e `resources/views/ong/{candidatos,criar,meus-eventos,painel_ong}.blade.php`. Arquivos locais não rastreados `resources/js/modules/managed-events.js`, `resources/views/components/managed-events.blade.php` e `tests/Frontend/managed-events.mjs`. |
| Outros itens anteriores | `.env.example` removido, `package-lock.json` modificado e `composer.phar` não rastreado. Sem evidência de autoria no histórico. |
| Git antes e depois | Inicialmente nove alterações rastreadas e quatro arquivos não rastreados, com stage vazio. O relatório é o único caminho acrescentado pela auditoria. Branch, HEAD, stage e delta de aplicação preservados. |
| Base auditada | `routes/{identity,web-event-management,web-event-participation,api}.php`, controllers de gestão, candidatura, catálogo público e seleção, `EventoPolicy`, `AgendaPolicy`, Models, Resources, Services, migrations aplicáveis, módulo HTTP e suítes F2/F4. |

A validação usou o [Catálogo Web](CATALOGO_WEB.md), a [matriz da equipe](MATRIZ_INTEGRACAO.md), o [checklist](PRONTIDAO.md), os contratos de [Eventos](CONTRATOS_F2_EVENTOS.md) e [participação](CONTRATOS_PARTICIPACAO.md), a [consolidação](CONSOLIDACAO.md), o [QA prévio da base](QA_INDEPENDENTE.md) e os recortes F2 dos Blocos 8.1 e 8.2. O PDF oficial indicado em `AGENTS.md` estava indisponível no caminho informado. Usei o resumo local de RN010–RN018 e as decisões mais recentes registradas. Nenhum relatório próprio de Enrico foi localizado no checkout. Os números da base servem como referência histórica.

## C. Matriz de funcionalidades

| Funcionalidade | Implementação e integração | Evidência desta execução | Situação ou pendência |
| --- | --- | --- | --- |
| Criar Evento como ONG ativa e organizador independente | Formulário e endpoints Web persistem com titularidade da sessão | Chromium criou pela ONG e recarregou gestão. HTTP retornou 201 para ambos, com estado pendente e proprietário esperado | Reenvio após resposta perdida duplica o Evento. QA-01 |
| Titularidade, atribuição e ONG inativa | Policies e Services da base. ONG proprietária e organizador designado operam no escopo previsto em D04/D05 | Suíte focal verificou escopo, negativa para ONG pendente e designado em outro Evento e criação de Agenda restrita ao proprietário | ONG reprovada não exercitada separadamente em Chromium. A permissão de criação institucional mudou desde o contrato 8.2 |
| Agendas, duração e persistência | Criação conjunta D02 e edição por Agenda. Capacidade independente | HTTP 422 para Evento sem Agenda. Duas Agendas com 2 e 5 lugares em Evento com total informativo 1. Chromium recarregou com capacidade 5. Testes da base cobriram duração inválida, rollback e última Agenda | Imagem no rollback usa disco fake. S3 fora do ensaio |
| Moderação, publicação e reanálise | Rota administrativa Web da base. Leitura real em feed e detalhe | Pendente fora de busca e detalhe com 404. Moderação 200 liberou ambos. Título manteve publicação. Alterar horário da Agenda retirou o detalhe e bloqueou nova inscrição | Motivo e reanálise já existem. Integração da fila pertence à frente de administração |
| Exploração, filtros, cards e detalhe | Feed e busca persistidos da base. Delta acrescenta capa e consulta de Agendas no detalhe | Filtros online/presencial e busca com fixture real. Chromium em três larguras. Visitante, ONG, pendente, organizador e voluntário receberam o detalhe publicado | Banner legado ausente na nova capa. QA-02. Escopo público da leitura das Agendas exige alinhamento |
| Candidatura e carta | Stepper e histórico Web preexistentes. Envio por Agenda | HTTP 201 com e sem carta, duplicidade 409, papéis inadequados 403 e reanálise 409. Histórico consultado por duas Pessoas, com carta restrita à conta correta. Chromium percorreu as quatro etapas e recarregou o histórico | Expiração de sessão durante o fluxo ainda sem ensaio Chromium |
| Seleção e ocupação | Delta usa gestão Web com acordeão por Agenda e decisão humana | Aprovou 2 na Agenda A, terceira 409. Agenda B continuou aceitando. Recusa preservada e repetição 422. Chromium carregou os estados reais. Última vaga concorrente com 200 e 409 | Falta teste de decisão pelo botão em Chromium e atualização após expiração |
| Compatibilidade e histórico | Backend calcula correspondências e projeta estados, carta e confiabilidade. Frontend mostra as três contagens e XP | Teste de match da base usa IDs conhecidos. Consulta HTTP confirmou match zero e permanência como pendente. Recusa, histórico do titular e isenção por alteração preservados | Omissão de confiabilidade na gestão. QA-03. Confirmação de presença/encerramento pertence à frente de participação |

## D. Auditoria dos testes existentes

| Testes | Classificação | Asserções que sustentam o resultado e limites |
| --- | --- | --- |
| `managed-events.mjs` — payload e modalidade online | Cobertura efetiva de unidade | Verificam normalização de CEP, números, IDs e exclusão do endereço desabilitado. Objetos artificiais substituem o DOM. |
| `managed-events.mjs` — duração e vagas | Cobertura parcial | Exercitam 29/30 min, conversão temporal, vagas inválidas e independência da capacidade. O controle artificial fornece a validade. A suíte não verifica evento de formulário, persistência e reabertura. |
| `managed-events.mjs` — multipart e imagem | Cobertura parcial e dependência de ambiente simulado | Verificam chaves aninhadas, omissão de titularidade, imagem opcional e rejeições preliminares. Limites de decodificação no navegador, resposta HTTP e arquivos efetivamente gravados ficam fora. |
| `Bloco9EventManagementTest` — criação Web e Bearer | Cobertura efetiva do backend | Afirma códigos, titularidade, estado pendente, contagem persistida, 29 min, Agenda não vazia, leitura de gestão e visibilidade. A sessão é montada no PHPUnit e o storage de imagem é fake. |
| `Bloco9EventManagementTest` — falha na segunda Agenda | Cobertura efetiva da transação com falha induzida | Listener interrompe a persistência. Há asserções de zero Eventos, zero Agendas e remoção do objeto fake. A UI e os retornos do storage externo ficam fora. |
| `Bloco9EventManagementTest` — atores, designação e órfão | Cobertura efetiva do contrato | Rejeições e respostas positivas no escopo, titularidade protegida, manutenção da última Agenda e órfão fora do catálogo. Parte usa Bearer. |
| `EventoOperacionalTest`, `AgendaInscricaoTest` | Cobertura efetiva de regras e parcial para integração da UI | Exercitam reanálise, lotação sequencial, corte exato em uma hora, contagens sintéticas de match, desistência, estados terminais e notificações. As chamadas de backend não exercitam os controles do módulo local. |
| `OngAuthAndEventoTest`, `PessoaValidationAccessTest`, `OrganizadorWorkflowTest` | Cobertura efetiva de autorização de backend | Exercitam estados de conta e papéis. Os casos selecionados não substituem o percurso humano no browser. |
| `Bloco81WebEventModerationTest`, `Bloco9WebEventParticipationTest` | Cobertura efetiva dos endpoints; parcial para seleção do delta | Exercitam permissão administrativa, titularidade e designação da Agenda, transições, autoria e estado. A suíte não dispara o acordeão do novo JavaScript nem reproduz duas escritas simultâneas. |
| Cliente HTTP em Node | Cobertura efetiva de transporte simulado | Mock de fetch exercita 401, 403, 404, 409, 419, 422, 429, 500, timeout, cancelamento e falha de rede. Pode passar mesmo se o formulário apresentar estado incorreto ou repetir uma criação já gravada. |
| `rendered-dialog-scripts.test.mjs` | Verificação de sintaxe em modo demonstrativo, dependente de assets | Primeiro ensaio amplo falhou. Com build temporário e modo demonstrativo explícito, o mesmo renderizador produziu três scripts, todos aceitos por `vm.Script`. O ensaio de renderização não cobre a entrada integrada. |

O QA da base de 24/11 registrou 259 aprovações e 3.068 asserções no integrado, uma exclusão de certificado legado, 95 testes JavaScript e um teste concorrente Bearer com 200/409. Os números são históricos. A execução atual usou o delta local, PHP e Vite diferentes, dois trabalhadores HTTP Web e sessões independentes. Os testes focais atuais terminaram sem casos ignorados. Não encontrei asserções do módulo local para tela de candidatos, paginação, recarga, erros de interface ou recuperação de resposta perdida.

## E. Defeitos

### QA-01 — Repetir o envio após perder a resposta cria dois Eventos

**Severidade média · reproduzido em Chromium e MySQL.** Funcionalidade de criação do Evento com Agendas. Dados sintéticos: ONG ativa, Evento online, uma Agenda futura. CDP interrompeu apenas a entrega da primeira resposta 201, depois de o servidor gravar. O navegador exibiu “Sem conexão com o servidor. Tente novamente.”. Ao repetir pela mesma tela, o usuário viu “Evento criado com sucesso. Estado: Aguardando aprovação.”. O total para aquele título passou de 0 para 1 e depois para 2 no MySQL. A trava no formulário impede cliques simultâneos enquanto a chamada está ativa, mas libera o reenvio após a resposta perdida.

- **Esperado:** recuperar o resultado já gravado ou permitir repetir a mesma operação sem duplicar o Evento e suas Agendas.
- **Impacto:** o gestor e a moderação recebem duas entradas para a mesma intenção de cadastro. O usuário precisa descobrir e reconciliar a duplicação.
- **Correção proposta:** usar idempotência por submissão, preservada na tentativa seguinte e escopada pela identidade, ou reconciliar explicitamente o resultado antes de habilitar novo envio.
- **Código:** [criação e reenvio](../../resources/js/modules/managed-events.js#L149), [contrato de criação](CONTRATOS_F2_EVENTOS.md#criação-conjunta).

### QA-02 — Detalhe publicado oculta banner legado válido

**Severidade média · reproduzido por HTTP e Chromium.** Funcionalidade de detalhe público. Dados sintéticos: Evento publicado com uma Agenda, `imagem_evento_link` apontando para imagem local válida e sem `managed_image_path`. A URL legada retornou 200 e apareceu no Resource público. No detalhe, o HTML solicitou exclusivamente `/api/v1/eventos/5/imagem`, que retornou 404. O cabeçalho publicado ficou sem imagem. Em 360, 768 e 1280 px, Chromium reportou `naturalWidth = 0` e `hidden = true`.

- **Esperado:** manter a imagem legada válida quando o Evento está publicado e priorizar a imagem gerenciada quando existir.
- **Impacto:** os Eventos existentes com banner adquirido pelo contrato anterior continuam sem capa no modo integrado. No HEAD compartilhado com a dev, o Blade também omitia a imagem no modo integrado. O delta introduziu a capa gerenciada sem resolver o caso legado.
- **Correção proposta:** usar a URL pública resolvida pelo backend para a capa. Adicionar regressão com registro legado e imagem gerenciada nas transições de visibilidade.
- **Código:** [imagem do detalhe](../../resources/views/eventos/detalhe.blade.php#L48), [seleção pública do Evento](../../app/Http/Controllers/PublicEventPagesController.php#L63), [projeção da imagem](../../app/Services/ManagedImageStorage.php#L41).

### QA-03 — Gestão de candidatos omite confiabilidade disponível

**Severidade média · confirmado por inspeção do consumidor e do contrato.** Funcionalidade de seleção RN016/RN027. Considere candidatura com resumo de confiabilidade visível segundo a regra de três participações. A listagem inclui `confiabilidade` no Resource. O renderizador cria linhas de status, match, XP e carta, sem consumir esse campo. O candidato com pontuação disponível deveria ter o indicador exibido separadamente do match. A confirmação da omissão vem do percurso do campo no código. Um ensaio Chromium com candidato elegível para exibir o indicador continua pendente.

- **Impacto:** o responsável perde um indicador previsto para a avaliação humana. A seleção e as contagens de match continuam funcionais.
- **Correção proposta:** renderizar o resumo quando autorizado e disponível, com a unidade da confiabilidade e sem misturá-la às três contagens.
- **Código:** [projeção do Service](../../app/Services/EventoParticipationService.php#L60), [Resource](../../app/Http/Resources/InscricaoResource.php#L38), [cartão de seleção](../../resources/js/modules/managed-events.js#L348).

## F. Lacunas de verificação e riscos

| Jornada | Ainda a verificar |
| --- | --- |
| Criação e edição | Fluxo Chromium de organizador designado, ONG reprovada, falha 500/503 no formulário, renovação de sessão durante envio e falha após editar no servidor. Exercitar data, hora e endereço nas três larguras. |
| Catálogo público | Paginação com mais de 12 Eventos, cidade, estados vazios e ação de uma Agenda lotada no detalhe. O detalhe exibe datas/vagas somente para voluntário aprovado. O roteiro pede exploração de Agendas por diversos atores e o catálogo existente restringe o endpoint de elegibilidade. É necessário explicitar o contrato da projeção pública de datas e capacidade, preservando dados personalizados. |
| Candidatura | Formulário inválido, retorno 401/419 após sessão expirada, falha de rede fora do cenário de 503 induzido, anônimo e Agenda inexistente no ensaio Web independente. Há cobertura de casos de regra no backend e de transporte no cliente. |
| Seleção e histórico | Aprovar e recusar pelo acordeão e teclado, refazer ação após falha de rede, paginação de inscrições, ausência de informação de confiabilidade quando inelegível e estados de presença/cancelamento em UI. |
| Auditoria de qualidade | Não foi feita inspeção visual humana das telas nem uma auditoria integral de acessibilidade. A amostra de Chromium verifica DOM, rede, viewport e um Tab no formulário. |

O rótulo das contagens do painel depende de carregar todas as páginas. A comunicação explicita essa condição, mas vale verificar a integração com volume superior a vinte Eventos para avaliar se o operador distingue total e página. O histórico e o escopo de seleção devem ser preservados quando a frente de participação assumir presença e encerramento.

## G. Segurança e privacidade

A rota de escrita em sessão ONG respondeu 419 sem prova CSRF. A suíte focal verificou as negativas de propriedade, designação e campos protegidos do contrato. HTTP independente confirmou 403 para três papéis inelegíveis na candidatura. Nenhuma atribuição vem do formulário. A comparação de históricos mostrou a carta do titular e ausência da mesma carta no histórico de outra Pessoa. O teste de gestão verifica que a carta chega ao ator autorizado. Nenhuma confirmação de exposição indevida foi observada no recorte. A inspeção de acesso, privacidade e segurança permanece limitada aos contratos e cenários descritos.

## H. Integridade e concorrência

A D02 foi validada com dois tipos de falha. Em HTTP, Evento sem Agenda retornou 422. No PHPUnit, uma exceção na segunda gravação deixou zero Eventos e zero Agendas e removeu a imagem fake. O total informativo do Evento ficou em 1 com Agendas de 2 e 5 vagas. A Agenda A bloqueou a terceira aprovação após 2 vagas preenchidas e a Agenda B aceitou uma inscrição independente. A recusa manteve a linha consultável e sua repetição retornou 422.

Para RN015, uma Agenda de 1 vaga recebeu duas candidaturas pendentes. Duas sessões Web independentes autenticadas como o mesmo gestor enviaram aprovações a processos PHP distintos. Uma conexão separada bloqueou a linha do Evento. Consultas a `performance_schema.data_lock_waits` confirmaram duas transações aguardando. Ao liberar o bloqueio, os retornos foram 200 e 409. A consulta final da tabela `p_ag_inscreve` registrou exatamente uma aprovada e uma pendente. Foi executado um par independente nesta combinação de ator e infraestrutura.

Ao editar o término da Agenda publicada, a inscrição existente continuou consultável e ganhou `desistencia_sem_penalidade_ate`. A suíte de regra também validou a desistência decorrente. A regressão QA-01 é distinta dessa atomicidade por requisição. Cada requisição gravou seus próprios registros completos.

## I. Interface e usabilidade

| Página | 360 px | 768 px | 1280 px |
| --- | --- | --- | --- |
| Feed | 345 px de documento | 768 px | 1280 px |
| Criação ONG | 345 px, catálogos e Agenda disponíveis | 753 px, catálogos e Agenda disponíveis | 1265 px, catálogos e Agenda disponíveis |
| Detalhe publicado | 345 px, capa legada oculta | 753 px, capa legada oculta | 1265 px, capa legada oculta |
| Candidatos | 345 px, estados pendente e aprovado | 753 px, estados pendente e aprovado | 1265 px, estados pendente e aprovado |

As medidas são de `document.documentElement.scrollWidth`. Nenhuma das superfícies medidas excedeu o viewport. As páginas de candidatos carregaram o acordeão e leram dados do MySQL via `GET /gestao/agendas/{agenda}/inscricoes`. A criação pela ONG no Chromium retornou 201, indicou aprovação pendente e permitiu abrir a gestão e recarregar com a mesma Agenda de 5 vagas. Tab transferiu o foco para um link em cada largura da criação. Foram coletados DOM e rede, sem capturas anexadas.

O percurso de candidatura no Chromium a 768 px passou por escolha da Agenda, apresentação, revisão e confirmação. O texto preenchido voltou intacto ao editar a apresentação. Removi o texto para validar o envio sem carta. Um 503 simulado antes de chegar ao servidor exibiu feedback de erro e manteve a revisão. A tentativa real recebeu 201, acionou a confirmação e persistiu no histórico após recarregar.

## J. Ambiente, comandos e resultados

| Verificação | Resultado |
| --- | --- |
| Isolamento | MySQL 8.4.3 iniciado com novo diretório temporário e bind em `127.0.0.1:33429`. UUID `9e9f38a9-e9f9-11f1-b7ef-0250de5958d2`. Antes das migrations e testes, a conexão Laravel confirmou schema, porta, UUID e diretório. Schemas `ajudae_qa9_20261126` e `ajudae_qa9_web_20261126`, com usuário restrito a eles. A instância nova exibia somente schemas do sistema antes do provisionamento. Dados sintéticos, conexão local e storage de QA. |
| Contratos registrados | `php artisan route:list -v --except-vendor` no modo integrado. Confirmou rotas, verbos, middleware e controllers. |
| PHP focal | `php vendor/bin/phpunit --do-not-cache-result --filter 'Bloco9EventManagementTest|Bloco9WebEventParticipationTest|EventoOperacionalTest|AgendaInscricaoTest|OngAuthAndEventoTest|Bloco81WebEventModerationTest|OrganizadorWorkflowTest|PessoaValidationAccessTest'`. PHP 8.5.9, **70 testes aprovados, 756 asserções**, sem ignorados. |
| PHP complementar | Mesmo preflight e `--filter 'AvaliacaoConfiabilidadeTest|ManagedImage'`. **34 testes e 376 asserções**, sem ignorados, incluindo cobertura de confiabilidade e imagem no backend. |
| Node | Node 24.16.0. `node --test tests/Frontend/managed-events.mjs` **6/6**. A execução ampla teve erro inicial de PATH do PHP. Com PATH corrigido, **100/101**, uma falha no renderizador sem o build no caminho padrão. O reteste ajustado usou os assets temporários e analisou três scripts com sucesso. |
| Vite | `npm.cmd run build -- --outDir <temporário>`, Vite 8.4.0, **38 módulos compilados** e manifesto produzido. |
| Web independente | Dois servidores PHP locais, sessões em arquivo e credencial limitada ao schema descartável. Catálogo, proteção CSRF, D02, candidatura, seleção, filtros, reanálise e corrida confirmados. Houve uma colisão de escrita do cache Blade no primeiro bootstrap concorrente. O reteste carregou as sessões em sequência antes de concorrência deliberada na seleção. |
| Chromium | Chrome 158.0.8127.88 em perfil temporário, viewport 360/768/1280. Primeira inicialização falhou por GPU no ambiente. A retomada usou perfil local dedicado, GPU desabilitada e resolução externa bloqueada. E2E de criação, candidatura e recarga, DOM das demais telas e interceptação de respostas. O primeiro percurso de candidatura foi interrompido por erro de espaço de escrita no temporário. Após liberar perfis de QA usados nas tentativas de inicialização, a renderização e o reteste concluíram. |
| Git | `git diff --check` sem erro. Nenhum commit, push, merge ou deploy. Nenhuma migração no banco principal. Nenhum arquivo de aplicação alterado pela auditoria. |

**Rotas verificadas por contrato ou uso.** Páginas `GET /feed`, `GET /eventos/{id}`, `GET /ong/eventos/criar`, `GET /ong/eventos`, `GET /ong/candidatos`, `GET /organizador/eventos/criar`, `GET /organizador/eventos`, `GET /candidatura?evento={id}` e `GET /minhas-candidaturas`. Dados e comandos em `GET|POST /gestao/eventos`, `GET /gestao/eventos/catalogos`, `GET|PATCH /gestao/eventos/{evento}`, `POST /gestao/eventos/{evento}/agendas`, `PATCH|DELETE /gestao/agendas/{agenda}`, `GET /gestao/agendas/{agenda}/inscricoes`, `PATCH /gestao/inscricoes/{inscricao}/selecao`, `PATCH /admin/fila-eventos/{evento}/decisao`, `GET /candidatura/{evento}/agendas`, `POST /candidatura/{evento}/enviar` e `GET /minhas-candidaturas/dados`. As trilhas efetivamente executadas constam na matriz.

## K. Riscos de integração e divergências de contrato

- **Mudanças compartilhadas.** O módulo novo entra por `resources/js/identity.js`. Coordenar inicialização com F1/F3/F4 e revisão das views de painel e navegação. A exclusão local de `.env.example`, a mudança do lockfile e `composer.phar` precisam de triagem na montagem do PR. `composer.json` e README usam `.env.example` na preparação.
- **D02 e D04/D05.** A descrição F2 do Bloco 8.2 previa criação em duas etapas e criação institucional por organizador vinculado. A consolidação do Bloco 9 exige Agenda na criação e reserva a criação de Evento institucional à ONG. As negativas do designado na suíte focal seguem a decisão nova.
- **RN012, RN013 e RN018.** `vagas_evento` é informativo e não dispara reanálise. Dados estruturais de Agenda seguem o contrato atual. Alterações de tempo preservam a isenção aplicável. O PDF oficial ausente deixa a comparação literal pendente.
- **Dependências.** Identidade e sessão pertencem à frente de cadastro. Moderação e designação pertencem à frente administrativa. Presença, monitoria e conclusão pertencem à frente de participação. A API Bearer continua com seus contratos próprios. A homologação S3 e Railway fica fora do resultado local.

## L. Veredito final

**REPROVADO — CORREÇÕES OBRIGATÓRIAS.** Corrigir primeiro a duplicação no reenvio após resposta perdida e a incompatibilidade da nova capa com o banner legado. Resolver a apresentação da confiabilidade e acordar a projeção de Agendas disponível para cada ator. A base de testes comprova o núcleo transacional observado, inclusive a última vaga concorrente. O risco no reenvio do fluxo essencial e a lacuna de compatibilidade impedem recomendar o delta atual para a `dev`. Quando o delta estiver em uma revisão identificável, repetir os testes de falha, legado, candidatura, seleção e recarga sobre essa revisão.

A atualização pontual proposta para `AGENTS.md` é registrar o contrato D02/D04/D05 e substituir a nota histórica de que o backend não tem carta opcional, citando o catálogo atual e a migration `2026_11_23_081200_add_carta_apresentacao_to_inscricao.php`. A auditoria não incluiu alteração em `AGENTS.md`. O único arquivo da entrega é este relatório. O protocolo de ZIP de delta de código não se aplica a uma auditoria documental.

### Decisões para você responder

Nenhuma.

