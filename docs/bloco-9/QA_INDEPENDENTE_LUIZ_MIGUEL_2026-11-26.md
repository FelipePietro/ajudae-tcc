# Bloco 9 · QA independente de Luiz Miguel

26/11/2026 · Ajudaê · `feat/participacao-administracao`

## 1. Resumo executivo e veredito

**REPROVADO — CORREÇÕES OBRIGATÓRIAS**

O checkout apresentou integração Web nas nove frentes auditadas. A suíte focada passou com **122 testes e 1.463 asserções** em MySQL descartável. Durante a sessão HTTP, as **21 consultas** atenderam aos códigos e formatos esperados. O Vite compilou 40 módulos e os **95 testes JavaScript** passaram.

O bloqueio para o PR está no Git. O HEAD e a referência local `origin/dev` coincidem, e a integração auditada depende de alterações locais e de módulos e views não rastreados. Portanto, o resultado comprovado pertence ao checkout. A [evidência da execução](evidencias/qa-independente-luiz-miguel-2026-11-26/execucao.json) registra as consultas e as medições.

## 2. Git auditado

| Item | Resultado |
| --- | --- |
| Checkout | `C:\laragon\www\ajudae-tcc`, branch `feat/participacao-administracao` |
| HEAD, rastreamento da branch e `origin/dev` local | `74609bfd03cfbdbfa8dbfdcaf603c5dabd91794e` nos três |
| Comparação | `git diff --stat origin/dev...HEAD`, `git diff --name-status origin/dev...HEAD` e `git log --left-right origin/dev...HEAD` sem delta. Ausência de branch `dev` local. `git ls-remote --exit-code origin refs/heads/dev` falhou na conexão a `github.com:443`, portanto a comparação usa a referência disponível no checkout. |
| Estado inicial | 14 caminhos rastreados alterados, incluindo a remoção de `.env.example`, e oito não rastreados. A branch atual já era a branch-alvo. |
| Delta local auditado | Controlador e serviços de participação, navegação administrativa, módulos JS de histórico/gestão/administração e suas views. `c1ff91f...HEAD` foi usado apenas como contexto do backend já comitado, com 71 arquivos. |
| Estado final | O delta anterior permanece presente. Foram acrescentados o relatório e as evidências de QA. Sem commit, push, PR, merge ou deploy. |

O arquivo [LUIZ_MIGUEL_INTEGRACAO.md](LUIZ_MIGUEL_INTEGRACAO.md) já atribuía a remoção de `.env.example` a trabalho anterior. A origem da remoção permanece fora do escopo desta auditoria.

## 3. Matriz das nove frentes

`Integrado` indica ligação Web real verificada no checkout. As limitações por linha definem a evidência disponível para o fluxo completo.

| Frente | Página e contrato confirmado | Classificação e verificação | Pendente |
| --- | --- | --- | --- |
| 1. Histórico pessoal | `/minhas-participacoes` · `GET /minhas-participacoes/dados` | **Integrado**. Dois titulares consultados por HTTP, cada qual com sua projeção. A inscrição de ausência do primeiro apareceu só para ele. Visitante recebeu 401. No Chromium apareceram Eventos ativos e concluídos, diversas Agendas e opções de acordo com o estado. | Volume superior à primeira página, ordenação com empates, filtros solicitados e renderização da lista vazia. O contrato disponível recebe `page` e a interface agrupa por status. |
| 2. Presença e monitoria | `/ong/eventos#gestao-participacoes` · `PATCH /gestao/inscricoes/{inscricao}/presenca`, `GET/PATCH …/monitoria` | **Integrado**. A interface usa `presenca` como único campo de presença. A suíte checou persistência, autoria, negativa antes do início, mudança de estado e justificativa de monitoria após encerramento. O novo GET confirmou presença e monitoria validada nas fixtures separadas. | Submissão em Chromium com os estados válidos, inválidos e o papel de organizador. |
| 3. Justificativas | Histórico pessoal e gestão · `GET/POST/PATCH /gestao/inscricoes/{inscricao}/justificativa` | **Integrado**. PHPUnit criou, decidiu e conservou a ausência, com negativa de autodeferimento. O HTTP e o Chromium leram protocolo deferido, motivo de envio e motivo da decisão já gravados. A definição usa 2.000 caracteres no modelo de formulário e no serviço. | Nova submissão pelo navegador, indeferimento de amostra e projeção de autoria, descrita em QA-04. |
| 4. Avaliações e finalização | Gestão e histórico · `GET/POST /gestao/eventos/{evento}/avaliacoes[…]`, `PATCH /gestao/agendas/{agenda}/finalizar`, `POST /gestao/eventos/{evento}/finalizar` e avaliação da ONG | **Integrado**. A suíte verificou uma nota Pessoa + Evento, bloqueio por Agenda aberta ou nota ausente, Agenda vazia, limites temporais, XP e certificado. Dois GETs confirmaram a nota do voluntário e a nota opcional já persistida. O formulário para outra avaliação elegível apareceu no Chromium. | Submissões, vários estados da UI e recarga após gravação no Chromium. Seção 8 para concorrência. |
| 5. Validação de Pessoas e documentos | `/admin/candidatos` · `/admin/validacoes-pessoa/…`, `PATCH /admin/documentos/{documento}/status` e `GET /admin/antecedentes/renovacoes` | **Integrado para validação, parcial para moderação de contas**. A fila, o detalhe e renovações responderam com dados reais. O navegador abriu o detalhe. PHPUnit confirmou regras de posse, reprovação definitiva restrita ao Master, histórico e documento privado em storage local/fake, conforme o teste. | Diretório `/admin/usuarios` (QA-03), decisão visual com documentos sintéticos e ensaio em bucket isolado. |
| 6. ONG e Eventos | `/admin/cadastros-ong`, `/admin/fila-eventos` · `GET …/dados`, `PATCH …/status` ou `…/decisao` | **Integrado**. Consultas autenticadas, suites de decisão institucional e moderação, e páginas abertas no Chromium. Os contratos de motivo são distintos, conforme QA-04. | Mutação pelo navegador seguida de feed, filtros e aprovação de Evento sem Agenda no mesmo percurso. |
| 7. Promoção e apadrinhamento | `/admin/upgrades-org`, `/admin/conclusoes-padrinho` · `/admin/solicitacoes-organizador/…` e `/admin/padrinhos/conclusoes/…` | **Integrado**. A suíte confirmou elegibilidade, análise e histórico. A conta exclusiva abriu a fila por link do painel e recebeu 403 na fila de Pessoas em HTTP. O estado vazio de apadrinhamento ficou visível. | Mutação no navegador, item de navegação ausente (QA-02) e contrato de motivo (QA-04). |
| 8. Campanhas de XP | `/admin/campanhas-xp` · lista, criação, edição e alteração de `ativa` | **Integrado**. GET confirmou paginador. PHPUnit confirmou capacidade própria, atualização auditada e histórico de XP congelado. A tela abriu no Chromium. | Criação e alteração no navegador, com recarga e cenário de edição durante concessão de XP. |
| 9. LGPD e auditoria | `/admin/solicitacoes-lgpd`, `/admin/responder-lgpd`, `/admin/log-acoes` | **Integrado**. GET de fila e log reais. O filtro `lgpd.status_atualizado` trouxe somente a ação solicitada e omitiu detalhes brutos. As suítes confirmaram escopo do protocolo, proteção das capacidades e registro. As três telas abriram no Chromium. | Envio e disputa por dois atendentes no navegador, conta somente de resposta e janela de exclusão de sete dias nesta rodada. |

O registro de rotas confirmou `/organizador/eventos`, que reutiliza a view de Eventos da ONG. A rota HTML de gestão confirmada no navegador foi `/ong/eventos`. As rotas operacionais `/gestao/…` atendem ao organizador habilitado por contrato e pela suíte. A navegação inteira do organizador no Chromium ficou pendente. Os métodos e os parâmetros de mutação são os registrados em [Contratos de participação](CONTRATOS_PARTICIPACAO.md) e no [Catálogo Web](CATALOGO_WEB.md). A view `participacoes/gestao.blade.php` citada pelo roteiro não é o ponto de entrada integrado da ONG.

## 4. Matriz de permissões

| Persona | Ação observada | Resultado e evidência |
| --- | --- | --- |
| Visitante | Histórico JSON e lista de inscrições | 401 no HTTP independente e na suíte, respectivamente. |
| Pessoa aprovada titular | Histórico, monitoria, justificativa própria e avaliação de ONG | 200 nas consultas. Criação e proteção da titularidade verificadas no PHPUnit. |
| Pessoa aprovada, terceiro | Histórico próprio e tentativa de acessar operações da gestão | 200 no histórico sem inscrição do outro titular. Gestão negada na suíte. |
| Pessoa fora da role administrativa com permissão atribuída | Operação administrativa | Negativa verificada nos testes de capacidades e role. A rodada HTTP e o Chromium usaram contas sintéticas sem essa combinação. |
| Administrador comum sem capacidade específica | Fila de Pessoas, promoções, logs e LGPD | 403 nas combinações cobertas pela suíte. Conta de apadrinhamento retornou 403 em validação de Pessoas no HTTP real. |
| Administrador comum com capacidade própria | Fila de apadrinhamento, campanhas e validações correspondentes | Acesso permitido nos cenários correspondentes da suíte e de HTTP. |
| Admin Master | Reprovação definitiva e tentativa de atuar no encerramento operacional | Reprovação definitiva coberta pela suíte de Pessoa. Gestão de Evento exige responsável operacional. A matriz desta rodada teve casos de negativa para administrador, sem repetição da finalização com sessão Master no navegador. |
| ONG titular ou terceira | Gestão de inscrições, presença e finalização | Titular permitida na suíte e nas consultas HTTP. Terceira recebeu 403 na suíte de participação. |
| Organizador independente, designado ativo ou com vínculo encerrado | Gestão e encerramento no Evento permitido | Independente titular e designado ativo permitidos nos casos da suíte. Sem designação ou vínculo encerrado, as operações testadas de gestão receberam 403. |

A cobertura é por fronteira comprovada, com autorização por recurso e capacidade. O produto cartesiano de onze personas com todas as mutações permanece pendente.

## 5. Revisão crítica das suítes

- **Web F4.** `Bloco9WebEventParticipationTest` verifica a resposta e o banco, incluindo autoria da presença e do encerramento, manutenção da ausência, uma transação de XP e efeito de vínculo revogado. A suíte usa sessão Laravel preparada pelo teste. A execução HTTP complementou com login e cookie reais.
- **Web F1/F3/F6.** `Bloco9WebOngRegistrationTest` usa storage fake e verifica decisão, rollback e trilha. `Bloco9WebAccountOperationsTest` cobre fronteiras entre permissões e a gravação de campanha. A presença de `Storage::fake` impede inferência sobre Railway S3. Parte da validação de Pessoas, promoção, apadrinhamento, privacidade e campanhas vem de casos de API ou da camada compartilhada, com alcance limitado para Blade e JS.
- **Tempos, XP e certificado.** `AvaliacaoConfiabilidadeTest`, `PresencaAusenciaDesistenciaTest`, `XpCampanhaRankingTest` e `FinalizacaoEventoCertificadoTest` exercitam limite opcional da avaliação, fronteira de 24h, exceções de cancelamento, integridade de XP e certificados. Testes sequenciais comprovam idempotência para os cenários executados. As corridas desta execução constam da seção 8.
- **Frontend.** Os 95 testes abrangem funções de domínio demonstrativo e scripts renderizados. Têm valor de regressão, mas os módulos novos de administração e gestão ainda carecem de testes automatizados de interação com os contratos reais. O build prova resolução dos imports e compilação para o checkout.
- **Ignorados.** PHPUnit focado e JavaScript terminaram com zero ignorados. Suites integradas condicionam casos à ativação de `AJUDAE_WEB_AUTH`, que ficou ativa no filtro PHP. A suíte PHP completa, migração histórica de certificado e a execução dedicada das Feature demonstrativas ficaram fora do filtro.

## 6. Resultados reproduzidos e ambiente

- **Git e inspeção.** `git status --short --branch`, `git branch -vv`, `git worktree list`, `git log -5 --oneline`, métodos e diffs de seção 2, e `git diff --check`. O delta rastreado passou na conferência de whitespace.
- **Rotas.** `php artisan route:list -v --except-vendor` com modo Web integrado mostrou 350 rotas. Conferidos métodos e agrupamentos das frentes auditadas. A consulta usou o Laravel do checkout.
- **PHP.** PHP 8.5.5, PHPUnit 12.5.38, MySQL 8.4.3, `DB_HOST=127.0.0.1`, `DB_PORT=34627`, `DB_DATABASE=ajudae_qa9_independente`. O preflight comparou schema, porta, UUID e diretório temporário pelo cliente MySQL e pelo PDO efetivo do Laravel antes de executar a suíte. `vendor/bin/phpunit --do-not-cache-result --testdox --filter 'Bloco9Web|Bloco81Web(Participation|EventModeration|Certificate)|AuthorizationAdmin|AvaliacaoConfiabilidade|FinalizacaoEventoCertificado|PadrinhoWorkflow|XpCampanhaRanking|PresencaAusenciaDesistencia|PessoaValidation(Workflow|Access)|OrganizadorWorkflow|DocumentoStorageLgpd|Bloco81PrivacyNotifications'` produziu **122 testes aprovados e 1.463 asserções**. Sessão/cache/e-mails em memória e armazenamento de teste.
- **HTTP.** O servidor local `127.0.0.1:8809` usa outro schema de homologação sintético, `ajudae_luiz_homologacao`, no MySQL `127.0.0.1:33429`. Identidade conferida por schema, porta, UUID e diretório descartável. A rodada abriu as sessões com os formulários normais de login. Executou 21 verificações de leitura, das quais 19 esperavam 200, uma 401 e uma 403. Os resultados bateram. As leituras de dados persistidos usaram fixtures anteriormente preparadas. Para o Chromium, as mesmas sessões sintéticas foram inicializadas por um auxiliar temporário de loopback. Aplicação e configuração do checkout foram preservadas.
- **JavaScript e build.** Reexecução de `node --test` nos `*.test.mjs` com PHP no PATH do processo. 95 aprovados. `npm.cmd run build -- --outDir <temporário>` aprovou 40 módulos. `php -l` nos 10 PHP/Blade rastreados alterados e `node --check` nos cinco módulos JS locais aprovados.
- **Falhas de ferramenta resolvidas.** A primeira tentativa JavaScript falhou porque o helper de Blade não encontrava PHP no PATH. O primeiro preflight do MySQL descartou a comparação do caminho retornado em modo batch. A segunda abertura do MySQL encontrou a porta 33567 ocupada. A execução válida saiu em porta distinta e com o caminho normalizado.
- **Fontes.** O PDF oficial de regras no caminho indicado pelo AGENTS.md estava indisponível. Foram consultados [texto local das RN](../../Ajudae_Regras_de_Negocio_V1.md), [extração local](../referencia-rn-v1-extraida-2026-10-21.md), catálogo e contratos. O roteiro anexo usa o manual de quatro integrantes para atribuição. A [matriz do repositório](MATRIZ_INTEGRACAO.md) mantém a divisão anterior em seis.

## 7. Achados priorizados

### QA-01 · Alto · Bloqueio de entrega comprovado

- **Pré-condição e reprodução.** Na branch-alvo, executar `git rev-parse HEAD origin/dev`, `git diff --name-status origin/dev...HEAD` e `git status --short`. Operação de Git somente leitura.
- **Esperado.** O commit candidato ao PR contém o código que passou pela auditoria.
- **Observado.** A referência local de base converge com HEAD e não apresenta diff. O checkout tem as mudanças e sete artefatos de aplicação novos fora do índice. Por exemplo, [event-moderation.js](../../resources/js/modules/event-moderation.js#L2) importa `admin-operations.js`, e [participation.js](../../resources/js/modules/participation.js#L2) usa outros dois módulos que ainda são não rastreados.
- **Impacto e revalidação.** O Git não oferece o delta auditado a um PR entre as referências observadas. Depois de incluir o delta autorizado e revisar `.env.example`, confirmar o diff contra `origin/dev` atualizado e compilar em checkout limpo do SHA candidato.

### QA-02 · Médio · Bug reproduzido na navegação de apadrinhamento

- **Pré-condição e reprodução.** Entrar com uma conta sintética que possua somente `padrinhos.validar_conclusao`. Abrir Administração e escolher **Abrir conclusões de apadrinhamento**. Operação de navegação por GET, sem payload.
- **Esperado.** A barra lateral administrativa apresenta a área que a conta pode operar, com o estado ativo na página correspondente.
- **Observado.** A área e o link do painel funcionam. Na barra lateral, o grupo Moderação mostra **Painel admin** sem o item de apadrinhamento. [WebNavigation](../../app/Support/WebNavigation.php#L30) registra a área. A lista fixa do [admin-sidebar](../../resources/views/components/admin-sidebar.blade.php#L24) omite a chave.
- **Impacto e revalidação.** Para reencontrar a fila pelo menu administrativo, o operador precisa voltar ao painel. Incluir a área no grupo pertinente e testar o destaque ativo com a conta exclusiva, além de confirmar que perfis sem capacidade continuam sem o item.

### QA-03 · Médio · Lacuna de implementação na moderação de Pessoas

A página HTML `/admin/usuarios` existe. No modo integrado, o campo de busca fica desabilitado e a view informa indisponibilidade de consulta. O backoffice de validação em `/admin/candidatos` continua sendo outra operação e tem ligação real. A evidência é de código e catálogo, sem percurso autenticado específico dessa rota nesta rodada. Confirmar com a equipe se o diretório de moderação pertence a esta entrega. Se pertencer, revalidar a integração sob `usuarios.moderar`. [usuarios.blade.php](../../resources/views/admin/usuarios.blade.php#L18)

### QA-04 · Médio · Divergências e lacunas contratuais

- **Justificativas.** O serviço registra autoria e mantém a ocorrência, mas o Resource Web projeta identificadores do protocolo e da inscrição, motivos e datas, sem autoria da decisão. O navegador confirmou o conteúdo projetado. Definir se o titular precisa receber uma identificação minimizada do decisor. [JustificativaParticipacaoResource](../../app/Http/Resources/JustificativaParticipacaoResource.php#L11)
- **Apadrinhamento.** A UI exige motivo em reprovação. O contrato executável aceita motivo nulo, como registrado no catálogo. O roteiro pressupõe validação 422 para motivo ausente. Alinhar a decisão com a equipe e conferir as duas camadas. [AdminPadrinhoController](../../app/Http/Controllers/Api/AdminPadrinhoController.php#L38)
- **ONG.** O contrato Web da decisão institucional recebe `status_ong`. O roteiro propõe testar motivo obrigatório sem apontar um campo implementado. O Evento usa contrato distinto, que prevê motivo em reprovação. A decisão de exigir motivo institucional precisa constar do contrato antes de atribuir falha à integração. [Catálogo Web](CATALOGO_WEB.md#f3--administração-organizadores-e-ong)

## 8. Integridade de dados e concorrência

A suíte confirmou autoria autenticada da presença e do encerramento, preservação do estado original depois de decidir justificativa, monitoria por Agenda, nota única por Pessoa e Evento, ausência de XP para Agenda cancelada, idempotência de XP e exclusão de Agenda cancelada da carga de certificado. Os testes de limite da avaliação de ONG e de 24h foram reproduzidos via suíte. Fuso de navegador distinto de servidor e relógio no limite exato de todas as jornadas Web permanecem pendentes.

A rodada concorrente usou outro MySQL descartável, `127.0.0.1:34631`, schema `ajudae_qa9_independente`, UUID `59ae42f1-ea03-11f1-bd24-0250784c2913`. Após o preflight, `php artisan migrate --force --no-interaction` preparou somente esse schema. O ensaio temporário usou dois processos PHP com conexões MySQL distintas em cada cenário. Ambos chegavam à barreira sobre a mesma linha de Evento antes de sua liberação. Foram chamados os serviços usados pelas rotas Web. Os resultados abaixo são desfechos dos serviços, sem códigos HTTP medidos.

| Corrida | Desfecho observado | Comprovação no MySQL |
| --- | --- | --- |
| Fechar o mesmo Evento duas vezes | Um sucesso e um conflito | Evento finalizado, autor gravado e uma transação de XP para a Agenda. |
| Presença contra encerramento da Agenda | Ambos concluíram nesta ordem de aquisição | Presença gravada, Agenda finalizada e uma transação de XP. |
| Avaliação contra encerramento do Evento | Avaliação gravada, primeira finalização em conflito | O Evento ficou ativo com uma nota. A finalização seguinte concluiu. |

Resultados em [concorrencia.json](evidencias/qa-independente-luiz-miguel-2026-11-26/concorrencia.json). **NÃO VALIDADO nesta execução** para: edição de campanha simultânea ao cálculo de XP, dois operadores com decisões administrativas conflitantes, dois atendentes do mesmo protocolo LGPD, cancelamento contra presença/XP e uma repetição HTTP com cookies/sessões reais para as corridas de serviços. O ensaio também não mediu emissão paralela de certificados. A revisão do código achou transações e bloqueios em campanha, moderação e LGPD. Isso sustenta o desenho de consistência, enquanto os estados finais dessas corridas ainda precisam de teste dedicado.

## 9. Segurança e LGPD

O modo Web autentica por sessão tipada. O cliente compartilhado envia CSRF nas mutações, restringe o destino à mesma origem e diferencia 401, 403, 404, 409, 419 e 422. As verificações de titularidade e de permissões observadas estão na seção 4. O módulo renderiza os dados retornados como texto e exige mesma origem para links de arquivos privados. A suíte cobriu downloads sintéticos, registro de auditoria e falha do storage. O log Web omite detalhes, e a fila LGPD reduz a projeção de casos cuja atribuição não é do leitor. A rodada não fez upload de documentos reais, acesso a S3 nem execução de expurgo ou exclusão irreversível. Pendente matriz completa de acessos a registros privados, revisão de minimização nos casos de resposta exclusiva e validação específica de 419 e 500 no navegador.

## 10. Usabilidade e responsividade

Em Chromium no navegador interno do Codex, foram medidos histórico, gestão da ONG, validação de Pessoas, cadastros de ONG, fila de Eventos, promoções, campanhas, consultas e resposta LGPD, logs e conclusões de apadrinhamento. Nenhuma das amostras ultrapassou 360, 768 ou 1280 px de largura de conteúdo. O histórico mostrou foco perceptível no controle da justificativa acionado por Enter. Foi possível abrir o detalhe de Pessoa e navegar do painel exclusivo à fila de apadrinhamento. Nesta fixture, a fila estava vazia. A captura abrangeu o estado vazio, a tela de entrada e a justificativa aberta. As imagens foram observadas no navegador, sem exportação de arquivos binários para o relatório. As medidas e os limites estão na [evidência](evidencias/qa-independente-luiz-miguel-2026-11-26/execucao.json).

As amostras administrativas abrangeram principalmente a página e a primeira carga. Formulários em estados diversos, erros após envio, rede desligada, Escape e travessia completa por teclado ficaram pendentes. As mutações PHP foram exercitadas em outro schema descartável. O Chromium usou o schema sintético anterior apenas para consultas de domínio.

## 11. Dependências da integração

- **Felipe** · identidade, login tipado, validação documental e storage. Integrar uma jornada de cadastro e renovação que gere os dados consumidos pela fila e confirme o download autorizado.
- **Enrico** · gestão de Eventos e Agendas, seleção e publicação. A página da ONG apresentou a seção integrada de participações e, em outro trecho, o aviso de edição indisponível. A coordenação da página de gestão e da edição pertence ao teste do conjunto das branches.
- **João Pedro** · histórico de XP e certificados na Jornada, e origem dos protocolos de privacidade conforme o manual de quatro integrantes. Confirmar o caminho titular → pedido → atendimento → atualização percebida pelo titular.
- **Railway/S3** · o resultado usa somente ambientes locais. A homologação integrada de armazenamento privado e de PHP 8.4 no Railway permanece pendente.

## 12. Critérios para revalidar o PR

1. Conferir que o commit candidato contém, em relação à `dev` atualizada, todos os módulos, views e contratos que sustentam os nove fluxos. Revisar o destino de `.env.example` com o responsável, sem misturar mudanças alheias.
2. Compilar e repetir a suíte focada a partir de checkout limpo do SHA candidato. Refazer as verificações HTTP com identidades sintéticas.
3. Reexecutar decisões e recargas em Chromium por persona, incluindo fila com registro pendente, erro de campo, repetição de ação, ausência de capacidade, 419 e falha de rede. Para cada gravação relevante, registrar o novo GET ou o estado persistido.
4. Validar a navegação do apadrinhamento, decidir a abrangência de `/admin/usuarios`, autoria da justificativa e contrato de motivo. Completar as corridas pendentes conforme seção 8.
5. Confirmar a sequência entre as branches e submeter documentos sintéticos à homologação em um bucket de teste isolado antes de declarar armazenamento homologado.

### Decisões para você responder

Nenhuma para encerrar esta auditoria. Os pontos contratuais a alinhar antes de homologar estão na seção 7.
