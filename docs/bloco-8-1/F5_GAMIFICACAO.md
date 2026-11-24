# F5 — XP, confiabilidade, ranking e certificados

O Bloco 8.1 mantém o cálculo e as chaves de idempotência de XP existentes (RN025–RN026). A confiabilidade (RN027) preserva a ocorrência de ausência ou desistência. Após justificativa deferida, o delta efetivo da ocorrência é zero e o detalhamento sinaliza a reversão. Motivo e conteúdo da justificativa permanecem privados. O ranking (RN028) continua ordenado pelo XP, com desempate técnico por identificador estável, e recebe dados persistidos no Web.

A emissão (RN029/D03) verifica monitoria confirmada nas Agendas estudantis elegíveis, dentro do bloqueio transacional do Evento. Repetir a emissão verifica essa exigência antes de retornar o certificado existente. A regularização da monitoria usa o fluxo F4 e preserva presença e XP. Certificados anteriormente emitidos continuam consultáveis pelos canais de leitura. A regra de retificação do documento depende de D09.

## Contratos Web

| Método e rota | Identidade | Dados |
| --- | --- | --- |
| GET `/ranking/dados` | Público | Ranking persistido, busca e filtros. |
| GET `/meus-certificados/dados` | Sessão de Pessoa com jornada pessoal | Página com certificados do titular e situação das participações concluídas por Evento. |
| POST `/meus-certificados/eventos/{evento}/emitir` | Sessão e CSRF | Emissão idempotente pelo serviço de certificados compartilhado com a API. |
| GET `/meus-certificados/{certificado}/pdf` | Sessão do titular | PDF sob demanda pelo mesmo gerador da API, com cache privado desabilitado. |
| GET `/certificados/verificar/dados?codigo=...` | Público | Validade, código, nome do Evento, carga horária e emissão. Sem nomes de pessoas, do responsável ou IDs pessoais. |

As páginas `/meus-certificados` e `/certificados/verificar` usam o cliente Fetch compartilhado e mantêm o layout e os componentes. A modalidade decorre da declaração estudantil registrada na inscrição. A verificação pública pela API mantém o contrato legado. O download API permanece privado e usa autenticação Bearer.

## Verificação

- Sintaxe PHP e JavaScript, Pint dos quatro arquivos PHP e `git diff --check` passaram para o lote. O teste `Bloco81WebCertificateTest` cobre consulta e emissão por sessão, replay, PDF, autorização de terceiros e minimização pública. A execução MySQL integrada é registrada no relatório geral.
- O teste F4 de monitoria cobre encerramento com pendência, regularização e ausência de XP duplicado. Os filtros de regressão de ranking, XP, confiabilidade e certificados ficam no relatório geral.
- Homologação humana do Web, consulta do extrato XP, badges e ajustes excepcionais D06–D09 permanecem pendentes. Nenhuma regra de compensação de XP ou retificação foi criada nesta frente.
