# Bloco 7.4 — matriz de segurança

| Persona e rota | Decisão aplicada pelo servidor | Resultado esperado e observado | Evidência |
|---|---|---|---|
| Visitante · GET /, /feed, /termos, /privacidade | `ResolveWebAccount:public`, catálogo de Evento ativo | 200 público. Perfil privado redireciona para login. Evento não publicado ou ausente dá 404 | Edge, `IntegratedNavigationTest` |
| Pessoa ou ONG · POST /login | Provider explícito, cookie de sessão, CSRF, limite por tipo/login/IP e por IP | Tipo válido redireciona ao destino permitido. CSRF ausente dá 419. Sexta falha no mesmo par dá 429 sem links da sessão já encerrada | Edge, `IdentityBoundaryTest`, MySQL |
| Pessoa · GET /perfil e /ong/perfil | `ResolveWebAccount:pessoa/ong`, chave tipada e versão | 200 no próprio perfil, 403 no outro tipo. Query de persona, ID, visão e cenário preservam o titular. Sem CPF ou referência privada a documento | Edge e `IntegratedNavigationTest` |
| ONG · GET /ong/perfil e /ong/painel | Tipo ONG, estado ativo via `AccountAccess` | Perfil próprio 200. Painel 200 para ativa e 403 para pendente. Sem o link do painel no menu pendente | Edge e `IntegratedNavigationTest` |
| Organizador · /organizador e API Evento | Papel aprovado, vínculo real, `EventoPolicy` com proprietário e responsável | Workspace 200, vínculo reavaliado a cada navegação. Operação não atribuída 403. Desvincular com Evento ativo 409. Sem gestão independente com vínculo | Edge independente, Feature com transições, MySQL |
| Admin · GET /admin e /admin/solicitacoes-lgpd | Papel, lista de permissões, Master válido, `lgpd.visualizar` para fila | Leitor e Master 200. Respondente sem leitura 403. A fixture Web de autor tem leitura e resposta, então recebeu 200. Sem fila ou escrita Web | Edge, Feature e catálogo |
| Admin LGPD · API GET/PATCH | Permissões distintas e serviço de privacidade com atribuição | Leitor não responde. Autor autorizado assume e conclui o caso. Outro respondente recebe 403. Documentos e confiabilidade detalhada exigem titularidade ou finalidade de revisão | MySQL e `IdentityBoundaryTest` |
| Conta em exclusão · /conta e perfil | `AccountAccess`, restrição de operações e `AccountDeletion` sob lock | Acompanhamento 200. Operação normal bloqueada. Dentro do prazo o cancelamento revoga credenciais. No instante do vencimento a solicitação não pode ser cancelada | Edge sem alterar fixture. MySQL com rollback e limite em teste |
| Titular · POST /conta/senha e /logout | CSRF, `AccountCredentials`, revogação por tipo e `auth_version` | Troca invalida duas sessões e tokens do titular e conserva conta de outro tipo com mesmo ID. Logout Web preserva bearer. Logout API encerra token corrente | MySQL e Feature |
| API /api/v1 · cookie, bearer e ambos | `auth:sanctum`, `RequireBearerAccount`, abilities e papel | Cookie sozinho 401. Token errado ou capacidade insuficiente recebe 403 conforme contrato. Bearer válido determina o principal mesmo com cookie Web de outro tipo | Edge, MySQL e `IdentityBoundaryTest` |
| Sessões · inatividade, teto e cache | `ResolveWebAccount`, relógio servidor e `private, no-store` | 29:59 válido, 30:00 ocioso expira. Navegação privada elegível renova só a inatividade. Páginas públicas, HEAD e prefetch não renovam. Teto de 12 h independente. Logout seguido de Voltar ou reload da segunda aba pede login | PHPUnit e Edge |
| Evento, Agenda, inscrição, certificado e notificação · API | Policy de escopo, estado, relação de titularidade ou participante | Casos representativos de propriedade, 403/404, mudanças negadas e conflitos 409. Casos e falhas da regressão ampliada aparecem no inventário | Ensaios MySQL transacionais |
| Referência visual e parâmetros · WEB integrada | `routes/web.php` escolhe grupo por config. Blade avalia principal tipado. Bundle `identity.js` separado | Páginas ausentes devolvem 404. Nenhum seletor demonstrativo governa capabilities. Flags simultâneas mantêm modo integrado. Roteamento compilado preserva contrato | Edge, PHPUnit e comparação do catálogo |

## Revisão estática das superfícies tocadas

Blade usa saída escapada para nomes, descrições e rótulos de preferências. As barras integradas apontam para aliases explícitos, sem desvio pelo parâmetro `intended` ou pelo histórico de navegação. O controlador Web valida e seleciona campos de senha/exclusão, sem repassar o corpo a `fill`. Os controladores de perfil projetam dados do titular em campos explícitos. Feed e detalhe restringem a consulta e a projeção. Nenhuma ação sensível depende da mera presença de um item no menu.

A atribuição de Evento ligado tem autoridade operacional no responsável designado pelo contrato atual da API. O backlog histórico D05 permanece referência para uma eventual revisão formal desse contrato. O bloco não alterou a Policy de Evento.

[Matriz de rotas do 7.3](bloco-7-3-matriz-rotas-navegacao.md) · [Catálogo executado 7.4](evidencias/bloco-7-4/rotas-integradas.json).
