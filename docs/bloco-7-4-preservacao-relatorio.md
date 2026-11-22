# Preservação e diagnóstico do frontend do Ajudae

Execução de 22/11/2026. O responsável confirmou as seis verificações manuais funcionais e de segurança da entrega anterior. Esta auditoria de código e o teste pontual de navegação complementam esse aceite. A comparação não estabelece fidelidade visual de todas as páginas.

## Estado preservado

- Branch feat/ajustes-pre-deploy, HEAD 1c028b311edcff2869f1d299dbbff5924425b450. Início com 85 arquivos rastreados modificados e 5.044 entradas não rastreadas. Index vazio.
- Backup integral das fontes elegíveis pré-ajuste, com 656 arquivos. Inclui tracked e untracked aprovados para código, documentação textual, ativos versionados, configuração não secreta, template .env.example, locks e placeholders Laravel. Os dados reais, .env, evidências brutas, arquivos de credenciais e homologação local, documentos de exemplo, base SQLite, dumps, public/build, public/hot, caches, dependências e metadados do Git foram excluídos. O manifesto lista 17 exclusões por arquivo ou árvore, identifica exceções e registra cada incluído com tamanho, SHA-256 e estado de rastreamento. O script local de homologação de auth ficou fora por conter atribuições de credenciais; ele permanece no workspace.
- Ajudae_Backup_Fonte_2026-11-22.zip · SHA-256 e9b79207bdcba7dd5c691f7b4dc614d350ce95b912ccdd62b93f9f57bfd09d8e. Reaberto para validar CRC, nomes e conteúdo. Os 656 hashes foram comparados ao workspace antes do ajuste. Use a pasta fonte/ em destino separado para inspeção e revalide pelo manifesto; o ZIP não é instrução de sobrescrita.
- AGENTS.md lido e preservado. O PDF oficial RN V1 foi conferido por extração textual. Folhas de trabalho, backups e QA ficaram fora do Git.

## Diagnóstico de código

- 57 GET do catálogo 6.5, com 60 linhas incluindo variantes. As 53 Blades históricas identificadas, incluídas as de diagnóstico HTTP, existem no checkout. Há cópia de 27 em deltas 6.x, das quais 19 coincidem byte a byte com a última cópia disponível. As outras 26 têm associação de rota ou fonte histórica sem cópia de bytes no conjunto. Não houve evidência de remoção dessas views. A atribuição a 7.2, 7.3 ou 7.4 só é conclusiva quando o arquivo foi incluído no pacote correspondente.
- O 6.5 traz somente duas views Blade no delta e preserva o catálogo amplo de rotas. A reconstrução documental usa os pacotes 6.1–6.4 e os deltas 7.2–7.4. Não foi encontrado pacote separado 6.0. As 57 rotas do recorte registram-se no modo de demonstração atual, que permanece isolado. O catálogo autenticado tem 39 GET Web no mesmo recorte, três desses destinos adicionais. Presença do arquivo e rota no modo visual são evidências diferentes de entrega para a sessão real.
- Os maiores desvios estão nos ramos integrados de detalhe de Evento, perfis, painéis e nos destinos de cadastro, ranking, participação, notificações e filas. A matriz identifica a diferença e o que reaproveitar por página. O inventário distingue integração pendente, ausência de dados e restrição de acesso válida.

## Ajuste pontual

- app/Support/WebNavigation.php retira a entrada comum Segurança da conta dos menus principais. O fluxo segue por Meu perfil → Segurança da conta para Pessoa, organizador e administração. A ONG mantém o acesso pela área cadastral e por Configurações. Acompanhamento de exclusão pendente mantém o atalho dedicado e sua restrição.
- tests/Feature/IntegratedNavigationTest.php reforça as asserções de navegação e permanência dos acessos. Nenhum Blade, JS, CSS, controller, guard, rota ou operação de conta foi editado. Os ramos de demonstração não foram ligados ao modo integrado.

## Verificação

- 11 testes Feature integrados com 274 asserções e 14 testes Feature visuais com 664 asserções passaram. Execução com SQLite em memória.
- 70 testes JavaScript passaram. A primeira execução não encontrava PHP no PATH para os testes de Blade e organizador. O reensaio com o runtime PHP do Laragon passou.
- Pint nos dois PHP alterados, lint PHP, build Vite isolado e git diff --check passaram.
- Edge/Playwright em SQLite isolado. 18 checagens em seis personas (Pessoa, ONG ativa, ONG pendente, organizador, admin leitor e Master), a 360, 768 e 1280 px. Em todas, o menu não apresentou Segurança da conta e o link fora do menu abriu /conta. 12 verificações nos menus compactos cobriram Enter, Escape e retorno do foco. Sem capturas nesta execução. [Registro Edge](bloco-7-4-preservacao-navbar-edge.json).
- Compilação Blade em diretório de QA passou. Catálogo Web antes e depois idêntico por SHA-256. Os hashes também confirmam que rotas da API, controllers, middleware e demais fontes permaneceram iguais.
- A comparação dos hashes de código e do status inicial comprova o delta desta execução. O ZIP de auditoria relaciona os dois arquivos técnicos e a documentação. Não houve Git add, commit, push, deploy, rollback, reset, checkout, limpeza, migração ou restauração em massa.

A atualização proposta para AGENTS.md é registrar o aceite manual comunicado, a referência desta matriz e as dependências funcionais após revisão da equipe.

[Matriz por página](bloco-7-4-preservacao-matriz.md) · [Inventário de mensagens](bloco-7-4-preservacao-indisponibilidade.md) · [Plano seletivo](bloco-7-4-preservacao-plano.md) · [Baseline Git para revisão](bloco-7-4-preservacao-baseline.md).
