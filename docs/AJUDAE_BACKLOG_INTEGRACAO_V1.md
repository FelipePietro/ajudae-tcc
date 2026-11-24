# Ajudaê — Registro central de decisões e pendências de integração V1

**Atualização:** 23/11/2026, Bloco 8.1. D01–D05 aprovadas pelo usuário. Base de 16/11/2026 preservada como referência histórica.
**Finalidade:** fonte de controle para integração do frontend demonstrativo com API, MySQL, autenticação real e regras RN001–RN034.  
**Estado:** documento de trabalho; **não equivale a autorização para alterar backend, migrations ou produção**.  
**Escopo auditado:** relatos e documentos dos Blocos Visuais 1–5.3, com especial atenção a 4.2/4.2.1 e 5.1–5.3.  
**Uso:** acompanhar decisões e pendências de integração. O estado de cada implementação é verificado nos relatórios do Bloco 8.1.

## Convenções e prioridades

- **APROVADO (produto):** comportamento deliberadamente decidido e homologado visualmente; ainda pode faltar persistência real.
- **DECISÃO ABERTA:** não substituir por suposição; obter decisão explícita e documentar a RN/contrato alterado.
- **PENDENTE TÉCNICO:** implementação, teste ou recurso necessário; não considerar resolvido porque funciona com `sessionStorage`.
- **VERIFICAR:** descrição encontrada no checkout; confirmar no código atual, migrations aplicadas e MySQL antes de mudar.
- **P0:** bloqueia integração segura/correta; **P1:** necessário à V1 completa; **P2:** qualidade, operação ou evolução.

## A. Decisões já tomadas — preservar

| ID | Prioridade | Definição | Estado de integração |
|---|---|---|---|
| A01 | P0 | Backend será fonte de verdade. Os parâmetros `contexto`, `jornada`, `cenario`, `exemplo` e `sessionStorage` servem somente à demonstração; não são autenticação/autorizações. | PENDENTE TÉCNICO |
| A02 | P0 | Capacidade efetiva de candidaturas é **por Agenda**. `vagas_evento` é informativo; não exigir soma de Agendas igual ao total do Evento. A RN012 distingue a capacidade operacional da Agenda do total informativo. | ALINHAMENTO DOCUMENTAL APLICADO |
| A03 | P0 | Carta de apresentação na candidatura é **opcional**, privada ao titular e ao responsável autorizado, sem seleção automática. | CAMPO PRIVADO E FLUXO DE CANDIDATURA ENTREGUES |
| A04 | P0 | Apadrinhamento é por **código/link de indicação**, sem confirmação por e-mail. Código opcional no cadastro; pedido pendente; padrinho aceita/recusa; aceite não aprova automaticamente a Pessoa. | PENDENTE TÉCNICO |
| A05 | P0 | Se moderador fundamentar exigência de padrinho, cadastro não pode ser aprovado até existir aceite/vínculo elegível. Análise de documentos pode prosseguir; antecedentes **não produzem reprovação automática**. | PENDENTE TÉCNICO |
| A06 | P0 | Solicitante acompanha pedido pendente/aceito/recusado, histórico e situação cadastral **separada**. Troca de código só em estados e correção permitidos; recusa preservada. | PENDENTE TÉCNICO |
| A07 | P0 | Presença e ausência são registradas **por inscrição/Agenda**, não pelo Evento inteiro. Justificativa aceita **não apaga** a ausência nem a desistência no histórico. | PENDENTE TÉCNICO |
| A08 | P0 | Monitoria é processo de validação distinto de presença. Não se deve conceder monitoria apenas por ter presença confirmada. | PENDENTE TÉCNICO |
| A09 | P0 | Finalizar Agenda exige horário final alcançado, autorização e comparecimento decidido para todas as inscrições ativas; Agenda **sem inscritos** pode encerrar. Canceladas e finalizadas são terminais. | PENDENTE TÉCNICO |
| A10 | P0 | Finalizar Evento exige Agendas terminais e avaliação válida **uma por Pessoa presente e Evento**, independentemente do número de Agendas. Avaliação da ONG feita pela Pessoa é opcional, não bloqueia encerramento. | PENDENTE TÉCNICO |
| A11 | P0 | Avaliações têm nota inteira de 1 a 5; sem múltiplos envios/alterações indevidas; avaliações individuais e comprovantes privados não aparecem em áreas públicas. | PENDENTE TÉCNICO |
| A12 | P0 | XP somente após presença confirmada em Agenda finalizada, com histórico e idempotência. Ranking por XP, **não** por confiabilidade. Certificado consolidado por Pessoa+Evento com elegibilidade e carga horária corretas. | PENDENTE PARA BLOCOS 5.4–5.6 E INTEGRAÇÃO |
| A13 | P0 | Perfis públicos não expõem dados privados nem ferramentas do titular; navbar e logomarca dependem da identidade/autorização real, não apenas de query string. | PENDENTE TÉCNICO |
| A14 | P0 | ONG titular e organizador só gerenciam Eventos em seu escopo; administrador não herda por padrão permissão de responsável pela presença/avaliação. | PENDENTE TÉCNICO |
| A15 | P0 | Aprovações administrativas são decisões humanas auditáveis; não publicar Evento automaticamente; ocorrências ficaram consultivas onde contratos não autorizam sanção; LGPD distingue consultar de responder. | PENDENTE TÉCNICO |

## B. Decisões de regras de negócio

| ID | Prioridade | Pergunta e contexto | Padrão demonstrativo atual | Critério de fechamento |
|---|---|---|---|---|
| D01 | P0 | **Aprovada em 23/11/2026.** Pessoa com participação elegível pode avaliar a ONG uma vez por Evento, com nota inteira de 1 a 5, até sete dias corridos do instante registrado na finalização, inclusive. | Consulta de avaliação própria segue após o prazo. | Validar limites exatos, alteração de horário, titularidade e duplicidade. Avaliação facultativa sempre. |
| D02 | P0 | **Aprovada em 23/11/2026.** Evento sem Agendas não pode finalizar. | Pelo menos uma Agenda, todas terminais e avaliações obrigatórias. | Testar zero Agendas, Agendas abertas e terminais, e concorrência. |
| D03 | P0 | **Aprovada em 23/11/2026.** Monitoria pendente não impede encerrar Agenda com presença resolvida. | Responsável pode regularizar depois, com autoria, data e histórico. | Revalidar monitoria na emissão de certificado estudantil, sem reabrir Agenda nem conceder XP novamente. |
| D04 | P0 | **Aprovada em 23/11/2026.** Em aprovação pendente ou reanálise, bloquear operações incompatíveis com o estado. | Consulta autorizada, moderação e exceções legais, de segurança ou expressas na regra. | Revalidar autorização e prazos depois da decisão. Testar Web e Bearer. |
| D05 | P0 | **Aprovada em 23/11/2026.** ONG proprietária ou organizador designado autorizado pode finalizar. | Em Evento independente, o organizador proprietário autorizado. | Aplicar RN024, vínculo/atribuição vigentes e registrar ator e instante. |
| D06 | P1 | **Revisão/correção excepcional de presença, avaliação ou encerramento:** é permitida? por quem, sob quais fundamentos, e com que impacto em XP/certificado? | Repetições/edições comuns bloqueadas. | Definir processo de revisão auditada, ou formalizar imutabilidade. |
| D07 | P1 | **Critérios de confiança e sanções**: confirmar fórmula, janelas e reversões conforme RN, sem converter ausência justificada em comparecimento. | Parcial em telas; Bloco 5.5 pendente. | Homologar Bloco 5.5 e confrontar serviços, migrations e RN. |
| D08 | P1 | **Níveis, multiplicadores, campanhas, arredondamento, teto, ajustes e estornos de XP** devem reproduzir RN sem números inventados. | Bloco 5.4 ainda pendente. | Homologar 5.4, registrar tabela de casos numéricos, replay e compensações. |
| D09 | P1 | **Carga horária e certificados** em múltiplas Agendas, cancelamentos, ausência e monitoria: política exata de elegibilidade/retificação/verificação pública. | Bloco 5.6 pendente. | Homologar 5.6 e conferir RN + contratos efetivos. |
| D10 | P1 | **Elegibilidade de padrinho** deve usar as métricas e limites da RN005, com revalidação em cadastro/aceite, impedimento de autoindicação e capacidade concorrente. | Interface 4.2 exibe critérios; backend serviço legado usa voluntário aprovado para pedir. | Conferir RN005 no `AGENTS.md`, código e regras atuais, sem divergência frontend/backend. |

## C. Backlog técnico do backend por domínio

### C1 — Identidade, conta, privilégios, privacidade (P0)

- Trocar `sessionStorage` e query strings demonstrativas por `auth()->user()`, Sanctum, policies/gates e recursos privados filtrados; não confiar em `contexto=pessoa|ong|administrador` como autorização.
- Impedir referências cruzadas: pessoa não vê inscrição/documento/nota de outra Pessoa; ONG não gerencia Evento de terceiro; admin sem permissão não acessa documentos ou dados indevidos.
- HTTP 401/403/404/409/422 coerentes; evitar enumeração de contas em recursos de consulta e códigos de indicação.
- Substituir projeções sintéticas de navbar, perfis públicos/privados, convites e responsabilidades por dados autenticados, preservando a separação de informações e a navegação homologada.
- Documentos: armazenamento privado, autorização do moderador, URLs temporárias ou streaming autenticado, expiração, auditoria de visualização, sem arquivos públicos; PNG e PDF com fallback seguro.
- LGPD: confirmar permissões de consulta/resposta, minimização, retenção e registro de decisões; notificações sem dados sensíveis.

### C2 — Cadastro, referral e apadrinhamento (P0)

- Adaptar `POST /api/v1/pessoas/register`: receber código opcional, resolver com validade e elegibilidade, criar Pessoa em validação + pedido pendente na **mesma transação**; preservar cadastro sem código.
- Criar código aleatório único, revogável e não autoritativo; definir tabela/índice/digest e maneira segura de reapresentar o código ao titular. Evitar exposição de ID, dados pessoais e capacidade do padrinho ao resolver link.
- Adaptar `PadrinhoService::solicitar`/`garantirAfilhado` para **origem por referral pré-aprovação**, sem abrir indevidamente o fluxo convencional que hoje exige Pessoa aprovada.
- Reaproveitar solicitações/vínculos, aceitar/recusar/cancelar com locks e revalidação de elegibilidade/capacidade; impedir duplicação, self-referral e duas aceitações pela última vaga.
- Expor acompanhamento privado por solicitante, pedidos recebidos por padrinho, histórico, motivos permitidos, elegibilidade e ações autorizadas. Criar troca autenticada de indicação quando a administração permitir correção; não substituir vínculo ativo silenciosamente.
- Persistir exigência **fundamentada** de padrinho RN004 separadamente do estado cadastral; `PessoaValidationService::aprovar` revalida documentos e vínculo aceito/elegível dentro da transação. Antecedente não reprova automaticamente.
- Reutilizar notificações internas, **sem confirmação por e-mail**. Trilha auditável de emissão/revogação, pedido, aceite, recusa, correção, exigência e aprovação.
- Fontes: `docs/bloco-visual-4-2-plano-backend-referral.md`, `docs/bloco-visual-4-2-1-notas-backend.md`.

### C3 — Inscrições, presenças, justificativas e monitoria (P0)

- Reutilizar `GET /api/v1/agendas/{id}/inscritos`, `PATCH /api/v1/inscricoes/{id}/status`, `GET /api/v1/minhas-inscricoes`, `PATCH /api/v1/inscricoes/{id}/presenca` após conferir contratos atuais.
- Preservar inscrição por Agenda, estados de seleção x participação, data de comparecimento, desistência e justificativa; aplicar guards temporais e status Evento/Agenda, inclusive reanálise conforme D04.
- Construir ou adaptar **envio, leitura e decisão de justificativa** para ausência/desistência; aceitação nunca altera histórico de presença nem transforma ausência em presença. Motivos, permissão e resultado devem ser auditados.
- Criar/confirmar fluxo operacional de **monitoria estudantil** vinculada a inscrição/Agenda, com decisão do responsável, resultado e carga horária; aplicar a decisão D03 ao fechamento e certificado.
- Usar recursos privados distintos para gestor, Pessoa e público; concorrência em registros e respostas ao usuário.
- Fonte: `docs/bloco-visual-5-1-integracao.md`.

### C4 — Encerramento de Agendas e Eventos (P0)

- Reaproveitar `POST /api/v1/agendas/{id}/finalizar` e `POST /api/v1/eventos/{id}/finalizar`; revalidar horas, presenças, estados, escopo e avaliações **dentro de transações**.
- Finalizar Agenda vazia (sem aprovações), com término alcançado; rejeitar ausência/presença pendente, Evento errado ou status incompatível; não duplicar histórico ao repetir/recarregar.
- Finalizar Evento somente se todas as Agendas forem finais/canceladas e houver uma avaliação 1–5 por Pessoa distinta presente. Bloquear tentativas com pendências e informar motivos atuais.
- Resolver D02 (Evento sem Agendas), D04 (reanálise), D05 (ator autorizado), D03 (monitoria pós-fechamento).
- Observar que `AgendaController::finalizar` já chama `XpService`; controlar cálculo e idempotência na integração 5.4, não conceder XP adicional no frontend nem em replays. Controlar outbox e logs após commit.
- Projeções de status e pendências por responsável, por Pessoa e por visitante, sem expor participantes no público.
- Fonte: `docs/bloco-visual-5-2-plano-backend.md`.

### C5 — Avaliações de voluntários e avaliação opcional da ONG (P0)

- Reaproveitar `POST /api/v1/eventos/{id}/avaliacoes/{pessoa}` e `POST /api/v1/eventos/{id}/avaliacao-ong`, com `nota` inteira 1–5; validar titularidade do avaliador e do voluntário; dados da Pessoa avaliando ONG vêm do token, não do corpo.
- Garantir unicidade persistida **por `(evento_id,pessoa_id)`**, mesmo com presença em múltiplas Agendas. Avaliação da ONG separada da avaliação do voluntário.
- Responsável designado registra avaliação; ONG proprietária pode visualizar **situação** quando não for autora, sem acessar nota individual indevida.
- A opção de avaliar ONG é facultativa; comprovante da própria Pessoa persiste e reenvio é bloqueado. Evitar autoavaliação e acesso por terceiros. Aplicar D01: sete dias corridos inclusive após o horário efetivo de finalização.
- Criar/confirmar leituras autorizadas para pendências, avaliações feitas, notas emitidas pelo autor e recibos, com mínimo de exposição. Atualizar estado do Evento a partir do servidor após gravação.
- Transações, unicidade, concorrência e auditoria com nota apenas em contexto privado; notificações sem payload sensível.
- Fonte: `docs/bloco-visual-5-3-plano-backend.md`.

### C6 — XP, gamificação e certificados (P0/P1; aguardam homologação 5.4–5.6)

- Confrontar `XpService` e migrações com RN e próximo Bloco 5.4. Conceder XP apenas para inscrição presente em Agenda finalizada; idempotência por inscrição/Agenda e estabilidade sob repetição/concurrency; evitar concessão dupla ao integrar API existente.
- Nível, histórico, campanhas e multiplicadores com fontes determinísticas, efeitos de cancelamento/correção, teto e arredondamento auditados.
- Confiabilidade separada de XP, com penalidades, isenções e reversões; ranking ordenado por XP, com critério de desempate explicitado nas RN ou decidido.
- Certificado único por Pessoa+Evento, após conclusão elegível, carga horária consolidada apenas em Agendas válidas, respeito à monitoria, emissão idempotente, PDF protegido e verificação pública por código não enumerável.
- **Não marcar fechado antes dos Blocos 5.4–5.6 e dos planos de integração específicos.**

### C7 — Administração, ONGs e organizadores (P0/P1)

- Leituras autorizadas para filas administrativas de ONG, Pessoa, Evento/reanálise, promoções, LGPD e auditoria; contadores derivados de estado real e filtros múltiplos consistentes.
- Verificação de padrinho obrigatória na aprovação da Pessoa quando exigido (C2); documentos separados da decisão cadastral; acesso auditado às prévias privadas.
- Responsabilidade de Evento e vínculo ONG/organizador: resolver convite por e-mail/login de forma autorizada sem expor existência de contas; backend histórico aceita `pessoa_id`, portanto será necessário contrato de resolução/convite consistente.
- Garantir que perfis de terceiros não recebam controles privados, e o responsável só receba capacidades de gestão a que tem direito.
- Histórico de ações administrativas durável, justificativas, e sem enviar dados sensíveis em notificações ou relatórios.

## D. Infraestrutura e qualidade — condições para integração

- **Preservar o worktree local** e revisar `git status`, `AGENTS.md` e relatórios atuais antes de qualquer alteração. Não executar commit/push/deploy sem autorização.
- Conferir migrations e FKs no **MySQL autorizado**, não assumir sucesso porque testes em SQLite falharam/foram filtrados. Casos conhecidos: migration de mídia/avaliações incompatível com SQLite; determinar se é limitação de ambiente de testes ou defeito de migration antes de declarar suíte completa.
- Garantir migrations incrementais, rollback/migração de dados legados, índices únicos e testes de concorrência reais (cadastro+pedido; aceitar dois pedidos; fechamento+presença; avaliação+fechamento; finalizar+XP; emitir certificado duas vezes).
- Auditar endpoints com Sanctum e políticas para cada persona; testar 401, 403, 404, 409, 422 e respostas com dados mínimos.
- Testes por camadas: Unit/Feature API em MySQL, JS, contratos UI/API, E2E browser autenticado (sem dependência de query string), 360/768/1280, segurança e privacidade.
- Uploads privados em storage (eventual S3/Railway): streaming/autorização, signed URLs curtas, MIME, imagens e PDF, erro/fallback, logs; confirmar em ambiente de teste antes de produção.
- Implementar trilha de auditoria transacional/outbox e notificações idempotentes; não enviar e-mail de confirmação do padrinho.
- Separar funcionamento demonstrativo (homologado visualmente) de funcionamento integrado real (a provar).

## E. Sequência de execução e critérios de liberação

1. Fechar e homologar módulos visuais 5.4 (XP), 5.5 (confiabilidade/ranking), 5.6 (certificados) e 6 (notificações/privacidade); complementar este documento com contratos e decisões que surgirem.
2. Aplicar D01–D05 e registrar decisões adicionais somente para os aspectos excepcionais ainda abertos em D06–D10.
3. Auditar APIs, bancos, migrations, políticas e lacunas da C1–C7 contra checkout atual; produzir matriz endpoint ↔ RN ↔ tela ↔ teste e plano de migração incremental.
4. Implementar por fatias verticais: autenticação/projeções; cadastros/referral; Pessoa/admin; candidaturas/presença; monitoria/justificativas; avaliações/fechamento; XP/ranking/certificados; notificações/LGPD.
5. Para cada fatia: API + tests MySQL + UI + E2E com 2+ perfis + segurança, concorrência, privacidade + nova homologação manual. Nunca declarar integrada só porque funcionou em uma aba.
6. Antes de deploy, revisar pendências P0 fechadas, riscos residuais, checklist de migração/rollback e autorização explícita.

## F. Registro de acompanhamento

| ID | Status em 16/11/2026 | Dono / decisão | Evidência de fechamento |
|---|---|---|---|
| D01–D05 | APROVADAS (produto) | F2–F5 | Regra, contrato e testes no Bloco 8.1. |
| D06–D10 | Aplicar regras V1 já definidas. Questões excepcionais seguem abertas. | Equipe de projeto | Ver matriz de decisões do Bloco 8.1. |
| C1–C7 | PENDENTE TÉCNICO | Equipe de integração | PR/commit autorizado + teste real + homologação |
| Blocos 1–4.2.1 | TELAS HOMOLOGADAS conforme histórico | Equipe visual | Checklists e ZIPs individuais |
| Bloco 5.1 | HOMOLOGADO VISUALMENTE | Equipe visual | 17 verificações manuais |
| Bloco 5.2 | HOMOLOGAÇÃO PARCIAL registrada; fluxo de Evento 8 originalmente bloqueado por data futura | Equipe visual | Regressão da jornada de encerramento no 5.3 / verificar última aprovação |
| Bloco 5.3 | HOMOLOGADO MANUALMENTE pelo usuário em 16/11/2026 | Equipe visual | Avaliações obrigatórias e opcionais, segurança, design |
| Blocos 5.4–5.6 e 6 | A IMPLEMENTAR | Equipe visual | Relatórios, ZIP, homologação manual |

## G. Referências documentais no repositório

- `AGENTS.md` (leitura obrigatória e regras RN001–RN034).
- `docs/bloco-visual-4-2-plano-backend-referral.md`.
- `docs/bloco-visual-4-2-1-notas-backend.md`.
- `docs/bloco-visual-5-1-integracao.md`.
- `docs/bloco-visual-5-2-plano-backend.md`.
- `docs/bloco-visual-5-3-plano-backend.md`.
- `docs/bloco-visual-5-1-matriz-regras.md`, `docs/bloco-visual-5-2-matriz-regras.md`, `docs/bloco-visual-5-3-matriz-regras.md`.

**Regra de manutenção:** toda nova decisão, divergência, falha de segurança, lacuna de contrato, pendência descoberta ou homologação de bloco deve atualizar este arquivo e a RN aplicável. Não remover itens por mera conclusão visual; somente registrar conclusão após evidência de integração real.
