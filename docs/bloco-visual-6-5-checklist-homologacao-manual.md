# Bloco 6.5 — homologação manual por jornada

Executar **na mesma aba/origem**, somente porta8000, dados sintéticos. Para isolamento use nova aba não duplicada, sem limpar storage global. Repita em360/768/1280px: Tab/Shift+Tab, Enter, Escape, retorno de foco, labels, scroll, sobreposição, refresh, ida/volta e console. Homologação humana: **pendente**.

## 1. Visitante, cadastro e perfis

[Abrir 1](http://127.0.0.1:8000/) → [Abrir 2](http://127.0.0.1:8000/cadastro?contexto=visitante) → [Abrir 3](http://127.0.0.1:8000/cadastro/ong?contexto=visitante) → [Abrir 4](http://127.0.0.1:8000/perfil?area=privada&contexto=pessoa) → [Abrir 5](http://127.0.0.1:8000/perfil?area=publica&pessoa=1&contexto=visitante) → [Abrir 6](http://127.0.0.1:8000/ong/perfil?ong=2&contexto=visitante)

**Pré-condição:** cenário sintético explícito, identidade da URL correspondente; nenhuma conta real.

Avance cadastro vazio e confira foco/erros. Para revisão preenchida, use cenário sintético de inspeção em cadastro?inspecao=1. Revise termos sem criar conta real. Abra Meu perfil e Ver perfil público. Público não mostra Meu cadastro/documentos. Confira Iniciante 450/500 na landing e Colaborador com 500 XP restantes no perfil de1000XP. Verificar código abre consulta pública; cards sem código não oferecem link morto.

**Resultado/evidência/status:** comparar com matriz RN, JSON de ações e capturas em evidencias/bloco-visual-6-5. Status humano: ☐ aprovado ☐ reprovado ☐ pendente. Anotar URL final, passo, largura e captura de qualquer diferença.

## 2. ONG, Evento, Agendas, candidatura e seleção

[Abrir 1](http://127.0.0.1:8000/ong/painel?contexto=ong) → [Abrir 2](http://127.0.0.1:8000/ong/eventos/criar?contexto=ong) → [Abrir 3](http://127.0.0.1:8000/ong/eventos/1/editar?contexto=ong) → [Abrir 4](http://127.0.0.1:8000/eventos/1?contexto=pessoa) → [Abrir 5](http://127.0.0.1:8000/candidatura?contexto=pessoa) → [Abrir 6](http://127.0.0.1:8000/ong/candidatos?contexto=ong) → [Abrir 7](http://127.0.0.1:8000/candidatura?contexto=ong)

**Pré-condição:** cenário sintético explícito, identidade da URL correspondente; nenhuma conta real.

Revise Evento e Agendas; vagas independentes, inclusive somatório maior que Evento. Percorra as4etapas com carta vazia e preenchida. Gestor seleciona por Agenda; pública não mostra carta. ONG não avança candidatura. Teste reanálise/cancelamento/filtros combinados, vazio e erro pelo catálogo de cenários existente. Cadastro/Admin/Evento não formam persistência real única neste demonstrativo.

**Resultado/evidência/status:** comparar com matriz RN, JSON de ações e capturas em evidencias/bloco-visual-6-5. Status humano: ☐ aprovado ☐ reprovado ☐ pendente. Anotar URL final, passo, largura e captura de qualquer diferença.

## 3. Promoção, convites e vínculo

[Abrir 1](http://127.0.0.1:8000/organizador/solicitacao?contexto=pessoa) → [Abrir 2](http://127.0.0.1:8000/ong/organizadores?contexto=ong&jornada=ong-vazia) → [Abrir 3](http://127.0.0.1:8000/organizador?contexto=organizador&jornada=convite-pendente) → [Abrir 4](http://127.0.0.1:8000/organizador?contexto=organizador-vinculado&jornada=vinculado-varios)

**Pré-condição:** cenário sintético explícito, identidade da URL correspondente; nenhuma conta real.

Compare elegível/inapto, justificativa e revisão. Na ONG convide lucas.pereira; teste Escape e retorno de foco; conclua revisão; recarregue e confira convite sem duplicar. Siga links gerados para preservar convites. Responda pela identidade convidada; conflito com Evento independente deve impedir aceite. Confira responsáveis designados, encerramento bloqueado e retorno ao perfil público correto. Mudanças legadas além do transporte dos IDs precisam ser distinguidas de persistência real.

**Resultado/evidência/status:** comparar com matriz RN, JSON de ações e capturas em evidencias/bloco-visual-6-5. Status humano: ☐ aprovado ☐ reprovado ☐ pendente. Anotar URL final, passo, largura e captura de qualquer diferença.

## 4. Admin e apadrinhamento

[Abrir 1](http://127.0.0.1:8000/admin/usuarios?contexto=administrador) → [Abrir 2](http://127.0.0.1:8000/admin/fila-eventos?contexto=administrador) → [Abrir 3](http://127.0.0.1:8000/admin/solicitacoes-lgpd?contexto=administrador&visao=revisao) → [Abrir 4](http://127.0.0.1:8000/perfil?area=privada&contexto=pessoa&apadrinhamento=padrinho) → [Abrir 5](http://127.0.0.1:8000/cadastro?contexto=visitante&inspecao=1)

**Pré-condição:** cenário sintético explícito, identidade da URL correspondente; nenhuma conta real.

Revise filas por permissão e negativa de LGPD para revisor. Fluxo de referral deve começar por link realmente gerado pelo padrinho. Aceite/recusa não aprova cadastro. Conclusão exige revisão administrativa. Não usar dados/documentos reais.

**Resultado/evidência/status:** comparar com matriz RN, JSON de ações e capturas em evidencias/bloco-visual-6-5. Status humano: ☐ aprovado ☐ reprovado ☐ pendente. Anotar URL final, passo, largura e captura de qualquer diferença.

## 5. Presença, justificativa, avaliação e fechamento (inclui5.2)

[Abrir 1](http://127.0.0.1:8000/ong/eventos/7/presenca?contexto=ong&exemplo=avaliacoes) → [Abrir 2](http://127.0.0.1:8000/ong/eventos/1/presenca?contexto=organizador-vinculado&jornada=vinculado&exemplo=encerramento) → [Abrir 3](http://127.0.0.1:8000/ong/eventos/8/presenca?contexto=organizador&jornada=independente-eventos&exemplo=encerramento) → [Abrir 4](http://127.0.0.1:8000/minhas-participacoes?contexto=pessoa&exemplo=confiabilidade#agenda-704) → [Abrir 5](http://127.0.0.1:8000/ong/eventos/7/presenca?contexto=pessoa)

**Pré-condição:** cenário sintético explícito, identidade da URL correspondente; nenhuma conta real.

Resolva presença/ausência/monitoria, finalize vazia, confirme futuro impedido e múltiplas Agendas. Evento fica bloqueado enquanto avaliação obrigatória faltar; avalie1–5 uma vez Pessoa+Evento e finalize. Registre justificativa da Agenda704, confira que705 não é afetada e decida como gestor; aceite conserva ocorrência e reverte penalidade. Atualize e volte/avance. Pessoa/Admin não podem assumir fechamento. Aceite humano complementar5.2 permanece em aberto.

**Resultado/evidência/status:** comparar com matriz RN, JSON de ações e capturas em evidencias/bloco-visual-6-5. Status humano: ☐ aprovado ☐ reprovado ☐ pendente. Anotar URL final, passo, largura e captura de qualquer diferença.

## 6. XP, confiabilidade, ranking e certificado

[Abrir 1](http://127.0.0.1:8000/perfil?area=privada&contexto=pessoa#meu-xp) → [Abrir 2](http://127.0.0.1:8000/perfil?area=privada&contexto=pessoa&exemplo=confiabilidade#confiabilidade) → [Abrir 3](http://127.0.0.1:8000/ranking?contexto=visitante) → [Abrir 4](http://127.0.0.1:8000/meus-certificados?contexto=pessoa&exemplo=certificados) → [Abrir 5](http://127.0.0.1:8000/certificados/verificar?contexto=visitante) → [Abrir 6](http://127.0.0.1:8000/meus-certificados?contexto=ong)

**Pré-condição:** cenário sintético explícito, identidade da URL correspondente; nenhuma conta real.

Confira XP somente presença+Agenda encerrada, replay sem duplicar e confiabilidade em formação. Ranking somente XP. Gere certificado geral2h, recarregue, abra visualização, Escape retorna foco, baixe PDF e siga Verificar certificado na mesma aba/origem. Estudantil exige monitoria. Código inválido não encontra. ONG recebe403. Outra aba/dispositivo não prova autenticidade do documento sintético.

**Resultado/evidência/status:** comparar com matriz RN, JSON de ações e capturas em evidencias/bloco-visual-6-5. Status humano: ☐ aprovado ☐ reprovado ☐ pendente. Anotar URL final, passo, largura e captura de qualquer diferença.

## 7. Notificações, privacidade e exclusão

[Abrir 1](http://127.0.0.1:8000/notificacoes?contexto=pessoa) → [Abrir 2](http://127.0.0.1:8000/ong/notificacoes?contexto=ong) → [Abrir 3](http://127.0.0.1:8000/perfil/privacidade?contexto=pessoa) → [Abrir 4](http://127.0.0.1:8000/ong/privacidade?contexto=ong) → [Abrir 5](http://127.0.0.1:8000/admin/solicitacoes-lgpd?contexto=administrador&visao=privacidade) → [Abrir 6](http://127.0.0.1:8000/perfil/excluir?contexto=pessoa) → [Abrir 7](http://127.0.0.1:8000/ong/perfil/excluir?contexto=ong)

**Pré-condição:** cenário sintético explícito, identidade da URL correspondente; nenhuma conta real.

Crie pedido sintético e consulte na mesma aba; leitor não responde, visão autorizada pode revisar. Confira histórico sem detalhe público, deduplicação, filtros/vazio/erro. Solicite exclusão com EXCLUIR somente no exemplo permitido, atualize, confira prazo original sete dias, reverta e atualize. Pendências impedem. Não enviar e-mail, exportar dados ou apagar conta real.

**Resultado/evidência/status:** comparar com matriz RN, JSON de ações e capturas em evidencias/bloco-visual-6-5. Status humano: ☐ aprovado ☐ reprovado ☐ pendente. Anotar URL final, passo, largura e captura de qualquer diferença.

## Limites e continuidade

O navegador Codex foi usado interativamente, mas suas capturas falharam; imagens complementares são de Edge/Playwright e não devem ser atribuídas ao Codex. Não houve leitor de tela real nem certificação WCAG/PDF-UA. Jornadas que dependem de uma persistência única entre cadastros/Admin/Evento permanecem demonstrações de estados, não E2E integrado. Não avançar para integração antes de aceite do grupo. Reparos6.5 continuam neste chat.
