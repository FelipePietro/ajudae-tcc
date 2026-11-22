# Bloco 7.3 — reconexão Web do Ajudae

Data de execução 20/11/2026. Branch `feat/ajustes-pre-deploy`, HEAD `1c028b311edcff2869f1d299dbbff5924425b450`. Baseline com 81 entradas Git previamente modificadas, 4.633 não rastreadas e 427 arquivos rastreados ou não rastreados com hash nos diretórios técnicos. A comparação de status preservou todas as entradas preexistentes. O pacote usa como origem o estado inicial da sessão, incluindo alterações deste bloco sobre arquivos que já continham trabalho local. O manifesto identifica cada arquivo.

## Implementação

| Arquivos | Mudança |
|---|---|
| `routes/identity.php`, `routes/public-pages.php` | Páginas públicas com resolução opcional, bloqueio das rotas de sessão e separação de rotas privadas Pessoa/ONG. Nomes compatíveis com os destinos existentes |
| `ResolveWebAccount.php`, `SessionAccountController.php`, `WebNavigation.php` | Menu, CTA e rota pós-login usam principal tipado e capacidades atuais. Sessão antiga em página pública expira e a página continua disponível como visitante. Exclusão mantém apenas acompanhamento e saída privada |
| `SessionPagesController.php` | Projeções mínimas para perfis próprios, guarda adicional do painel ONG, vínculo real de organizador, entrada administrativa e leitura LGPD granular |
| `PublicEventPagesController.php`, `feed/index.blade.php`, `eventos/detalhe.blade.php`, `auth/public-*.blade.php` | Catálogo paginado e detalhe de Eventos ativos, coerentes com a seleção pública da API. A projeção omite campos operacionais e relações |
| `perfil/area-privada.blade.php`, `ong/area-cadastral.blade.php`, templates de painel/organizador/admin e parciais | Cabeçalho, cartões, abas e componentes do projeto. Dados próprios e estados indisponíveis nas superfícies que dependem de integração futura |
| `landing-page.blade.php`, `legal/*`, `auth/*`, `components/*navbar*`, `components/web-navigation*`, `components/footer.blade.php`, `layouts/{account,identity,web-workspace}` | Navegação responsiva, marca, cabeçalhos, rodapés e breadcrumbs. Cadastro separado de login por página de disponibilidade. Saída POST com CSRF |
| `identity.js`, `navigation-accessibility.js`, `account.css`, `vite.config.js` | Entrada JS integrada sem módulos de referência, abas e navegação reutilizando acessibilidade compartilhada. Proteção visual para restauração de página em cache do navegador até revalidação |
| `IntegratedNavigationTest.php`, `IdentityBoundaryTest.php` | Regressões de navegação, privacidade, isolamento de guard, autorização, versão e expiração. Atualização de expectativas de rotas e destinos |

A matriz descreve as 20 rotas Web integradas, a fonte de cada projeção e as diferenças esperadas de 200/302/403/404. O modo de referência segue condicionado a local/testing e flag própria. O catálogo de rotas da API foi equivalente entre os modos. Os hashes iniciais de API, Models, Policies, migrações, configurações de autenticação e serviços de domínio coincidiram depois da execução.

## Ambiente e fontes

- Foram consultados o `AGENTS.md` do checkout, RN001–RN034 no Markdown e no PDF oficial de 15 páginas, o backlog de integração local, a matriz e inventário 6.5, os documentos 7.1 e 7.2 no checkout e nas pastas de auditoria do Desktop.
- O servidor da porta 8000 usa o bootstrap isolado do 7.2. O verificador carregado pelo roteador confere host, porta 33379, schema `ajudae_72_descartavel_20261120`, usuário restrito e UUID da instância antes de servir requests dinâmicos. A homologação técnica usou somente as contas sintéticas desse ambiente.
- A primeira leitura devolveu 500 em templates que passaram a depender do novo bundle. A compilação e atualização de assets e manifesto no diretório público isolado restabeleceram as páginas. O processo existente continuou ativo. O arquivo `.env` principal foi preservado.
- A primeira regressão ampla incluiu por engano `ManagedImageTest`; a preparação de schema falhou em `ALTER TABLE … ADD CONSTRAINT` dentro do SQLite em memória. O filtro por nomes qualificados separou os testes visuais e passou. Nenhuma migration ou seed foi executada no MySQL.
- A comparação visual com a referência privada 6.5 foi por captura no mesmo viewport. Os perfis conservaram hierarquia, avatar, perfil em grid, cartões e rodapé. As diferenças de conteúdo refletem dados próprios e áreas fechadas. A seleção de aba recebeu estilo ativo e o menu das áreas de trabalho ganhou saída de alto contraste.
- O navegador do Codex sofreu timeout na inicialização. A alternativa foi Edge com Playwright. Em uma primeira passagem, o verificador esperava uma URL sem fragmento após Voltar. A inspeção confirmou que o navegador chegou a `/login#resumo` com tela de login. A reexecução passou a verificar o caminho e a indisponibilidade do perfil anterior.

## Aceite e próximos contratos

Páginas do titular e entradas por capacidade estão disponíveis localmente. Cadastro, edição, perfis compartilháveis, documentos, gestão de Eventos, candidaturas, filas administrativas, gamificação, certificados e notificações aguardam os contratos enumerados no mini-backlog. A fila LGPD tem apenas uma página de disponibilidade com permissão de leitura, sem acesso a registros nem comando Web.

A credencial padrão da conta aprovada não foi aceita. A troca de senha com revogação e o vínculo ativo/inativo foram exercitados em SQLite descartável. O cancelamento real da exclusão permaneceu disponível para a homologação do responsável. Os resultados não equivalem ao aceite manual do bloco.

Proposta para a próxima atualização autorizada de `AGENTS.md`: registrar os destinos do 7.3, as rotas operacionais ainda fechadas e esta matriz. A homologação manual deve atualizar o status separadamente. Sem commit, push ou deploy.

[Matriz de rotas](bloco-7-3-matriz-rotas-navegacao.md) · [Testes](bloco-7-3-matriz-testes.md) · [Inventário](bloco-7-3-inventario-mocks-pendentes.md) · [Checklist](bloco-7-3-checklist-homologacao-manual.md) · [Mini-backlog](bloco-7-3-mini-backlog.md)
