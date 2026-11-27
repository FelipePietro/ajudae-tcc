# Bloco 9 · QA final independente de Luiz Miguel

27/11/2026 · `America/Sao_Paulo` · `feat/participacao-administracao` → `dev`

## 1. Veredito executivo

**REPROVADO — CORREÇÕES OBRIGATÓRIAS**

O SHA publicado passou na regressão integrada, na demonstrativa, no JavaScript e no build. Os contratos exercitados por HTTP conservaram autorização, persistência e trilha nas amostras sintéticas. O checkout publicado perdeu `.env.example`, embora o setup Composer ainda dependa dele para criar `.env`. A etapa de geração da chave falhou em clone limpo. O pacote precisa voltar a oferecer um setup reproduzível antes da liberação.

Evidência de interface, suas limitações e verificação de concorrência constam das seções 4 e 6.

## 2. Pré-voo Git e fontes

| Item | Resultado |
| --- | --- |
| Repositório e ponta remota | [GitHub · commit 815617f](https://github.com/FelipePietro/ajudae-tcc/commit/815617f46c6d676d9681f94a518487f770b88f1a). A consulta pelo nome da branch no conector GitHub devolveu `815617f46c6d676d9681f94a518487f770b88f1a`. O HEAD local e a referência de acompanhamento tinham o mesmo SHA. |
| Base e ancestralidade | `dev` e o ancestral comum `74609bfd03cfbdbfa8dbfdcaf603c5dabd91794e`. A comparação GitHub e `git rev-list --left-right --count origin/dev...HEAD` coincidiram em 0 commits atrás e 2 à frente. O delta tem 48 caminhos, sendo 19 adições, 28 modificações e 1 exclusão. |
| Clone | Clone local separado, sem hardlinks Git e sem `.env`. Árvore `63cf38fb853b96604ec48d5508a9a16865162e69`. O documento Word não rastreado no checkout de trabalho ficou fora do clone. Dependências locais instaladas foram copiadas a partir de diretórios separados e os assets foram gerados no clone. |
| Pré-voo | Executados `git status --short --branch`, `git branch -vv`, `git worktree list`, `git remote -v`, `git log -5 --oneline`, `git fetch origin`, `git ls-remote`, `git rev-parse`, `git merge-base`, `git diff --name-status` e inspeção da árvore. O fetch falhou na escrita de `.git/FETCH_HEAD` sob sandbox e o transporte Git HTTPS falhou na conexão à porta 443. O conector GitHub confirmou ponta e base diretamente. |
| Entradas rastreadas | `admin-operations.js`, `participation-management.js`, `web-operations.js`, `participation.js`, `event-moderation.js`, view de conclusões, componentes Web compartilhados, gestão de participações e regressão documental estão no SHA. Os imports foram resolvidos no build. |
| Normas e contratos | O PDF oficial estava indisponível nos caminhos consultados. Foram usados o [texto local RN V1](../../Ajudae_Regras_de_Negocio_V1.md), o [catálogo Web](CATALOGO_WEB.md), os [contratos de participação](CONTRATOS_PARTICIPACAO.md), a [matriz do repositório](MATRIZ_INTEGRACAO.md) e os quatro relatórios anteriores citados no roteiro. A matriz histórica de seis integrantes e o recorte das nove frentes do roteiro têm delimitações diferentes. Este relatório usa o escopo explícito do roteiro. |

## 3. Revalidação dos achados

| ID | Resultado | Prova independente e limite |
| --- | --- | --- |
| QA-01 · Git | **Falhou no setup. Consolidação comprovada.** | Ponta confirmada no GitHub, módulos rastreados, 40 módulos Vite e suítes em clone. O setup padrão falha no candidato pelo arquivo excluído. Ver seção 7. |
| QA-02 · Sidebar | **Resolvido.** | Sessão com capacidade exclusiva seguiu o link da sidebar no Edge, conferiu `aria-current="page"` e decidiu um pedido. Leitor sem a capacidade ficou sem o item no HTML e recebeu 403 na rota de dados. |
| QA-03 · Diretório de moderação | **Resolvido quanto à delimitação.** | Um moderador abriu `/admin/usuarios` com indicação de consulta indisponível e recebeu 403 em `/admin/validacoes-pessoa/dados`. A capacidade é `usuarios.moderar`. A validação de cadastro usa `usuarios.validar`. A implementação do diretório está fora do escopo de aceite. |
| QA-04 · Autoria e motivo | **Resolvido no contrato HTTP e no banco.** | O gestor recebeu `decidido_por` com `tipo` e `nome`. O titular recebeu status, motivo e data sem autoria. A ocorrência permaneceu como ausência. A reprovação de apadrinhamento sem motivo retornou 422 e manteve a pendência. Com motivo, retornou 200, gravou um log com o mesmo ator e devolveu 409 na repetição. Aprovação de ONG usou apenas `status_ong`. Reprovação de Evento exigiu motivo. |
| QA-R01 · Renovação | **Resolvido no fluxo exercitado.** | Edge abriu a fila, baixou PDF sintético de disco privado com SHA-256 conferido, aprovou com 200 e recarregou. MySQL com aprovação, ator, data e retenção de 30 dias. Em HTTP, terceiro comum, administrador sem capacidade, documento histórico, de tipo indevido ou fora da retenção receberam 403. A autorização dos operadores da fila continua como interpretação contratual, detalhada na seção 8. |
| QA-R02 · Cadastro inicial | **Resolvido.** | Sem posse, Edge mostrou orientação e zero links acionáveis. Endpoint direto negou com 403. Após assumir e recarregar, o navegador recebeu o PDF. Outro analista recebeu 403. A regressão PHPUnit reproduziu expiração de 24 h e transferência de posse. |

`documento.download` registrou `entrega_iniciada` com ator e documento. A verificação do binário veio dos bytes recebidos pelo cliente e da comparação com o SHA-256 da fixture. A linha de auditoria comprova o início da entrega.

## 4. Nove frentes

Todas as linhas abaixo têm evidência de integração Web pela aplicação executada com sessão e MySQL. Os caminhos representam as páginas do usuário e as rotas de dados principais.

| Frente | HTTP e autorização | Persistência e estado posterior |
| --- | --- | --- |
| 1 · Histórico pessoal | `/minhas-participacoes` e `GET /minhas-participacoes/dados`. Titular com paginação de 12 itens, outra Pessoa sem suas inscrições, estado vazio e visitante 401. | Após encerramento e avaliação, o registro apareceu em nova consulta na página 2, com Evento finalizado e presença confirmada. A primeira expectativa do ensaio supôs página 1. O reteste percorreu as duas páginas e confirmou a ordenação por ID decrescente de Evento. |
| 2 · Presença e monitoria | Gestão em `/ong/eventos#gestao-participacoes`, `PATCH /gestao/inscricoes/{inscricao}/presenca` e `GET/PATCH …/monitoria`. ONG terceira 403, Agenda futura 409, designado autorizado na leitura e rejeição de monitoria sem motivo 422. | Presença da ONG registrada na Agenda da fixture com autoria e data. Monitoria confirmada e lida novamente pelo titular. |
| 3 · Justificativas | Histórico do titular e gestão, `GET/POST/PATCH /gestao/inscricoes/{inscricao}/justificativa`. Campo de 2.001 caracteres 422, envio 201, autodeferimento 403 e decisão pela ONG 200. | Status deferido e autoria gerencial minimizada em nova consulta. Titular viu motivo e data sem autor. MySQL preservou ausência e gravou data, decisão e ator. |
| 4 · Avaliações e encerramento | Gestão, `PATCH /gestao/agendas/{agenda}/finalizar`, `POST /gestao/eventos/{evento}/avaliacoes/{pessoa}`, `POST /gestao/eventos/{evento}/finalizar`. Agenda aberta, nota pendente e duplicação tiveram 409. Avaliação opcional da ONG 201. | Agenda e Evento finalizados, uma nota obrigatória e 100 XP na amostra. Para a inscrição estudantil do ensaio, o serviço bloqueou emissão sem monitoria e emitiu após validação, com 60 minutos e uma linha única na repetição. |
| 5 · Pessoas e documentos | `/admin/candidatos`, rotas de análise cadastral, renovação e `/conta/documentos/{documento}/download`. 419 sem CSRF, comum 403 em reprovação definitiva, cenários QA-R01/R02 na seção 3. | Atribuição cadastral, downloads auditados e renovação aprovada persistiram. Retenção calculada em 30 dias. |
| 6 · ONGs e Eventos | `/admin/cadastros-ong` e `/admin/fila-eventos`. ONG aprovada com `status_ong`; rejeição de Evento sem motivo 422, válida 200, repetida 409. | ONG ativa e fora da fila. Evento reprovado. Dois moderadores distintos verificaram exclusão mútua no ensaio concorrente. |
| 7 · Promoção e conclusão | `/admin/upgrades-org` e `/admin/conclusoes-padrinho`. Promoção assumida e rejeitada. Capacidade exclusiva e motivo de apadrinhamento conforme QA-02/04. | Promoção reprovada em novo GET. Conclusão rejeitada com motivo, ator, data e um log no MySQL. |
| 8 · Campanhas de XP | `/admin/campanhas-xp`, POST, PATCH de dados/status e GET da lista. Criação, edição e desativação retornaram os estados esperados. | Campanha desativada com fator 2,500000 e três logs. Desativação concorrente preservou a concessão única e o XP histórico. |
| 9 · LGPD e auditoria | `/admin/solicitacoes-lgpd`, `/admin/responder-lgpd`, `/admin/log-acoes`. Leitor viu projeção reduzida, atendente exclusivo recebeu 403 na listagem. Assunção e resposta gravadas, 422 sem resposta e log filtrado. | Chamado concluído com responsável e resposta, dois logs. Corridas preservaram a mesma atribuição. A suíte incluiu prévia e execução suspensa de exclusão. A regra de sete dias em `AccountDeletion` foi inspecionada. |

### Percursos no navegador

Edge **158.0.4370.45** em perfil descartável, com dados do schema de UI e login pelo formulário da aplicação. As evidências registram persona, métodos, endpoints e estados pós-recarga. Os 401/403/409/419/422 desta rodada foram medidos em HTTP direto. A validação nativa de motivo também foi exercitada no formulário de apadrinhamento.

| Frente | Ação pelo navegador e verificação |
| --- | --- |
| Histórico | Titular abriu o histórico, clicou em **Carregar mais**, encontrou a ocorrência na segunda página e recarregou. Outra sessão sem inscrições mostrou o estado vazio. |
| Presença e monitoria | ONG abriu a Agenda gerida, registrou presença e confirmou monitoria por formulários PATCH 200. Reabriu a Agenda após recarga. O banco confirmou presença, monitoria e autoria da ONG. |
| Justificativas | Titular abriu ocorrência na página 2 e enviou o motivo, POST 201. ONG abriu a Agenda da ausência e deferiu, PATCH 200. Após recarga, o gestor viu a autoria e o MySQL preservou a ausência. A projeção reduzida do titular foi verificada por HTTP. |
| Avaliação e encerramentos | ONG finalizou a Agenda, enviou nota 5 e finalizou o Evento, com PATCH 200, POST 201 e POST 200. Após recarga, MySQL com Agenda e Evento finalizados, uma nota e 100 XP. |
| Pessoas e documentos | Operador viu orientação antes da posse, assumiu análise, recarregou e baixou um PDF de 1.992 bytes. A renovação também foi baixada com SHA-256 conferido e aprovada via PATCH 200. O banco confirmou ator e retenção. |
| ONG e Evento | Operador aprovou ONG pelo formulário, PATCH 200, e confirmou saída da fila após recarga. Em outra execução, reprovou Evento pelo formulário com motivo, PATCH 200, e conferiu a fila após recarga. |
| Promoção e apadrinhamento | Promoção assumida e rejeitada com motivo pela UI. Admin exclusivo entrou pela sidebar, conferiu o destaque ativo, acionou o formulário sem motivo e observou campo inválido. Enviou motivo e recebeu POST 200. Recarga preservou as decisões. MySQL confirmou o log de conclusão. |
| Campanhas | Admin criou, editou e desativou pela UI, com POST 201 e dois PATCH 200. Após recarga apareceu campanha inativa. Um GET recebeu 503 sintético via interceptação do navegador e **Tentar novamente** recuperou a lista. O banco confirmou fator e três logs. |
| LGPD | Atendente exclusivo preencheu o protocolo em **Assumir atendimento** e **Concluir solicitação**, dois PATCH 200. Após recarga sob outro perfil, estado concluído, resposta oculta ao leitor sem atribuição e evento no log. MySQL confirmou resposta e ator. |

Na fila de conclusões, as larguras de rolagem foram **360, 768 e 1280 px**, iguais aos respectivos viewports. O foco alcançou o controle de expansão. A abertura por Enter enviada pela automação não foi confirmada. Escape, inspeção visual manual e teclado de ponta a ponta permanecem pendentes. Não houve captura de tela. O Chromium interno expirou na inicialização e o Edge restrito falhou antes de renderizar. A execução aprovada com perfil descartável permitiu os ensaios acima. Os retestes corrigiram pressupostos da automação sobre paginação, seletores e privacidade no LGPD.

O ensaio complementar aprovou um segundo Evento, RG, certidão e Pessoa pela UI. As quatro mutações devolveram 200 e os GETs de recarga das filas devolveram 200. O MySQL confirmou Evento ativo e cadastro com os dois documentos aprovados. A última asserção DOM esperava `aria-busy="false"` onde os renderizadores removem o atributo ao concluir. O resultado persistido está comprovado. A retirada desses dois itens da tela por essa asserção ficou sem prova. O caso de reprovação de ONG, o fluxo de presença no navegador como designado e o download do certificado estudantil também ficaram sem prova de UI.

## 5. Execuções e regressão

| Verificação | Ambiente, comando e resultado |
| --- | --- |
| Runtime | PHP **8.5.5**, Laravel **13.35.0**, PHPUnit **12.5.38**, Node **24.16.0**, MySQL **8.4.3** e Vite **8.2.1**. PHP 8.4 não estava disponível no runtime inspecionado. |
| MySQL | Instância própria em `127.0.0.1:34689`, UUID `1705c0e7-ea29-11f1-a25f-0250784c2913`, diretório descartável `.qa-final-815617f/mysql-data`. Cliente e PDO coincidiram em host configurado, porta, schema, UUID e diretório antes das gravações. Schemas separados `ajudae_qa_final_suite`, `ajudae_qa_final_browser` para HTTP e concorrência, e `ajudae_qa_final_ui` para Edge. As migrations existentes foram aplicadas somente nesses schemas. |
| PHP integrado | `AJUDAE_WEB_AUTH=true`, `AJUDAE_DEMO=false`, MySQL descartável e `DB_URL` vazio. `php vendor/bin/phpunit --do-not-cache-result --filter 'Bloco9Web\|Bloco81Web(Participation\|EventModeration\|Certificate\|PessoaRegistration)\|AuthorizationAdmin\|AvaliacaoConfiabilidade\|FinalizacaoEventoCertificado\|PadrinhoWorkflow\|XpCampanhaRanking\|PresencaAusenciaDesistencia\|PessoaValidation(Workflow\|Access)\|OrganizadorWorkflow\|Documento(StorageLgpd\|Pessoa)\|AntecedentesRenovacao\|Bloco81PrivacyNotifications\|IntegratedNavigation\|ExclusaoContasPendentesScheduler'`. **162 aprovados, 2.275 asserções, zero falhas ou ignorados informados.** O recorte desta execução incluiu explicitamente exclusão agendada e regressões de cadastro/documentos. São 6 testes e 58 asserções a mais que o total anteriormente relatado. O filtro exato da contagem anterior de 156 não consta no relatório de correção. |
| PHP demonstrativo | `AJUDAE_WEB_AUTH=false`, `AJUDAE_DEMO=true`, SQLite em memória e sessão em memória. `php vendor/bin/phpunit --do-not-cache-result --filter 'VisualFour\|VisualParticipation'`. **18 aprovados, 309 asserções, zero falhas ou ignorados.** |
| JavaScript | `node --test --test-isolation=none --test-concurrency=1 --test-reporter=spec` nos 14 arquivos `tests/Frontend/*.test.mjs`. **95 aprovados, zero falhas ou ignorados.** Inclui unidade de respostas 500 e falha de rede, além de helpers demonstrativos. |
| Build e rotas | `npm.cmd run build` gerou **40 módulos** e manifest. `php artisan route:list -v --except-vendor` listou **350 rotas**. `php -l` passou nos **29** PHP e Blade acrescentados ou modificados. `node --check` passou nos cinco JS do delta. |
| Whitespace | `git diff --check origin/dev...HEAD` apontou whitespace final em [relatório anterior, linha 3](QA_REVALIDACAO_INDEPENDENTE_LUIZ_MIGUEL_2026-11-27.md#L3), em quebra explícita de Markdown. Registrado como ocorrência documental. |
| HTTP direto | Documentos, sidebar e histórico **31/31**. Demais jornadas **40/41** no primeiro ensaio. A diferença foi a expectativa de página no caso de histórico. A nova consulta localizou a inscrição na página 2 e confirmou o estado esperado. Os códigos observados incluem 401, 403, 409, 419 e 422, com recuperação dos dados por GET e consultas de persistência. |
| Falhas iniciais de ferramenta | PowerShell rejeitou executar o arquivo `.ps1`. A sessão executou os mesmos comandos diretamente. Inicialização precoce do MySQL encontrou um arquivo em uso, e o primeiro cliente interpretou de forma incorreta a sintaxe abreviada de host. Ambos foram corrigidos antes do PDO. Os primeiros testes demonstrativos e o helper Blade antecederam o manifest Vite. As duas novas execuções após o build passaram. |

Os testes documentais novos de PHPUnit usam `Storage::fake('local')`. A verificação de PDF desta rodada ocorreu também em HTTP real com disco local privado e cliente separado. Os testes JS cobrem sobretudo funções demonstrativas, cliente HTTP e sintaxe dos scripts renderizados. A evidência das telas integradas decorre dos percursos registrados separadamente.

## 6. Concorrência

Foram usados pares de processos e os mesmos registros sintéticos dentro do schema de HTTP. O coordenador manteve o lock MySQL da linha relevante, aguardou as duas sinalizações de prontidão e liberou a transação. Os timestamps mostram os dois inícios anteriores à liberação em cada par. Para serviços, os IDs de conexão foram distintos. Os ensaios HTTP usaram duas sessões e processos PHP em `8891` e `8892`.

| Cenário | Desfecho observado | Inspeção final no MySQL |
| --- | --- | --- |
| XP e desativação da campanha | Conexões 223/224. Ambos os serviços concluíram. | Uma transação de 200 XP com um fator. Histórico anterior com 100 XP, campanha desativada, um log de atualização e zero concessões na repetição. |
| Moderação de Evento por dois operadores | Aprovação 409, reprovação 200. | Evento reprovado, uma decisão auditada e ator registrado. |
| LGPD · assumir e responder | Em cada um dos dois pares, 200 para o atendente que ficou com o protocolo e 403 para o outro. | Protocolo recusado pelo responsável que o assumiu, com um log de atribuição e um de decisão. |
| Cancelamento e presença | Conexões 260/262. Cancelamento venceu, presença entrou em conflito, concessão ficou bloqueada. | Agenda cancelada, inscrição continuou aprovada e zero transações de XP. |
| Certificado e encerramento | A emissão antes do fechamento entrou em conflito. As duas emissões depois do fechamento retornaram ID igual, em conexões 285/286. | Um certificado, uma nota e uma transação XP. |

As três corridas originais de 26/11 permanecem como evidência histórica. O delta posterior das correções documentais e da auditoria de apadrinhamento ficou fora das transações daqueles três casos.

## 7. Defeito confirmado

### QA-F01 · Médio · Setup padrão falha sem `.env.example`

- **Reprodução.** Abrir clone limpo de `815617f` e verificar que `.env` e `.env.example` estão ausentes. Conferir as duas etapas de ambiente da tarefa `setup` em [composer.json](../../composer.json#L43). Executar `php artisan key:generate --no-interaction` depois da etapa de cópia prevista. O ensaio independente isolou o passo da chave. O setup completo também chama migração.
- **Esperado.** O template rastreado permite criar o arquivo de ambiente e gerar a chave no setup padrão.
- **Obtido.** O Git confirma a exclusão de `.env.example`. A etapa de chave falha com `file_get_contents(.../.env): Failed to open stream: No such file or directory`. As suítes puderam rodar porque as variáveis sintéticas foram injetadas no processo.
- **Impacto.** O checkout publicado não contém um pré-requisito do próprio setup. Afeta reproduzir o ambiente da branch a partir de clone novo.
- **Menor correção.** Repor um `.env.example` sanitizado compatível com as variáveis do branch, ou ajustar a tarefa de setup para criar a configuração mínima por um mecanismo rastreado equivalente. Reexecutar o bootstrap em outro clone limpo com banco descartável.

## 8. Ambiguidades, riscos e lacunas

- **Interpretação de RN002/RN003/RN006/RN032.** A revisão de cadastro exige atribuição individual vigente. Na renovação, o catálogo vigente admite a capacidade `usuarios.validar` e restringe o documento a certidão ativa, pendente, de voluntário aprovado sem exclusão pendente e com arquivo retido. A referência local exige necessidade de análise e não determina se operadores da fila precisam de atribuição individual na renovação. A decisão necessária é confirmar o escopo de acesso pela capacidade da fila. [Policy](../../app/Policies/DocumentoPessoaPolicy.php#L11) · [modelo](../../app/Models/DocumentoPessoa.php#L58)
- **Risco estático de consistência de retenção.** A projeção do detalhe Web usa `hasRetainedFile()`. A extensão de renovação da policy também usa essa condição, mas a primeira regra da policy delega a autorização de titularidade ou posse do cadastro. Durante a retenção operacional pendente de expurgo, falta provar a paridade do bloqueio do link com o controlador para essas outras autorizações. Esta análise não confirmou acesso indevido a arquivo real. [Resource](../../app/Http/Resources/DocumentoPessoaResource.php#L34) · [controlador](../../app/Http/Controllers/Api/DocumentoPessoaController.php#L82)
- **RN021.** O contrato integrado documenta que a ONG proprietária pode registrar nota mesmo havendo organizador designado. A referência normativa local reserva a nota ao designado nesse caso. O caso auditado seguiu a decisão D04/D05 do contrato. A redação normativa continua a reconciliar. [Contrato](CONTRATOS_PARTICIPACAO.md#autorização-por-registro)
- **Exclusão reversível.** Ensaio do serviço com relógio controlado e conta sintética em MySQL confirmou janela de 604.800 segundos. Cancelou um segundo antes do limite e recusou no instante exato de outro agendamento. A conta permaneceu preservada e a sequência de agendamento, cancelamento e novo agendamento teve três logs. O percurso de exclusão pelo navegador e a execução irreversível ficaram fora da prova. [Evidência](evidencias/qa-final-luiz-miguel-2026-11-27/boundaries.json)
- **Cobertura.** PHP 8.4, instalação fria de dependências, expurgo físico real, combinações completas de roles, fuso do navegador e PDF de certificado estudantil permanecem fora da prova. A cobertura de permissões combina amostras de interface, testes HTTP e PHPUnit.
- **Integração coletiva.** Revalidar as fronteiras com identidade e documentos, Agendas e gestão de Eventos, designação, certificados e os dois lados da jornada LGPD quando as branches forem integradas na `dev`.
- **Railway e S3.** Homologação externa pendente. A revisão de PDF usou disco privado local, a suíte rodou com PHP 8.5.5 e o servidor HTTP foi apenas local. Nenhuma operação em Railway, S3 ou banco principal.

## 9. Checklist pendente para PR

- [ ] Entregar o template de ambiente requerido pelo setup ou ajustar o processo de bootstrap e validá-lo em clone limpo com schema descartável.
- [ ] Confirmar com os responsáveis se `usuarios.validar` atende à necessidade de acesso na fila de renovação e alinhar a redação normativa de RN021 ao contrato.
- [ ] Fechar as lacunas de navegador registradas na seção 4 e repetir a regressão no novo SHA publicado.
- [ ] Confirmar PHP 8.4 e revalidar a integração coletiva nos fluxos dependentes. Homologação Railway/S3 segue etapa própria.

## 10. Evidências e preservação

[Evidências estruturadas](evidencias/qa-final-luiz-miguel-2026-11-27/). O delta entregue consiste deste relatório e seus registros de QA. Nenhum ZIP de código foi necessário. Sem alteração na implementação, stage, commit, push, PR, merge, rebase ou deploy.

Atualização pontual proposta para `AGENTS.md`: registrar o SHA publicado e a regressão independente do Bloco 9, o bloqueio do setup sem `.env.example` e o escopo em aberto da autorização de renovação, com referência a este relatório.

### Decisões para você responder

Nenhuma para encerrar esta execução. A escolha de escopo de autorização da renovação requer confirmação contratual da equipe antes da liberação da jornada.
