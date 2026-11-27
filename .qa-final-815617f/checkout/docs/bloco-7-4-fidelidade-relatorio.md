# Relatório da correção complementar do Bloco 7.4

Executado no mesmo chat e no worktree existente. A comparação usou os ZIPs oficiais indicados do 6.5 e do 7.4 anterior. Capturas adicionais do responsável não vieram anexadas ao pedido. A tabela de entrada foi registrada antes das alterações de código. [Matriz visual](bloco-7-4-fidelidade-matriz.md) · [Origem das capturas](evidencias/bloco-7-4-fidelidade/fontes.json).

## Correções

- **Privacidade do titular.** `privacidade/conta.blade.php` reaproveita o layout e as regiões do 6.5 em `/perfil/privacidade` e `/ong/privacidade`. Os controladores usam a sessão tipada. Retorno ao cadastro e revisão de exclusão levam às superfícies existentes. Consulta, envio, histórico e exportação aguardam F6.
- **Feed e perfil.** `feed/index` conserva grade, filtros, ranking e badges contextuais. Ranking e sino recuperam destinos próprios sem contadores fictícios. `navbar-feed` restabelece Meu perfil, o sino e a ordem de Minha atuação. No feed institucional, a sessão da ONG mantém a sidebar. Candidaturas e participações têm páginas próprias. Os links de seção, como documentos no perfil, continuam âncoras internas.
- **Organizador.** `organizadores/workspace` volta ao `layouts/organizer` e ao `organizer-links`. A composição inclui cabeçalho, Eventos, convites, vínculo atual e histórico expansível. A organização associada vem do vínculo ativo e de uma ONG apta. As operações, a lista de Eventos atribuídos e o histórico aguardam F2/F3.
- **ONG.** `navbar-ong`, a grade original e os atalhos do dashboard abrem destinos próprios com estado contextual e retorno. O guard já existente protege o tipo. A validação do controlador exige ONG ativa para Eventos, candidatos e organizadores. Perfil, privacidade, notificações e segurança permanecem acessíveis ao próprio titular pendente.
- **Administração.** `components/admin-sidebar` e `admin/admin` usam `WebNavigation` para sidebar, cartões, abas e atalhos. A autorização é reavaliada no controlador em cada URL. Configurações informativas ficam restritas ao Master válido, sem permissão nova de configuração. Relatórios e ocorrências seguem as capacidades correspondentes na matriz. LGPD mantém a distinção de leitura e resposta e a API continua autorizando caso a caso.

As páginas contextuais novas compartilham os layouts de conta, institucional e administração. Elas explicam qual operação está pendente e oferecem retorno em URL própria. A composição do dashboard e do workspace preserva as regiões onde futuramente entram os dados reais.

## Evidências e verificação

- PHP. 22 testes focados em navegação e autorização, com 381 asserções. A regressão dos fluxos visuais somou 37 testes e 2.313 asserções.
- JavaScript e build. 70 testes Node, sintaxe, compilação Vite, compilação Blade isolada, PHP lint e Pint dos quatro arquivos PHP alterados. git diff --check passou.
- Rotas. 44 rotas Web catalogadas e 162 rotas da API v1. A comparação do catálogo da API registrou zero divergências. Na porta 8000, o feed devolveu 200 e o acesso anônimo à privacidade da Pessoa devolveu 302. [Catálogo Web](evidencias/bloco-7-4-fidelidade/rotas-web.json) · [Comparação de contrato](evidencias/bloco-7-4-fidelidade/contrato.json).
- Edge. 12 cenários e 41 cliques com HTTP 200 no destino, sendo 6 na navbar Pessoa, 6 na navbar organizador, 11 na sidebar ONG e 18 na sidebar administração. Foram verificados 24 destaques ativos. Conforme o cenário, o roteiro conferiu perfis incompatíveis, acesso anônimo, permissões de LGPD/logs, CSRF, bearer, consultas demonstrativas ignoradas e logout entre abas. Conferiu também o histórico expansível e o teclado dos menus nos tamanhos móveis. [Registro de navegação](evidencias/bloco-7-4-fidelidade/depois/navegacao.json).
- Capturas. 27 de referência, 18 da entrega anterior e 54 desta execução, incluindo menus e páginas a 360, 768 e 1280 px. [Matriz visual](bloco-7-4-fidelidade-matriz.md).
- Preservação. 17 arquivos técnicos modificados e três criados. Os outros 422 arquivos técnicos com hash inicial permaneceram idênticos. Todas as 5.018 entradas iniciais continuaram no status do Git. [Comparação do worktree](evidencias/bloco-7-4-fidelidade/preservacao.json).

O navegador do Codex excedeu o tempo de inicialização. O Edge/Playwright fez os percursos completos onde indicado no registro de navegação. O feed vazio foi testado na porta 8000. Os papéis e dados sintéticos complementares usaram SQLite isolado, sem editar a base de homologação. Os screenshots de referência contêm dados visuais do 6.5. Os screenshots novos usam dados autorizados de QA.

O delta inclui apenas código, relatórios e evidências. Guards, middleware, Gates, Policies, CSRF, revogação e endpoints bearer preservados. Nenhuma migração, alteração de schema da aplicação, reset, limpeza, commit, push ou deploy. O aceite do responsável continua pendente.

[Matriz de links e autorização](bloco-7-4-fidelidade-rotas.md) · [Checklist da porta 8000](bloco-7-4-fidelidade-checklist.md).


## Atualização proposta para AGENTS.md

Após a homologação manual, acrescentar à tabela histórica o status desta correção, apontar para este relatório e registrar as rotas privadas de privacidade, os destinos próprios dos menus e as dependências F2/F3/F5/F6 listadas nas matrizes.
