# Bloco 9 — Integração Web de Felipe

## Escopo e estado do Git

- Branch de trabalho `feat/identidade-cadastros`, já ativa e sem alterações rastreadas ou não rastreadas na abertura.
- Base inicial `74609bfd03cfbdbfa8dbfdcaf603c5dabd91794e`. O `git fetch origin` confirmou a referência remota correspondente à base.
- Commits de código `63c2a95` para cadastros, `70cc9ac` para jornada privada e organizadores e `3826a84` para corrigir o carregamento do apadrinhamento. O manifesto registra o HEAD após o commit deste relatório.
- O anexo atribui cadastro de ONG e organizadores a Felipe. A matriz histórica atribui as mesmas telas a Oliver. Esta execução segue o anexo e usa a matriz como referência dos contratos e dependências.
- A referência normativa consultada foi `Ajudae_Regras_de_Negocio_V1.md`. O PDF mencionado no `AGENTS.md` e o manual de quatro integrantes estavam indisponíveis no checkout.
- A entrega inicial preservou todas as rotas. A retomada autorizou exclusivamente a alteração de `GET /cadastro/ong`. Controllers, Services, Form Requests, Policies, Models, migrations, configurações e cliente HTTP compartilhado permaneceram sem alterações. O workspace local usa o contrato Web com `AJUDAE_WEB_AUTH=true`, cookies de sessão e CSRF.

## Auditoria e implementação

| Responsabilidade | Estado inicial | Resultado e pontos de entrada |
| --- | --- | --- |
| Cadastro de Pessoa | Assistente integrado, com validação do navegador e requisição real. A orientação sobre a etapa documental terminava em indisponibilidade. | `GET /cadastro` e `POST /cadastro` preservam a navegação em etapas. O envio segue o contrato JSON, usa o cliente existente e aponta para o envio privado após entrar. A confirmação informa validação pendente. A aceitação de termos e a ausência de autenticação automática foram verificadas. |
| Login, logout e senha | Integrados por formulários Laravel. | Mantidos `POST /login`, `POST /logout`, `GET /conta` e `POST /conta/senha`. A cobertura inclui Pessoa, ONG, credencial incorreta, limite de tentativas, senha atual incorreta, troca, logout, revogação e acesso privado. Recuperação de senha esquecida está fora do escopo. |
| Cadastro de ONG | `GET /cadastro/ong` exibia a indisponibilidade. O formulário existente servia à demonstração. `POST /cadastro/ong` já aceitava multipart. | `GET /cadastro/ong` agora abre o formulário integrado para visitantes. O wizard envia os dados do responsável e `documento_identidade` por multipart, indica erros por campo, bloqueia envios concorrentes e confirma que a aprovação permanece pendente. |
| Validação e documentos privados | A jornada autenticada tinha seções sem consulta, upload ou reenvio. | `/perfil#documentos` consulta `GET /conta/pessoa/validacao` e `GET /conta/pessoa/antecedentes`, aceita upload multipart via `POST /conta/pessoa/documentos/{tipo}` e abre o download autorizado em `GET /conta/documentos/{documento}/download`. `/perfil#validacao` mostra situação, bloqueio, exigência de padrinho e reenvio via `POST /conta/pessoa/validacao/reenviar` quando o estado permite. |
| Apadrinhamento | A área integrada mostrava indisponibilidade. Os fluxos detalhados existentes eram demonstrativos. | `/perfil#apadrinhamento` utiliza as rotas de `/conta/pessoa/padrinhos` para ler, emitir e revogar código, registrar indicação, listar elegíveis, solicitar, aceitar, recusar e cancelar, consultar recebidas, histórico e vínculos, obter progresso, solicitar conclusão e encerrar com motivo. O link de cadastro transporta o código no fragmento e o formulário o remove do endereço ao carregar. |
| Organizadores | Convites, resposta e encerramento integrados. Promoção, resumo da ONG e responsabilidade por Evento exibiam indisponibilidade. | A promoção abre na área autenticada de `/perfil` e usa `GET/POST /conta/pessoa/solicitacao-organizador`. `/ong/painel`, `/ong/organizadores` e `/organizador` usam a listagem de vínculos e `GET /gestao/eventos`. O responsável operacional é atualizado com `PATCH /conta/ong/eventos/{evento}/responsavel` por ONG proprietária. O aceite e o encerramento atualizam o escopo do organizador e a ação de criação de Evento. |

### Arquivos compartilhados

- `resources/js/identity.js` registra os módulos da jornada privada, promoção e Eventos de organizadores.
- `resources/views/components/{account-field,account-wizard,private-documents,registration-status,organizer-promotion-summary}.blade.php` reaproveitam os componentes de cadastro e situação para exibir os dados integrados.
- `resources/views/perfil/area-privada.blade.php`, `resources/views/auth/institutional-panel.blade.php` e `resources/views/ong/painel_ong.blade.php` conectam as seções do escopo. As demais áreas de perfil, participação, privacidade e gestão de Eventos seguem pelos seus próprios pontos de entrada.
- As rotas e entradas demonstrativas continuam disponíveis segundo a configuração existente. Nenhuma integração usa `/api/v1`, token Bearer, armazenamento público de documentos ou credenciais no navegador.

## Verificação da entrega inicial

- Baseline anterior às edições com `node --test tests/Frontend/api-client.test.mjs tests/Frontend/sponsorship-demo.test.mjs` passou com 36 testes.
- A primeira tentativa de `php artisan route:list -v` encontrou `vendor` ausente. Foi instalado PHP 8.4.28 para atender ao projeto e as dependências foram restauradas pelos lockfiles. A listagem integrada foi executada com sucesso. O Composer registrou um aviso preexistente de PSR-4 em `app/View/Components/botao-primario.php`.
- `php artisan test --filter 'Bloco81WebPessoaRegistrationTest|Bloco9WebOngRegistrationTest|Bloco9WebAccountOperationsTest|IntegratedNavigationTest|DocumentoPessoaTest|DocumentoStorageLgpdTest|PessoaValidationWorkflowTest|PessoaValidationAccessTest|PadrinhoWorkflowTest|OrganizadorWorkflowTest|Bloco9EventManagementTest|Bloco9WebEventParticipationTest' --compact` passou com 105 testes e 1.476 asserções. Foram usados MySQL 8.4.3 em loopback e o schema local descartável `ajudae_bloco9_phpunit`. A suíte PHP integral ficou fora da seleção.
- `node --test tests/Frontend/*.test.mjs` passou com 95 testes. `npm run build`, `node --check`, Pint nos três testes Feature alterados, `php artisan view:cache` e `git diff --check` também passaram.
- Chromium headless verificou `/cadastro`, `/login` e `/cadastro/ong` em 360, 768 e 1280 px. Nenhuma página pública apresentou transbordamento horizontal. O cadastro moveu o foco para o primeiro campo inválido e uma escrita sem CSRF retornou 419. As capturas de 360 e 1280 px estão no ZIP. O Edge local encerrou na inicialização e a verificação foi repetida com o Chromium.
- A migração independente do navegador foi inicialmente bloqueada pela revisão automática. O usuário autorizou a operação em `ajudae_bloco9_browser`. A execução confirmou a instância MySQL isolada e aplicou as migrations somente nesse schema.
- No Chromium, o cadastro de Pessoa retornou 201 e permaneceu pendente, sem login automático. Login de Pessoa e ONG, troca de senha, logout, upload inválido e upload válido, download privado e persistência após recarga foram exercitados.
- A ONG enviou o convite, o organizador aceitou e a designação foi gravada. A confirmação do convite respeitou Escape. O organizador vinculado visualizou o Evento designado, recebeu 403 na criação e voltou à atuação independente após o encerramento do vínculo.
- O navegador emitiu e revogou código de apadrinhamento, consultou elegíveis, enviou e aceitou solicitação, leu o progresso e encerrou o vínculo. O primeiro ensaio identificou que ações recém-renderizadas podiam receber cliques durante a consulta. Os botões agora aguardam o fim do carregamento, e o novo teste confirmou o POST de aceite e o vínculo ativo. A promoção foi enviada e permaneceu pendente após recarregar.
- Documentos, apadrinhamento e designação também passaram pelas verificações de largura a 360, 768 e 1280 px. O ZIP inclui capturas de telas representativas e os resultados das verificações de navegador. A homologação manual da equipe continua pendente.

## Abertura do cadastro de ONG

- A base da retomada foi `fbd7a4e3305ed752a04d2fbe6d4aaccd690438a0`. A documentação do Bloco 8.2 registrava a indisponibilidade enquanto faltavam o wizard integrado e o upload seguro. Ambos já estavam implementados no Bloco 9. A leitura de RN009, RN032 e do catálogo Web confirmou que a entrada pública é compatível com cadastro pendente e documento privado.
- Em `routes/identity.php`, a rota manteve o nome `ong.cadastrar.form`, o middleware de identidade pública e o bloqueio de sessão por requisição. Visitantes recebem `ong.cadastro_ong`. Uma sessão válida é encaminhada a `/conta`, seguindo o cadastro de Pessoa. O POST continua em `routes/web-account-operations.php`, com CSRF, autorização para visitante, limitação de tentativas, validação e armazenamento privado.
- A verificação de fronteira em `tests/Unit/IdentityBoundaryTest.php` agora espera o wizard institucional integrado. `tests/Feature/Bloco9WebOngRegistrationTest.php` testa o GET real e cobre o redirecionamento de Pessoa e ONG autenticadas, com POST proibido e ausência de criação de conta ou arquivo. O POST mantém a cobertura de persistência, upload privado, validação de CNPJ, CPF, tipo e tamanho, falha de armazenamento, reversão de gravação parcial e isolamento.
- O `git fetch origin` confirmou `origin/dev` em `74609bfd03cfbdbfa8dbfdcaf603c5dabd91794e`, ancestral da branch de Felipe. Não foi identificado conflito nesta comparação. `routes/identity.php` é compartilhado e poderá exigir conciliação se outra branch modificar a mesma rota.

### Verificação da retomada

- O Chromium saiu da página inicial e abriu as seis etapas em `/cadastro/ong`. As verificações de largura passaram em 360, 768 e 1280 px. Foram inspecionadas capturas reais do formulário em 360 px e da confirmação em 1280 px.
- Arquivo incompatível e termos ausentes foram sinalizados na interface. O POST sem CSRF retornou 419. O multipart com CNPJ e CPF inválidos retornou 422 e o foco voltou ao primeiro campo rejeitado. Com os dados corrigidos, o multipart retornou 201 e a confirmação mostrou aprovação pendente, sem login automático.
- Após login, a conta institucional persistiu como pendente. A consulta anônima ao documento retornou 401. O titular recebeu um anexo privado com `no-store` e conteúdo idêntico ao PDF sintético enviado. O painel protegido retornou 403. O Chromium também confirmou logout, o wizard de Pessoa e o redirecionamento das duas identidades autenticadas para `/conta`.
- A tentativa sem filtro no modo integrado executou classes dependentes das rotas demonstrativas e registrou 81 falhas dessas classes. A suíte foi então dividida por modo. No schema descartável `ajudae_bloco9_ong_integrado`, passaram 265 testes e 3.198 asserções, com um caso ignorado. No schema `ajudae_bloco9_ong_demo`, passaram 91 testes de rotas demonstrativas e 3.631 asserções. Juntos, os grupos complementares cobrem 357 casos, com 356 aprovados, zero falhas e um teste legado de upgrade de certificados ignorado por exigir instância dedicada e ativação explícita. A execução sem filtro no modo demonstrativo também passou, com 309 aprovados e 48 casos ignorados.
- `node --test --test-reporter=dot tests/Frontend/*.test.mjs` passou com 95 testes. `npm run build`, Pint, `php -l`, `git diff --check` e a geração e listagem do cache de rotas em arquivo temporário passaram.

## Limites e veredito

- **Elegibilidade e decisões.** O backend revalida as regras ao receber escritas de promoção, padrinho, vínculo e designação. O GET de promoção informa a última solicitação. Ele ainda não oferece um resultado consolidado de elegibilidade. A interface apresenta os critérios e exibe os conflitos devolvidos pelo backend.
- **Armazenamento externo.** A cobertura de armazenamento foi executada localmente. A homologação no Object Storage do Railway continua pendente.
- **Veredito local** — as seis jornadas de Felipe estão integradas e validadas nos cenários executados no Bloco 9, incluindo a jornada completa de cadastro institucional nesta retomada. A homologação futura do ambiente Railway deve cobrir a configuração Web e o upload e download no Object Storage de teste.

O manifesto do ZIP registra os arquivos criados e modificados, o HEAD final e os hashes. O trabalho foi mantido na branch de Felipe, sem publicação, merge, PR ou deploy.

Na próxima atualização do `AGENTS.md`, registrar as entradas Web de validação, apadrinhamento e promoção sob `/perfil` e o cadastro institucional sob `/cadastro/ong`, com referência a este relatório.
