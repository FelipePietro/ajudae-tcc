# Consolidação do backend para o Bloco 9

## Base Git local

| Verificação | Resultado |
| --- | --- |
| Referência de entrada | `codex/baseline-ajudae-7-4`, `7bd046a4ad54411f662c1666d1d4d9000bcf963b`, remoto `origin` |
| Árvore de entrada | 98 modificados rastreados, cinco removidos, 5.006 não rastreados e stage vazio |
| Commit-base | `codex/bloco-9-base`, `c1ff91f2d92f26115ade7e7687c27ee7c2467fdd`, `chore(base): consolida blocos 7.5 a 8.2 para integração` |
| Seleção | 192 caminhos, incluindo os catálogos 8.2 e as três dependências sintéticas de `resources/fixtures/admin-documents`. Stage comparado com a lista explícita. |
| Integridade | 159 caminhos únicos dos manifestos 8.1/8.2 conferiram antes do stage. Os manifestos incrementais 7.5 completaram a identificação. Git normalizou CRLF de acordo com `.gitattributes`. |
| Espaços no stage | `git diff --cached --check` sinalizou três quebras explícitas em Markdown e separadores da estrutura do PDF sintético. O restante passou, e os dois arquivos conferiram com a fonte auditada. |
| Preservação | 4.917 caminhos não rastreados permaneceram após o commit. Incluem artefatos de navegador, ZIP de migrations, evidências históricas, versões auxiliares de docs e testes testemunhais antigos. Nenhum foi apagado. |

As cinco remoções selecionadas são `admin/area-indisponivel`, `admin/integrated-entry`, `auth/feature-unavailable`, `auth/institutional-area` e `auth/public-event` em `resources/views`. A busca em aplicações, rotas, views e testes não encontrou usos dos nomes removidos. O [manifesto do commit](BASE_ARQUIVOS.md) lista status e SHA-256 por caminho.

## Decisões incorporadas

| Decisão | Regra e implementação | Evidência funcional |
| --- | --- | --- |
| D01 · RN022 | `AvaliacaoService` exige estado `finalizado`, carimbo `finalizado_em`, presença elegível e nota inteira de 1 a 5. O envio é aceito até o instante de sete dias corridos, inclusive. Projeções Web/API partilham validação e preservam unicidade. | `AvaliacaoConfiabilidadeTest` e `Bloco81WebParticipationTest`. A avaliação facultativa permanece independente do XP e do certificado. |
| D02 · RN010–RN013/RN024 | `EventoManagementService` e `AgendaManagementService` criam Evento/Agendas na mesma transação e defendem a última Agenda. `EventoModerationService` recusa aprovação de órfão. Imagem gerenciada pode compor a transação com compensação. | `Bloco9EventManagementTest`, `EventoOperacionalTest` e `OngAuthAndEventoTest`. O [contrato F2](CONTRATOS_F2_EVENTOS.md) descreve legado, payload e regras de transição. |
| D04/D05 · RN008/RN010/RN021/RN024 | `EventoPolicy`, `AgendaPolicy` e os Services operacionais usam titularidade, designação e vínculo. A ONG proprietária pode operar o próprio registro mesmo se designou organizador. O designado opera exclusivamente o Evento atribuído enquanto o vínculo e a ONG estão ativos. Ele não cria Eventos institucionais nem Agendas. O independente preserva criação e operação próprias quando não vinculado. | `Bloco9WebEventParticipationTest`, `OrganizadorWorkflowTest` e testes de Agenda/Evento. O [contrato F4](CONTRATOS_PARTICIPACAO.md) detalha permissões e estados. |

D02 antecipa o requisito de Agenda previsto no encerramento na decisão anterior. D04/D05 substitui a permissão de criação institucional via organizador vinculado descrita em RN008/RN011 de outubro, e permite avaliação pela ONG proprietária mesmo com responsável designado. O PDF e as decisões históricas foram preservados como referência. O catálogo desta execução registra o contrato efetivo e as divergências.

## Operações reaproveitadas, criadas e ajustadas

- **Já existentes** Cadastro de Pessoa, sessão tipada, mudança de senha, catálogo e leitura pública do feed, candidatura, desistência, fila de Eventos, convites, histórico de participações, ranking, certificados, notificações e protocolos LGPD do titular.
- **Reaproveitadas no Web** Documentos de Pessoa, apadrinhamento, edição de perfis, preferências, referências de catálogos, filas de Pessoa/promoção e parentes, leitura de antecedentes, campanhas, atendimento LGPD, imagem e gestão de candidatos. `UseResolvedWebActor` entrega aos handlers compartilhados a identidade já verificada pela sessão apenas nas rotas Web explicitamente registradas. API preserva Bearer, e guards e tokens permanecem segregados.
- **Criadas** Cadastro institucional multipart, metadados e download privado, fila institucional e auditoria, gestão Web de Eventos e Agendas, operações de participação por sessão, leitura agregada de preferências próprias e consulta reduzida de logs.
- **Ajustadas** Criação de Evento e cadastro ONG no endpoint Bearer correspondente, janela e autoria de avaliações, veto à criação pelo designado, verificação de escopo na gestão, auditoria de campanhas e ordenação de middleware. O expurgo físico permanece dependente de autorização e ambiente de homologação.
- **Bloqueadas ou parciais** Recuperação de senha, homologação S3, expurgo físico institucional e reconciliação de legado. Níveis e badges sem regra aprovada, extrato, retificação e correções excepcionais estão no backlog do produto.

## Arquitetura e entrega

Blade e JavaScript consomem rotas Web de mesma origem com sessão Laravel e CSRF. Rotas `/api/v1` mantêm autenticação Bearer e compartilham regras e autorização aplicáveis. O delta desta execução concentra-se em `app/`, `routes/`, `bootstrap/app.php`, migrations incrementais, testes e esta pasta de documentação. As interfaces preservam o estado da base e passam à equipe para integração. O trabalho permanece local, sem push, merge, deploy, alteração do remoto ou migração no banco principal.

[Catálogo Web](CATALOGO_WEB.md) · [Matriz](MATRIZ_INTEGRACAO.md) · [Prontidão](PRONTIDAO.md) · [QA](QA_INDEPENDENTE.md)

O registro futuro de `AGENTS.md` pode apontar este commit-base e a aprovação das decisões D01, D02 e D04/D05. A atualização deve incluir os caminhos Web verificados e a situação de recuperação, armazenamento, retenção e legado.
