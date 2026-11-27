# API V1 — antecedentes e padrinhos

Base: `/api/v1`. Todas as rotas exigem Bearer token Sanctum. As rotas de Pessoa exigem habilidade `pessoa`; as administrativas exigem conta `administrador` com permissão indicada ou Admin Master. Os exemplos abaixo mostram apenas o campo `data` de respostas bem-sucedidas; a API mantém o envelope padrão (`success`, `message`, `data`). Erros seguem o contrato existente: 401 sem autenticação, 403 sem permissão, 404 para recurso alheio, 409 para conflito de estado e 422 para payload inválido.

## Antecedentes criminais

| Método | URI | Acesso | Uso |
| --- | --- | --- | --- |
| GET | `/pessoas/me/antecedentes` | Própria Pessoa | Última versão aprovada, validade real, renovação ativa e necessidade de renovar. |
| POST | `/pessoas/me/documentos/antecedentes_criminais` | Própria Pessoa | Upload privado; multipart com `arquivo` PDF/JPEG/PNG até 10 MiB. Rota documental já existente. |
| GET | `/documentos/{documento}/download` | Titular ou admin autorizado | Download autorizado; rota documental já existente. |
| GET | `/admin/antecedentes/renovacoes?per_page=20` | `usuarios.validar`/Master | Fila paginada de renovação pendente de Pessoa já aprovada. |
| PATCH | `/admin/documentos/{documento}/status` | `usuarios.validar`/Master | Análise manual; `{"status":"aprovado"}` ou `{"status":"rejeitado","motivo":"..."}`. Rota documental já existente. |

Exemplo de situação própria:

```json
{"status_validacao":"aprovado","ultima_aprovacao":{"documento_pessoa_id":12,"status":"expirado","validado_em":"2025-10-13T12:00:00.000000Z","valido_ate":"2026-10-13T12:00:00.000000Z","vigente":false},"renovacao":{"documento_pessoa_id":13,"status":"pendente","enviado_em":"2026-10-13T12:01:00.000000Z"},"renovacao_necessaria":true}
```

`expirado` é estado derivado da última aprovação, não decisão criminal nem novo estado global. O vencimento não bloqueia candidatura nem remove `status_validacao=aprovado`. A nova versão pendente não apaga a decisão anterior. Um documento reprovado permanece no histórico. Arquivo, path privado, número de documento e URL nunca entram nessa resposta, fila ou notificação. A política definitiva de retenção/LGPD fica para o próximo bloco.

`antecedentes:alertar-renovacao` procura versões aprovadas vencidas, envia uma notificação por versão e grava `alerta_renovacao_em` para idempotência. A tarefa foi agendada diariamente em `routes/console.php`; no Railway será necessário ativar a execução do scheduler Laravel e armazenamento privado persistente antes da implantação.

## Padrinhos

| Método | URI | Acesso | Request / resultado |
| --- | --- | --- | --- |
| GET | `/padrinhos/elegiveis?per_page=20` | Voluntário aprovado | Lista paginada: `pessoa_id`, nome, cidade, UF. |
| GET | `/padrinhos/me` | Pessoa | Solicitações e vínculos próprios paginados. |
| GET | `/padrinhos/recebidas?per_page=20` | Pessoa | Solicitações pendentes endereçadas ao padrinho. |
| POST | `/padrinhos/solicitacoes` | Afilhado | `{"padrinho_id":123}`; 201 com ID/status. |
| POST | `/padrinhos/solicitacoes/{pedido}/aceitar` | Padrinho destinatário | Aceita atomicamente e cria vínculo; retry do aceite retorna o mesmo vínculo. |
| POST | `/padrinhos/solicitacoes/{pedido}/recusar` | Padrinho destinatário | Preserva pedido como recusado. |
| POST | `/padrinhos/solicitacoes/{pedido}/cancelar` | Afilhado | Cancela apenas pedido pendente. |
| GET | `/padrinhos/vinculos/{vinculo}` | Participantes | Status, datas e progresso, sem documentos. |
| POST | `/padrinhos/vinculos/{vinculo}/encerrar` | Participantes | `{"motivo":"..."}` obrigatório; encerra sem conclusão. |
| POST | `/padrinhos/vinculos/{vinculo}/solicitar-conclusao` | Afilhado | 201 após seis meses completos e cinco Agendas confirmadas ocorridas durante o vínculo. |
| GET | `/admin/padrinhos/conclusoes?per_page=20` | `padrinhos.validar_conclusao`/Master | Fila paginada de pedidos pendentes. |
| POST | `/admin/padrinhos/conclusoes/{pedido}/aprovar` | Mesmo admin | Revalida e conclui vínculo. |
| POST | `/admin/padrinhos/conclusoes/{pedido}/reprovar` | Mesmo admin | `{"motivo":"..."}` opcional; mantém vínculo ativo. |

Exemplo de vínculo: `{"acompanhamento_id":9,"padrinho_id":123,"apadrinhado_id":456,"status":"ativo","aceito_em":"2026-10-13T12:00:00.000000Z","encerrado_em":null,"motivo_encerramento":null,"concluido_em":null}`. O detalhe soma `progresso` com data de seis meses e total de participações no período.

Afilhado precisa ser voluntário aprovado, ter menos de cinco participações acumuladas na solicitação, nenhum pedido pendente/vínculo ativo e não ser padrinho ativo. Padrinho precisa ser voluntário aprovado há 60 dias (`aprovado_em`), ter dez participações confirmadas, média de avaliação de voluntário >=4,5, nenhuma ausência penalizável e até uma desistência tardia penalizável nos 90 dias, no máximo dois afilhados ativos, sem vínculo ativo como afilhado e sem bloqueio vigente. São contadas Agendas finalizadas distintas de Eventos não cancelados. A elegibilidade é reavaliada sob lock no aceite; depois dele, sua perda não encerra o vínculo. Solicitações pendentes não ocupam vaga. As constraints MySQL de colunas geradas impedem dois pedidos pendentes ou dois vínculos ativos para o mesmo afilhado, mantendo histórico ilimitado.

Não há acesso do padrinho a documentos, dados pessoais privados, funções de ONG/organizador/admin, nem XP, penalidade, chat ou conclusão automática.
