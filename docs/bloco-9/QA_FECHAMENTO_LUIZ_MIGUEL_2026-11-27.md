# QA de fechamento do Bloco 9 — Luiz Miguel

27/11/2026 · America/Sao_Paulo · `feat/participacao-administracao` → `dev`

## Veredito

**REPROVADO — CORREÇÃO OBRIGATÓRIA.** O QA-F01 foi reproduzido no commit publicado. Antes de abrir o PR para `dev`, publique um `.env.example` sanitizado e compatível com a configuração da branch, valide o bootstrap em checkout limpo e conclua as três jornadas de navegador. Antes do merge, a equipe precisa registrar o escopo de leitura das renovações e a autoridade para avaliar o voluntário na RN021.

## Versão e estado do Git

| Item | Evidência desta execução |
| --- | --- |
| Branch publicada | O conector GitHub consultou o nome da branch e devolveu `815617f46c6d676d9681f94a518487f770b88f1a`, correspondente ao HEAD local. O `git ls-remote` falhou com `getaddrinfo() thread failed to start`. |
| Base e delta | A comparação GitHub devolveu `dev` e ancestral comum em `74609bfd03cfbdbfa8dbfdcaf603c5dabd91794e`. O `git rev-list --left-right --count <base>...HEAD` confirmou 0 atrás e 2 à frente. São 48 caminhos, com 19 adições, 28 modificações e a remoção de `.env.example`. |
| Árvore auditada | `63cf38fb853b96604ec48d5508a9a16865162e69`. O clone local falhou ao iniciar o shell MSYS por restrição de acesso do ambiente. Usei `git archive` no SHA confirmado e extração em diretório isolado para reproduzir a cópia e executar as verificações sem arquivos de trabalho não rastreados. |
| Checkout de trabalho | Inicialmente sem alterações rastreadas e com cinco itens não rastreados preexistentes, incluindo o template local, a pasta de QA anterior e seu relatório/evidências. Este relatório é o único arquivo entregue pela rodada. |

[Commit publicado](https://github.com/FelipePietro/ajudae-tcc/commit/815617f46c6d676d9681f94a518487f770b88f1a) · [Comparação com `dev`](https://github.com/FelipePietro/ajudae-tcc/compare/74609bfd03cfbdbfa8dbfdcaf603c5dabd91794e...815617f46c6d676d9681f94a518487f770b88f1a)

## QA-F01 — template ausente no bootstrap

**Severidade média, impeditivo de liberação.** A consulta ao conteúdo da branch via GitHub retornou 404 para `.env.example`. `git ls-tree HEAD -- .env.example` não retornou entrada. No checkout de trabalho, Git mostrou `?? .env.example`. No snapshot extraído estavam ausentes tanto o template quanto o `.env`.

Passos reproduzidos no snapshot, em PHP 8.5.5:

```text
php -r "file_exists('.env') || copy('.env.example', '.env');"
PHP Warning: copy(.env.example): Failed to open stream: No such file or directory
ENV_CREATED=False
TEMPLATE_EXISTS=False
```

O `composer setup` depende dessa cópia antes de `artisan key:generate` e inclui `migrate --force`. Após instalar as dependências, `php artisan key:generate --no-interaction` chegou ao comando de chave e falhou com código 1 e `file_get_contents(.../.env): Failed to open stream: No such file or directory`. `.env` continuou ausente. A sanitização e a cobertura das variáveis precisam ser conferidas no template publicado após a correção. O template local ficou fora da verificação do SHA. [Scripts Composer](../../composer.json#L43) · [QA anterior](QA_FINAL_INDEPENDENTE_LUIZ_MIGUEL_2026-11-27.md#7-defeito-confirmado)

## E2E de navegador

Os três percursos ficaram **sem execução nesta rodada**. Além da falha no bootstrap padrão, a tentativa de conexão com a instância MySQL descartável falhou. A revisão se limitou às rotas, às ações da interface e aos contratos. Ficaram pendentes o login normal, as respostas HTTP de cada ação, a recarga, a persistência, a auditoria e os casos negativos.

| Cenário | Ator, interface e rota conferidos no código | Evidência ainda necessária |
| --- | --- | --- |
| E2E-01 · Reprovar ONG | Operador com `ongs.validar`. Em `/admin/cadastros-ong`, `Reprovar ONG` envia `PATCH /admin/validacoes-ong/{ong}/status` com `status_ong: reprovado`. | HTTP, retirada da fila após recarga, novo estado e log com operador. Negativa para identidade sem capacidade. |
| E2E-02 · Presença pelo designado | Organizador designado e com vínculo ativo. Em `/organizador/eventos#gestao-participacoes`, `Salvar presença` envia `PATCH /gestao/inscricoes/{inscricao}/presenca`. | HTTP, autoria e estado persistidos após recarga. Negativa para organizador sem designação ou com vínculo encerrado. |
| E2E-03 · Certificado estudantil | Titular elegível. `/meus-certificados` consulta `/meus-certificados/dados`, emite por `POST /meus-certificados/eventos/{evento}/emitir` e atribui `pdf_url` ao link de download de `GET /meus-certificados/{certificado}/pdf`. | Provar que a jornada entrega PDF da modalidade estudantil para o titular, com monitoria válida. Conferir binário, recarga e repetição sem duplicidade. Exercitar monitoria não validada ou ator não autorizado. |

[Operações administrativas](../../resources/js/modules/admin-operations.js#L89) · [Gestão de participação](../../resources/js/modules/participation-management.js#L76) · [Rotas de certificados](../../routes/identity.php#L92)

**Certificado estudantil — lacuna identificada no código.** O modo integrado emite um certificado único e o serviço verifica a monitoria. O modo integrado oculta a modalidade na tela, o endpoint não recebe modalidade nem a devolve no Resource, e o PDF usa o título genérico “Certificado de participação voluntária”. O seletor Geral/Estudantil só existe no modo demonstrativo. Conferir com produto se o documento deve identificar a modalidade. Se for um requisito, o ajuste mínimo é transportar e exibir essa informação no fluxo existente, preservando a unicidade por Pessoa e Evento. O download pelo navegador e o conteúdo binário continuam pendentes de prova. [Tela](../../resources/views/certificados/lista.blade.php#L12) · [Emissão Web](../../app/Http/Controllers/WebCertificateController.php#L71) · [PDF](../../resources/views/certificados/documento.blade.php#L22)

## Decisões contratuais antes do merge

- **Certidões de renovação.** A revisão do cadastro inicial exige atribuição individual vigente para o acesso administrativo ao documento. A autorização Web de renovação também admite operador habilitado com `usuarios.validar` na fila, para certidão ativa e pendente de voluntário aprovado sem exclusão pendente e com arquivo retido. O Resource fornece o link mediante autorização e o controlador volta a verificar o acesso e registra a entrega. RN002 descreve a posse da análise cadastral, RN003 trata da renovação, RN006 exige capacidade e RN032 restringe o acesso a quem precisa analisar. A referência local é omissa sobre posse individual na renovação. A equipe responsável por produto e privacidade deve ratificar a capacidade da fila com os limites atuais ou exigir atribuição específica à renovação. Preserve o fluxo do cadastro inicial. Esta rodada não confirmou exposição indevida. [RN V1](../../Ajudae_Regras_de_Negocio_V1.md#rn002--validacao-do-voluntario) · [Policy](../../app/Policies/DocumentoPessoaPolicy.php#L11) · [Resource](../../app/Http/Resources/DocumentoPessoaResource.php#L34) · [Controlador](../../app/Http/Controllers/Api/DocumentoPessoaController.php#L80) · [Catálogo e testes](CATALOGO_WEB.md#administração)
- **RN021, autoria da avaliação.** A RN021 local atribui a nota da ONG com responsável designado ao próprio organizador. O contrato do Bloco 9 atribui a D04/D05 a permissão adicional para a ONG proprietária. O serviço usa a autorização operacional e grava o tipo e ID do ator, de modo que a diferença afeta quem pode enviar a nota. O registro de aprovação do Bloco 8.1 descreve D04 como bloqueio em reanálise e D05 como autoridade de finalização. Esse registro, sozinho, não comprova a ampliação de autoria da nota. A equipe de produto responsável pelas RN deve confirmar a autorização adicional. Se confirmada, alinhar a RN021 e o registro de decisão. Se a RN021 prevalecer, restringir a autorização da avaliação e manter a política específica de finalização. [RN021](../../Ajudae_Regras_de_Negocio_V1.md#rn021--avaliacao-do-voluntario) · [D04/D05](../bloco-8-1/DECISOES_BLOQUEANTES.md#decisões) · [Contrato F4](CONTRATOS_PARTICIPACAO.md#autorização-por-registro) · [Serviço](../../app/Services/AvaliacaoService.php#L65)

O PDF oficial no caminho indicado pelo `AGENTS.md` estava indisponível. A análise normativa usou a referência Markdown rastreada, os registros de decisão e os contratos do repositório.

## Regressão proporcional

| Verificação | Resultado desta execução |
| --- | --- |
| Plataforma PHP | `php -v` identificou PHP **8.5.5**. `composer check-platform-reqs --lock` passou nesse runtime. As instalações PHP do Laragon inspecionadas eram 8.3.30 e 8.5.5. PHP 8.4 ficou sem prova. |
| Dependências PHP | `composer install --no-interaction --no-scripts --prefer-dist --no-progress` instalou **126 pacotes** do lock no reteste, com o cache local em modo somente leitura e a extensão ZIP habilitada para o comando. A primeira tentativa sem esse cache e sem ZIP falhou. O reteste foi uma instalação com cache, sem scripts Composer. O autoload emitiu um aviso PSR-4 para `App\View\Components\Botao`, em `app/View/Components/botao-primario.php`. |
| Sintaxe e whitespace | `php -l` passou em 29 arquivos PHP/Blade e `node --check` passou nos cinco scripts do delta. `git diff --check` do checkout passou. A comparação `dev...HEAD` apontou whitespace em `QA_REVALIDACAO_INDEPENDENTE_LUIZ_MIGUEL_2026-11-27.md:3`, uma quebra explícita de linha Markdown que já constava no QA anterior. |
| PHP integrado e MySQL | A inicialização isolada do MySQL 8.4.3 passou e o processo sinalizou prontidão em `127.0.0.1:34719`. O cliente falhou com `ERROR 2003` de Windows 10013 no sandbox. A consulta somente leitura fora do sandbox também falhou, com 10060. O comando de criação dos schemas era condicionado à prova de porta, UUID e diretório, e não chegou a ser executado. Nenhuma migração ou suíte integrada rodou. Os 162 testes e 2.275 asserções do QA anterior são evidência histórica do mesmo SHA. |
| PHP demonstrativo | `php vendor/bin/phpunit --do-not-cache-result --filter 'VisualFour\|VisualParticipation'`, com SQLite em memória, `AJUDAE_WEB_AUTH=false`, `AJUDAE_DEMO=true` e chave sintética na variável do processo. **18 testes passaram, 309 asserções.** |
| JavaScript | `node --experimental-test-isolation=none --test --test-concurrency=1 --test-reporter=spec` sobre os 14 arquivos `tests/Frontend/*.test.mjs`. **95 passaram**, sem falhas ou ignorados. O reteste resolveu as duas falhas ambientais dos helpers PHP apontadas na execução sob sandbox. |
| Build Vite | Após a falta de cache no primeiro `npm ci --offline --ignore-scripts`, reutilizei o cache npm local em uma cópia isolada. `npm ci --offline --ignore-scripts --no-audit --no-fund` instalou 60 pacotes. `npm run build` passou com Vite **8.2.1**, **40 módulos** e `public/build/manifest.json` gerado. Node **22.22.0**. |
| Registro de rotas | `php artisan route:list --except-vendor --json` retornou **350 rotas** com `AJUDAE_WEB_AUTH=true`, `AJUDAE_DEMO=false` e configuração de teste injetada no processo. A contagem confirma o registro das rotas, sem prova de requisições ou persistência. |
| Concorrência e QA-R01/R02 | Sem repetição. A ponta publicada segue no mesmo SHA do QA anterior. A revisão documental desta rodada se concentrou nas duas decisões de produto. |

O sandbox fez o PHP rejeitar o diretório de cache e o arquivo de autoload nas primeiras tentativas. A geração de chave, a regressão demonstrativa, o reteste JS e a listagem de rotas foram repetidos fora do sandbox, no snapshot isolado, com SQLite em memória nas verificações que iniciaram a aplicação. A chave da regressão existiu apenas no processo. Nenhuma dessas execuções criou `.env`.

Sem edição de código, stage, commit, push, PR, merge, deploy, migração ou acesso a banco real. A entrega consiste no relatório de QA, sem ZIP de delta de código. Após a próxima rodada, `AGENTS.md` pode referenciar o resultado do bootstrap e a situação das três jornadas no novo SHA.

## Decisões para você responder

Nenhuma para encerrar a rodada. Antes do merge, a equipe responsável pelas regras deve confirmar o escopo de leitura das renovações e quem pode registrar a avaliação prevista na RN021.
