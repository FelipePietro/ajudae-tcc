# Bloco 8.2 — Checklist de prontidão

**Pronto para iniciar os trabalhos de integração por domínio, com liberação de fluxos condicionada às dependências abaixo.** O catálogo e o cliente isolado permitem começar a implementação. A disponibilidade completa da plataforma depende da integração definitiva e da homologação da equipe.

| Entrega | Situação | Evidência ou condição |
| --- | --- | --- |
| Cadastro Pessoa e artefatos locais | Fechado no recorte 8.2 | QA independente em Edge com bundle final e atualização local verificada. |
| Cadastro ONG | Bloqueado | Mapear formulário, validação institucional e arquivo privado. Autorização e aprovação continuam a ser requisitos. |
| Criação de Evento | Bloqueado no Web | Mapear identidade operacional, comando de Evento, Agendas, imagem, moderação e tratamento de gravações parciais. |
| Candidatura | Contrato e regressão disponíveis | Homologar com Evento publicado, Agenda elegível e Pessoa aprovada. |
| Inicialização e cliente futuro | Preparados | Dois modos Vite com bootstrap comum. Cliente isolado com testes independentes. |
| 173 entradas `/api/v1` | Inventariadas | QA comparou caminhos e métodos com o runtime. A revisão semântica é amostral e os detalhes de Resources permanecem ancorados no código. |
| Segurança e permissões | Verificadas no recorte local | Manter credenciais Web, Bearer e CSRF no canal próprio. Homologar proteção e armazenamento externos em ambiente autorizado. |
| Responsáveis | A confirmar na equipe | A matriz propõe domínios e próximos passos para os seis integrantes. |
| Produção, storage e aceite humano | Pendentes | Executar o roteiro da equipe com dados sintéticos e origem HTTPS autorizada. |

## Antes de liberar uma tela

1. Escolher o contrato real do catálogo e conferir a origem Laravel, o Resource, a validação, os estados 401/403/409/422 e autorização por registro.
2. Confirmar o canal de autenticação, os campos privados, o vazio e a recuperação de erro. Testar o upload e o PDF como arquivos quando aplicável.
3. Validar o fluxo com dados sintéticos, pelo menos um ator autorizado e um negado. Conferir teclado, foco e larguras aplicáveis.
4. Conferir que a página é atendida pelo manifesto correspondente ao código. Em desenvolvimento, o indicador hot deve apontar para o Vite efetivamente em execução. Fora desse modo, servir os assets de um build atual.
5. Registrar evidência, pendências e titular responsável. Concluir mudanças D01, D02 ou D04 apenas depois de reconciliar as divergências registradas.

Após o aceite manual, a atualização pontual de `AGENTS.md` pode registrar o diagnóstico de assets, o mapa API e os bloqueios 8.2. O arquivo foi mantido como referência histórica nesta execução.

[Relatório de correções](RELATORIO_CORRECOES.md) · [QA independente](QA_INDEPENDENTE.md) · [Matriz](MATRIZ_INTEGRACAO.md) · [Decisões e pendências](DECISOES_E_PENDENCIAS.md)
