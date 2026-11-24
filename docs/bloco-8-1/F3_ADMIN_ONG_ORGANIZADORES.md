# F3 — Administração, ONG e organizadores

## Entrega de implementação

- RN007/RN031/RN034. Aprovação e reprovação de promoção registram o administrador e a mudança de estado no log. A Pessoa recebe notificação privada. A regra preexistente mantém o histórico da Pessoa e a exclui da candidatura após a promoção.
- RN008/RN031/RN034. Novo convite, aceite, recusa e encerramento registram a transição e emitem aviso ao destinatário na mesma transação. Convite e resposta recarregam o ator e conferem sua versão de autenticação sob lock. Repetições não geram segundo aviso ou segundo log.
- RN008/RN032. A ONG ativa pesquisa o destinatário por identificador exato em uma operação autenticada de convite, sem endpoint de busca. Resposta genérica 202 e limitação de envio no Web. A lista do titular mostra o nome e o estado do vínculo, sem login, e-mail, CPF ou dados documentais. Nas duas listagens Bearer, a relação da contraparte mantém ID/nome, e o restante do payload de vínculo segue disponível.
- RN006/RN010/RN011. As decisões continuam com as permissões e a autoria do domínio. A aprovação cadastral aciona a guarda de F1. A atribuição de responsável continua restrita à ONG dona e a organizador com vínculo vigente antes do início. A decisão D05 foi implementada em F4, preservando a distinção entre encerramento do Evento e operações do responsável.
- RN011. A fila `/admin/fila-eventos` mostra Eventos pendentes, busca, detalhes e Agendas reais. A decisão Web usa o mesmo domínio da API e exige motivação para reprovar. O serviço verifica `eventos.aprovar` e revalida a conta e o estado do Evento sob lock antes de registrar decisão, trilha e notificação.

## Contratos Web

| Método e caminho | Operação |
| --- | --- |
| GET `/conta/vinculos-organizador/dados` | Listagem paginada do próprio ator, com nomes e operações permitidas. |
| POST `/conta/vinculos-organizador/convites` | ONG ativa envia `identificador`, e-mail ou usuário exato. Retorno 202 genérico. |
| POST `/conta/vinculos-organizador/{vinculo}/resposta` | Organizador responde seu convite com `aceitar` booleano. |
| DELETE `/conta/vinculos-organizador/{vinculo}` | ONG/organizador titular encerra vínculo ativo conforme as guardas existentes. |
| GET `/admin/fila-eventos/dados` | Página de Eventos pendentes. Aceita `q` para busca pelo título. |
| PATCH `/admin/fila-eventos/{evento}/decisao` | Moderador decide com `status_evento` (`ativo` ou `reprovado`) e `motivo` obrigatório para reprovar no Web. |

As páginas de entrada são `/ong/organizadores` e `/organizador`. Ambas usam o cliente HTTP compartilhado e sessão/CSRF. API Bearer continua em `routes/api.php`. Sem novas migrations nesta frente.

## Verificação e pendências

- O PHP passou na checagem de sintaxe e no Pint para os cinco arquivos F3. O Node passou na checagem do módulo de vínculos. `git diff --check` passou. `route:list` em modo integrado exibiu as quatro rotas.
- A regressão `OrganizadorWorkflowTest` cobre separação de atores, elegibilidade, retomada de convites, titularidade, atribuição e histórico. O teste novo exige MySQL descartável com `AJUDAE_WEB_AUTH=true` e cobre sessões tipadas, retorno genérico, exclusividade, privacidade da projeção e auditoria. A execução MySQL fica com o coordenador.
- Para homologação manual, teste enviar um convite a uma conta sintética, abrir a sessão de organizador, confirmar ou recusar, verificar estados e avisos nas duas sessões e testar a ação de encerramento com e sem Evento sob responsabilidade.
- O teste `Bloco81WebEventModerationTest`, também em modo integrado, cobre sessão, permissão, busca, validação, auditoria, notificação e conflito de decisão repetida.
- As demais filas administrativas, delegação em interface, cadastro e documentos da ONG ainda precisam de integração e homologação. Concorrência real em duas conexões, CSS e navegação em 360/768/1280 px ainda não têm evidência específica desta frente.
