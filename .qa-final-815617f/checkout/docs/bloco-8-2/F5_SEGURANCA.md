# F5 — Segurança e permissões

Revisão defensiva do Bloco 8.2 em 23/11/2026. Baseline `7bd046a4ad54411f662c1666d1d4d9000bcf963b` em `codex/baseline-ajudae-7-4`, com alterações locais do projeto. Esta frente registrou o contrato observado e os resultados de teste.

## Identidade Web por sessão

| Controle | Comportamento observado |
| --- | --- |
| Tipo da conta | `POST /login` exige `tipo_login` (`pessoa` ou `ong`) e usa o guard de sessão correspondente, `web_pessoa` ou `web_ong`. O login limita tentativas em uma janela de 15 minutos, inclusive a sexta tentativa da mesma combinação de tipo, login e IP, com `429` e `Retry-After`. |
| Isolamento | O middleware exige um único guard ativo e confere tipo, identificador, `auth_version` e estado de acesso da identidade. Rotas tipadas de outro perfil retornam `403`. A jornada pessoal aceita voluntário ou organizador elegível, com acesso administrativo separado. |
| Expiração | A sessão expira no instante de 30 minutos de inatividade ou 12 horas absolutas. Resposta privada bem-sucedida pode renovar a inatividade. Página pública, `HEAD` e prefetch ficam fora dessa renovação. Identidade inválida ou vencida volta à navegação pública anônima. Rotas privadas respondem com redirecionamento para `/login` em HTML ou `401` em JSON. |
| Exclusão pendente | A navegação privada fica restrita aos pontos de conta, cancelamento e saída. A tentativa de acessar os demais pontos responde com redirecionamento a `/conta` em HTML ou `403` em JSON. |
| Sessão e cookies | O middleware recusa drivers `cookie` e `array` fora de `testing`, com `503`. O projeto configura `HttpOnly` e `SameSite=Lax` por padrão, além de `Secure` por padrão em produção. Respostas de sessão privadas usam `Cache-Control: private, no-store`. |

Fontes: [Rotas de identidade](../../routes/identity.php), [guards](../../config/auth.php), [tempos da identidade](../../config/identity.php), [sessão](../../config/session.php), [resolução da identidade](../../app/Http/Middleware/ResolveWebAccount.php) e [controller de sessão](../../app/Http/Controllers/SessionAccountController.php).

## API por Bearer, expiração e revogação

- A API protegida exige `auth:sanctum` e verificação do token pessoal. Sanctum tem `guard` vazio e a aplicação mantém o bootstrap sem `statefulApi()`. Cookie Web, token ausente, token transitório ou titular fora dos tipos Pessoa e ONG recebem `401`. A habilidade precisa corresponder ao tipo do titular. Habilidade incorreta ou tipo sem acesso à rota autenticada recebem `403`. Durante a exclusão pendente, o middleware limita a API ao estado da própria conta, cancelamento e logout, sujeitos ao estado e prazo da conta.
- `config/sanctum.php` mantém `expiration = null`. A emissão pública de token no cadastro ou login dos controllers Pessoa e ONG informa a habilidade do tipo e omite `expires_at`. Logo, o cliente não deve presumir que esses tokens expiram junto com a sessão Web. Alterar prazo de token exigiria uma decisão explícita de autenticação.
- Logout por API remove o token corrente. Logout Web invalida a sessão corrente e renova o token CSRF. Alteração de senha, solicitação ou cancelamento de exclusão e a reprovação definitiva contemplada pelo serviço de validação revogam as credenciais da conta via `AccountCredentials`, com atualização de `auth_version` e exclusão dos tokens pessoais. O novo `auth_version` invalida sessões anteriores na próxima resolução.
- O cliente HTTP preparado para uso futuro mantém modos sessão, Bearer e público com credenciais separadas. O token é obtido por uma função a cada chamada, sem gravação em `localStorage` ou `sessionStorage`. A busca por importações encontrou somente o teste do módulo. O cliente Web existente segue usando `credentials: 'same-origin'` e `X-CSRF-TOKEN` quando aplicável.

Fontes: [Bootstrap](../../bootstrap/app.php), [Sanctum](../../config/sanctum.php), [middleware Bearer](../../app/Http/Middleware/RequireBearerAccount.php), [rotas API](../../routes/api.php), [Pessoa API](../../app/Http/Controllers/Api/PessoaController.php), [ONG API](../../app/Http/Controllers/Api/OngController.php), [credenciais](../../app/Services/AccountCredentials.php), [exclusão](../../app/Services/AccountDeletion.php), [validação](../../app/Services/PessoaValidationService.php), [cliente HTTP](../../resources/js/core/api-client.js) e [cliente Web](../../resources/js/core/http.js).

## Permissões administrativas e CSRF

- `admin` verifica que o autor é uma Pessoa administradora e confere a permissão declarada quando a rota a exige. No LGPD, a consulta requer `lgpd.visualizar` e a alteração de status requer `lgpd.responder`. A lista privada e as notificações são limitadas à conta autenticada. A trilha LGPD testada registra os campos de auditoria sem copiar texto livre do titular.
- Métodos de escrita Web passam pelo middleware padrão Web de Laravel. No Laravel instalado, uma origem `same-origin` reconhecida pelo middleware ou um token CSRF válido satisfaz a proteção. Sem ambos, uma requisição de escrita falha com `419`. Formulários Blade usam `@csrf`. A execução de PHPUnit em `testing` contorna essa validação do framework e, por si só, cobre rotas, autorização e efeitos da operação, sem medir a rejeição CSRF.

Fontes: [Middleware administrativo](../../app/Http/Middleware/AdminMiddleware.php), [Pessoa](../../app/Models/Pessoa.php), [rotas de identidade](../../routes/identity.php), [rotas API](../../routes/api.php) e [teste de privacidade e notificações](../../tests/Feature/Bloco81PrivacyNotificationsTest.php).

## Documentos e projeções públicas

- `POST /api/v1/pessoas/me/documentos/{tipo}` usa Bearer de Pessoa. O arquivo é PDF, JPEG ou PNG, até 10.240 KiB. Os tipos aceitos são `documento_identidade`, `antecedentes_criminais` e `cnh`. O armazenamento é privado, o arquivo anterior fica sujeito à política de retenção e o Resource omite a chave do objeto.
- `GET /api/v1/documentos/{documento}/download` autoriza Pessoa titular com jornada pessoal e conta apta a operar, ou Pessoa com `usuarios.validar` no caso que ela mesma assumiu. A análise precisa estar `em_analise`, com menos de 24 horas, e a conta do titular deve estar fora de exclusão pendente. A política também restringe Master ao caso atribuído. ONG recebe recusa. O backend entrega o binário com `Cache-Control: private, no-store` e `X-Content-Type-Options: nosniff`. O log de revisão de download omite binário e chave.
- A consulta pública Web de certificado usa projeção minimizada. A API pública legada `GET /api/v1/certificados/verificar/{codigo}` ainda projeta `nome_participante` e `nome_responsavel`. Levar a divergência à decisão de privacidade e ao teste de contrato antes de usar esse endpoint no frontend.

Fontes: [Controller de documentos](../../app/Http/Controllers/Api/DocumentoPessoaController.php), [política de documentos](../../app/Policies/DocumentoPessoaPolicy.php), [política de Pessoa](../../app/Policies/PessoaPolicy.php), [Resource de documentos](../../app/Http/Resources/DocumentoPessoaResource.php), [certificado Web](../../app/Http/Controllers/WebCertificateController.php) e [certificado API](../../app/Http/Controllers/Api/CertificadoController.php).

## Execução e limites

| Verificação | Resultado |
| --- | --- |
| Execução inicial de PHPUnit com PHP 8.5.5, `APP_ENV=testing`, SQLite `:memory:`, sessão `array` e Web integrado | 30 testes, 28 aprovados e 701 asserções. `IntegratedNavigationTest` e `Bloco81PrivacyNotificationsTest` aprovaram os 19 casos de recurso. Nove dos 11 casos de `IdentityBoundaryTest` aprovaram, incluindo expiração, cookie Web insuficiente para API, token transitório ou de tipo errado, limites de login e caso documental atribuído. |
| Divergências na execução inicial de `IdentityBoundaryTest` | `test_integrated_landing_is_public_without_reenabling_demo_routes` esperava cadastro indisponível quando `/cadastro` já exibia o fluxo de Pessoa. `test_login_dispatches_to_the_explicit_guard_without_using_a_database` esperava `/perfil` para uma fixture que obteve `/conta`. A segunda divergência interrompeu as asserções seguintes desse teste. A coordenação atualizou as expectativas de rota do Bloco 8.1 e marcou a fixture de `/perfil` como Pessoa voluntária aprovada. |
| Suíte MySQL descartável da coordenação | 104 aprovados e 1.064 asserções no conjunto, incluindo `DocumentoPessoaTest` (12/12), `PessoaValidationAccessTest` (7/7), `PessoaAuthTest` (5/5) e `OngAuthAndEventoTest` (5/5). Ver o [relatório consolidado](RELATORIO_CORRECOES.md). |
| Execução direta de `PreventRequestForgery` com ambiente local e sessão sintética em memória | Quatro casos confirmados. `POST` sem origem reconhecida ou token válido e `POST` `cross-site` sem token resultaram em `419`. `PATCH` com token correto e `POST` de origem `same-origin` chegaram ao handler de controle, que retornou `204`. O ensaio chamou o middleware diretamente, sem HTTP externo ou banco. |
| `node --test tests/Frontend/api-client.test.mjs` | 25 aprovados. Inclui segregação de credenciais, Bearer corrente a cada chamada e envio de CSRF no modo sessão para escrita. |

O runner desta frente confirmou o driver e o banco em memória antes de executar a suíte. A verificação de arquivos cobriu contrato, políticas e código de armazenamento, com teste da janela de acesso administrativo. A coordenação registra o resultado da nova execução de `IdentityBoundaryTest` no [relatório consolidado](RELATORIO_CORRECOES.md). Homologação de upload/download em S3, execução de retenção e validação do ambiente publicado permanecem fora da execução.
