# Bloco 7.3 — matriz de rotas e navegação

Modo integrado com `AJUDAE_WEB_AUTH=true`. O roteamento usa nomes de rota, principal tipado e capacidades do servidor. Parâmetros de perspectiva do modo de referência não mudam papel, perfil selecionado ou permissão.

| URL de referência → integrada | Handler / view | Acesso e fonte | Resultado |
|---|---|---|---|
| / → / | `public-pages.php` / `landing-page` | Público. Menu contextual pela sessão verificada, apresentação editorial com imagem do feed identificada como ilustrativa | 200 |
| /termos, /privacidade → mesmas | `public-pages.php` / `legal.*` | Público, conteúdo editorial | 200 |
| /login → /login | `SessionAccountController` / `login` | Anônimo. Tipo de formulário cosmético. POST real com CSRF, limitação e provider tipado | GET 200, login válido 302, inválido 302 com erro ou 422 JSON, CSRF 419, limite 429 |
| /cadastro, /cadastro/ong → mesmas | Views de indisponibilidade | Público, sem formulário de cadastro. Link de entrada para contas existentes | GET 200. Cadastro Web fechado |
| /feed → /feed | `PublicEventPagesController::index` / `feed.index` | Público. Eventos ativos, projeção mínima paginada. Sem paginação por parâmetros de persona | 200 |
| /eventos/{id} → mesma | `PublicEventPagesController::show` / `eventos.detalhe` | Público. Evento ativo por ID. Nome, descrição, cidade, UF e modalidade. Sem agenda, endereço detalhado ou comandos | 200 ou 404 |
| /perfil?area=privada → /perfil | `SessionPagesController::person` / `perfil.area-privada` | Pessoa da sessão. Nome, contato, localidade, validação e rótulos de preferências próprias | 200 ou 302/403 |
| /ong/perfil?area=privada → /ong/perfil | `SessionPagesController::organization` / `ong.area-cadastral` | ONG da sessão. Nome, contato institucional, localidade, descrição e estado cadastral | 200 ou 302/403 |
| /ong/painel → mesma | `SessionPagesController::institutionalPanel` / `ong.painel_ong` | ONG ativa, nome próprio. Entrada sem operações | 200 ou 302/403 |
| /organizador → mesma | `SessionPagesController::organizer` / `organizadores.workspace` | Pessoa organizadora aprovada. Estado do vínculo próprio e nome institucional somente se a ONG estiver ativa | 200 ou 302/403 |
| /admin → mesma | `SessionPagesController::admin` / `admin.admin` | Pessoa administradora com pelo menos uma capacidade prevista ou papel administrador com flag Master. Exibe apenas capacidades | 200 ou 302/403 |
| /admin/solicitacoes-lgpd → mesma | `SessionPagesController::privacyQueue` / `admin.solicitacoes-lgpd` | Pessoa administradora com `lgpd.visualizar`. Exibe indisponibilidade sem listar processos | 200 ou 302/403. Escrita Web ausente |
| /conta → /conta | `SessionAccountController::account` / `auth.account` | Sessão Pessoa ou ONG. Segurança ou acompanhamento restrito | 200 ou 302 |
| Segurança e exclusão → POST /conta/senha, POST/PATCH /conta/exclusao, POST /logout | `SessionAccountController` e serviços 7.2 | Tipo/estado do titular, CSRF e bloqueio de sessão. Cancelamento somente dentro do prazo | Redirecionamento e feedback, 419 sem CSRF |
| Perfis de terceiros, ranking, certificados, notificações, edições, candidaturas, Agendas e filas operacionais | Nenhum handler Web integrado | Contratos e projeções pendentes | 404 em rotas Web ausentes. Rotas API existentes seguem o contrato próprio |

`ResolveWebAccount:public` personaliza páginas públicas, invalida sessão vencida sem retirar a página pública e envia `private, no-store`. A proteção de cada entrada usa `ResolveWebAccount:pessoa`, `:ong` ou `:required` mais as capacidades do controlador. A rota usa o principal da requisição, sem ID de perfil privado na URL. O usuário anônimo vai para o login. O tipo errado retorna 403. Em exclusão, a página própria redireciona a /conta e o tipo errado continua em 403. Os redirecionamentos pós-login usam nomes de rota fixos e dispensam `intended`.

| Persona | Destino após login | Links privados adicionais |
|---|---|---|
| Visitante | — | Nenhum |
| Pessoa pendente/aprovada | /perfil | Segurança da conta |
| Pessoa ou ONG em exclusão | /conta | Acompanhamento, cancelamento e saída |
| ONG pendente/reprovada | /ong/perfil | Segurança da conta |
| ONG ativa | /ong/painel | Minha organização e segurança |
| Organizador independente ou vinculado | /organizador | Meu perfil e segurança. Sem operações sobre Eventos |
| Admin leitor LGPD | /admin | Perfil, segurança e entrada da fila via `lgpd.visualizar` |
| Admin respondente | /admin | Perfil e segurança. Entrada da fila também exige `lgpd.visualizar` |
| Admin Master | /admin | Acesso às entradas administrativas registradas. Processos e ações privadas continuam fechados |
