# Bloco 7.5 — exclusão de contas administrativas

22/11/2026. Correção incremental sobre o Bloco 7.5, na branch codex/baseline-ajudae-7-4 e no HEAD 7bd046a4ad54411f662c1666d1d4d9000bcf963b. [Segregação administrativa anterior](bloco-7-5-correcao-autorizacao-admin.md)

## Alterações

O papel administrativo bloqueia a solicitação de exclusão em todas as combinações de permissões, incluindo Leitor, Respondente e Master. O formulário de /conta usa a mesma regra da autorização. A Web recusa até uma requisição sem confirmação antes de validar o formulário. O serviço recarrega a identidade sob bloqueio transacional e verifica o papel antes de gravar o agendamento, revogar credenciais ou registrar a ação. As rotas da API de Pessoa e ONG já chamam esse serviço.

| Recurso | Administrador | Pessoa voluntária, organizador e ONG |
|---|---|---|
| GET /conta | Segurança, situação e alteração de senha. O formulário de solicitação fica ausente | Formulário disponível quando a conta está fora de exclusão pendente |
| POST /conta/exclusao e DELETE /api/v1/pessoas/{id} | 403 ao tentar iniciar com a própria identidade operacional. Query e corpo não mudam o papel | O titular elegível segue a validação de confirmação Web e as verificações de processos ativos |
| DELETE /api/v1/ongs/{id} | A rota exige a identidade institucional titular | O titular elegível continua pelo mesmo serviço |
| Cancelamento de pedido já existente | Uma pendência anterior ainda pode ser cancelada dentro do prazo | Janela de sete dias, bloqueios, revogação e eventos de auditoria seguem as regras existentes |

O bloqueio não cria um evento de agendamento. O histórico existente foi mantido. O teste da API também cobre identidade carregada com papel desatualizado, pois o papel atual na conta bloqueada prevalece.

## Arquivos

- [AccountAccess.php](../app/Support/AccountAccess.php) define a regra por papel.
- [AccountDeletion.php](../app/Services/AccountDeletion.php) aplica a regra à conta obtida sob bloqueio.
- [SessionAccountController.php](../app/Http/Controllers/SessionAccountController.php) autoriza o POST e fornece a disponibilidade para a view.
- [account.blade.php](../resources/views/auth/account.blade.php) exibe o formulário apenas quando permitido.
- [IntegratedNavigationTest.php](../tests/Feature/IntegratedNavigationTest.php) verifica o bloqueio, a preservação do histórico e os fluxos legítimos de solicitação e cancelamento.

## Checklist manual

1. Com contas sintéticas de Leitor, Respondente e Master, abrir [Minha conta](http://localhost:8000/conta?contexto=pessoa&papel=voluntario). Conferir que **Alterar senha** aparece e que **Excluir conta** e **Solicitar exclusão** estão ausentes.
2. Em ambiente isolado, enviar o POST Web autenticado com CSRF e o DELETE para a API com bearer da própria conta administrativa. Esperar 403 também com papel de voluntário no corpo e na URL. Conferir a ausência de novo agendamento e que a sessão segue válida.
3. Com Pessoa e ONG elegíveis em base descartável, abrir /conta, iniciar a solicitação, autenticar novamente e cancelá-la dentro de sete dias. Conferir o estado da conta e a trilha de solicitação e cancelamento.
4. Se houver uma pendência administrativa sintética anterior, conferir que /conta oferece o cancelamento. A solicitação de um novo pedido deve continuar bloqueada.

## Verificação

- IntegratedNavigationTest em SQLite em memória. 16 testes e 529 assertivas passaram. Inclui UI e POST Web dos três perfis, DELETE via bearer, manipulação de parâmetros, papel desatualizado, preservação da auditoria e solicitação e cancelamento de Pessoa e ONG com prazo e revogação.
- Pint, sintaxe dos PHP alterados, compilação Blade isolada e git diff --check passaram.
- Edge/Playwright em SQLite isolado. Dez combinações de perfil e largura em 360 e 1280 px passaram, com alteração de senha visível e disponibilidade do formulário conforme a identidade. Sem erros JavaScript nem transbordamento horizontal. Evidência de interação, sem capturas. [Resultado do navegador](evidencias/bloco-7-5-exclusao-contas-administrativas.json)
- A homologação manual final e a suíte completa de backend ficaram fora desta verificação.

O escopo permaneceu no Bloco 7.5. Não houve migrations, alterações em dados persistidos do usuário, commit, push ou deploy.
