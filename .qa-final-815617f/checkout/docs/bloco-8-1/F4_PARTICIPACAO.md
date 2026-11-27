# F4 — Participação, monitoria, avaliações e encerramento

## Transições e responsabilidades

| Operação | Estado exigido | Ator e efeito |
| --- | --- | --- |
| Selecionar inscrição ou decidir presença | Evento e Agenda ativos. A presença exige Agenda iniciada, inscrição aprovada ou participando e ausência de decisão anterior. | O responsável operacional registra uma única decisão por inscrição. A presença guarda data e identidade do decisor. |
| Finalizar Agenda | Evento e Agenda ativos, horário encerrado e presença ou ausência resolvida para cada inscrição aprovada. | O responsável operacional conclui a Agenda e consolida XP na mesma transação. Monitoria pendente segue visível na consulta privada. |
| Decidir monitoria | Evento ativo ou finalizado, Agenda ativa ou finalizada, presença confirmada e declaração estudantil na inscrição. | O responsável operacional confirma ou rejeita uma vez. A rejeição e a regularização após encerramento exigem justificativa. A decisão guarda autoria e data e preserva a participação. |
| Enviar e decidir justificativa | Ausência confirmada ou desistência penalizável. Evento ativo ou finalizado e Agenda sem cancelamento. | O titular envia uma justificativa. O responsável operacional aceita ou rejeita uma vez. Ausência e desistência mantêm estado e classificação originais. |
| Avaliar voluntário | Evento ativo, Agendas terminais e presença confirmada em Agenda finalizada. | O responsável operacional dá nota inteira de 1 a 5, única por Pessoa e Evento. |
| Avaliar ONG | Agendas terminais e presença confirmada em Agenda finalizada. Evento ativo ou até 7 dias corridos depois do carimbo de finalização, com limite inclusivo. | O voluntário titular registra uma nota inteira de 1 a 5, uma vez por Evento. Pode consultar a nota após o prazo. |
| Finalizar Evento | Evento ativo com pelo menos uma Agenda. Todas as Agendas terminais e todos os presentes avaliados. | ONG proprietária, organizador designado com vínculo vigente ou dono independente podem concluir. A transação grava estado final, autor e data do servidor. |

O estado `aguardando a confirmação` representa a aprovação inicial ou revisão. As consultas autorizadas permanecem acessíveis durante esse estado. Evento finalizado em versão anterior sem carimbo verificável permite consultar avaliações existentes. O envio pós-finalização exige carimbo válido.

## Rotas e contratos

| Rota API v1 | Operações |
| --- | --- |
| `/agendas/{id}/inscricoes` e `/agendas/{id}/inscrever` | `POST` aceita `declarou_estudante` booleano opcional. A declaração fica na inscrição daquela Agenda. Inscrições anteriores seguem sem declaração estudantil. |
| `/inscricoes/{id}/monitoria` | `GET` devolve `status`, declaração e data da decisão. `PATCH` usa `confirmada` booleano e `justificativa` quando exigida. |
| `/inscricoes/{id}/justificativa` | `GET` consulta o caso privado. `POST` usa `motivo`. `PATCH` usa `deferida` booleano e `motivo`. |
| `/eventos/{id}/avaliacao-ong` | `GET` consulta a nota do titular. `POST` mantém o campo `nota`. |
| `/inscricoes/{id}/presenca`, `/agendas/{id}/finalizar`, `/eventos/{id}/finalizar` | Contratos existentes preservados. Guardas de estado e autoria são aplicadas no servidor. |

O `MonitoriaService::exigirValidacaoParaCertificado()` verifica todas as inscrições estudantis elegíveis no Evento. A emissão deve chamá-lo sob lock do Evento. Uma confirmação válida tem resultado positivo, carimbo e responsável registrado. A tabela `justificativa_participacao` representa a decisão separadamente da inscrição. F1 e F5 podem consultar o status `deferida` sem ler o motivo. `Inscricao::justificativaParticipacao()` expõe a relação para projeções de elegibilidade e confiabilidade.

## Evidência e limites

As suítes de regressão incluem o instante exato do fechamento da janela, leitura depois do prazo, duplicidade e tentativa por outra Pessoa. O fechamento testa ausência de Agendas, estado não terminal, vínculos revogados, terceiros, administradores e repetição. A cobertura de monitoria executa declaração, seleção, presença, encerramento com pendência, regularização auditada e ausência de XP duplicado. A cobertura de justificativa verifica titularidade, revisão humana e preservação do histórico.

Os testes de concorrência com duas conexões MySQL e a homologação dos painéis Web dependem da integração coordenada. A retificação excepcional de decisões, presença, horas ou encerramento permanece vinculada às decisões D06–D09. O protocolo de justificativa recebe texto privado. Anexos exigem definição de armazenamento e retenção antes de integração.
