# Ajudaê — Bloco 8.1

O segundo anexo de autorização foi conferido do início ao fim, incluindo a seção 10 e a definição completa de D05. A confirmação posterior do usuário fixou D01 em sete dias corridos inclusivos. A regra, o backlog e os contratos foram atualizados.

| Frente | Recorte entregue | Pendência relevante |
| --- | --- | --- |
| F1 · Identidade | Cadastro Web de Pessoa, código de indicação, aceite, exigência fundamentada e guarda de aprovação. | Cadastro de ONG, upload e abas de perfil e apadrinhamento. |
| F2 · Eventos | Quatro etapas de candidatura, carta privada, Agendas com capacidade própria, histórico e desistência do titular. | Gestão Web de criação, edição, Agendas e seleção. |
| F3 · Administração | Convites, resposta e vínculo via Web, avisos e trilha. Fila de Evento e decisão Web por capacidade. | Demais filas, delegação e moderação institucional na Web. |
| F4 · Participação | D01–D05, presença, justificativa, monitoria e fechamento. Histórico e avaliação da ONG pela Web. | Operação da presença, justificativa, monitoria e encerramento ainda usa API. |
| F5 · Gamificação | Confiabilidade considera reversão auditada. Ranking, emissão, lista, PDF e verificação de certificados pela Web. | Extrato, badges, ajustes e retificação excepcional. |
| F6 · Privacidade | Protocolo LGPD do titular, central de notificações, trilha de cancelamento e proteção de casos. | Fila LGPD administrativa, catálogo de notificações e storage externo. |

O checkpoint Web/API foi ensaiado da criação de Evento por ONG ativa até XP, certificado e avaliação da ONG. A jornada combina a Web nas telas integradas e a API nas operações que ainda não têm tela persistida. A homologação humana, a homologação de storage e a exposição do contrato legado de certificado público ficam pendentes.

O delta parte da branch `codex/baseline-ajudae-7-4`, HEAD `7bd046a4ad54411f662c1666d1d4d9000bcf963b`, incluindo o trabalho não commitado já presente na entrada. Houve migrações somente na instância descartável verificada com UUID, porta, diretório e schema. Não houve commit, push, deploy ou alteração de banco principal.

Após o aceite manual, a atualização pontual de `AGENTS.md` pode registrar D01–D05, as rotas integradas e as pendências remanescentes com referência a este relatório. O arquivo foi preservado nesta execução.

[Regras e estados](MATRIZ_RN.csv) · [Backlog](BACKLOG_STATUS.csv) · [Contratos](CONTRATOS_WEB_API.md) · [Testes](TESTES_EVIDENCIAS.md) · [Homologação](CHECKLIST_MANUAL.md)
