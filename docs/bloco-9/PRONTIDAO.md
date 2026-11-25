# Checklist de prontidão para o Bloco 9

**AINDA NÃO PRONTO para declarar o escopo integral disponível.** O contrato Web está disponível para avançar nas frentes listadas abaixo. Esta etapa partiu do commit-base local `c1ff91f2d92f26115ade7e7687c27ee7c2467fdd`.

| Domínio | Classificação | Verificação ou impedimento |
| --- | --- | --- |
| F1 · Cadastro Pessoa, login, senha autenticada, documentos, validação e padrinhos | PRONTO no contrato local | Cadastro pendente, gestão documental e decisões com ator. A recuperação pública está **BLOQUEADA** por falta de canal verificado e fluxo de recuperação para `Pessoa`/`Ong`. |
| F1/F3 · Cadastro institucional e aprovação | PRONTO para integração em armazenamento privado local | Conta pendente, upload, rollback, auditoria, fila e download autorizado. `railway_private` ainda exige ensaio externo em bucket isolado. O expurgo físico institucional fica bloqueado por autorização; o acesso expira em 30 dias. Referências anteriores sem metadados exigem reconciliação específica. |
| F2 · Editores, Agendas, imagem, moderação, candidatura e seleção | PRONTO no contrato local | D02 exige criação simultânea. Revisar clientes Bearer que enviavam Evento antes das Agendas. Fluxo completo das interfaces cabe ao Bloco 9. |
| F3 · Filas e decisões de Pessoa/ONG/Evento, promoção e vínculos | PRONTO no contrato local | Atribuição por `pessoa_id` de vínculo ativo, escopo e capacidades verificados. Gestão excepcional de ocorrências e mudanças no modelo de delegação seguem fora do escopo ordinário. |
| F4 · Presença, monitoria, justificativas, avaliação e encerramento | PRONTO no contrato local | D01 e D04/D05; inspecionar as [regras e operações F4](CONTRATOS_PARTICIPACAO.md). D06 e ajustes excepcionais seguem no registro de decisão. |
| F5 · XP, ranking, campanhas, confiabilidade e certificados | PRONTO para fluxos ordinários | Níveis, badges, extrato e retificação são **PARCIAIS** ou **NÃO APLICÁVEIS** ao núcleo V1. Consumir verificação pública Web antes da definição D09 sobre a projeção Bearer legada. |
| F6 · Notificações, LGPD, exclusão reversível, auditoria e recursos públicos/privados | PRONTO no contrato local | Encerramento irreversível de conta permanece desabilitado no agendamento. Proteção externa de objetos deve ser homologada em armazenamento descartável. |

## Antes de homologar integração

- Ativar a identidade integrada em ambiente de integração com sessão persistida do Laravel, origem e CSRF válidos.
- Aplicar apenas em ambiente próprio as migrations aprovadas e testar download de objeto privado, rollback e acesso cruzado com documentos sintéticos. Banco principal e Railway ficam fora desta entrega.
- Criar ONG e Pessoas sintéticas nas funções necessárias. Cadastrar Evento com Agenda, aprovar e testar o caminho de candidatura e presença até certificado, incluindo um teste de permissão negada por domínio.
- Rever consumidor Bearer da criação de Evento e do cadastro ONG. Os novos campos obrigatórios têm exemplos no catálogo. O contrato de criação isolada e o campo de link de identidade foram substituídos por exigência D02 e RN032.
- Definir recuperação de senha para as duas identidades, com comprovação de titularidade, entrega e revogação. A integração desse controle depende desse trabalho.
- Homologar com autorização o plano de expurgo institucional de acordo com a retenção após decisão e a gestão de referências legadas. A revisão automática bloqueou a alteração que habilitaria exclusão física antes dessa autorização.

[Catálogo](CATALOGO_WEB.md) · [Matriz da equipe](MATRIZ_INTEGRACAO.md) · [QA independente](QA_INDEPENDENTE.md)
