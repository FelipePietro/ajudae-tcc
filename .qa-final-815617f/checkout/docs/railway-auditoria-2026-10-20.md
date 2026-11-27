# Auditoria Railway — Ajudaê — 20/10/2026

## Resultado e limites

Correções locais preparadas para duas causas comprovadas: URLs HTTP em respostas HTTPS e entradas CSS ausentes do manifest Vite. Produção ainda apresenta os defeitos: nenhuma correção foi publicada. Não houve mudança de variáveis, deploy/restart, migration, seed, escrita em bucket, cadastro, commit, staging ou push. As requisições hospedadas foram GET/HEAD de baixo volume, com timeout de 15–25 segundos. Nenhum endpoint de pessoa/listagem de dados pessoais foi consultado; `/pessoas/me` foi chamado sem autenticação e respondeu 401.

## Inventário confirmado pelo conector

| Recurso | Evidência |
| --- | --- |
| Projeto | `exciting-elegance`, `dda3dba4-c6da-4965-88c4-36ddb66147d4` |
| Ambiente | `production`, `627f4be3-17ea-4726-b231-e41e231c1e13`, sem alterações staged |
| Laravel | `ajudae-tcc`, `1020d392-b4f4-42bd-a8a6-56c5a3082e2e`, uma réplica em `iad` |
| Deploy | `127cf338-b2c8-440f-ada0-e9eac04d8796`, SUCCESS, criado em `2026-10-20T05:02:12.044Z` |
| Origem | `FelipePietro/ajudae-tcc`, `feat/ajustes-pre-deploy`, `a6ceabf5eb26225c6c2e4cee548911a5ff3d4592` |
| Runtime/build | Railpack V3, runtime V2, logs PHP 8.4.26 / FrankenPHP / Vite 8.2.1; Laravel instalado no lock local: 13.35.0 |
| Domínio | `https://ajudae-tcc-production.up.railway.app`, porta interna 8080, DNS/TLS gerenciados pelo Railway; nenhum TCP proxy no serviço Laravel |
| MySQL | `99a29290-b77d-449f-af08-b81d1f79a0fb`, imagem `mysql:8`, SUCCESS, volume `/var/lib/mysql`, `iad` |
| Bucket | `ajudae-storage`, `2c2809b8-c185-46b8-a676-5fbd35b72900`, live em `iad` |

O plugin oferece inventário, configuração, domínio, variáveis e logs, mas não terminal remoto, `migrate:status`, SQL ou operações S3 de leitura. As variáveis são redigidas pelo OAuth (`valuesRedacted=true`): valores e expressões de referência não estão disponíveis. Não foram solicitadas credenciais do bucket.

Presenças relevantes: `APP_URL`, `APP_ENV`, `APP_DEBUG`, `APP_KEY`, `TRUSTED_PROXIES`, `SESSION_SECURE_COOKIE`, `SESSION_DRIVER`, `CACHE_STORE`, `CORS_ALLOWED_ORIGINS`, `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET`, `AWS_ENDPOINT`, `AWS_USE_PATH_STYLE_ENDPOINT`, `IMAGE_STORAGE_DISK`, `DOCUMENT_STORAGE_DISK`, `RAILPACK_BUILD_APT_PACKAGES`, `RAILPACK_DEPLOY_APT_PACKAGES`. `ASSET_URL` e `DOCUMENT_PRIVATE_DISK` não constam na lista. Presença não comprova valor correto nem referência ao serviço esperado. A correção prévia de `APP_URL` para HTTPS é informação do anexo; seu valor atual não pôde ser lido.

## Causas comprovadas

### 1. Proxy TLS não configurado no Laravel

`HEAD https://.../`: 200, `Content-Type: text/html; charset=utf-8`, servidor `railway-hikari`. `HEAD http://.../`: 301, `Location: https://ajudae-tcc-production.up.railway.app/`.

O HTML recebido por HTTPS contém:

```html
<link ... href="http://ajudae-tcc-production.up.railway.app/build/assets/app-DwwQN3vt.css" ...>
<script ... src="http://ajudae-tcc-production.up.railway.app/build/assets/app-DDXb1QT_.js" ...></script>
```

`GET /api/v1/categorias-evento`: 200, `success:true`, paginação vazia, `first_page_url` e `path` com `http://`. Causas, habilidades, recursos e agendas também geraram paginação HTTP. Repetir categorias com `X-Forwarded-Proto: http` e `https` no cliente manteve a paginação HTTP; isso não permite observar o cabeçalho entregue internamente, pois o edge pode sobrescrevê-lo.

No commit auditado, `bootstrap/app.php` não configura trusted proxies, `config/trustedproxy.php` não existe e não há leitura de `TRUSTED_PROXIES`. O middleware instalado em Laravel 13 lê `config('trustedproxy.proxies')` e não lê automaticamente essa variável de ambiente. A autodetecção do framework não inclui Railway. Assim, a variável definida no painel era ineficaz para esse código.

O Railway documenta terminação TLS e `X-Forwarded-Proto=https`. O log de Caddy com HTTPS interno desabilitado é compatível com essa arquitetura, não evidência de certificado público quebrado. `APP_URL` não substitui a detecção de esquema da requisição; `ASSET_URL` corrigiria só assets, não paginação. A correção usa trusted proxies, sem `URL::forceScheme` global.

### 2. CSS existente, mas ausente do build

Os dois assets publicados respondem 200 por HTTPS, com MIME `text/css` e `text/javascript`. O manifest remoto também responde 200, porém contém somente `resources/css/app.css` e `resources/js/app.js`.

Quatro CSS versionados são pedidos pelas views e estavam fora das entradas Vite: `cadastro_ong.css`, `editar_ong.css`, `painel_ong.css`, `perfil_ong.css`. `GET /cadastro/ong` respondeu 500. Log redigido de runtime:

```text
[2026-10-20 06:32:59] production.ERROR: Unable to locate file in Vite manifest:
resources/css/cadastro_ong.css. (View: /app/resources/views/ong/cadastro_ong.blade.php)
```

Os logs de build confirmam geração apenas do CSS/JS principal no deploy atual. Não faltam arquivos CSS versionados nessa ocorrência; a correção apenas os adiciona ao build. Nenhum frontend foi inventado.

`public/hot` não existe localmente, e `/hot` respondeu 404 remotamente. Como o HTML usa assets com hash e não servidor Vite dev, não há evidência de hot mode ativo. O overlay `railpack.json` não executa cache de configuração no build/start; não há cache local `bootstrap/cache/config.php`. Não foi possível inspecionar o filesystem remoto ou excluir cache antigo por observação direta. Nenhuma limpeza foi executada.

## Alterações e validação local

| Arquivo | Alteração |
| --- | --- |
| `bootstrap/app.php` | Confia somente em `X-Forwarded-Proto`; não aceita IP/host/porta/prefixo encaminhados. Import ordenado pelo Pint. |
| `config/trustedproxy.php` | Lê `TRUSTED_PROXIES` via configuração, compatível com cache; aceita IPs/CIDRs separados por vírgula ou wildcard explicitamente configurado. |
| `.env.example` | Documenta a variável vazia para execução local; nenhum `.env` alterado. |
| `vite.config.js` | Inclui os quatro CSS existentes nas entradas de produção. |
| `tests/Feature/TrustedProxyHttpsTest.php` | Regressões de esquema, paginação, rejeição de headers não confiáveis e renderização com manifest real. A rota de diagnóstico existe só durante os testes. |
| `docs/railway-predeploy.md` | Orientação segura de proxies, assets e conferência pós-deploy. |
| Este relatório | Evidências, matriz e pendências. |

Resultados reais desta execução:

- `npm run build`: passou; seis entradas no manifest, sete módulos transformados, cinco CSS e um JS gerados. O build demorou aproximadamente 3,5 minutos, concentrados nos hooks de Tailwind/CSS. `public/build` continua ignorado pelo Git.
- PHP 8.5.5 local (diferente do PHP 8.4.26 remoto); `php -l` passou nos arquivos PHP de implementação/teste.
- `php vendor/phpunit/phpunit/phpunit --filter 'TrustedProxyHttpsTest|PredeployCorsTest|ExampleTest'`: **9 passaram, 35 assertions**, sem skips reportados. Inclui `/` e `/cadastro/ong` com URLs de assets HTTPS e manifest real. Esses testes não usam `RefreshDatabase`, não executam migrations e não acessam S3.
- Pint nos três arquivos PHP envolvidos: passou após ordenar o import preexistente em `bootstrap/app.php`.
- `git diff --check`: passou. Nenhuma dependência foi alterada.
- A suíte completa anterior de 188 testes citada no anexo não foi reproduzida nesta execução. As migrations têm SQL específico de MySQL; não se declarou equivalência com SQLite. Nenhum resultado anterior foi contado como homologação hospedada.

`TRUSTED_PROXIES` fica vazio localmente: HTTP local permanece HTTP. Wildcard só é admissível como escolha explícita para ingresso controlado que sobrescreve os headers e não permite bypass público. O serviço auditado tem domínio HTTP Railway e não tem TCP proxy; acesso privado por outros serviços continua sendo parte da fronteira de confiança. Não foram inventados CIDRs nem aplicado wildcard automaticamente. O valor atualmente redigido precisa ser conferido no painel. A identificação do IP real para rate limiting é uma pendência separada; esta correção não passa a confiar em `X-Forwarded-For`.

## Matriz de homologação

| Área / verificação | Status | Ambiente | Evidência / limite |
| --- | --- | --- | --- |
| Saúde e TLS | passou | produção | `/up` e `/api/health` 200; HTTP redireciona 301 para HTTPS. |
| Assets disponíveis por HTTPS | passou | produção | CSS principal e JS 200, MIME correto. |
| HTML e paginação sem mixed content | falhou | produção | URLs HTTP dentro da resposta HTTPS; aguarda publicação e reteste. |
| Cadastro ONG renderizável | falhou | produção | `/cadastro/ong` 500; exceção de manifest confirmada em log. |
| Correção HTTPS e manifest | passou | local | Build e testes das duas páginas, URLs/paginação HTTPS, HTTP local preservado. Não inclui inspeção visual de navegador. |
| API pública de referência | passou | produção | Categorias, causas, habilidades, recursos, eventos e agendas: 200 com `success:true`; paginação HTTP registrada separadamente como falha. Corpos de dados não exportados. |
| Anônimo em rota protegida | passou | produção | `/api/v1/pessoas/me`: 401, `success:false`, `message`, `errors`. |
| Erro de recurso inexistente | passou | produção | Rota API inexistente e código fictício de certificado: 404, envelope JSON esperado. |
| CORS | passou | local | Origem permitida e origem diferente verificadas nos testes existentes. |
| Login, logout, token, papéis e 403 | bloqueado | produção | Exigem contas/token isolados; nenhuma conta real usada. 401 não comprova 403. |
| MySQL: conectividade das consultas públicas | passou | produção | Catálogos e agendas consultam o banco e responderam 200; não comprova todas as tabelas. |
| MySQL: 45 tabelas / 23 migrations Ran | bloqueado | produção | Informação histórica do anexo; sem ferramenta SQL/terminal para reconferir. Não executar migrations novamente. |
| S3: existência e nomes de variáveis | passou | controle Railway | Bucket live, variáveis AWS presentes; discos privados confirmados no código. Valores/referências redigidos. |
| S3: seleção do disco documental | falhou | configuração de produção | `DOCUMENT_STORAGE_DISK` presente, mas código lê `DOCUMENT_PRIVATE_DISK`, ausente; padrão do código é `local`. Configuração efetiva remota/cache não inspecionável. |
| S3: credenciais/conectividade/privacidade efetiva | bloqueado | produção | Plugin não expõe HeadBucket/HeadObject/terminal; sem exportar credenciais ou listar objetos. |
| Eventos/agendas: leitura | passou | produção | GET das coleções respondeu 200; não atesta dados ou fluxos operacionais. |
| Eventos/agendas/candidaturas: criar, aprovar, presença, avaliar | não testado | produção | Ações persistentes dependem de aprovação e dados isolados. Rotas temporárias de demonstração também não comprovam integração. |
| Imagens/documentos: upload, download e autorização | não testado | produção | Nenhuma escrita em bucket; downloads documentais podem gerar auditoria, por isso precisam do plano autorizado. |
| Certificados: código inválido | passou | produção | Consulta do código fictício retornou 404 controlado. |
| Certificados/XP/LGPD: geração, concessão, retenção/exclusão | não testado | produção | Sem contas/eventos isolados nem autorização para efeitos persistentes, jobs ou scheduler. |

## Pendências de segurança/configuração

1. **Documentos em disco efêmero:** o código usa `env('DOCUMENT_PRIVATE_DISK', 'local')`; `DOCUMENT_STORAGE_DISK` não é lida em nenhum ponto de `app/` ou `config/`. Antes de uploads reais, conferir no painel e aprovar `DOCUMENT_PRIVATE_DISK=railway_private`. Não remover a variável antiga nem migrar/apagar objetos automaticamente. Trocar o disco de novas gravações não move dados já existentes. Não foi possível confirmar valores de `IMAGE_STORAGE_DISK` e credenciais/referências AWS.
2. **Dependências:** `npm audit --json --ignore-scripts` confirmou 3 alertas: 2 críticos (`concurrently`, `shell-quote`) e 1 alto (`source-map-js`), com correção disponível. São dependências de desenvolvimento/build, mas o risco da cadeia de build deve ser avaliado. Referências retornadas pelo audit: [shell-quote](https://github.com/advisories/GHSA-pqg4-j6r4-53mv), [source-map-js](https://github.com/advisories/GHSA-68fv-2mgg-jv7q). Não foi usado `npm audit fix` nem alterado o lockfile nesta correção de HTTPS/assets.
3. **Fronteira de proxy/IP:** conferir valor de `TRUSTED_PROXIES`, ingresso privado e chave real do rate limiter antes de escalar réplicas. Sem ver o valor atual, não é possível garantir que apenas publicar o código habilitará a confiança correta.
4. **Frontend de demonstração:** rotas web têm dados fixos e algumas rotas temporárias de candidatura redirecionam/exibem mensagens. Renderizar uma tela não prova integração completa com a API. Nenhum contrato API foi alterado.

## Sequência para publicação e cadastro controlado

1. Revisar o diff local e confirmar a configuração não secreta de `TRUSTED_PROXIES` no painel; conferir `APP_URL` HTTPS, `APP_DEBUG=false` e `SESSION_SECURE_COOKIE`. Manter `ASSET_URL` ausente salvo CDN deliberada. Nada exige troca de `APP_KEY`.
2. Com autorização explícita, versionar os sete arquivos desta alteração, fazer push **apenas** em `feat/ajustes-pre-deploy` e acompanhar o deploy. O serviço está ligado a essa branch: o push pode disparar autodeploy. Não acionar redeploy adicional por rotina. Não migrar o banco já migrado.
3. Conferir o SHA efetivamente publicado, HTML sem URLs HTTP de assets, `/cadastro/ong` 200, manifest com seis entradas, CSS/JS por HTTPS sem bloqueio no navegador, paginação HTTPS e smoke 401/404. Se a correção não aparecer, diagnosticar runtime/cache antes de qualquer limpeza autorizada separadamente.
4. Antes de cadastrar, aprovar a matriz abaixo, o ambiente, identificador do lote e uma política explícita de limpeza. Preferir ambiente de homologação isolado; criar/clonar ambiente não está autorizado nesta execução. Se escolhido production, autorizar expressamente as escritas e seus efeitos.
5. Conferir o disco documental correto e acesso S3 por operação somente leitura disponível; obter acesso operacional para `migrate:status`/inspeção de schema somente leitura sem pedir nem imprimir credenciais. Não repetir migration aplicada.
6. Preparar payloads para os contratos atuais e dados totalmente sintéticos. Usar emails em domínio controlado para testes, logins exclusivos, adulto fictício e identificadores fiscais de teste válidos para os validadores, sem vincular pessoa real. Guardar senhas e tokens fora de relatório/logs; não executar chamadas externas de CNPJ ou notificações sem incluí-las no escopo.
7. Executar um cadastro `POST /api/v1/pessoas/register`, login, `/me` e logout; verificar 201/200/401 e respostas sem campos privados. Depois testar conta sem privilégio recebendo 403. A API documental é uma etapa própria, não pré-requisito para inventar URLs de documentos no cadastro.
8. Executar somente os outros fluxos aprovados abaixo; registrar IDs sintéticos/chaves de teste de forma privada. A limpeza deve restringir-se ao lote; exclusão LGPD é agendada e não equivale a limpeza física imediata. Não rodar expurgos, reconciliação ou scheduler global para limpar testes.

| Teste a autorizar | Contas/dados isolados | Efeitos que precisam ser aprovados | Limpeza a acordar |
| --- | --- | --- | --- |
| Cadastro/login/logout/403 | Duas pessoas sintéticas (titular e terceiro); admin de homologação só se necessário | Pessoas, aceite de termos, tokens e eventuais registros de auditoria | Revogar tokens; decidir exclusão lógica ou remoção administrativa apenas dos IDs do lote. |
| Cadastro/aprovação ONG e eventos | ONG sintética, organizador e admin de homologação | Cadastro, aprovação, vínculo, evento e agenda | Ordem inversa das dependências; nunca usar exclusão em massa. |
| Candidatura/presença/avaliação/XP/certificado | Evento e agenda do lote, titular e organizador | Inscrição, presença, avaliações, transações XP e certificado | Preservar integridade/histórico; preferir descarte de ambiente isolado autorizado. |
| Imagem/documento | PNG/PDF sintéticos, sem PII, titular/terceiro/admin | Put/Get/Delete de chaves descartáveis do lote; logs de download | Aprovar previamente objetos descartáveis e exclusão exata; não listar/exportar objetos de usuários reais. |
| LGPD/retenção | Conta/arquivos somente do lote | Solicitação, exclusão lógica, retenção e eventual expurgo | Definir efeito e prazo; nenhuma execução global de jobs/scheduler. |

## Git ao encerrar

- Branch: `feat/ajustes-pre-deploy`.
- HEAD preservado: `a6ceabf5eb26225c6c2e4cee548911a5ff3d4592`.
- Quatro arquivos rastreados modificados: `.env.example`, `bootstrap/app.php`, `docs/railway-predeploy.md`, `vite.config.js`.
- Três novos arquivos sem staging: `config/trustedproxy.php`, `tests/Feature/TrustedProxyHttpsTest.php`, este relatório.
- Staging vazio; sem commit/push/merge/rebase/deploy. `public/build` gerado localmente e ignorado.

## Decisões para você responder

1. Autorizar commit/push na branch atual e a publicação pelo Railway, após conferir `TRUSTED_PROXIES` no painel; caso o valor precise mudar, autorizar essa alteração específica separadamente.
2. Autorizar a correção de configuração `DOCUMENT_PRIVATE_DISK=railway_private` antes de qualquer upload real.
3. Para testes persistentes, escolher homologação isolada ou produção e aprovar as contas/dados sintéticos, as linhas da matriz desejadas e a forma de limpeza. Até isso ser definido, não executar cadastros/uploads/fluxos mutáveis hospedados.

Fontes técnicas consultadas: [Laravel 13 — trusted proxies](https://laravel.com/framework/docs/requests#configuring-trusted-proxies), [Railway — headers e TLS](https://docs.railway.com/networking/public-networking/specs-and-limits). As evidências de produção foram obtidas diretamente por HTTP e pelo conector; documentação não substitui verificação hospedada após publicação.
