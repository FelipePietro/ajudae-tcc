# Ajudae — Auditoria complementar de autorização do Bloco 7.2

20/11/2026 · Pendente de aceite manual

A separação entre Pessoa e ONG foi confirmada pelo status da resposta, pelo middleware efetivo e pela ausência de conteúdo privado nas negativas. A view `auth.account` é compartilhada nas três rotas. O controlador usa a identidade resolvida no servidor e não recebe um ID de titular da URL.

| Rota GET | Handler | Autorização efetiva |
|---|---|---|
| `/conta` | `SessionAccountController::account` | `ResolveWebAccount:required`. Aceita sessão válida de Pessoa ou ONG. Se houver exclusão pendente dentro do prazo, expõe somente o acompanhamento. |
| `/perfil` | `SessionAccountController::account` | `ResolveWebAccount:pessoa`. Exige sessão Pessoa. ONG recebe 403. Pessoa em exclusão pendente recebe 302 para `/conta`. |
| `/ong/perfil` | `SessionAccountController::account` | `ResolveWebAccount:ong`. Exige sessão ONG. Pessoa recebe 403. ONG em exclusão pendente seria redirecionada para `/conta` pela mesma regra. O teste de exclusão usou Pessoa. |

As três rotas usam o grupo `web` e bloqueio de sessão do Laravel. O middleware confere guard exclusivo, tipo do modelo, ID, versão de autenticação, validade da conta e idade da sessão. As respostas de conta e os redirecionamentos emitidos pelo middleware usam `Cache-Control: private, no-store`. Sem sessão válida, as rotas encaminham para o login. As mutações Web usam CSRF. Na API, `auth:sanctum`, `bearer.account` e `abilities` exigem token persistido do tipo e da permissão apropriados. Os controladores de cancelamento conferem titularidade e o serviço revalida a versão e o prazo sob lock transacional.

## Resultados observados

| Cenário | Web | API bearer |
|---|---|---|
| Sessão Pessoa e ONG em recurso próprio | 200 em ambos | 200 em ambos |
| Pessoa e ONG em recurso privado do outro tipo | 403 em ambos | 403 em ambos. Cookies web isoladamente retornaram 401. |
| Exclusão em curso | Acompanhamento 200, perfil próprio 302 para `/conta`, perfil ONG 403 | Status 200 com apenas `exclusao_pendente` e `deletar_em`. O login restrito entregou o mesmo conjunto de campos de conta. |
| Operações proibidas durante a exclusão | Alteração de senha e nova solicitação com CSRF válido receberam 302 para `/conta` | 403 para validação privada, antecedentes, convites, alteração de senha e cancelamento de terceiro. Sem campos de dados do recurso. |
| Cancelamento após vencer | 302 para o login com a sessão anterior. Credenciais recusadas com 422 no POST com `Accept: application/json`. | 403 para cancelar com bearer anterior, 401 ao tentar novo login. |
| Cancelamento dentro do prazo | Botão de cancelamento devolveu 302 para o login e encerrou a sessão. Logout voluntário durante a espera também devolveu 302. | O bearer anterior recebeu 401 após o cancelamento Web. |

A execução registrou 43 respostas reais em Edge/Playwright contra `127.0.0.1:8000`. O navegador do Codex confirmou o redirecionamento de visitante. O PHPUnit executou 11 testes de identidade e 100 asserções com a conexão de banco desativada, incluindo o limite exato do prazo e as regras bearer.

## Banco e fixture

A conexão foi validada por schema `ajudae_72_descartavel_20261120`, porta 33379 e UUID `0144f6d6-e524-11f1-a1bd-0250a97aef29`. Antes e depois dos blocos negativos, foram comparados os hashes das tabelas `pessoa`, `ong`, `evento` e `admin_log`, além da composição dos tokens, da versão da conta e do prazo. Os hashes de estado coincidiram. O acompanhamento normal do Sanctum pode atualizar `last_used_at`, que foi excluído da comparação dos tokens.

A conta sintética estava sem solicitação ativa no início. A exclusão foi reagendada pelo fluxo normal. Para exercitar o vencimento, apenas o prazo dessa fixture foi deslocado temporariamente e depois restaurado. O cancelamento permitido limpou a flag e a data, incrementou `auth_version`, revogou o bearer e gerou uma entrada de auditoria. A fixture foi deixada novamente em exclusão pendente para reprodução, com prazo exibido como 27/11/2026 às 23:01 UTC e sem bearer de teste ativo. As entradas legítimas de auditoria foram preservadas.

Foi usado `qa72.organizador` como Pessoa porque a senha de `qa72.pessoa.aprovada` já divergia do checklist local. Essa credencial foi preservada. Nenhum acesso indevido foi observado nos cenários exercitados. O código, as telas e a API não precisaram de correção. A exclusão definitiva e as integrações visuais do Bloco 7.3 ficaram fora do ensaio. Não houve migrations, commit, push ou deploy.
