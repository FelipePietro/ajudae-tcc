# QA independente de revalidação · Luiz Miguel · Bloco 9

**Execução** 27/11/2026, America/Sao_Paulo  
**Candidato** `9fcd085a1342be00141ec36d1fa92d8f726284d1`, branch `feat/participacao-administracao`

## 1. Resumo executivo e veredito

**REPROVADO — CORREÇÕES OBRIGATÓRIAS**

O commit local é reproduzível e contém o código antes restrito ao checkout. Um bloqueio funcional novo impediu a revisão de antecedentes de Pessoas já aprovadas. O operador autorizado pela fila recebeu `403` para abrir o PDF local, mas conseguiu aprovar aquele mesmo documento e a decisão persistiu. A rodada executou 157 testes PHP, 95 testes JavaScript, 105 verificações HTTP de contrato e sete pares concorrentes em cinco cenários. **Zero fluxos E2E foram executados** porque o controle do navegador foi interrompido.

As fontes são o [QA de 26/11](QA_INDEPENDENTE_LUIZ_MIGUEL_2026-11-26.md), as [correções de 27/11](CORRECOES_QA_LUIZ_MIGUEL_2026-11-27.md), o [registro da integração](LUIZ_MIGUEL_INTEGRACAO.md), o [catálogo Web](CATALOGO_WEB.md), os [contratos F4](CONTRATOS_PARTICIPACAO.md), a [matriz](MATRIZ_INTEGRACAO.md), a [prontidão](PRONTIDAO.md) e as [RN V1 locais](../../Ajudae_Regras_de_Negocio_V1.md), além do manual de quatro integrantes e do código do SHA. O PDF oficial no caminho indicado pelo AGENTS.md estava indisponível.

## 2. Git e reprodutibilidade

| Checagem | Evidência |
| --- | --- |
| Worktree original | Branch correta, um commit à frente da referência publicada, com remoção preexistente de `.env.example`. A remoção foi preservada no checkout original. |
| Árvore candidata | SHA `9fcd085a1342be00141ec36d1fa92d8f726284d1`, árvore `e7e716a8865fbae9770ddcd0b900370b98c16764`. O delta de 33 arquivos contém os cinco módulos JS citados no roteiro, as views e os componentes necessários. Inclui `.env.example` e exclui `.env`, perfis, cookies, dumps e credenciais de teste. |
| Base | `merge-base` e `origin/dev` local apontam para `74609bfd03cfbdbfa8dbfdcaf603c5dabd91794e`. O [GitHub confirmou a mesma referência de dev](https://github.com/FelipePietro/ajudae-tcc/commit/74609bfd03cfbdbfa8dbfdcaf603c5dabd91794e) e o mesmo SHA na branch remota de Luiz. A API retornou 422 na consulta direta a `9fcd085`. O transporte Git por HTTPS falhou na porta 443. O candidato local continua sem publicação confirmada. |
| Execução independente da árvore suja | Testes, Vite e Laravel servidos pelo clone local limpo de `9fcd085`. O clone estava sem `.env`, com `.env.example`, e terminou sem alterações rastreadas. |
| Dependências | Dependências instaladas foram copiadas para o clone. Composer 2.9.4 aprovou `check-platform-reqs --lock`. `npm ls --depth=0` confirmou as dependências diretas do clone. Uma instalação fria a partir de registries ficou fora da execução. |
| Integridade | `git diff --check` passou contra o SHA-base. Pint passou no conjunto corrigido e a sintaxe foi validada para 21 PHP/Blade e 5 JS. O clone limitado à branch candidata foi comparado pelo SHA da base, pois sua referência local `origin/dev` não existe. |

## 3. Revalidação dos achados anteriores

| Achado | Status | Evidência |
| --- | --- | --- |
| QA-01 · Consolidação Git | **FECHADO para o candidato local** | Módulos e views rastreados, build de 40 módulos e suítes executadas no clone. Publicação remota é uma etapa separada. |
| QA-02 · Sidebar de apadrinhamento | **NÃO VALIDADO integralmente** | O teste de DOM e o HTML de uma sessão real confirmaram link ativo com `aria-current="page"`. A conta sem a capacidade ficou sem o item. A conta exclusiva recebeu `403` em validação de Pessoas. Consultas confirmaram fila pendente e vazia. Clique, teclado e exibição visual no Chromium ficaram **NÃO EXECUTADOS**. |
| QA-03 · Diretório de usuários | **FORA DO ESCOPO** | `/admin/candidatos` realiza validação de cadastros e documentos por `usuarios.validar`. `/admin/usuarios` tem capacidade `usuarios.moderar` e informou indisponibilidade da consulta no HTML. A Tarefa 5 do manual cita a view para inspeção e enumera rotas de validação sem contrato de diretório. |
| QA-04A · Autoria de justificativa | **FECHADO no contrato HTTP** | Deferimento e indeferimento persistiram. PATCH e novo GET do gestor retornaram tipo e nome de exibição. O titular obteve status, data e motivo da decisão sem autoria nem ID do decisor. O banco preservou a ocorrência, marcou autor e registrou a data. Autodeferimento, terceiro e repetição tiveram recusas. Exibição após clique ficou **NÃO EXECUTADA**. |
| QA-04B · Motivo de apadrinhamento | **NÃO VALIDADO integralmente** | Web devolveu `422` para ausência, string vazia, espaços e 2.001 caracteres, preservando o pedido. Com 2.000 caracteres devolveu `200`, persistiu motivo, autor e instante, e a repetição recebeu `409`. O Bearer real aceitou motivo ausente conforme legado. A consulta é de pendências e não devolve um histórico com motivo após decisão. Cliques e visibilidade do motivo em UI ficaram **NÃO EXECUTADOS**. |
| QA-04C · ONG versus Evento | **FECHADO no contrato HTTP** | ONG foi aprovada apenas com `status_ong` e saiu da fila. Evento rejeitado sem motivo recebeu `422`, com motivo recebeu `200`. Evento sem Agenda recebeu `409` na aprovação. A URL pública passou de `404` a `200` após outra aprovação legítima. |

Dados e resultados das requisições em [http.json](evidencias/qa-revalidacao-luiz-miguel-2026-11-27/http.json). Estado confirmado no banco em [persistencia.json](evidencias/qa-revalidacao-luiz-miguel-2026-11-27/persistencia.json).

## 4. Matriz das nove frentes

| Frente | Ator e percurso executado por HTTP | Resultado e persistência | Lacunas |
| --- | --- | --- | --- |
| 1. Histórico | Titular, terceira Pessoa, titular com histórico vazio e visitante em `GET /minhas-participacoes/dados` | Titular com 12 Eventos na primeira página e registros na segunda. Histórico vazio, isolamento de inscrições e 401 anônimo confirmados. Depois das decisões, a ocorrência continuou como ausência. | Ordenação visual, carregamento da página seguinte e elegibilidade em tela. |
| 2. Presença e monitoria | ONG proprietária, ONG terceira, designado, independente, vínculo encerrado e titular nas rotas `/gestao` | Presença persistida na lista da Agenda. Registro futuro 409. Terceira ONG e vínculo encerrado 403. Designado e independente consultaram o escopo devido. Monitoria rejeitada sem justificativa 422, confirmada e lida pelo titular em novo GET. | Submissões pelo browser e gestão do organizador no Chromium. |
| 3. Justificativas | Titular, terceira Pessoa, ONG responsável e ONG terceira em GET, POST e PATCH de `/gestao/inscricoes/{id}/justificativa` | POST 201, deferimento e indeferimento 200, duplicação 409. Houve validação de 2.000/2.001 e 419 sem CSRF. Dois registros persistidos com ocorrência original e autoria gerencial minimizada. | Feedback visual, foco e conteúdo longo. |
| 4. Avaliações e encerramentos | ONG e titular nas rotas de Agenda, Evento e avaliação | Agenda aberta ou ausência de nota impediu encerramento. Nota inválida 422, válida 201, repetida 409. Agenda e Evento finalizaram em ordem. Avaliação facultativa 201 e GET posterior. Uma transação de XP no Evento exercitado e um certificado no teste concorrente. | Fluxo no navegador, fuso do navegador e feed visual. |
| 5. Validação de Pessoas e documentos | Admin com `usuarios.validar`, terceira Pessoa, leitura de fila, detalhe, PDF sintético, decisão e renovação | Detalhe e decisão da Pessoa persistiram. Rejeitar RG sem motivo 422. Reprovação definitiva por admin comum 403. Download de terceiro negado e PDF acessível ao analista após assumir o cadastro. A renovação permitiu decisão com o PDF inacessível ao operador. | **QA-R01 impeditivo** e QA-R02. Decisões no Chromium e S3. |
| 6. ONG e Eventos | Admin de validação institucional e moderação, terceiro no detalhe público | ONG aprovada somente com status e removida da fila. A reprovação de Evento exigiu motivo. Sem Agenda retornou 409. Detalhe público disponível após aprovar. Dois moderadores concorrentes tiveram 200/409 e uma trilha. | Renderização de fila, confirmação pelo feed do navegador e storage remoto. |
| 7. Promoção e apadrinhamento | Admin de promoções, admin exclusivo de apadrinhamento, leitor sem a capacidade e sessão Bearer | Promoção assumida e reprovada com motivo. Novo GET preservou a decisão. Sidebar e fila verificadas por HTML/HTTP. Reprovação Web e legado persistiram conforme seção 3. | Ações e acessibilidade em navegador. Histórico visual do motivo. |
| 8. Campanhas | Admin de XP nas rotas POST/PATCH/GET `/admin/campanhas-xp` | Período inválido 422. Criou, editou e desativou a campanha com nova leitura. Corrida com concessão produziu uma transação e preservou XP histórico. | Formulários e erros de campos no navegador. |
| 9. LGPD e auditoria | Leitor exclusivo, atendentes distintos, admin de logs | Leitor recebeu projeção reduzida e não decidiu. Atendente exclusivo não abriu a lista. Caso assumido e concluído em HTTP, com 422 sem resposta. Log filtrado por ação e sem detalhes brutos. Corridas preservaram atribuição. | Navegação, formulário na tela e janela de exclusão no relógio do navegador. |

## 5. E2E, responsividade e usabilidade

**NÃO EXECUTADO nas nove frentes.** O Chromium interno não acoplou o webview. As tentativas pelas interfaces alternativas de navegador não permitiram anexar uma aba local. A última tentativa no Windows foi interrompida pelo controle de computador porque ele não teve confiança suficiente na URL da janela. A sessão de browser foi encerrada e nenhuma gravação pela UI foi atribuída à rodada.

Larguras **360, 768 e 1280 px**, foco, Escape, teclado, modais, visualização de estados vazios e preenchidos, mensagens longas, falha de rede e feedback visual de 500/419 permanecem pendentes. O relatório registra HTML retornado, novos GETs e banco como evidências funcionais dos contratos. Capturas de tela desta rodada indisponíveis.

## 6. Permissões e privacidade

Foram usadas identidades sintéticas com sessões separadas por persona. O sujeito terceiro, ONG terceira, organizador com vínculo revogado, visitante, leitor LGPD e admin com capacidade de apadrinhamento exclusiva tiveram recusas coerentes nos recursos testados. As suites cobrem o Master e as regras de role, inclusive um papel não administrativo com permissão atribuída. O download inicial e a renovação foram confrontados com um PDF sintético em disco privado local.

A resposta do titular de uma justificativa não contém `decidido_por` nem `decidido_por_id`. O gestor recebeu apenas tipo e nome do autor no campo adicional. O log Web entregue ao leitor autorizado omitiu detalhes brutos, e o protocolo consultado por leitor sem atribuição ocultou identificação e resposta. O cliente Web usa sessão, CSRF e mesma origem, e os dados renderizados nas operações auditadas passam pelo cliente HTTP. A validação de sessão e CSRF por HTTP tem cobertura parcial das combinações possíveis.

## 7. Concorrência e integridade

MySQL 8.4.3 com o schema descartável `ajudae_qa9_independente` em `127.0.0.1:34647`. A barreira do processo pai travou a linha compartilhada até os dois processos chegarem ao ponto de corrida. Os tempos registrados em [concorrencia.json](evidencias/qa-revalidacao-luiz-miguel-2026-11-27/concorrencia.json) mostram início dos dois lados antes da liberação. Os casos de serviço informam IDs de conexão. Os casos HTTP usaram sessões distintas e dois processos PHP em `8817` e `8818`.

| Cenário | Pares | Resultado observado e invariante |
| --- | --- | --- |
| Campanha desativada durante XP | 1 | Desativação e concessão concluíram. A transação capturou o fator temático então aplicável, 200 XP com um fator. A concessão permaneceu única. O histórico anterior ficou em 100 XP, a desativação teve log e uma repetição não concedeu novamente. |
| Dois moderadores do mesmo Evento, HTTP | 1 | Aprovação 200, reprovação concorrente 409. Evento ativo, um registro de decisão com ator. |
| Dois atendentes do mesmo protocolo, HTTP | 2 | Assumir 200/403 e responder 200/403. Caso ficou recusado pela mesma conta que assumiu. Dois logs, um de atribuição e outro de decisão. |
| Cancelar Agenda versus presença e XP | 1 | Cancelamento ocorreu primeiro. Presença entrou em conflito e preservou inscrição aprovada. Zero entradas de XP. |
| Emitir certificado versus fechar Evento | 2 | Emissão inicial em conflito enquanto o Evento estava ativo. Depois do encerramento, duas emissões simultâneas devolveram o mesmo ID. Um certificado, uma avaliação e uma transação de XP. |

A suíte integrada reexecutou o limite de **24 h** da desistência e a janela de **sete dias inclusive** da nota de ONG, com relógio controlado em teste. A configuração Laravel do ambiente Web foi **UTC**. As mudanças de fuso entre navegador e servidor e a reexecução dos três pares registrados em 26/11 ficaram fora desta rodada. As seis requisições concorrentes HTTP são contadas à parte das 105 verificações de contrato.

## 8. Cobertura automatizada e ambiente

| Verificação | Resultado |
| --- | --- |
| PHP integrado | `vendor/bin/phpunit --bootstrap <temporário> --do-not-cache-result --filter <grupos Web e domínio>` no SHA limpo com `AJUDAE_WEB_AUTH=true`. **139 aprovados, 2.023 asserções, 0 ignorados**. O filtro completo está em [execucao.json](evidencias/qa-revalidacao-luiz-miguel-2026-11-27/execucao.json). |
| PHP demonstrativo | `AJUDAE_WEB_AUTH=false`, modo demonstrativo ativo, lista de filtros `VisualFour` e `VisualParticipation`. **18 aprovados, 309 asserções, 0 ignorados**. |
| JavaScript | `node --test --test-reporter=spec` em `tests/Frontend/*.test.mjs`. **95 aprovados, 0 ignorados**. O primeiro ensaio apresentou falha de JSON no helper de Blade. A execução final completou a preparação de dependências e forneceu uma chave temporária de teste em modo demonstrativo. |
| Build e análise | Vite 8.2.1 compilou 40 módulos. PHP 8.5.5 com `pdo_mysql`, `pdo_sqlite`, DOM, GD e OpenSSL. Composer 2.9.4 e Node 24.16.0. Pint, sintaxe e Git aprovados. Runtime PHP 8.4 não disponível nesta execução. |
| HTTP direto | **105 casos**, com 103 correspondências às expectativas gravadas, uma expectativa de teste revista (download de cadastro antes de assumir) e uma divergência de produto (QA-R01). Os 103 incluem o reteste com expectativa correta `403`. |
| E2E | **0**, com bloqueio da ferramenta de navegador descrito na seção 5. |

As duas instâncias de MySQL foram confirmadas por cliente e PDO efetivo do Laravel quanto a host, porta, schema, UUID e diretório. A suíte integrada usou a porta **34649**, e os testes HTTP e concorrentes usaram a **34647**. As migrations foram aplicadas em schemas recém-criados. A suíte transacional reaproveitou as tabelas preparadas e os casos de navegação com SQLite em memória usam sua configuração de teste. Banco principal, credenciais reais e serviços remotos ficaram fora dos ensaios.

Os casos de domínio e Web exercitam persistência, permissões e conflitos, mas as suítes de storage usam dados sintéticos e também incluem `Storage::fake`. A checagem histórica do PDF privado testa o caso com análise atribuída. Um defeito importante pode escapar mesmo com toda a suíte verde. **O teste de download não cruza o operador de renovação com o estado cadastral aprovado.** Os testes JS cobrem helpers e replays em modo demonstrativo, com cobertura insuficiente de cliques, feedback depois de respostas negativas, e foco na UI integrada.

## 9. Bugs e lacunas reproduzidos

### QA-R01 · Alta · Renovação pode ser aprovada sem o PDF acessível ao analista

- **Pré-condição.** Documento de antecedentes sintético e pendente de uma Pessoa aprovada, armazenado no disco privado local. Operador com `usuarios.validar` autenticado na sessão normal. A fila inclui a renovação.
- **Passos e observado.** Consultar `GET /admin/antecedentes/renovacoes` para localizar a fixture. Em seguida, tentar o download pela ação contratada em `GET /conta/documentos/{documento}/download`, que devolveu `403`. A operação `PATCH /admin/documentos/{documento}/status` com `{"status":"aprovado"}` devolveu `200`. O banco gravou o validador e a renovação saiu da fila. O caminho habitual de assumir a análise também devolveu `409` para a Pessoa já aprovada.
- **Esperado.** A jornada deve permitir revisão autorizada do binário antes de decidir a renovação, com isolamento por finalidade e registro de auditoria.
- **Causa no código.** Download delega à autorização de histórico privado. Para o analista, ela exige cadastro `em_analise`, posse do caso e prazo válido. A validação de renovação admite documento de Pessoa aprovada. Assim, as duas operações adotam pré-condições diferentes. [DocumentoPessoaPolicy](../../app/Policies/DocumentoPessoaPolicy.php#L11) · [PessoaPolicy](../../app/Policies/PessoaPolicy.php#L19) · [PessoaValidationService](../../app/Services/PessoaValidationService.php#L215)
- **Impacto e aceite.** O processo de renovação não oferece ao operador acesso à evidência que precisa inspecionar e ainda permite gravar uma decisão. Corrigir o contrato autorizado de revisão da renovação e testar leitura auditável com fixture própria, recusa a terceiros, decisão e persistência.

### QA-R02 · Média · A ação de download do cadastro pode ser oferecida antes da posse da análise

- **Pré-condição.** Cadastro sintético aguardando validação, com documento disponível e operador com `usuarios.validar`, sem análise assumida.
- **Observado.** O detalhe retornou o documento. O código JS inclui **Baixar documento** pela disponibilidade do arquivo sem checar a posse da análise. A requisição de download recebeu `403` antes de assumir e `200` após assumir no mesmo cadastro. A entrega posterior foi registrada em auditoria. Não houve inspeção visual do link nesta rodada. [admin-operations.js](../../resources/js/modules/admin-operations.js#L14) · [PessoaPolicy](../../app/Policies/PessoaPolicy.php#L19)
- **Esperado e aceite.** A tela deve mostrar o requisito de assumir e habilitar o download quando a autorização do servidor permitir. Preservar a negativa para terceiros e o prazo de posse.

## 10. Riscos, cobertura e limitações

- **BLOQUEIO DE AMBIENTE** · controle do navegador indisponível para esta rodada. Zero mutações na UI, resoluções e teclado não medidos.
- **LACUNA** · API Bearer de apadrinhamento verificada em runtime sintético. O catálogo Web descreve lista pendente, sem histórico de decisão ou consulta de motivo. A persistência do motivo está confirmada. A expectativa de visibilidade de histórico do roteiro requer delimitação com a equipe.
- **LACUNA** · PHP 8.4, instalação fria das dependências, falhas de rede/500 em UI, matriz integral de permissões e S3 ficaram pendentes.
- **RISCO NÃO CONFIRMADO** · integração coletiva pode alterar ordem de inicialização, cards, dados de documentos e navegação de organizador, ainda que um merge textual seja possível.
- **PUBLICAÇÃO** · a branch publicada permanece em `74609bfd`. A ausência remota do candidato foi confirmada pela consulta ao GitHub durante esta execução. Nenhum PR foi aberto ou atualizado.

## 11. Dependências de integração

- **Felipe** · confirmar o contrato de acesso ao PDF de renovação com o dono de identidade e documentos. A dependência é do fluxo documental comum, exercitado pela tela do Luiz.
- **Enrico** · revalidar as views de Eventos compartilhadas, a seção de participação da ONG, Agendas e designação. A moderação habilitou o detalhe público via HTTP. O comportamento do feed na interface ainda precisa de E2E coletivo.
- **João Pedro** · integrar a jornada de emissão de certificado, observação de XP e ida e volta de protocolo LGPD com as mesmas contas sintéticas e a mesma projeção permitida.

## 12. Checklist de liberação para PR

- [x] Commit local inclui módulos, views e contratos, com build e suítes no clone.
- [x] Base remota consultada e publicação do candidato distinguida do checkout.
- [x] Contratos Web, Bearer e corridas prioritárias revalidados em ambiente isolado.
- [ ] Corrigir QA-R01 e repetir fila → download autorizado → decisão → persistência com acesso restrito e auditável.
- [ ] Corrigir ou delimitar o feedback de QA-R02 e a consulta do motivo de apadrinhamento.
- [ ] Rodar E2E de escrita para as nove frentes em dois perfis de browser, incluídas larguras e estados de erro.
- [ ] Validar o candidato corrigido com as branches da equipe após publicação. Homologar PHP 8.4 e Railway/S3 em etapa própria.

## 13. Conclusão

**O SHA `9fcd085` ainda não pode ser liberado como aprovado para PR na `dev`.** Há um bloqueio reproduzido na revisão documental de renovação e a prova E2E de escrita continua pendente. As correções originais verificadas por HTTP e o pacote local foram preservados. Nesta auditoria, o delta entregue contém o relatório e as evidências. Não houve alteração de aplicação, commit, push, PR ou deploy.

### Decisões para você responder

Nenhuma para encerrar a revalidação. O defeito impeditivo e os critérios objetivos de correção estão na seção 9.
