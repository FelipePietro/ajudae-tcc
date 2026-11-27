# Bloco 7.5 — correção final de autorização administrativa

22/11/2026. Branch codex/baseline-ajudae-7-4, HEAD 7bd046a4ad54411f662c1666d1d4d9000bcf963b. Esta correção parte da entrega do 7.5 já presente no checkout. [Relatório original](bloco-7-5-relatorio.md)

## Causa e correção

A sessão separava Pessoa e ONG. Como o administrador também é um registro Pessoa, WebNavigation oferecia “Meu perfil” no menu geral e na sidebar administrativa. O middleware de várias rotas pessoais aceitava qualquer papel daquele modelo. Candidaturas e o workspace de organizador já tinham verificações adicionais.

A navegação passa a separar sessão válida de acesso a uma jornada pessoal. As rotas Web pessoais agora exigem vínculo com Pessoa voluntária ou organizadora, conforme o recurso. O perfil, preferências, privacidade, participações e certificados continuam disponíveis para essas jornadas. A elegibilidade para se candidatar continua exclusiva do voluntário aprovado. A opção “Gerenciar minha conta” fica no corpo do painel administrativo e abre /conta para Leitor, Respondente e Master.

## Matriz antes e depois

| Recurso | Antes | Depois |
|---|---|---|
| Sidebar administrativa e header contextual | “Meu perfil” aparecia para administrador porque a sessão era do tipo Pessoa | Leitor, Respondente e Master têm navegação administrativa, Notificações e Sair. A conta abre pelo atalho no corpo do painel |
| /perfil, /perfil/editar, /perfil/preferencias, /perfil/privacidade | Tipo Pessoa era suficiente, incluindo administrador | Voluntário e organizador mantêm a área própria. Administrador e ONG recebem 403. Visitante vai ao login |
| Meu XP, confiabilidade, badges e apadrinhamento pessoal | Abas seguiam a autorização do perfil, com estado cadastral aplicado dentro da view | A autorização da página é por papel. O administrador recebe 403 inclusive em URLs com query ou fragmento apontando para as abas. Listagens públicas continuam públicas |
| /minhas-participacoes e /meus-certificados | Qualquer sessão Pessoa | Titular voluntário ou organizador. Administrador e ONG recebem 403 |
| /candidatura e /minhas-candidaturas | Já distinguiam voluntário no controller | Guarda de jornada antes do controller. A candidatura continua exigindo aprovação e Evento publicado. Acompanhamento restrito ao voluntário |
| /admin/solicitacoes-lgpd, /admin/responder-lgpd e /admin/configuracoes | Capacidade real de leitura, resposta ou Master, conforme a área | Leitor mantém consulta, Respondente mantém resposta e Master mantém as capacidades administrativas. URL e parâmetros de contexto não concedem papel |
| API pessoal | Bearer Pessoa, mais verificações locais. Alguns endpoints aceitavam administrador por tipo ou pelo papel administrativo genérico | Papel de jornada e titularidade nas áreas pessoais. Apadrinhamento pessoal fica restrito ao voluntário. Promoção e histórico de convites exigem papel de jornada. Leitura administrativa de histórico segue a regra de capacidade e caso atribuído da PessoaPolicy |
| /conta, perfil público e ranking público | Conta por sessão válida. Projeções públicas disponíveis pelas rotas públicas próprias | Mesmas condições. A exclusão pendente continua redirecionando para /conta. Gestão institucional e workspace do organizador continuam sob seus próprios escopos |

## Arquivos

- Sessão e papel em [ResolveWebAccount.php](../app/Http/Middleware/ResolveWebAccount.php), [identity.php](../routes/identity.php), [AccountAccess.php](../app/Support/AccountAccess.php) e [WebNavigation.php](../app/Support/WebNavigation.php).
- Navegação e atalho em [navbar.blade.php](../resources/views/components/navbar.blade.php), [context-navbar.blade.php](../resources/views/components/context-navbar.blade.php), [web-navigation-links.blade.php](../resources/views/components/web-navigation-links.blade.php), [landing-page.blade.php](../resources/views/landing-page.blade.php) e [admin.blade.php](../resources/views/admin/admin.blade.php).
- Autorização de histórico, inscrições, certificados, apadrinhamento e organização em [PessoaPolicy.php](../app/Policies/PessoaPolicy.php), [InscricaoController.php](../app/Http/Controllers/Api/InscricaoController.php), [CertificadoController.php](../app/Http/Controllers/Api/CertificadoController.php), [PadrinhoController.php](../app/Http/Controllers/Api/PadrinhoController.php) e [OrganizadorController.php](../app/Http/Controllers/Api/OrganizadorController.php).
- Regressão HTTP em [IntegratedNavigationTest.php](../tests/Feature/IntegratedNavigationTest.php).

## Checklist manual

1. Abrir [Administração](http://localhost:8000/admin) como Leitor, Respondente e Master. Conferir os atalhos, abrir “Gerenciar minha conta” e verificar o nome da conta em [Minha conta](http://localhost:8000/conta). Repetir no desktop e no menu móvel.
2. Em 360 e 768 px, abrir o menu pelo teclado, percorrer com Tab e fechar com Escape. O foco deve voltar ao controle de abertura. Em 1280 px, percorrer a sidebar e ativar o atalho de conta com Enter.
3. Com cada administrador autenticado, abrir [Perfil](http://localhost:8000/perfil?contexto=pessoa&papel=voluntario#meu-xp), [Preferências](http://localhost:8000/perfil/preferencias), [Candidaturas](http://localhost:8000/minhas-candidaturas) e [Participações](http://localhost:8000/minhas-participacoes). Esperar 403. Abrir [Ranking público](http://localhost:8000/ranking) e esperar 200.
4. Confirmar [consulta LGPD](http://localhost:8000/admin/solicitacoes-lgpd) e [resposta LGPD](http://localhost:8000/admin/responder-lgpd). Uma conta com só leitura ou só resposta recebe 403 na área da outra capacidade. Verificar configurações como Master e repetir com visao=master em conta sem permissão.
5. Trocar para voluntário, ONG e organizador. Confirmar o perfil próprio de cada jornada, a separação do painel ONG e do workspace e a recusa de /admin mesmo com contexto=administrador.

## Verificação

- IntegratedNavigationTest em SQLite em memória, com 14 testes e 469 assertivas aprovadas. Cobertura de leitura, resposta e Master, GET e HEAD diretos, parâmetros de URL, conta própria, bearer pessoal, regressões de voluntário, ONG e organizador e exclusão pendente.
- Pint, php -l, compilação Blade e git diff --check passaram. As duas primeiras passagens PHP encontraram conflito de escrita no cache Blade compartilhado do Windows. A repetição com o cache em diretório temporário exclusivo passou.
- Edge/Playwright em sessão Web e SQLite isolados. Nove combinações de perfil/largura em 360, 768 e 1280 px passaram, incluindo conta via Enter e menu móvel com Tab, Escape e retorno do foco. A primeira abertura do navegador expirou e a repetição passou. Evidência de interação sem capturas. [Resultado do navegador](evidencias/bloco-7-5-autorizacao-admin.json)
- A suíte backend com banco real e a homologação manual final ficaram fora desta verificação. F1–F6 permanecem pendentes nos pontos registrados no relatório original.

Esta execução preserva a baseline e os arquivos locais do 7.5. Nenhuma migration, alteração em banco real, commit, push ou deploy. O Bloco 8 continua fora do escopo.
