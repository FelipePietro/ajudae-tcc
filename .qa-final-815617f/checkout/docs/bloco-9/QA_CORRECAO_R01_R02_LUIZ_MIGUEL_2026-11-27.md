# QA-R01 e QA-R02 · Correção local do Bloco 9

27/11/2026 · Branch `feat/participacao-administracao` · Candidato `9fcd085a1342be00141ec36d1fa92d8f726284d1`

## Escopo e fonte

A revalidação independente registrou um 403 no download da renovação e confirmou que o operador conseguia decidir o documento em seguida. Também registrou o 403 esperado antes da posse cadastral, embora a interface oferecesse o link. As evidências originais foram preservadas. O PDF oficial no caminho do AGENTS.md estava indisponível. Usei a referência RN V1 local, o catálogo Web e o código. [Revalidação original](QA_REVALIDACAO_INDEPENDENTE_LUIZ_MIGUEL_2026-11-27.md) · [RN V1](../../Ajudae_Regras_de_Negocio_V1.md)

## Correção local

| Achado | Alteração e critério |
| --- | --- |
| QA-R01 · Renovação | A autorização do download Web permite a leitura administrativa de uma certidão de antecedentes ativa e pendente de renovação de voluntário aprovado, sem exclusão pendente e dentro da retenção. A conta precisa operar e ter `usuarios.validar`. A fila Web oferece a URL resultante da mesma policy e o controlador usa o disco privado e o log `documento.download`. |
| QA-R02 · Cadastro inicial | O detalhe Web retorna `download_url` somente quando permitido e informa `download_requires_assignment` quando falta uma atribuição válida. O JavaScript mostra a instrução de assumir e só renderiza o link quando recebeu a URL do servidor. Outro analista e uma atribuição expirada ficam sem acesso. |
| Apadrinhamento · Auditoria | O motivo continua no pedido. Na mesma transação da decisão, o serviço registra `padrinho.conclusao_decidida` com ator, pedido, transição e motivo quando fornecido. A fila continua limitada às pendências. O motivo segue obrigatório na reprovação Web e opcional no Bearer. |

O download Web reutiliza a rota existente. A ampliação de autorização se limita ao Web e ao documento da renovação. O Bearer conserva a policy de download anterior. A projeção não expõe caminhos do storage. A validação de cadastro permanece em `/admin/candidatos`. A autoria nominal de justificativa continua restrita à gestão e as decisões de ONG e Evento mantêm seus contratos.

### Rotas e arquivos

- `GET /admin/candidatos`, `GET /admin/validacoes-pessoa/{pessoa}` e `POST /admin/validacoes-pessoa/{pessoa}/assumir`
- `GET /admin/antecedentes/renovacoes`, `GET /conta/documentos/{documento}/download` e `PATCH /admin/documentos/{documento}/status`
- `GET /admin/conclusoes-padrinho` e `POST /admin/padrinhos/conclusoes/{pedido}/reprovar`
- Policy, modelo e projeção em `app/Policies/DocumentoPessoaPolicy.php`, `app/Models/DocumentoPessoa.php` e `app/Http/Resources/DocumentoPessoaResource.php`
- Controladores `app/Http/Controllers/Api/{AdminPessoaValidation,Antecedentes,DocumentoPessoa}Controller.php` e nomes das rotas em `routes/web-account-operations.php`
- Ações de tela em `resources/js/modules/admin-operations.js` e auditoria em `app/Services/PadrinhoService.php`
- Regressões em `tests/Feature/Bloco9WebDocumentReviewTest.php` e `tests/Feature/Bloco9WebAccountOperationsTest.php`. Contrato revisado no [Catálogo Web](CATALOGO_WEB.md).

## Chromium e persistência

MySQL 8.4.3 descartável, porta 33429, schema `ajudae_luiz_homologacao`, com UUID confirmado. O Laravel de homologação rodou em `127.0.0.1:8809`. A sessão administrativa tinha somente `usuarios.validar` e o PDF era a certidão sintética existente no repositório, gravada em disco privado local.

- **QA-R02.** O cadastro inicial aberto exibiu o aviso de posse e zero links de download. A análise foi assumida pela interface. Após recarregar e reabrir o detalhe, a tela exibiu o link autorizado. O banco confirmou `em_analise` com o operador que assumiu.
- **QA-R01.** A renovação apareceu na seção de antecedentes. O Chromium recebeu o download do PDF a partir do link da fila. Em seguida, a aprovação foi enviada pela interface. Uma recarga seguida de nova abertura da fila mostrou o estado vazio. A consulta MySQL confirmou o documento aprovado, o operador e a data gravados, o titular ainda aprovado e um log de entrega do PDF para aquele operador e documento.
- **Responsividade.** A tela de candidatos com a fila de renovações aberta após a decisão foi medida em **360, 768 e 1280 px**. A largura de rolagem foi igual à largura visível nos três casos. Essa medição cobre a tela e o estado exercitados. As nove frentes ainda precisam de revisão visual independente.
- O Edge dedicado estava indisponível na automação. Os dois percursos foram concluídos no Chromium interno. Não foram exportadas capturas.

## Validação automatizada

- PHP integrado · **156 aprovados, 2217 asserções** com `AJUDAE_WEB_AUTH=true`. Usei o MySQL descartável previamente migrado na porta 34649, schema `ajudae_qa9_independente`. O cliente MySQL e o PDO do Laravel concordaram em schema, porta, UUID e diretório antes da suíte. Os casos de navegação rodaram com SQLite em memória no mesmo comando final.
- Regressão focada · **5 testes, 95 asserções** para revisão documental e operações de conta. Cobertura de arquivo privado, retenção de autorização por escopo, auditoria do PDF, aprovação persistida, download antes e depois da atribuição e após expiração, e auditoria da reprovação de apadrinhamento.
- PHP demonstrativo · **18 aprovados, 309 asserções** com `AJUDAE_WEB_AUTH=false` e `AJUDAE_DEMO=true`.
- JavaScript · **95 aprovados**. O ensaio inicial falhou no Windows ao iniciar processos filhos. O reteste passou com `--test-isolation=none --test-concurrency=1`.
- Build Vite · **40 módulos**. A sintaxe dos PHP alterados, do JavaScript e `git diff --check` passou.
- No primeiro ensaio amplo, `DB_URL` configurado para MySQL também incidiu sobre o nome de conexão SQLite dos testes de navegação, com dez erros de tabela existente no schema descartável. O ensaio final usou parâmetros MySQL separados e neutralizou `DB_URL` para permitir o SQLite em memória. Nos primeiros ensaios focados, a fixture de sessão também foi ajustada para recarregar `auth_version` do banco antes de simular a sessão. A comparação com os 403 do candidato anterior usa as evidências HTTP independentes anexas.

## Encaminhamento para revalidação independente

Usar o candidato corrigido em uma nova rodada independente. O [roteiro de revalidação](QA_REVALIDACAO_INDEPENDENTE_LUIZ_MIGUEL_2026-11-27.md) cobre as nove frentes, e as inclusões prioritárias são:

1. Histórico pessoal · testar isolamento, página seguinte e recarga por titular.
2. Presença e monitoria · testar gravação, escopo do gestor e impedimentos de estado.
3. Justificativas · testar envio pelo titular, decisão do gestor e acesso à autoria nominal.
4. Avaliações e encerramentos · testar ordem, nota duplicada, XP e nova leitura.
5. Validação de Pessoas e documentos · repetir antes da posse, atribuição vigente, outro operador, prazo expirado, PDF de renovação, decisão e log.
6. ONG e Evento · testar payloads distintos, aprovação, reprovação e estado posterior.
7. Promoção e apadrinhamento · testar capacidade exclusiva, motivo da reprovação, fila após recarga e log. A consulta posterior do motivo pela interface depende de definição da equipe sobre perfil e escopo do histórico.
8. XP · testar criação, alteração, desativação e preservação do histórico de concessão.
9. LGPD e auditoria · testar atribuição, leitor reduzido, resposta e filtros.

Repetir permissões, escritas e recarga no Chromium em 360, 768 e 1280 px. Separar E2E de evidência HTTP, registrar os casos não executados e expor o resultado ao revisor antes da liberação para PR.

## Situação da entrega

- **Correção local.** Implementada com a nova cobertura e com dois percursos Chromium executados.
- **Publicação no GitHub.** Nenhum stage, commit, push ou PR nesta execução. A remoção de `.env.example` e os artefatos da revalidação anterior já estavam presentes no início.
- **Integração coletiva.** Revalidação independente das nove frentes e revisão com as outras branches pendentes.
- **Railway e S3.** Homologação externa pendente. O ensaio documental desta rodada usou armazenamento privado local. Sem deploy ou migrações novas.

### Decisões para você responder

Nenhuma para a correção local.
