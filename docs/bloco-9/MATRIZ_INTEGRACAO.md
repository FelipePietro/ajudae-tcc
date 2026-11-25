# Matriz de integração dos seis integrantes

Use os métodos, payloads, respostas e códigos da [referência Web F1–F6](CATALOGO_WEB.md), da [referência F2](CONTRATOS_F2_EVENTOS.md) e da [referência F4](CONTRATOS_PARTICIPACAO.md). As rotas abaixo são os pontos de consumo, com os detalhes operacionais nas referências vinculadas. Cada responsável deve manter os estados e componentes existentes e exercitar os erros em dados sintéticos. O [checklist](PRONTIDAO.md) registra os fluxos com dependências.

## Felipe — Identidade, Pessoa e documentos

- **Telas e arquivos** `cadastro.blade.php`, `auth/login-form.blade.php`, `auth/account.blade.php`, `perfil/area-privada.blade.php`, `perfil/partials/apadrinhamento.blade.php`, `components/account-wizard.blade.php`, `modules/pessoa-registration.js` e `modules/identity-navigation.js`.
- **Consumo** POST `/cadastro`, `/login`, `/logout`, `/conta/senha`. GET `/conta/pessoa/validacao` e `/conta/pessoa/antecedentes`. POST `/conta/pessoa/validacao/reenviar`, multipart `/conta/pessoa/documentos/{tipo}` e download `/conta/documentos/{documento}/download`. Use o grupo `/conta/pessoa/padrinhos` para código, pedido, vinculação e conclusão. O capítulo F1 detalha formulário, JSON e arquivo.
- **Dependências e divisão** Oliver recebe a mesma validação para revisão humana. João Pedro cuida dos campos de perfil e preferências. Pedro Henrique integra exclusão e privacidade. O fluxo de recuperação segue bloqueado até definição do canal de recuperação.
- **Aceite e cenários** Cadastro cria conta pendente e não inicia sessão. Confirme 422 por menoridade e duplicidade, subida de RG/antecedentes com 201, download do titular e negação para outra identidade. Exercite reenvio, vínculo de padrinho, troca de senha e sessão antiga revogada. Preserve o login tipado e a expiração.

## João Pedro — Perfis, preferências e gamificação

- **Telas e arquivos** `perfil/{editar-perfil,preferencias,index-perfil}.blade.php`, `ong/perfil_ong.blade.php`, `ranking/index.blade.php`, `certificados/{lista,verificacao}.blade.php`, `modules/{ranking,certificates}.js` e os componentes de perfil existentes.
- **Consumo** GET `/conta/pessoa/dados`, `/conta/pessoa/preferencias`, `/catalogos/{habilidades,recursos,causas}`. PATCH `/conta/pessoa/{id}/dados`; POST e DELETE de foto e itens de preferência. GET `/ranking/dados`, `/conta/pessoa/{pessoa}/confiabilidade`, `/meus-certificados/dados`, `/meus-certificados/{certificado}/pdf`, `/certificados/verificar/dados`; POST de emissão conforme F5. Perfis públicos já usam GET HTML.
- **Dependências e divisão** Oliver integra perfil editável institucional. Luiz Miguel libera as condições de encerramento, avaliação e monitoria de emissão. Felipe mantém conta e senha. Níveis/badges e extrato têm o recorte indicado em F5.
- **Aceite e cenários** Alteração persiste apenas para o titular. Verifique preferência duplicada, perfil pendente, ranking por cidade e projeção pública sem dados privados. Emita duas vezes o mesmo certificado elegível, confira mesmo id, rejeite download alheio e confirme consulta pública reduzida.

## Enrico — Eventos, Agendas e candidaturas

- **Telas e arquivos** `feed/index.blade.php`, `eventos/detalhe.blade.php`, `ong/{criar,editar,meus-eventos,candidatos}.blade.php`, `candidatura.blade.php`, `minhas-candidaturas.blade.php`, `components/{agenda-editor,agenda-applications,web-application-steps}.blade.php`, `events-demo.js`, `modules/{public-feed,application}.js`.
- **Consumo** GET/POST `/gestao/eventos`, GET/PATCH `/gestao/eventos/{evento}`, imagem e catálogos conforme F2. Agenda usa POST `/gestao/eventos/{evento}/agendas` e PATCH/DELETE `/gestao/agendas/{agenda}`. Utilize `/candidatura/{evento}/agendas`, `/candidatura/{evento}/enviar`, `/minhas-candidaturas/dados` e `/gestao/.../inscricoes` e seleção conforme F2/F4.
- **Dependências e divisão** Oliver revisa o Evento e designa responsáveis. Luiz Miguel opera após seleção. Compartilhem o documento F2 e o significado de estados e das IDs de Evento, Agenda e inscrição.
- **Aceite e cenários** Criar Evento com múltiplas Agendas e banner, manter estado pendente, liberar feed apenas após moderação, confirmar capacidade independente por Agenda, reanálise de alteração sensível e proibição de excluir última Agenda. Candidatar, selecionar a última vaga e desistir com carta restrita. Exibir erros `403`, `409` e `422` sem inferir aprovação pelo cliente.

## Oliver — ONG, organizadores e administração

- **Telas e arquivos** `ong/{cadastro_ong,editar_ong,area-cadastral,painel_ong}.blade.php`, `organizadores/{ong,workspace,solicitacao}.blade.php`, `admin/{cadastros-ong,candidatos,fila-eventos,upgrades-org,campanhas-xp}.blade.php`, `modules/{organizer-links,event-moderation}.js`. Reutilize os formulários prévios e a navegação contextual.
- **Consumo** POST multipart `/cadastro/ong`, GET/UPDATE `/conta/ong/.../dados`, download de identidade e imagem institucional. GET/PATCH `/admin/validacoes-ong/...`, fila e decisões de Pessoa/Evento/promoção, GET/POST `/conta/pessoa/solicitacao-organizador`, GET/POST/DELETE `/conta/vinculos-organizador/...`, PATCH `/conta/ong/eventos/{evento}/responsavel`. F3 define capacidades, campos e resposta.
- **Dependências e divisão** Use a mesma capacidade e o mesmo conjunto de estados das filas. Felipe mantém a origem dos documentos de Pessoa. Enrico cria o Evento. Pedro Henrique integra telas LGPD e log.
- **Aceite e cenários** Cadastro multipart e conta pendente, fila com download somente autorizado, aprovar e operar como ONG ativa. Exercite autorização de administrador sem capacidade, convite uniforme, aceite, designação em Evento da mesma ONG e rejeição após o início. Projete ações de acordo com o ator e o registro.

## Luiz Miguel — Participação, encerramento e QA integrado

- **Telas e arquivos** `participacoes/{pessoal,gestao}.blade.php`, `components/participation-agendas.blade.php`, `modules/participation.js`, páginas de gestão Evento/Agenda em coordenação com Enrico.
- **Consumo** `/gestao/inscricoes/{inscricao}/{presenca,monitoria,justificativa}`, `/gestao/eventos/{evento}/avaliacoes`, avaliação individual por Pessoa, fechamento de Agenda e Evento. GET `/minhas-participacoes/dados`, GET/POST `/minhas-participacoes/{evento}/avaliacao-ong`. F4 documenta payload, resposta e estado por operação.
- **Dependências e divisão** Enrico entrega inscrição e seleção. Oliver entrega designação e capacidade da ONG. Valide com João Pedro a elegibilidade de certificados, em especial monitoria estudantil.
- **Aceite e cenários** Exercite Agenda sem inscritos, presença/ausência, correção permitida, justificativa e reversão, presença estudantil e monitoria. Feche as Agendas, avalie voluntários uma vez e feche Evento. Envie avaliação da ONG após a finalização e teste o limite e a duplicidade. Compare ONG proprietária, designado válido, vínculo revogado e outro Evento.

## Pedro Henrique — Notificações, LGPD e auditoria

- **Telas e arquivos** `notificacoes/central.blade.php`, `privacidade/conta.blade.php`, `auth/account.blade.php`, `admin/{solicitacoes-lgpd,log-acoes}.blade.php`, `modules/{notifications,privacy-requests}.js`.
- **Consumo** GET `/conta/notificacoes/dados` e detalhe, PATCH leitura, DELETE por id; GET/POST `/conta/privacidade/solicitacoes`; GET `/admin/solicitacoes-lgpd/dados` e PATCH de decisão. POST/PATCH `/conta/exclusao` via formulário e GET `/admin/log-acoes/dados`. F6 descreve retorno, filtro e formato do protocolo.
- **Dependências e divisão** Felipe mantém Minha conta, sessão e guarda. Oliver mantém navegação e capacidades. Consulte Enrico e Luiz Miguel para gatilhos de notificações operacionais.
- **Aceite e cenários** Use duas contas com mesmo id numérico em tipos distintos. Confirme caixa isolada, resposta LGPD só por capacidade e atribuição, redução de dados do leitor, feedback idempotente de leitura e cancelamento de exclusão dentro de sete dias. O log de interface usa metadados e não replica conteúdo do caso.

### Arquivos compartilhados e limites

Coordenem alterações em `resources/js/core/http.js`, `resources/js/bootstrap/app.js`, `resources/js/{app,identity}.js`, `resources/views/layouts` e componentes de botões, modal, navegação e CSRF. Façam mudanças dos inicializadores sob controle de um responsável por integração. As tarefas individuais conectam os consumidores aos contratos, com Services, Policies, rotas, migrations e o cliente Bearer fora da rotina de edição. Divergências de contrato devem voltar ao coordenador com reprodução e referência do catálogo.
