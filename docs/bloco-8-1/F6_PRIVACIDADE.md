# F6 — Cancelamentos, notificações, LGPD e auditoria

Execução de 23/11/2026 sobre a branch `codex/baseline-ajudae-7-4`, HEAD `7bd046a4ad54411f662c1666d1d4d9000bcf963b`.

| Regra | Delta | Pendência |
|---|---|---|
| RN030 | Cancelamento de Agenda ou Evento registra ator, estado anterior, estado final e se o início já havia ocorrido. Motivo permanece no registro do cancelamento. Notificação explicita a ausência de penalização. As escritas ocorrem na transação existente e a repetição em estado terminal retorna 409. | Confirmar a classificação do cancelamento tardio antes do início. O registro usa o fato temporal observado. |
| RN031 | Alterações de data, horário ou local avisam os inscritos sobre a desistência sem penalização até o início da Agenda. Mudanças de status LGPD avisam o titular. A central Web carrega, filtra, pagina e marca avisos por sessão; a mudança de leitura é restrita ao destinatário. | Alertas de avaliação/finalização e antecedência do vencimento documental seguem pendentes nesta frente. Verificar emissores de validação e vínculos com F1/F3. |
| RN032 | Protocolos Web consultados e criados pelo titular via sessão e CSRF. A fila Bearer limita identidade, descrição e resposta aos responsáveis pelo caso ou Master. O Leitor mantém consulta aos metadados. Responder exige a capacidade específica e, para não Master, atribuição. | Fila administrativa Web, exportação e storage S3 sem homologação nesta frente. |
| RN033 | Auditoria do agendamento e cancelamento registra os estados e conserva o prazo anterior. Janela, bloqueios, revogação e restrição de administradores preservados. | Anonimização definitiva e expurgo físico dependem de autorização e revisão de retenção. |
| RN034 | Cancelamento e status LGPD registram metadados dentro da transação. Repetir a mesma decisão LGPD preserva o registro e evita novas notificações e logs. | Concorrência real e relações históricas precisam de verificação na suíte MySQL descartável. |

## Rotas

- Centrais existentes `/notificacoes` e `/ong/notificacoes`. GET `/conta/notificacoes/dados`, PATCH `/conta/notificacoes/{id}/lida` e PATCH `/conta/notificacoes/lidas`.
- Páginas existentes `/perfil/privacidade` e `/ong/privacidade`. GET e POST `/conta/privacidade/solicitacoes`.
- API Bearer mantém `/api/v1/notificacoes`, `/api/v1/solicitacoes-lgpd`, `/api/v1/minhas-solicitacoes-lgpd` e `/api/v1/admin/solicitacoes-lgpd`. O novo escopo da fila omite conteúdo privado de caso não atribuído. Um respondente assume um caso sem atribuição enviando `em_analise` antes de decidir.

## Arquivos

- `WebNotificationController.php`, `WebPrivacyRequestController.php`, `PrivacyRequests.php`, `SolicitacaoLgpdController.php`, `AccountDeletion.php` e `EventoOperationalService.php`.
- Chamadas localizadas nos controllers API de Agenda e Evento. Views `notificacoes/central.blade.php` e `privacidade/conta.blade.php`. Módulos `notifications.js` e `privacy-requests.js`.
- Regressões em `tests/Feature/Bloco81PrivacyNotificationsTest.php`. Bindings compartilhados em rotas e entrada Vite foram feitos pelo coordenador.

## Verificação

Executar `Bloco81PrivacyNotificationsTest` com `AJUDAE_WEB_AUTH=true`, `DB_CONNECTION=sqlite` e `DB_DATABASE=:memory:`. A classe cria somente o esquema mínimo em memória. A cobertura inclui titularidade cruzada Pessoa/ONG, resposta 401/403/404/422, leitura idempotente, atualização LGPD com escopo e redação da fila. O recorte usa dados sintéticos. Sem migrations, banco principal, commit, push ou deploy.

## Conferência manual

1. Acessar notificações com Pessoa e ONG em abas de sessão independentes. Aplicar filtros, paginar, marcar um ou todos os avisos. Repetir em 360, 768 e 1280 px, incluindo navegação por teclado.
2. Criar um pedido de privacidade em cada tipo de conta, consultar protocolo e simular resposta de um administrador atribuído em ambiente isolado. A resposta aparece somente na conta titular e na análise autorizada.
3. Alterar a localização ou horário, verificar o aviso explícito e cancelar uma Agenda com inscrições sintéticas. Repetir o cancelamento e conferir estado, trilha e ausência de notificações adicionais.
