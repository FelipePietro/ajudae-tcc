# Bloco 8.2 — Decisões e pendências

## Divergências entre o anexo e o contrato vigente

| Referência | Divergência observada | Contrato registrado para a equipe |
| --- | --- | --- |
| D01 · RN022 | O §9 cita comentário opcional. `AvaliacaoController::regrasNota()` exige nota inteira de 1 a 5 e proíbe comentário, texto e justificativa. | Uma avaliação facultativa por Pessoa e Evento, com janela de sete dias corridos inclusiva. Qualquer extensão de payload precisa de decisão de produto e contrato explícito. |
| D02 · RN024 | O §9 fala em pelo menos uma Agenda para todo Evento. A decisão 8.1 aplica o requisito ao encerramento. | O fluxo atual cria o Evento, depois suas Agendas. Encerramento exige uma ou mais Agendas em estado terminal e as avaliações obrigatórias. Antes de mudar a criação, confirmar se o texto pretende antecipar o requisito. |
| D03 · RN020/RN023/RN029 | O §9 usa a expressão genérica “regularização após o encerramento”. | A autorização 8.1 trata da monitoria estudantil, com ator, data e justificativa quando aplicável. O contrato da emissão revalida essa confirmação. |
| D04/D05 · RN008/RN010/RN019/RN021/RN024 | O §9 atribui consulta a organizador em D04 e operações ao organizador formalmente autorizado em D05. | D04 em 8.1 trata de Evento pendente ou em reanálise. A operação distingue o responsável designado com vínculo válido, organizador independente proprietário e ONG. A finalização segue a autoridade específica de D05. |

O §3 pede preservar as regras aprovadas durante a preparação. O Bloco 8.2 registra essas diferenças para decisão posterior e usa os contratos efetivos na documentação. [Decisões 8.1](../bloco-8-1/DECISOES_BLOQUEANTES.md) · [Regras V1](../../Ajudae_Regras_de_Negocio_V1.md)

## Pendências por tipo

| Tipo | Pendência e próxima ação | Frente sugerida |
| --- | --- | --- |
| Integrações da equipe | Conectar cadastro institucional, perfil, catálogo de preferências, operação de Eventos, Agendas, seleção, participação e filas restantes após homologar cada contrato. | F1–F4 |
| Endpoint ausente | Recuperação pública de senha em duas etapas não tem contrato versionado identificado. Definir emissão, validação, revogação e canal de entrega. | F1 |
| Contrato incompleto | Separar inscrição, gestão e upload de documentos pessoais/institucionais. Conferir por campo a transição de cadastro ONG e armazenamento privado antes de liberar a tela. | F1/F3/F6 |
| Contrato incompleto | Fixar projeções e consulta segura de organizador no canal API. O vínculo por identificador de login/e-mail no Web existente difere do `pessoa_id` recebido pelo Bearer. | F2 |
| Contrato incompleto | Documentar níveis e extrato como projeções pendentes quando a rota não existir. Revisar campos públicos da verificação legada e autorização de download privado. | F5/F6 |
| Dependência externa | Provedor de recuperação, origem HTTPS, política de CORS e storage descartável são dependências de homologação da equipe. | F1/F6 e responsável de infraestrutura |
| Decisões em aberto | D06–D10 continuam no registro central. Requerem revisão os fluxos de correção excepcional, compensações, retificação e quaisquer mudanças de regra além da RN vigente. | Produto e frentes correspondentes |
| Testes futuros | Jornada de candidatura em ambiente próprio depende de Evento publicado, Agenda elegível e Pessoa aprovada. Validar antes de homologar o percurso completo em navegador. | F2 e QA |
| Homologação externa | Verificar S3, TLS e deploy no ambiente de homologação com autorização específica. | Infraestrutura e QA |

[Backlog central](../AJUDAE_BACKLOG_INTEGRACAO_V1.md) · [Decisões abertas 8.1](../bloco-8-1/DECISOES_ABERTAS.md)
