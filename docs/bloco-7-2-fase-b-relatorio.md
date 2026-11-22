# Ajudae — Relatório técnico do Bloco 7.2

20/11/2026 · Fase B, implementação local e validação sem banco. A homologação permanece pendente.

## Entrega

A arquitetura A ganhou dois guards Blade, providers explícitos, um titular tipado por requisição e isolamento da API Sanctum. A área da conta verifica o guard, o tipo, a chave, a versão da credencial e a vigência da sessão. Uma identidade inválida ou ambígua encerra a sessão. O guard regenera o identificador no login. O logout POST invalida a sessão e renova o CSRF. A vigência é de 30 minutos ociosos e 12 horas absolutas. O cookie integrado é host-only, HttpOnly, SameSite Lax, com Secure forçado em produção. Lembrança de sessão está desabilitada. O sexto erro no mesmo par tipo/conta e IP em 15 minutos é bloqueado. O limite secundário ficou em 100 tentativas por IP na janela, sujeito a calibração operacional. Os dados de login ficam fora do flash.

O modo integrado disponibiliza login, logout, perfil próprio e operações da conta. A demonstração em local/testing continua disponível com seu markup, fixtures e scripts. O modo integrado exclui navegação e aliases de demonstração. /admin, /organizador, painéis e módulos operacionais Blade aguardam ligação protegida e revisão de navegação no escopo correspondente. Controles de operação já existem na API. A interface integrada reutiliza os componentes de conta e botões, com página de erro que funciona sem a navbar demonstrativa.

## Fronteiras e decisões de implementação

| Área | Mudança |
|---|---|
| Sessão e revogação | auth_version por ator, com migration aditiva incluída no delta e ainda sem aplicação. Troca voluntária confirma senha atual em ambos os canais. A mesma transação grava a senha, incrementa a versão e apaga os tokens Sanctum da identidade. Outros dispositivos perdem acesso na requisição seguinte. A autenticação e emissão de token usam o lock da conta para serializar troca e login. A reprovação definitiva de Pessoa revoga a identidade. |
| Bearer | Sanctum consulta apenas bearer. O middleware exige token do tipo persistido e ability compatível com o principal. O prazo de emissão continua inalterado. Logout web encerra apenas sua sessão e logout API revoga o token corrente. Rotas e formatos de login ordinário são preservados. |
| Exclusão reversível | Agenda em transação após checar Eventos não terminais, inscrições ativas, análise cadastral vigente, promoção, convites/vínculos de ONG e processos de apadrinhamento aplicáveis. O histórico terminal segue disponível segundo a política do domínio. Na espera, apenas status mínimo, cancelamento antes de deletar_em e logout. Agendar e cancelar revogam sessões/tokens existentes. Após o vencimento, o login normal e a reversão ficam fechados. Logs registram a ação e o prazo, quando aplicável. |
| LGPD | GET exige lgpd.visualizar. PATCH exige lgpd.responder, papel válido e revisão dentro da transação. Caso em análise com respondente existente fica sob o mesmo operador ou Master válido. A mudança de estado registra antes e depois. |
| Documentos e confiabilidade | Titular autorizado ou administrador com usuarios.validar, titular atribuído e análise vigente de até 24 h. A definição de finalidade usa os campos da revisão existente. O download e a consulta administrativa registram acesso com dados mínimos. Acesso administrativo fora desse fluxo, inclusive renovação/atendimento LGPD sem atribuição específica, fica fechado até definição do caso. |
| Evento e Agenda | Policy exige vínculo vigente, ONG ativa, escopo de titularidade e designação. A ONG proprietária ativa conserva gestão institucional. Seleção, presença, avaliação e fechamento usam o responsável operacional. Mutações centrais revalidam a Policy sob lock. O dono do Evento independente legado é responsável se a coluna de responsável estiver vazia. Desvinculação comum bloqueada enquanto houver Evento não terminal sob a responsabilidade. Atribuição auditada, antes do início, sem transferência automática. |

A troca de senha na API usa o campo adicional senha_atual apenas quando é enviada senha nova. Login e /me em exclusão pendente expõem somente o status e o prazo. Operações antes permitidas podem retornar 401, 403 ou 409 segundo os novos controles. A janela usa o limite estrito agora < deletar_em. O fluxo de recuperação pública por redefinição não tem rota ou prova de identidade de recuperação vigente neste checkout. A implementação disponível cobre troca voluntária e fornece a revogação transacional a ser acionada por uma futura redefinição verificada. Nenhum canal de recuperação ou envio foi ativado.

## Rotas locais e configuração

Base única [http://127.0.0.1:8000](http://127.0.0.1:8000). Os caminhos abaixo foram registrados no inventário. A porta observada já tinha processos escutando e a execução preservou esses processos. Para homologação autenticada, direcionar a porta 8000 ao ambiente isolado após aprovação e preparar assets válidos.

| Rota | Comportamento no modo integrado |
|---|---|
| GET /login e POST /login | Formulário real e autenticação com o tipo selecionado |
| GET /conta, /perfil, /ong/perfil | Estado do próprio titular. As duas rotas de perfil exigem o respectivo tipo |
| POST /conta/senha | Troca com senha atual e novo login |
| POST e PATCH /conta/exclusao | Agendamento com confirmação e cancelamento pelo titular |
| POST /logout | Saída da sessão com CSRF |

AJUDAE_WEB_AUTH seleciona o modo integrado e tem default false. AJUDAE_DEMO pode desligar a demonstração. Ela só registra rotas em local/testing com integração desativada. Requer sessão server-side e cache que compartilhe locks e rate limit entre trabalhadores. O modo integrado usa cookie separado e força política de sessão. Preparar schema e assets antes de ativar, revisar caches de rotas/configuração por ambiente e confirmar proxy TLS efetivo. Para reverter, fechar a superfície integrada e preservar as proteções API, ou manter fechadas as rotas afetadas. O schema aditivo pode permanecer. Nenhum rollback de banco foi executado.

## Banco descartável e verificações

O candidato usa MySQL 8.4.3 do Laragon, configuração explícita, loopback 127.0.0.1:33379, sem named pipe, shared memory, MySQL X ou leitura local de arquivos. O diretório de dados novo permaneceu vazio após mysqld --validate-config, saída 0. A porta não tinha listener na verificação. Isso comprova a separação da configuração e do diretório. A verificação de identidade da instância e dos grants exige um servidor inicializado. O nome de schema proposto é ajudae_72_descartavel_20261120. A execução parou antes da inicialização e do DDL. O banco ajudae_tcc_testing não foi consultado e seu estado continua indeterminado.

A matriz de testes distingue verificações em memória, testes de regressão do demonstrativo e casos que aguardam autorização MySQL. A primeira rodada detectou que a página de erro integrada ainda tentava acessar nomes de rotas demonstrativas removidas. O componente passou a usar somente as rotas registradas e os testes seguintes confirmaram a resposta 404 e o fluxo de login pelo kernel. Sem navegação real, capturas ou aprovação visual nesta execução. Sem commit, push ou deploy. AGENTS.md preservado. Uma atualização desse arquivo após validação manual pode registrar a arquitetura, o estado da migration, as rotas verificadas e as lacunas de 7.2.

## Fontes

Fase A A1–A6, autorização D1–D6, PDF de RN V1 consultado na Fase A, plano 6.5 e auditoria estática 7.1. Estado auditado em feat/ajustes-pre-deploy, HEAD 1c028b311edcff2869f1d299dbbff5924425b450. O manifesto do ZIP lista os arquivos e os hashes deste delta sobre o worktree preexistente.
