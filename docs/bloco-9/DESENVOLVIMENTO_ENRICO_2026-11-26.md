# Desenvolvimento F2 — correções pós-QA de Enrico — 26/11/2026

## Escopo e ponto de partida

A execução começou em `feat/eventos-candidaturas`, commit `74609bfd03cfbdbfa8dbfdcaf603c5dabd91794e`, com a integração F2 ainda no checkout. A consulta ao GitHub confirmou esse mesmo SHA em `dev`, Eventos, Identidade, Participação e Perfis. O stage inicial estava vazio. A reprodução partiu do relatório local `QA_INDEPENDENTE_ENRICO_2026-11-26.md`, dos contratos F2/F4 atuais e das regras RN011, RN013, RN016 e RN027 disponíveis no repositório. O PDF oficial estava indisponível no caminho indicado em `AGENTS.md`.

## QA-01 — Criação idempotente

O editor gera uma chave por intenção e a mantém na sessão da aba, escopada pelo tipo e ID da conta. JSON e multipart enviam `Idempotency-Key`. Após falha de conexão ou erro sem confirmação, repetir os mesmos dados reaproveita a chave. Um conflito oferece consulta aos Eventos e a escolha explícita de iniciar outra criação.

Uma migration incremental cria a tabela de intenções da operação Web com unicidade por ator e hash da chave. A primeira gravação confirma intenção, Evento, Agendas e associações na mesma transação. O hash dos dados inclui o conteúdo da imagem. A ordem das Agendas faz parte da comparação. O reenvio devolve 201 com o ID original e o estado atual autorizado. A mesma chave com dados validados diferentes devolve 409. Chaves distintas permitem Eventos iguais. Se o Evento tiver sido removido, a intenção permanece e o reenvio devolve 409. A compensação existente de imagem permanece fora da transação e cobre falhas e a tentativa concorrente descartada. A API Bearer e clientes Web sem cabeçalho conservam seu contrato.

## QA-02 — Capa pública

O controller resolve a capa somente depois de encontrar o Evento publicado. A referência gerenciada válida tem prioridade e usa a rota pública de imagem, com a verificação de publicação existente. Na ausência de referência gerenciada válida, o detalhe aceita link HTTP(S) externo válido ou caminho local público de imagem. O Blade recebe a URL resolvida e mantém o fundo quando a imagem estiver ausente ou a carga falhar. O detalhe não imprime chaves do armazenamento nem usa a rota de imagem da gestão.

## QA-03 — Confiabilidade na seleção

A gestão renderiza `confiabilidade` em uma linha própria. O score formado usa a escala de 0 a 100. Quando o Resource informa o estado em formação, o cartão exibe esse texto sem pontuação. Campo ausente ou sem valor válido fica sem indicador. O match continua mostrando as contagens de habilidades, causas e recursos. O XP continua separado.

## Verificações locais

| Verificação | Evidência |
| --- | --- |
| Ambiente | PHP 8.5.9, Node 24.16.0, MySQL 8.4.3 em `127.0.0.1:33429` com UUID `9e9f38a9-e9f9-11f1-b7ef-0250de5958d2`. O preflight de Laravel conferiu schema, porta, diretório temporário e UUID. Schemas `ajudae_qa9_20261126` e `ajudae_qa9_web_20261126`, com dados sintéticos. A migration nova foi aplicada somente à instância QA. |
| Resposta perdida | Antes da correção, um 201 interrompido no Chromium seguido de reenvio elevou a contagem de um para dois Eventos. Depois da correção, o navegador conservou a chave após reabrir o formulário. Mudança das vagas devolveu 409. Os dados originais devolveram 201 com o único Evento já persistido e sua Agenda. |
| Concorrência de criação | Duas sessões Web, dois processos PHP e multipart com banner para a mesma identidade e chave. MySQL registrou duas transações aguardando o bloqueio da ONG. Após liberação, dois 201 com o mesmo ID e um Evento, duas Agendas e uma intenção no banco. A mesma chave com outro payload deu 409. Outra chave com os mesmos dados gerou um ID separado. |
| Rollback | Teste PHP induziu erro ao gravar a segunda Agenda. O rollback deixou zero Eventos, zero Agendas e zero intenções, e removeu o objeto fake. O mesmo cabeçalho com payload corrigido persistiu um Evento, duas Agendas, uma intenção e uma imagem. |
| Capas | Chromium em 360, 768 e 1280 px carregou as imagens legada e gerenciada. Também verificou o detalhe sem elemento de imagem para Evento sem capa. Na trilha HTTP, o detalhe e a imagem gerenciada pendentes retornaram 404. A imagem retornou 200 depois da publicação. |
| Confiabilidade | O contrato Web entregou score 80 e o estado em formação sem score para candidatos sintéticos. Chromium exibiu “Confiabilidade: 80 de 100” e “Confiabilidade: Em formação — 0 de 3” em linhas próprias, com as contagens de match em linhas separadas. |
| Candidaturas e seleção | HTTP independente confirmou inscrição com e sem carta, duplicidade 409, aprovações, recusa e repetição inválida, reanálise e ocupação independente de duas Agendas. A terceira aprovação na Agenda de duas vagas retornou 409 e a Agenda de cinco vagas continuou aceitando inscrição. Na última vaga concorrente, 200 e 409, com uma aprovação persistida. |
| PHPUnit | `php vendor/bin/phpunit --do-not-cache-result --filter 'Bloco9EventManagementTest|Bloco9WebEventParticipationTest|EventoOperacionalTest|AgendaInscricaoTest|OngAuthAndEventoTest|Bloco81WebEventModerationTest|OrganizadorWorkflowTest|PessoaValidationAccessTest|AvaliacaoConfiabilidadeTest|ManagedImage'`. Resultado final de **110 testes aprovados e 1.195 asserções**, sem ignorados. Na primeira rodada, o teste de escopo falhou ao reutilizar sessão de tipos diferentes no mesmo cliente PHPUnit. O reteste inicia cada ator com sessão e guard limpos. |
| JavaScript | `node --test tests/Frontend/managed-events.mjs tests/Frontend/api-client.test.mjs`, **33 testes aprovados**. Sintaxe dos módulos alterados aprovada. |
| Build e estilo | `npm.cmd run build -- --outDir <temporário>` com Vite 8.4.0, **38 módulos compilados**. `php -l`, Pint focal e `git diff --check` passaram. |
| Browser | Chrome 158, perfil descartável com resolução de rede externa bloqueada. Evidências de DOM, rede e MySQL. Nenhuma captura foi produzida. |

Rotas exercitadas incluem `GET /ong/eventos/criar`, `POST /gestao/eventos`, `GET /eventos/{id}`, `GET /api/v1/eventos/{id}/imagem`, `GET /ong/candidatos?evento={id}`, `GET /gestao/agendas/{agenda}/inscricoes`, `POST /candidatura/{evento}/enviar`, `PATCH /gestao/inscricoes/{inscricao}/selecao` e `PATCH /admin/fila-eventos/{evento}/decisao`. O [contrato F2](CONTRATOS_F2_EVENTOS.md) registra os cabeçalhos, estados e respostas.

## Arquivos desta execução

| Área | Arquivos |
| --- | --- |
| Backend | `app/Http/Controllers/{PublicEventPagesController,WebEventManagementController}.php`, `app/Services/EventoManagementService.php`, `database/migrations/2026_11_26_000001_create_evento_criacao_idempotencia.php` |
| Interface | `resources/js/core/http.js`, `resources/js/modules/managed-events.js`, `resources/views/eventos/detalhe.blade.php`, `resources/views/ong/criar.blade.php` |
| Testes | `tests/Feature/{Bloco9EventManagementTest,Bloco9WebEventParticipationTest}.php`, `tests/Frontend/managed-events.mjs` |
| Documentação | `docs/bloco-9/CONTRATOS_F2_EVENTOS.md` e este relatório |

A revisão local em Git inclui os pontos de entrada F2 já existentes e necessários para que o novo módulo rode a partir da base, incluindo `resources/js/identity.js`, o editor de Agendas, as páginas de gestão e seu componente Blade compartilhado. O relatório independente anterior e as mudanças locais de dependências ficaram fora desse recorte.

## Revisão dos itens locais e integração

- `.env.example` já estava removido no checkout. A base Git ainda o contém. README e Composer o usam na preparação de um ambiente novo. A origem da remoção local permanece desconhecida.
- `package-lock.json` já atualizava Vite 8.2.1 para 8.4.0, Rolldown 1.2.3 para 1.3.4 e tipos OXC 0.143.0 para 0.160.0. O `package.json` não mudou. O build desta execução usou a instalação já disponível.
- `composer.phar` já estava não rastreado. Foi identificado como um artefato local de 3.820.169 bytes, sem confirmação de origem e sem execução nesta tarefa.
- `resources/js/identity.js` já importava e inicializava `managed-events.js`. As referências remotas consultadas partem da mesma versão desse inicializador. O cliente `requestJson` usa o novo cabeçalho somente quando o chamador fornece a chave. Deltas locais dos outros integrantes podem exigir conciliação no ponto de inicialização.
- Os itens anteriores de configuração e dependência permaneceram no checkout. A auditoria independente anterior foi preservada.

## Pendências e limites

Os três defeitos tiveram regressões específicas executadas nesta revisão. Falta a rodada de QA independente sobre o commit local. A projeção pública de datas e ocupação para atores sem elegibilidade, já apontada no relatório anterior, segue uma decisão de produto fora destas correções. A execução teve MySQL e armazenamento locais. PHP 8.4, S3 e Railway ficaram fora do ensaio. A nova migration é um pré-requisito para usar idempotência no ambiente de integração. A execução não fez push, merge ou deploy.

Para uma futura atualização de `AGENTS.md`, proponho registrar os contratos D02/D04/D05 e a chave opcional de criação Web, com referência a este relatório e ao reteste independente quando ocorrer.

### Decisões para você responder

Nenhuma.
