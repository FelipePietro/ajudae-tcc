# Bloco Visual 6.1 — notificações e central de mensagens

Execução iniciada em 17/11 e concluída em 18/11/2026. Branch `feat/ajustes-pre-deploy`, HEAD `1c028b311edcff2869f1d299dbbff5924425b450`. Somente frontend demonstrativo, testes e documentação. Centenas de alterações locais anteriores foram usadas como baseline, sem restauração por HEAD. AGENTS.md integralmente lido e preservado; nenhum AGENTS mais específico encontrado. Anexo do usuário executado apenas no módulo 6.1.

## Resultado

As duas rotas existentes de notificações agora usam uma central compartilhada com busca, tipo, leitura, combinação de filtros, páginas de oito avisos, leitura individual e em lote, contador imediato e estado por aba. A marcação em lote alcança a caixa atual inteira, inclusive itens fora do filtro, sem alterar outra identidade. O clique marca lida antes de navegar. Os botões reaproveitam os componentes existentes. Não foi necessário acrescentar dropdown nem URL paralela.

Navbar Pessoa/organizador, sidebar ONG e sidebar administrativa oferecem entrada contextual. ONG mantém sidebar e breadcrumb; Admin reutiliza o layout administrativo e filtra filas por permissão. Perfis públicos não recebem a caixa nem conteúdo das notificações. Visitante recebe 403 na central. Seletores arbitrários de destinatário são rejeitados. O histórico inicial é explicitamente rotulado `Histórico de exemplo`; ações locais aparecem como `Atividade nesta aba`.

Cada aviso tem identificador de evento local, destinatário sintético, tipo, data, contexto do objeto e estado de leitura. URLs são reconstruídas por catálogo fechado, sem aceitar URL de storage. Eventos não catalogados e destinos fora do escopo operacional ficam indisponíveis. `pessoa:1`, `ong:1`, `organizador:1`, `organizador-vinculado:1` e padrinho principal têm caixas distintas. Isso é isolamento demonstrativo de apresentação, não autenticação ou segurança de servidor.

Há emissão após ações locais válidas em candidatura, seleção, desistência, edição de Evento, convites/vínculos, promoção, decisões cadastrais do caso compartilhado de apadrinhamento, presença/ausência, justificativa, avaliação do titular e certificado. Participação, justificativa e certificado continuam usando seus stores 5.x; não há reconstrução nem reemissão retroativa desses históricos. As notificações não armazenam carta, justificativa textual, nota, código de certificado, documento ou credencial. Há deduplicação por evento/destinatário; recarga não reaplica ação.

## Evidências e verificações

- Feature: **88 testes, 4.008 asserções, todos passaram**; resultado final em [feature-final.txt](evidencias/bloco-visual-6-1/feature-final.txt); SQLite em memória, acesso a banco e HTTP real proibidos nas classes visuais. A suíte integral de backend não foi executada.
- JavaScript: [javascript-final.tap](evidencias/bloco-visual-6-1/javascript-final.tap), 58 testes passaram.
- Edge 157 / Playwright: [navegador.json](evidencias/bloco-visual-6-1/navegador.json), 115 visitas, 24 cliques de ação, 45 verificações e 45 capturas de central/contextos/negativos/destinos nas larguras 360/768/1280; [acoes-cruzadas.json](evidencias/bloco-visual-6-1/acoes-cruzadas.json), 16 visitas, 16 cliques e 8 verificações de candidatura, edição de local, justificativa, convite e padrinho; [regressoes.json](evidencias/bloco-visual-6-1/regressoes.json), 33 visitas e 31 cliques em logos. Consultar o inventário para os totais finais e URLs, sem somar testes históricos como validação atual.
- Sem exceções JavaScript ou chamada à API no percurso principal. Fontes HTTPS externas preexistentes sofreram `ERR_NETWORK_ACCESS_DENIED`; as capturas usam fallback quando necessário. Isso está registrado, sem alteração global das fontes.
- Navegador completo Codex: aplicação local aberta; leitura individual reduziu 3→2 não lidas e conservou foco no título; busca por certificado e limpeza de filtros; revisão visual Pessoa, ONG e Admin. `getScreenshot()` falhou; `tab.screenshot()` funcionou. O override de viewport do navegador completo não ficou confiável: numa solicitação de 768 px o DOM informou 1024 px. Por isso as evidências dimensionais são do Edge/Playwright, com larguras explícitas. A moldura administrativa final foi conferida por captura Playwright após a revisão interativa. Não se afirma teste com leitor de tela real.
- Capturas reais representativas inspecionadas: central 360, ONG desktop, Admin revisão 1280 e filtro vazio no navegador completo. Não se afirma inspeção individual de todas as capturas. Tab/Shift+Tab e Escape, foco após marcação, recarga, ida/volta, negativos HTTP, armazenamento inválido e emissão única foram exercitados nos recortes descritos nos scripts.
- Build Vite, Pint somente nos PHP do escopo, lint PHP/JS, cache Blade, rotas e whitespace: [build](evidencias/bloco-visual-6-1/build.txt), [Pint](evidencias/bloco-visual-6-1/pint-final.txt), [verificações](evidencias/bloco-visual-6-1/verificacoes.txt). A rota prévia e a atual passaram no Pint após importar a fixture nova, sem reformatar o projeto inteiro.

## Falhas encontradas e corrigidas

1. Link de pendências do organizador vinculado apontava para o Evento 7 de outro responsável: a fixture usa seu Evento 1 e o resolvedor rejeita destino operacional alheio. Regressão JS adicionada e todos os destinos permitidos repetidos no navegador.
2. Comparar strings ISO e datetime-local classificava mudança de formato como mudança de horário: a comparação passou a usar instantes; local compara os campos pertinentes. Capacidade não cria isenção por si só. Formulário sem alteração não emite aviso de edição.
3. O destinatário de uma decisão gerencial não deve ser inferido do contexto do gestor: a participação usa o titular sintético do registro. Seleção de candidatos conserva `pessoa_id` de cada candidato e não manda decisões de Alex/Participante para a caixa do titular Lucas.
4. Testes novos corrigidos: endereço administrativo real `/admin`, contagem de três avisos de Eventos, convite histórico já lido e seletor de summary direto do accordion. Falhas originais não são apresentadas como defeitos homologados.
5. Extração inicial do PDF falhou pela codificação do terminal; repetida com UTF-8. PDF de 15 páginas, SHA-256 `cf36b76fd0061feaaac774a2f85ef9ddff70b3d9f1bf03f9d146b3a3d7117d48`; RN012 e RN030–034 conferidas na fonte, além dos relatórios atuais 1–5.6 e guia de integração.

## Limites e pendências rastreadas

Cancelamento, lembrete de antecedentes, filas novas e alguns estados administrativos sem comando demonstrativo nas telas atuais têm exemplos históricos e destinos exercitados. Não se adicionou fluxo de cancelamento nem rotina de vencimento neste módulo, e não se afirma que foram acionados dinamicamente. A seleção atual de candidatos 11/12/13 não altera a identidade do titular 1 para fazê-lo receber decisões alheias. Convites continuam com transporte de cenário por URL conforme o Bloco 3; o store de avisos não transforma esse transporte em persistência real de vínculo.

Nenhum e-mail, SMS, push, fila, cron, API, auth, DB, migration, job, arquivo privado ou Railway foi ativado/alterado. Sem política definitiva de retenção. A deduplicação é demonstrativa, não concorrência transacional de produção. Janela/aba nova constitui novo estado inicial; estado não é sincronizado entre dispositivos. Homologação humana final permanece pendente.

O relatório 5.2 ainda menciona avaliação de voluntário de ponta a ponta pendente naquele marco. O 5.6 registra jornada integrada de avaliação e fechamento; este módulo executou regressão visual/Feature correspondente, mas não refez integralmente todos os percursos 5.2 e não elimina aquela pendência histórica sem conferência específica. Backlog `AJUDAE_BACKLOG_INTEGRACAO_V1` não encontrado, inclusive variantes por nome: a seção de integração é entregue separadamente para fusão futura.

## Preservação e entrega

[Inventário](bloco-visual-6-1-inventario.json), [matriz RN](bloco-visual-6-1-matriz-regras.md), [checklist por jornada](bloco-visual-6-1-checklist-homologacao-manual.md), [plano de integração](bloco-visual-6-1-plano-integracao.md). O delta compara os hashes iniciais de 1.981 arquivos, não apenas HEAD. Manifesto, patch e validação independente do ZIP discriminam as mudanças desta execução. Arquivos completos compartilhados preservam o trabalho local anterior; o patch mostra somente este módulo. Não houve git add, commit, push, deploy, migração, seeder ou ação Railway.

Proposta pontual para AGENTS.md, sem editá-lo: registrar central 6.1 e suas duas URLs existentes, avisos internos sintéticos, estado por aba e limites de disparos históricos, apontando para estes documentos após aceite humano. Decisões bloqueantes: **Nenhuma**. Não foi iniciado o módulo 6.2.


## Continuação 6.1.1 — 18/11/2026

O usuário aprovou a listagem/contador, leituras, filtros/busca/paginação, estados, candidatura Pessoa→ONG, navbar contextual e responsividade/acessibilidade. Os sete roteiros complementares continuavam sem comprovação manual. Corrigidos o badge do sino, o destino pendente do convite e a referência da Agenda em avisos novos de justificativa. [Relatório corretivo](bloco-visual-6-1-1-relatorio.md) e [homologação guiada por etapa](bloco-visual-6-1-1-checklist-complementar.md) prevalecem como roteiro atualizado, mantendo os resultados históricos acima. Homologação humana destes itens permanece pendente; não foi iniciado 6.2.
