# Bloco 7.4 — pendências de integração

| Frente | Liberação Web futura | Contrato e aceite mínimo |
|---|---|---|
| F1 · Identidade | Cadastro, edição, documentos, perfis compartilháveis, recuperação de senha | Providers tipados no Blade, validação e CSRF em mutações, minimização do perfil público, upload privado e autorização por caso. Definir recuperação com revogação e novo login antes de expor comandos |
| F2 · Eventos | Filtros completos, Agendas, gestão, inscrição, seleção e desistência | Evento/Agenda/Inscrição, escopo por propriedade e atribuição, capacidades transacionais exclusivamente por Agenda, carta opcional privada e visibilidade apenas após publicação |
| F3 · Administração e organização | Filas, promoção, atribuição, diretório, convites, vínculo e apadrinhamento | Gates/Policies por finalidade e atribuição, resolução autorizada de identidade sem enumeração, trilha de decisões e revogação efetiva do vínculo no request seguinte |
| F4 · Participação | Presença, monitoria, avaliações, justificativas e encerramento | Fonte autoritativa por Agenda, papéis e prazos, avaliação única por Pessoa e Evento, lock e idempotência |
| F5 · Gamificação | XP, confiabilidade, ranking, certificados e verificação pública de código | Ledger autoritativo, projeção pública mínima, emissão sob demanda, controle de titularidade e prova verificável |
| F6 · Privacidade | Notificações, fila LGPD operacional, trilhas e retenção | Capacidade distinta para ler e responder, finalidade por caso, idempotência, destinatário e destino revalidados. Governança de expurgo e exclusão definitiva separada |

A expiração global bearer de sete dias permanece proposta. Validar estratégia de transição e homologar sem interromper clientes existentes. Para expiração ociosa, A infraestrutura de sessão em múltiplos servidores e o futuro polling exigem classificação de atividade e testes em infraestrutura compartilhada. O catálogo Web atual não oferece polling autenticado. As versões futuras devem preservar as proteções do Bloco 7.2.

Fontes do projeto [mini-backlog 7.3](bloco-7-3-mini-backlog.md) · [plano 6.5](bloco-visual-6-5-plano-integracao.md) · [contratos da API](guia-integracao-api.md).
