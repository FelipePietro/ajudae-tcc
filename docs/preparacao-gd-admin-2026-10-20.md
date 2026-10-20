# Preparação local — GD e administrador sintético

## Estado e autorização

Preparação concluída localmente, em `feat/ajustes-pre-deploy`, HEAD preservado `f82a530a970c10bc6f24d70a374dea832ab7484f`, staging vazio. **Sem commit, push, deploy, alteração de variável, cadastro ou mudança de registro hospedado nesta etapa.** Não houve chamada de escrita ao Railway. Nenhum seeder ou migration de produção foi executado.

Os relatórios e scripts das rodadas anteriores estavam sem staging ao iniciar e foram preservados. A mudança de aplicação desta rodada compreende somente os arquivos listados abaixo.

## Diff preparado

| Arquivo | Mudança |
| --- | --- |
| `composer.json` | Declara `ext-gd` e `ext-exif`. EXIF é uma dependência já utilizada pelo sanitizador JPEG, descoberta pelo teste do fluxo existente. |
| `composer.lock` | Apenas content-hash e requisitos de plataforma GD/EXIF; arrays de pacotes preservados integralmente. |
| `railpack.json` | Preflight no build e no binário FrankenPHP antes do start; mantém PHP 8.4 e o entrypoint HTTP existente. |
| `scripts/check-gd.php` | Verifica versão, GD, EXIF, codecs e round trip real JPEG/PNG/WebP, sem inicializar Laravel/DB. |
| `app/Services/SyntheticAdminProvisioner.php` | Lifecycle transacional e idempotente, somente para uma identidade sintética fixa do lote. |
| `app/Console/Commands/HomologacaoAdmin.php` | Comando Artisan com leitura por padrão, confirmação explícita, senha oculta e mensagens sanitizadas. |
| `tests/Feature/SyntheticAdminProvisioningTest.php` | Isolamento SQLite em memória; idempotência, rollback, limites de privilégio, CLI e revogação. |
| `tests/Feature/GdFormatsTest.php` | JPEG/PNG/WebP reais passam pelo sanitizador de imagens existente, sem S3. |
| Este documento | Evidências, limites e procedimento exato para revisão. |

Trecho do diff de Composer:

```diff
 "require": {
     "php": "^8.4",
+    "ext-gd": "*",
+    "ext-exif": "*",
```

O Composer atualizou inicialmente URLs de metadados de phpdotenv sem mudar versão. Essa alteração não relacionada foi removida. A comparação estrutural final com HEAD confirmou `packages` (88) e `packages-dev` (33) **idênticos**, incluindo versões, referências e URLs. Nenhuma biblioteca foi instalada/atualizada por esse comando (`update --lock --no-install --no-scripts --no-interaction`). `composer validate --strict --no-check-publish` passou após a sincronização final.

Trecho do diff de Railpack:

```diff
 "php artisan package:discover --ansi",
+"php scripts/check-gd.php --require-php84",
 "npm run build",
```

```diff
-"startCommand": "docker-php-entrypoint --config /Caddyfile --adapter caddyfile 2>&1"
+"startCommand": "sh -eu -c 'frankenphp php-cli /app/scripts/check-gd.php --require-php84; exec docker-php-entrypoint --config /Caddyfile --adapter caddyfile 2>&1'"
```

As extensões devem ser instaladas pelo provider a partir dos requisitos `ext-*` do Composer. O guard do build verifica seu PHP CLI; o guard do start usa **FrankenPHP**, para não confundir o PHP de build com o binário que atende HTTP. Os guards falham se PHP não for 8.4, se GD/EXIF não estiverem disponíveis, se faltar codec ou se o round trip de algum formato falhar. Não há migration, seed, cache cleanup ou criação de conta no build/start. Não foram alteradas as regras de sanitização nem o contrato HTTP de uploads.

Referências primárias: [Railpack — extensões PHP](https://railpack.com/languages/php/), [FrankenPHP — execução CLI](https://frankenphp.dev/docs/).

## Resultados de testes reais

1. PHP oficial **8.4.26 NTS x64 para Windows**, extraído em diretório temporário, sem alterar o Laragon. Arquivo obtido de `windows.php.net/downloads/releases/`, SHA-256 validado contra os metadados oficiais: `da68394f9193b7f6b89d0c76861a4034ae10efee7fd55a7255d8118c2acf70d7`.
2. Preflight nesse PHP 8.4.26: GD `bundled (2.1.0 compatible)`, EXIF disponível, codificação e leitura de **JPEG, PNG e WebP** aprovadas, com imagem sintética 64×64.
3. Testes negativos: sem GD, o preflight terminou com código 1 e `GD is unavailable`; com GD mas sem EXIF, terminou com código 1 e `EXIF is required by the existing JPEG sanitizer`. Esses são bloqueios esperados, não testes aprovados artificialmente.
4. O primeiro teste do sanitizador JPEG revelou a falta de EXIF no runtime temporário inicial. A dependência explícita e a carga de EXIF resolveram o teste sem modificar a sanitização existente.
5. Conjunto final no PHP 8.4.26: **20 testes passaram, 110 assertions**. Filtro: `SyntheticAdminProvisioningTest|GdFormatsTest|TrustedProxyHttpsTest|PredeployCorsTest|ExampleTest`. Os testes do provisionador criam somente schemas necessários pelas migrations existentes em SQLite `:memory:`; não usam DB de desenvolvimento/MySQL nem Railway. Não foi usado o schema completo incompatível com SQLite.
6. Cobertura específica: default/dry-run sem escrita; criação mínima; repetição sem duplicar/rotacionar senha; permissões exatas e ausência de Master; recusa de conta preexistente, drift e reativação; rollback se auditoria falhar; rejeição de entrada inválida; terminal não interativo recusado; remoção de permissões/tokens e troca de senha na revogação; campos privilegiados não mass assignable; JPEG/PNG/WebP passam por `ManagedImageStorage::prepare`.
7. `npm run build`: passou, seis entradas no manifest, sete módulos transformados; aproximadamente 1m43s. Pint nos cinco arquivos PHP novos: passou. `git diff --check`: passou. Sintaxe PHP verificada separadamente no runtime 8.4.

**Limite relevante:** não há Docker/Railpack CLI disponíveis nesta máquina, portanto não foi construída a imagem Linux/FrankenPHP integralmente. O runtime temporário Windows verifica PHP 8.4 e os formatos, mas não prova disponibilidade na futura imagem Linux. Não se declara GD corrigida em produção. A execução do guard FrankenPHP no container real, os logs de extensão e o reteste de upload continuam obrigatórios após uma publicação expressamente autorizada. A suíte completa com MySQL não foi repetida; a validação não substitui os fluxos hospedados.

## Investigação administrativa

- `Pessoa` é o usuário autenticável, com `HasApiTokens`; `role_pessoa=administrador` identifica um administrador.
- `Pessoa::hasPermission()` recusa papéis não administrativos; para administrador comum consulta `permissao_pessoa`/`Permissao`; `admin_master=true` libera todas as permissões.
- `AdminMiddleware` delega ao mecanismo acima. Algumas rotas, como gestão de categorias, exigem apenas o papel administrativo; validação de Pessoas/ONGs e aprovação de eventos usam permissões nominais.
- `PessoaValidationService` contém operações exclusivas de Master, além da autorização comum. Não há necessidade de Master para o lote mínimo proposto.
- `role_pessoa` e `admin_master` não são fillable no cadastro público. A criação pública permanece voluntário não aprovado, sem promoção por payload.
- O `DatabaseSeeder` registra categorias e nomes de permissões, **não cria administradores**. Não foi identificado comando de bootstrap existente. Os testes criam admins via modelos apenas nos seus bancos isolados.
- Não existe bloqueio geral de login pelo status de validação em `PessoaController::login`. Por isso, revogar apenas o status não impediria login com a senha anterior: o procedimento também troca a senha por valor aleatório não divulgado e revoga tokens.

## Comportamento do novo procedimento

Identidade fixa: `hmlv1_20261020_admin`, nome `[HML - NAO REAL] Admin F82A530`, email sintético em `example.test`, lote `HML-V1-20261020-F82A530`. Não adota Pessoa A/B nem qualquer registro preexistente. O comando também recusa colisão do identificador fiscal ou email. Não há opção para login arbitrário, outros lotes ou Admin Master.

Permissões: somente `usuarios.validar`, `ongs.validar`, `eventos.aprovar`, pelos modelos/tabelas já existentes. Caso algum desses três nomes ainda não exista, usa `Permissao::firstOrCreate` especificamente para eles; não chama seeder. Não concede gestão de XP, LGPD, moderação ou Master.

`plan` e qualquer ação sem `--apply` são somente leitura e não criam nem auditoria. Para escrita, exige `--apply`, lote exato e identificador não secreto de operador/ticket. O primeiro provisionamento exige terminal interativo com senha oculta, confirmação e 20–72 bytes (limite compatível com bcrypt); recusa fallback que pudesse ecoar a senha. Não aceita senha em argumento, variável Railway, arquivo versionado ou saída. Recomenda-se senha aleatória exclusiva de 32–48 caracteres guardada no gerenciador de senhas do operador.

Criação, permissões e `AdminLog` estão na mesma transação. Se auditoria falhar, tudo é revertido. Eventos: `homologacao.admin.provisionado` e `homologacao.admin.revogado`, com alvo sintético, lote, operador/ticket, origem `artisan` e permissões; sem senha, hash, token ou PII real. O ator API é nulo porque a autoridade iniciadora é o operador do terminal, não uma pessoa autenticada na API.

Repetição ativa retorna `unchanged`, sem nova conta, senha, token ou log. Verifica identidade, proveniência em `AdminLog`, estado e permissões exatas; divergência interrompe em vez de reconciliar silenciosamente. Conta revogada não pode ser reativada pelo comando.

Na revogação: remove somente os tokens e vínculos de permissão da conta sintética comprovada, retira papel administrativo/Master, marca validação como reprovada definitivamente e substitui a senha por um valor aleatório não emitido. A conta e os logs são retidos. Não agenda exclusão, não remove históricos, não ativa jobs/scheduler e não interfere nas outras contas/permissões. Repetição revogada retorna `unchanged`. Se houver drift (inclusive mudança para Master), recusa alteração e requer revisão do operador.

## Plano exato para provisionar — NÃO executado em Railway

1. Após confirmação do usuário, revisar e publicar somente os nove arquivos desta preparação. Não incluir automaticamente scripts/relatórios antigos sem staging. Acompanhar instalação de GD/EXIF, build e guard do start. Sem migrations, seeders, reset de banco ou mudança de segredos.
2. Verificar o SHA publicado, serviço, domínio, páginas e `/up`. O operador autorizado deve abrir terminal **interativo** no container do serviço existente `ajudae-tcc`, ambiente `production`, projeto `exciting-elegance`. O conector atual não fornece terminal/SQL: é necessário acesso operacional oficial ao container. Não compartilhar credenciais no chat, não gravar sessão e não ativar tracing de shell (`set -x`).
3. No container e antes de qualquer escrita, executar:

```sh
frankenphp php-cli /app/scripts/check-gd.php --require-php84
frankenphp php-cli /app/artisan homologacao:admin plan
```

O primeiro deve mostrar PHP 8.4, GD, EXIF e os três formatos aprovados. O segundo deve mostrar lote correto e `absent`; se `active`, revisar proveniência e não recriar; se divergente, parar. Confirmar que a conexão pertence ao serviço/ambiente correto antes de aplicar.

4. Somente após autorização de provisionamento, com ticket não secreto `HML-20261020-01` (ou identificador aprovado equivalente):

```sh
frankenphp php-cli /app/artisan homologacao:admin provision --apply --confirm-batch=HML-V1-20261020-F82A530 --operator=HML-20261020-01
frankenphp php-cli /app/artisan homologacao:admin plan
```

Digitar a senha apenas nos dois prompts ocultos. Não passar `--no-interaction`. Resultado inicial esperado `created`, seguido de `active`; uma repetição válida deve retornar `unchanged` sem pedir/rotacionar senha. Esse passo cria uma Pessoa sintética, até três registros canônicos de permissões ausentes, três vínculos e um `AdminLog`. Não cria token nem assinatura fictícia de termos: trata-se de provisionamento operacional, não aceite em nome de usuário real.

5. Usar a conta somente em sessão controlada para as aprovações do lote: Pessoas/ONG já sintéticas, categoria e evento sintéticos aprovados no plano anterior. O papel administrativo existente **não impõe escopo por lote no servidor**; a disciplina de IDs sintéticos é um limite operacional, não uma permissão nova. Não consultar/modificar dados reais. Não chamar operações exclusivas de Master. Novas áreas/permissões exigem revisão separada.
6. Ao concluir a homologação, encerrar requisições/sessões dessa conta e executar:

```sh
frankenphp php-cli /app/artisan homologacao:admin revoke --apply --confirm-batch=HML-V1-20261020-F82A530 --operator=HML-20261020-01
frankenphp php-cli /app/artisan homologacao:admin plan
```

Esperado `revoked`; nova execução deve retornar `unchanged`. Verificar `AdminLog` pelo canal administrativo autorizado, tokens revogados e login com a senha anterior rejeitado (401), sem imprimir senha/token. Nenhum token antigo pode continuar com privilégio administrativo porque o papel e as permissões foram retirados. Não usar o DELETE de conta, que agenda exclusão em sete dias.

## Situação por ambiente

| Item | Preparado | Testado localmente | Publicado agora | Homologado em produção |
| --- | --- | --- | --- | --- |
| GD/EXIF e formatos | Sim | PHP 8.4.26 + sanitizador existente | Não | Não; erro anterior permanece até publicação |
| Guard Railpack/FrankenPHP | Sim | Script validado; container integral não disponível | Não | Não |
| Comando administrativo | Sim | SQLite em memória, ciclo completo e proteções | Não | Não; nenhuma conta administrativa criada |
| Imagens/aprovações/eventos/XP/certificados | Sem mudança funcional nesta rodada | Apenas os testes locais selecionados | Nenhuma nova publicação | Continuação depende da próxima autorização |

## Decisões para você responder

1. Aprovar o diff local para commit/push na branch atual e deploy, com acompanhamento do guard FrankenPHP e reteste de imagem usando apenas a ONG sintética já existente?
2. Aprovar o provisionamento posterior de administrador **comum, sem Master**, com as três permissões propostas e o ciclo de revogação descrito? É preciso definir o operador/canal de terminal interativo no Railway; não enviar senha/token ao chat.
