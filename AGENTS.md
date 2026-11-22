# AGENTS.md — AJUDAÊ | Instruções permanentes para agentes de código

> **Finalidade:** tornar cada nova sessão Codex autossuficiente, minimizando perda de contexto, regressões, alterações fora do escopo e alegações de homologação não comprovadas. **Leia integralmente antes de modificar código.**
>
> **Snapshot informativo:** consolidado até 28/10/2026, com base no histórico do projeto, relatórios de auditoria e regras V1. **Não é fotografia em tempo real do workspace.** Toda informação de estado deve ser conferida novamente na execução. A documentação local, o código e a especificação original podem ter mudado.
>
> **Autoridade:** instrução explícita atual do usuário > regras de negócio oficiais + decisões posteriores aprovadas > contratos e testes existentes > este resumo > exemplos e fixtures. Ao encontrar contradição, **registre-a e não invente solução silenciosa**.

## 0. Protocolo obrigatório de início de CADA NOVA SESSÃO

1. Ler este `AGENTS.md` do começo ao fim e os `AGENTS.md` mais específicos, caso existam. Se houver um `agents.mk`, considere-o cópia auxiliar, não substituto do arquivo reconhecido pelo Codex.
2. Identificar tarefa pontual passada no prompt atual. **Não retomar automaticamente tarefas anteriores; não ampliar escopo** sem pedido explícito.
3. Inspecionar `pwd`, `git branch --show-current`, `git rev-parse HEAD`, `git status --short`, arquivos não rastreados, mudanças não commitadas, rotas e dependências pertinentes. **Não presumir worktree limpo.**
4. Ler documentação atual de `docs/`, começando pelos relatórios do bloco afetado e seu guia de integração, além do PDF oficial de RN em `C:\Users\felep\Downloads\Ajudae_Regras_de_Negocio_V1.pdf` se disponível. Se o arquivo não estiver disponível, declarar a limitação e consultar cópias ou documentos locais verificáveis; **não inventar texto do PDF**.
5. Inspecionar views, componentes, JavaScript, fixtures, rotas e testes existentes **antes de editar**. Para trabalho backend, inspecionar migrations aplicáveis, Models, Services, Policies, Controllers, Resources e testes.
6. Apresentar plano curto, arquivos previstos, riscos e limites. Implementar apenas o que estiver apoiado em código/contratos e nas decisões documentadas.
7. Validar o que for possível; relatar resultados reais, testes não executados e falhas de ferramentas. Não declarar screenshots, testes nem homologação visual que não ocorreram.
8. Produzir relatório objetivo e **ZIP de auditoria do delta desta execução**, se a tarefa alterar código, com `MANIFESTO.md`, hashes SHA-256, caminhos relativos e evidências possíveis; sem segredos e sem incluir o projeto inteiro.
9. Encerrar com alterações, arquivos afetados, rotas acessíveis, verificações, limitações, lacunas de backend, ZIP e hash. Se houver novas decisões realmente bloqueantes, listar em `Decisões para você responder`; caso contrário escrever `Nenhuma`.

### Regras negativas inegociáveis por padrão

- **NÃO** executar `git add`, commit, push, merge, rebase, `git reset --hard`, `git clean`, sobrescrita destrutiva, deploy, redeploy, migrações, seeders ou alterações no Railway sem autorização explícita para aquela execução.
- **NÃO** ativar API/BFF nem substituir mocks em tarefas de frontend visual. Integração real é etapa posterior dividida entre integrantes da equipe.
- **NÃO** editar contratos de backend, policies, migrations, banco, produção ou PDF oficial em bloco visual. Exceções somente mediante pedido novo explícito.
- **NÃO** usar contas, dados pessoais, credenciais, tokens, documentos ou banco real em demonstrações. Nunca registrar segredos no relatório, ZIP ou logs.
- **NÃO** redesenhar telas existentes como solução automática, remover recursos funcionais, criar rotas paralelas sem justificativa ou transformar página pública em painel privado.
- **NÃO** confundir evidências automatizadas/DOM com inspeção visual efetiva. Não fabricar screenshots.

## 1. Identidade, equipe e propósito

**Projeto:** Ajudae — *Plataforma Digital de Conexão Solidária: Voluntariado e Eventos Sociais Gamificados*. TCC da Etec de Guarulhos, Técnico em Desenvolvimento de Sistemas, 2026.

**Objetivo:** conectar Pessoas voluntárias, ONGs e organizadores a Eventos e Agendas de ação social, com moderação, inscrições, presença, avaliações, XP, certificados, perfis e proteção de dados.

**Equipe histórica:** Enrico Yoshida de Oliveira; Felipe Pietro da Costa Luiz; Luiz Miguel Leles dos Santos; Pedro Henrique Dias Roger; João Pedro Gomes dos Santos; Oliver Barbosa de Alcantara. **Orientador:** Prof. Jefferson Riper. Papéis atuais de integração não devem ser presumidos apenas com base em distribuições históricas.

**Repo:** `https://github.com/FelipePietro/ajudae-tcc`.
**Local usual:** `C:\laragon\www\ajudae-tcc` (Windows, Laragon).
**Branch histórica de trabalho:** `feat/ajustes-pre-deploy`.

## 2. Arquitetura e organização técnica

- **Backend:** Laravel 13 / PHP `^8.4` / Eloquent / MySQL 8.4 / API REST `/api/v1` / Laravel Sanctum / Services, Policies, Form Requests, Resources e roles/permissões. Ambiente local relatado PHP 8.5.5; Railway PHP 8.4.26 via FrankenPHP/Railpack. Comparar diferenças de runtime.
- **Frontend:** Blade + Tailwind/CSS existente + Vite + JavaScript, organizado em layouts, componentes, fixtures demonstrativas e rotas locais. Reutilizar o que existe, sem introduzir framework SPA ou biblioteca nova sem necessidade.
- **API:** respostas tipicamente `{ "success": boolean, "message": string, "data": ... }`; erros 401/403/404/409/422; usar rotas/Resources atuais para payloads precisos, sem inventar contratos.
- **Separação:** Blade visual e mocks não significam integração funcional. Não chamar diretamente a API/BFF em blocos visuais. Há BFF histórico isolado que **não deve ser reativado** inadvertidamente.
- **Arquivos relevantes:** `routes/api.php`, `routes/web.php`, `routes/demo.php`, `app/Support/NavigationContext.php`, `app/Support/VisualOneFixtures.php`, `VisualTwoFixtures.php`, `VisualThreeFixtures.php`, `PublicProfileFixtures.php`; `resources/views/components/*`, `resources/views/layouts/*`, `resources/js/*`, `resources/css/*`, `tests/Feature/*`, `tests/Frontend/*`.
- **Imagens:** disco lógico `railway_images`; documentos privados: `railway_private`; ambos usam armazenamento compatível S3 no Railway, com autorização via Laravel. PDFs de certificados gerados sob demanda com DomPDF. Conferir detalhes atuais em `config/filesystems.php` e nos Services.
- **Banco:** migrations Laravel são fonte do esquema implementado, mas não prova do esquema *já aplicado* ao MySQL. As migrations foram analisadas em pacote de 23 arquivos; não reescrever migrations históricas executadas em produção. Alterações futuras por migrations incrementais e com autorização.
- **DER:** existem versões SVG e PDF. O SVG gerado a partir de Models pode omitir FKs/colunas ou esconder atributos; para auditoria priorizar migrations, banco efetivo e Models. DER baseado em migrations tem limitações para alterações condicionais e constraints reais; nunca tratá-lo como verdade de produção sem inspeção.

## 3. Fontes normativas e decisões posteriores

**Fonte primária:** `Ajudae_Regras_de_Negocio_V1.pdf` (RN001–RN034; versão de outubro/2026). **Este arquivo resume as regras, não substitui a leitura do PDF para mudanças de comportamento.**

**Decisões explícitas posteriores, mandatórias para o frontend V1:**

1. **Carta de apresentação da candidatura é OPCIONAL**, na inscrição por Agenda, privada para candidato e ONG/organizador autorizado. Não é critério automático. Backend atual foi reportado sem campo próprio em `p_ag_inscreve`: documentar extensão futura (`carta_apresentacao` é sugestão, não requisito de nomenclatura). Não alterar backend durante fases visuais.
2. **Vagas controladas EXCLUSIVAMENTE por Agenda** (`vagas_agenda`). `evento.vagas_evento` é informativo, não limita somatório, não exige igualdade. Cenários válidos: Evento 100/Agenda 30; Evento 100/Agendas 70+60; Evento 30/Agenda 50. A menção a vagas em RN012 pode precisar reconciliação documental futura; **não impor limite global novamente**.
3. Perfis públicos de Pessoa e ONG são sempre públicos em conteúdo, independentemente do contexto de quem navega. Navbar contextual acompanha visitante; ações privadas apenas na área do titular. Perfil público não recebe breadcrumb administrativo.
4. **Perfil privado e público separados**: `Meu perfil` do próprio usuário deve abrir área privada com configurações; `Ver perfil público` de terceiros mostra identidade selecionada, sem `Meu cadastro` nem `Alterar preferências`. Em tarefas recentes foi relatado possível bug em `Meu perfil` de voluntário/organizador; **verificar estado atual, não marcar como corrigido sem execução**.
5. `resources/views/perfil/area-privada.blade.php` é um **caminho de view, não URL**. Rota demonstrativa reportada: `/perfil?area=privada&contexto=pessoa` (confirmar em `routes/demo.php`). Não inventar `/perfil/area-privada` como rota.
6. Promoção/estado de organizador localizados na área privada Pessoa, não como atalho de `Organizar atividades` na navbar pública. Workspace operacional é distinto de candidatura pessoal. Organizador aprovado deixa de ser voluntário para novas candidaturas (RN007).
7. Perfil público de ONG aberto a partir de Evento pode oferecer botão secundário **`Retornar ao evento`** para Pessoa/visitante com origem válida, destino por ID e contexto preservado, sem `history.back()` como fonte única e sem open redirect.
8. Botões apresentados visualmente como ações usam obrigatoriamente componentes existentes; links informativos e `Esqueceu a senha?` podem permanecer links textuais bem estilizados. Componentes: `botao-primario`, `botao-secundario`, `botao-dourado`, `botao-declined`; sem CSS improvisado.
9. Sidebar ONG preserva identidade original, item `Explorar eventos` integrado à lista, destaque ativo centralizado para todas as categorias. `Organizadores` é item do Bloco 3. Sem links soltos fora da sidebar.
10. Não exibir banner/caixa global enorme de `Aprovação pendente` ou situação acima do breadcrumb; mensagens contextualizadas ao Evento/formulário. Não eliminar estados de aprovação necessários.
11. Modais com largura apropriada (medidas históricas de referência: revisão 512px; compacto 448px; conta 480px em desktop), acessibilidade de foco/Escape/scroll; não usar dois controles `Fechar` e `Voltar` redundantes. **Última correção solicitada sobre essa redundância ainda requer conferência de execução.** Se as ações diferirem semanticamente, preservar ambas.
12. Cards de Eventos individuais e claramente identificados, com situação (`Publicado`, etc.), escopo de atuação e responsabilidade em destaque; ações agrupadas lado a lado e responsivas.
13. Convites de ONG exibidos separadamente em card de formulário, pendências, vínculos ativos, histórico. UI permite informar e-mail ou login/username sintético; backend atual reportado recebe somente `pessoa_id`. Resolução segura de identidade e persistência real são **pendências backend**, não fingir que existem. O login privado `login_pessoa` tem limite relatado 64; não afirmar que é username público.
14. Nenhuma alteração visual deve produzir textos visíveis como `mock`, `demo`, `API`, `BFF`, `Admin Master`, `entrar na demonstração` ou explicações técnicas supérfluas. Mocks continuam explicitamente documentados no código e relatórios.
15. Quando ONG explora Eventos, ações de candidatura de Pessoa devem ficar desabilitadas para ela; diferenciar organizar/gerir de candidatar-se.
16. Dados em cards de candidatos podem incluir XP e participações concluídas e destacar correspondências de habilidade, **sem percentual de match** e sem expor carta ou dados privados publicamente.

## 4. Regras de negócio V1 — resumo de RN001 a RN034

As regras abaixo são especificação funcional; **não presuma que o frontend demonstra, nem que o backend integra, todas integralmente**.

- **RN001 Cadastro Pessoa:** somente maiores de 18 anos; CPF, e-mail, nascimento e duplicidade validados; criar conta não equivale a aprovação de voluntário.
- **RN002 Validação Pessoa:** `aguardando_validacao`, `em_analise`, `correcao_solicitada`, `aprovado`, `reprovado`, `reprovado_definitivamente`; Master exclusivo para reprovação definitiva; análise assumida com expiração após 24h; três reprovações/30 dias bloqueiam reenvio por sete dias; pendente não se candidata nem aparece publicamente como aprovado.
- **RN003 Documentos:** identidade/RG e certidão de antecedentes obrigatórios, CNH quando atividade exigir; guardar binário em armazenamento privado; expurgo físico após 30 dias da validação e retenção mínima auditável; renovação de antecedentes a cada 12 meses sem revogação automática; rotina destrutiva ainda dry-run até homologação.
- **RN004 Antecedentes/padrinho:** antecedente não reprova automaticamente; caso aplicável, exigir padrinho elegível, aceite e avaliação humana.
- **RN005 Padrinho:** aprovado há ≥60 dias, ≥10 presenças, média ≥4,5/5, sem ausência injustificada em 90 dias, até 1 desistência tardia em 90 dias, ≤2 apadrinhados ativos; encerramento após ≥6 meses e ≥5 participações do apadrinhado, sujeito a análise humana. Quem possui apadrinhados ativos não solicita promoção a organizador.
- **RN006 Administração:** role `administrador` + permissões explícitas; Master possui todos poderes. Permissões incluem `usuarios.validar`, `ongs.validar`, `eventos.aprovar`, `usuarios.moderar`, `lgpd.visualizar`, `lgpd.responder`, `admin.logs.visualizar`, `organizadores.validar`, `xp.campanhas.gerenciar`; não confiar em flags visuais como autorização.
- **RN007 Promoção:** Pessoa aprovada com ≥5 participações, confiabilidade ≥80, média ≥4,0, sem bloqueios/apadrinhados ativos; justificativa obrigatória; um pedido em aberto; após reprovação aguardar 30 dias; revisão humana; aprovação preserva histórico e retira elegibilidade de candidatar-se como voluntário.
- **RN008 Vínculos:** **apenas uma ONG ativa por organizador por vez**; ONG convida, organizador aceita/recusa; convite pendente mantém independência. Vínculo ativo impede criar Evento independente; responsável opera só Eventos designados; encerrar vínculo se não responsável por Evento ativo; não transferir responsável de Evento iniciado.
- **RN009 ONG:** entidade autenticável própria, pendente/reprovada não opera recursos protegidos; propriedade institucional mantida; validação auditada.
- **RN010 Evento:** pertence exatamente à ONG **ou** ao organizador independente. Independente é dono/responsável; ONG pode ser responsável institucional sem organizador designado; se designado, ONG continua proprietária, organizador é responsável operacional. Troca responsável só antes do início.
- **RN011 Aprovação Evento:** novo Evento requer aprovação administrativa antes de exibição pública/candidaturas, inclusive criado por ONG ou independente; criar ≠ publicar.
- **RN012 Edição:** campos sensíveis (descrição, tempo, local, vagas/Agendas, responsável, causas/habilidades/recursos) exigem reanálise e suspendem novas candidaturas; título/banner não; toda edição notifica inscritos; mudanças de data/hora/local garantem desistência sem penalidade. **Reconciliar RN012 e decisão posterior de vagas_evento apenas informativas; não mudar regra unilateralmente.**
- **RN013 Agendas:** múltiplas por Evento, cada uma com suas próprias vagas; duração declarada ≥30min; participações por Agenda, vazia pode finalizar; finalizada/cancelada são terminais.
- **RN014 Candidatura:** só Pessoa aprovada e ainda voluntária, por Agenda até 1h antes do início, sem espera; lotação conforme aprovados por Agenda; habilidades não eliminatórias; carta opcional por decisão posterior.
- **RN015 Seleção:** aprovação humana; respeitar vagas da Agenda com proteção transacional contra concorrência.
- **RN016 Match:** habilidades, causas e recursos, contagens/chips informativos, sem porcentagem, classificação automática ou bloqueio; confiabilidade separada.
- **RN017 Histórico:** não apagar inscrição para modelar desistência/ausência; preservar estados e trilha auditável.
- **RN018 Desistência:** ≥24h normal, <24h tardia; justificativa humana pode reverter penalidade sem apagar histórico; mudança unilateral relevante garante saída sem penalidade até início da Agenda.
- **RN019 Presença:** por Agenda; ausência injustificada afeta confiabilidade; XP só por presença confirmada e Agenda concluída.
- **RN020 Monitoria:** estudante declarado; validação por Agenda para certificado estudantil; não inventar credenciamento obrigatório de monitor físico.
- **RN021 Avaliação de voluntário:** responsável avalia 1–5, obrigatória para presentes, **uma por Pessoa + Evento** mesmo com várias Agendas.
- **RN022 Avaliação de ONG:** voluntário avalia opcionalmente 1–5, uma por Evento; não condiciona XP/certificado.
- **RN023 Conclusão Agenda:** aprovados resolvidos como presente/ausente; agenda vazia finaliza; monitoria pertinente para certificado; presença por Agenda.
- **RN024 Conclusão Evento:** todas Agendas terminais e presentes avaliados; Admin não finaliza em nome do responsável.
- **RN025 XP:** base100 por Agenda com presença confirmada e Agenda encerrada; 30min x0,5; 1h x1; acima disso +0,1/hora proporcional (1h30 x1,05); XP=100×multiplicador duração×multiplicador temático; transações idempotentes e histórico não recalculado.
- **RN026 Campanhas:** bônus temáticos multiplicativos com teto conjunto x3; duração fora do teto; configuração auditada.
- **RN027 Confiabilidade:** visível a partir de 3 participações, base100 faixa0–100; injustificada −20, tardia injustificada −10, normal sem justificativa −5, participação +3; justificativa deferida reverte penalidade conservando registro. Métrica separada de ranking/match.
- **RN028 Ranking:** ordenado somente por XP; aprovados; pode ter ranking por cidade. XP anterior à promoção permanece.
- **RN029 Certificado:** um por Pessoa+Evento; Evento concluído, participação confirmada, soma de durações declaradas das Agendas válidas; monitoria estudantil confirmada; código verificável, PDF sob demanda.
- **RN030 Cancelamento:** por responsável/ONG autorizados, motivo e notificação; sem penalidade ao voluntário; Agenda cancelada não gera XP/carga; Evento parcialmente cancelado pode finalizar; ocorrência histórica, sem reputação automática do organizador V1.
- **RN031 Notificações:** edições, cancelamentos, desistência sem penalidade, pendências, validação, padrinhos, vencimento de antecedentes, convites/vínculos, decisões administrativas.
- **RN032 LGPD:** minimização e autorização; documentos e antecedentes privados; expurgo binário após 30 dias sob controle; anonimização de históricos preservados; não criar URLs públicas duráveis.
- **RN033 Exclusão:** reversível por 7 dias, bloquear enquanto houver pendências ativas; manter auditoria/histórico com anonimização; rotina irreversível ainda desabilitada/dry-run até autorização explícita.
- **RN034 Auditoria:** logs de decisões e mudanças relevantes com agente/antes-depois/motivo/data; integridade, idempotência, proteção transacional, evita cascatas destrutivas.

## 5. Implementação frontend — blocos, correções e status histórico

**Modelo de trabalho:** um chat Codex por correção/tarefa; ler este arquivo e relatórios relevantes em cada novo chat. **Cada tarefa termina em auditoria e homologação manual.** Não presumir que testes automatizados validam estética.

| Marco | Entrega reportada | Validação reportada | Observações |
|---|---|---|---|
| Auditoria inicial 21/10 | 34 RN, rotas, views, lacunas, risco de senha em sessão | 29 verificações de contas e outras auditorias | Encontrado P0 de senha mock; corrigido posteriormente. |
| B0/B1 21–22/10 | Tentativa inicial ativou BFF e suprimiu mocks | Corrigido em nova execução | **NÃO repetir esse erro:** preservar frontend visual demonstrativo. |
| Visual 1 22/10 | Cadastros em etapas, login, docs, preferências, perfis e áreas privadas | 19 testes/772 asserts | Perfil público original preservado/restituído. |
| Visual 2 23–24/10 | Feed, Eventos/Agendas, candidatura, seleção, cards e navegação | 28 testes/1.499 asserts | Rota pública vs privada, navbar contextual. |
| Usabilidade 1–2 24/10 | Candidatura 4 etapas/carta opcional, exclusão, CEP, vagas por Agenda, modal, candidatos | 33 testes/1.576 asserts | Divergência RN012 e backend carta pendentes. |
| Navegação/stepper 24/10 | Stepper superior restaurado, botões padronizados, cards expansíveis | 40 testes/1.726 asserts | Capturas parciais. |
| Perfil ONG/sidebar 24/10 | Perfil ONG público separado, sidebar ativa, Explorar eventos, remove caixa superior | 44 testes/1.983 asserts | Limite de screenshots. |
| Retornar ao Evento 24/10 | Botão secundário contextual no perfil ONG | 46 testes/2.078 asserts reportados | Usuário confirmou funcionamento manual. |
| Visual 3 24/10 | Promoção, organizador independente/vinculado, convites/vínculos, ONG organiza | 45 Feature/2.222 asserts | 25 cenários sintéticos; sem integração. |
| Visual 3.1 25/10 | Modais, Evento por card, convite por email/alias local, seção promoção privada, perfil público Pessoa | 51 Feature/2.757 asserts; 6 JS; 54 medições | Fotos falharam; DOM não substitui inspeção fotográfica. |
| **Correção 3.2 solicitada 28/10** | `Meu perfil` privado, remover `Minhas candidaturas` de configurações organizacionais, eliminar `Fechar`+`Voltar` redundantes | **Sem conclusão comprovada neste resumo** | Prompt gerado; **não presumir aplicado**. |

**Bloco 4 — Administração:** ainda não confirmado implementado. Abrange validação Pessoa/ONG, análise documental/padrinho, aprovação/reaprovação de Eventos, solicitações de organizador, permissões, logs e decisões humanas.

**Bloco 5 — Participação/gamificação:** ainda não confirmado; presença/ausência, monitoria, avaliações, XP/confiabilidade, ranking, campanhas e certificado.

**Bloco 6 — Notificações, LGPD e acabamento:** ainda não confirmado; notificações, fluxos de privacidade, exclusão reversível, acessibilidade, erros e consistência final.

**Homologação visual final e integração:** pendentes. Implementação demonstrativa ≠ integração do time ≠ deploy/homologação real.

### Rotas e componentes historicamente afetados

- Promoção: `resources/views/organizadores/solicitacao.blade.php`; área privada `resources/views/perfil/area-privada.blade.php`.
- Organizador: `resources/views/organizadores/workspace.blade.php`; layout `resources/views/layouts/organizer.blade.php`.
- ONG organizadores: `resources/views/organizadores/ong.blade.php`, `resources/views/components/organizer-links.blade.php`, `organizer-link-card`, `organizer-event-card`, `organizer-promotion-summary`, `organizer-responsibilities`, `organizer-scope`.
- Públicos: `resources/views/perfil/index-perfil.blade.php`, `resources/views/ong/perfil_ong.blade.php`; **perfis públicos não possuem ferramentas privadas**.
- Candidatura: `resources/views/candidatura.blade.php`, `resources/views/minhas-candidaturas.blade.php`, componentes `application-steps`, `agenda-applications`, `agenda-candidates`, `agenda-editor`, `agenda-options`.
- Eventos/ONG: `resources/views/feed/index.blade.php`, `resources/views/eventos/detalhe.blade.php`, `resources/views/ong/{painel_ong,meus-eventos,criar,editar,candidatos,area-cadastral,editar_ong}.blade.php`.
- Navbar: `resources/views/components/{context-navbar,navbar-feed,navbar-ong,breadcrumb-ong}.blade.php`, `app/Support/NavigationContext.php`.
- JS: `resources/js/{events-demo,account-demo,organizer-demo,dialog-accessibility}.js`; CSS `resources/css/components/{events,account}.css`; imports em `resources/js/app.js`.
- **Rotas URL são as de `route:list` / `routes/demo.php`, NUNCA deduzir diretamente do caminho de view.**

## 6. Pendências técnicas conhecidas — não resolver sem pedido explícito

1. **Carta opcional**: backend atual não foi reportado com coluna/validação/Resource na inscrição. Futuro contrato deve limitar tamanho, privacidade e autorização; não torná-la obrigatória nem autoaprovar.
2. **Convite por email/login**: backend recebe `pessoa_id` e não tem lookup público seguro de organizadores; definir resolução autorizada, prevenção de enumeração e histórico/pendências antes de integrar.
3. **Leitura privada de Eventos** para organizadores e ONGs, diretório seguro de organizadores, elegibilidade consolidada, minimização de relações e validação de ONG ativa na atribuição foram registradas como lacunas no Bloco 3.
4. **RN012 versus vagas_evento**: precisa reconciliação na documentação/serviços sem desfazer controle exclusivo por Agenda.
5. **Padrinhos, revisão administrativa, justificativas e monitoria**: verificar cobertura real endpoint a endpoint, não inferir do frontend.
6. **Certificados**: há migration histórica de upgrade de esquema legado; índices de unicidade por Pessoa+Evento e geração on-demand foram reportados. Conferir aplicado no DB antes de mexer; evitar migração irreversível.
7. **Vínculos/promotorias**: migrations nem sempre criam índices únicos para pedidos abertos/convites pendentes; Services podem impedir duplicidade; verificar concorrência e integridade.
8. **Presença/monitoria** duplicam campos de horas/presença; definir fonte autoritativa nos Services antes de alterar cálculos.
9. **Autenticação**: `users`, `pessoa`, `ong` coexistem; verificar uso Sanctum/guards; não unificar por conta própria.
10. **Railway storage**: bucket `ajudae-storage` aparecia associado visualmente ao serviço, porém upload/download/exclusão real não foram homologados. S3 refs podem estar configuradas, não comprova funcionalidade.
11. **Railway deploy:** em consulta de 28/10/2026, aplicação e MySQL online e deploy ativo `SUCCESS` do **20/10** no commit `1c028b3`; os blocos visuais posteriores permaneciam locais sem publicação. Verificar novamente antes de qualquer ação.
12. **LGPD:** jobs de expurgo físico/exclusão irreversível não devem ser ativados apenas por teste ou design; revisar autorização, retenção e backup.
13. **Front 3.2:** bug reportado em `Meu perfil` e candidaturas indevidas nas configurações do organizador; redundância `Fechar`/`Voltar`; verificar correção antes de seguir.

## 7. Infraestrutura Railway e segurança

- Plataforma: Railway; app `ajudae-tcc`, banco `MySQL`, bucket `ajudae-storage`; environment `production`, URL pública histórica `https://ajudae-tcc-production.up.railway.app`.
- Runtime: FrankenPHP/Railpack, PHP 8.4.26, GD/EXIF, Vite; php local reportado 8.5.5. HTTPS/proxy e mixed content foram problemas históricos; após deploy de 20/10 houve correções de código e testes HTTPS, mas **revalidar produção em qualquer retomada**.
- Bucket S3: variáveis típicas `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET`, `AWS_ENDPOINT`, `AWS_USE_PATH_STYLE_ENDPOINT`; chaves nunca devem ser copiadas para prompts/ZIPs. Para discos, confirmar `config/filesystems.php`.
- Sem ações Railway por padrão: nem alterações de variáveis nem logins administrativos, nem upload de arquivos privados, nem testar seed/migrate em produção.
- Usuário prefere contas de teste sintéticas via API quando começar integração, não seeders populando produção. Confirmar autorização e ambiente antes.
- **Pushing na branch conectada ao Railway pode disparar deploy automático.** Não fazer push incidental nem dar como inofensivo.

## 8. Qualidade de UI/UX obrigatória

- Manter a linguagem visual original da equipe; padronizar com botões/componentes existentes, tipografia, cards, navbar, sidebar, breadcrumbs e cores originais.
- Feedback de formulários perto dos campos; CEP apenas formato numérico conforme validação do fluxo; capacidade de Agenda não excede vagas da própria Agenda, **sem limite global Evento**.
- Candidatura visual com **4 etapas** e stepper gráfico superior: Agenda → carta opcional → revisão → confirmação; botões finais `Ver minhas candidaturas` e `Escolher outra Agenda` via componentes.
- Quando um usuário desiste, usar modal de largura adequada, sem punir automaticamente sem contrato. Candidatura de ONG desabilitada.
- Gestão candidatos por Evento e Agenda em accordions expansíveis, habilidades compatíveis destacadas, XP/participações somente quando visíveis/autorizados, carta privada.
- Eventos e convites exibidos em cards individuais, status e escopo destacados; botões alinhados e responsivos.
- Perfil público Pessoa/ONG: identidades corretas, sem ferramentas privadas, sem breadcrumb administrativo; navbar do contexto visitante e retorno validado à origem quando pertinente.
- Verificar 360/768/1280px, transbordamento, botões, teclado, foco, Escape, backdrop, aria, rolagem de diálogo. Não aplicar `width: 100%` em desktop para diálogos compactos.
- Revisar semântica de links vs botões: navegação pode ser `<a>` estilizado por componente, ação local `<button>`; link textual `Esqueceu a senha?` permitido.

## 9. Processo de trabalho: uma conversa Codex por correção

**O novo prompt deve começar instruindo:** `Leia AGENTS.md integralmente, confira o estado real do repositório e os relatórios relevantes antes de executar a tarefa.`

Uma sessão executa **somente** a tarefa delimitada. Fluxo recomendado:

1. **Diagnóstico:** reproduzir bug em rota/cenário correto; mapear origem, dependências e contratos; registrar se não reproduzível.
2. **Plano mínimo:** indicar arquivos alvo, possíveis componentes compartilhados, risco de regressão e critérios de aceite.
3. **Implementação incremental:** preservar worktree e design, reaproveitar componentes; mudanças pequenas, sem reforma geral.
4. **Verificação automatizada:** testes Feature/JS pertinentes e regressões, `npm run build`, Pint, `php -l`, `node --check`, Blade/rotas, `git diff --check`; comparar contratos API e arquivos backend protegidos. Suíte backend integral somente em ambiente seguro e autorizada.
5. **Teste navegador:** percursos concretos, dados sintéticos, contextos Pessoa/ONG/visitante/organizador, telas 360/768/1280, interação e estado; capturas verdadeiras quando disponível, senão DOM/medidas com limitação expressa.
6. **Documentação:** relatório em `docs/`, mapa de navegação/integracao se pertinente, lista de páginas/URLs efetivas e cenários para teste manual.
7. **Pacote:** ZIP por execução em `%USERPROFILE%\Desktop\Zips`, delta relativo do projeto, manifesto com criado/modificado, SHA-256 de arquivos e ZIP, validação independente de conteúdo/CRC, excluir segredos/dependências/build/cache/.git; não sobrescrever pacote anterior.
8. **Resposta:** resumo, arquivos e rotas, testes realmente executados, riscos e próximo teste manual. Sempre registrar que **não houve commit/push/deploy** se for verdadeiro.

### Protocolo de atualização deste arquivo

- `AGENTS.md` é **documentação viva**, não ordem para blindar versões antigas. Ao finalizar um bloco, **propor atualização pontual** de `AGENTS.md` com status, decisões novas, rotas verificadas, pendências alteradas e referências aos relatórios.
- **Não editar `AGENTS.md` automaticamente em cada tarefa** sem instrução explícita, salvo se o prompt específico permitir; não substituir regras aprovadas por inferências.
- Quando uma informação ficar comprovadamente desatualizada, indicar a correção com evidência e fonte/arquivo, sem apagar histórico relevante.
- Novas decisões do usuário têm precedência; registrar data, decisão e efeito sobre RN/contrato.
- Resumos de testes acumulados são históricos e **não podem ser tratados como resultados do código atual** sem nova execução.

## 10. Arquivos de documentação histórica prioritários

Localizar os nomes concretos no repositório (alguns podem variar de data ou não existir neste checkout):

- `docs/guia-integracao-api.md`; `docs/railway-storage-s3.md`.
- `docs/bloco-visual-3-relatorio-2026-10-24.md`, `docs/bloco-visual-3-integracao-2026-10-24.md`, `docs/bloco-visual-3-matriz-rn-2026-10-24.md`.
- `docs/bloco-visual-3-1-relatorio-2026-10-25.md`, `docs/bloco-visual-3-1-contratos-2026-10-25.md`, `docs/bloco-visual-3-1-validacao-2026-10-25.json`.
- `docs/perfil-ong-sidebar-relatorio-2026-10-24.md`, `docs/perfil-ong-sidebar-mapa-2026-10-24.md`, `docs/retornar-evento-perfil-ong-2026-10-24.md`.
- `docs/ajustes-visuais-navegacao-relatorio-2026-10-24.md`, `docs/ajustes-visuais-navegacao-botoes-2026-10-24.md`.
- `docs/usabilidade-blocos-1-2-relatorio-2026-10-24.md`, `docs/usabilidade-blocos-1-2-revisao-v1-2026-10-24.md`, `docs/usabilidade-blocos-1-2-contratos-2026-10-24.md`, `docs/usabilidade-blocos-1-2-integracao-2026-10-24.md`.
- Demais auditorias `docs/auditoria-frontend-v1-*`, documentos `visual-2-*` e evidências `docs/evidencias/**`.
- PDF de regras do negócio V1, migrations e diagrama ER apenas para apoio; contratos reais provêm de código atual e banco efetivo.

## 11. Modelo mínimo de resposta Codex

```markdown
# Resultado — <tarefa>
**Escopo executado:** ...
**Causa reproduzida:** ...
**Arquivos criados/alterados:** ...
**Rotas reais para testar:** ...
**Mudanças visuais e fluxos:** ...
**Testes executados:** comandos, totais, falhas, ignorados
**Testes não executados e limitações:** ...
**Preservação:** trabalho preexistente, backend, RN, segredos; sem commit/push/deploy
**Lacunas de integração real:** ...
**Documentação:** ...
**ZIP e SHA-256:** ...
## Decisões para você responder
Nenhuma / somente decisões realmente bloqueantes.
```

**Lembrete final:** entregar algo visualmente atraente não autoriza afirmar que API, banco, permissões, LGPD, upload ou Railway estejam homologados. **Preservar a identidade visual, as regras, a segurança e o trabalho dos colegas é tão importante quanto implementar a tarefa.**
