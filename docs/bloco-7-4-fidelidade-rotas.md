# Matriz de navegação e autorização

A coluna de entrada descreve o 7.4 anterior. Todos os destinos novos de operação são GET informativos. Os métodos da API bearer continuam separados. Visitante recebe 302 para login nas rotas privadas, tipo incorreto ou permissão insuficiente recebe 403, e exclusão pendente redireciona para a conta. As opções de aparência na URL não alteram a autorização.

## Pessoa e organizador

| Item | Rota no 6.5 | Entrada 7.4 | Destino corrigido e autorização |
|---|---|---|---|
| Ajudae | início contextual | `inicio` | `/` público, 200 |
| Feed | `feed` | `feed` | `/feed` público, 200. Filtros existentes |
| Candidaturas | `candidaturas`, /minhas-candidaturas | /perfil#candidaturas | `/minhas-candidaturas`, apenas Pessoa voluntária, 200 de disponibilidade ou 403 |
| Meu perfil e avatar | `perfil`, seleção visual | /perfil | /perfil, titular Pessoa, 200 |
| Ranking e Ver ranking completo | /ranking | /feed#feed-ranking | /ranking público, 200 de disponibilidade |
| Notificações | `notificacoes`, /notificacoes | Ausente | /notificacoes, Pessoa, 200 de disponibilidade. Administração mantém o seu shell |
| Participações no perfil | `participacoes`, /minhas-participacoes | /perfil#participacoes | /minhas-participacoes, titular Pessoa, 200 de disponibilidade |
| Privacidade e dados | `perfil.privacidade`, /perfil/privacidade | /privacidade pública | /perfil/privacidade, titular Pessoa, 200. Retorno em /perfil e segurança/exclusão em /conta |
| Minha atuação | `organizador.workspace` | Mesma URL | /organizador, organizador aprovado, 200. Vínculo ativo avaliado pelo servidor |
| Meus eventos e Ver gestão de Eventos | `ong.eventos.index` no contexto demonstrativo de organizador | Ausente | /organizador/eventos, organizador aprovado, 200 de disponibilidade. A separação evita abrir o namespace institucional para uma Pessoa |
| Criar Evento e Candidaturas recebidas da atuação | Destinos demonstrativos de gestão | Ausentes | Criação independente indicada como indisponível no painel. Pessoa vinculada sem CTA de criação independente. Candidaturas operacionais aguardam F2/F3 |
| Segurança da conta e Sair | Ferramentas da conta | /conta e POST /logout | /conta com sessão tipada. POST /logout com CSRF, 302. API bearer exige token próprio |

## Sidebar ONG

Middleware `ResolveWebAccount:ong` em toda a área institucional. A guarda do controlador exige ONG ativa nos quatro GETs operacionais. Nada é executado ou persistido nesses destinos.

| Item | Nome / URI no 6.5 | Entrada 7.4 | Destino e estado corrigidos |
|---|---|---|---|
| Dashboard | ong.painel · /ong/painel | Mesmo | /ong/painel, ativa 200, pendente 403 |
| Explorar eventos | feed · /feed | Mesmo | /feed público 200 |
| Meus eventos | ong.eventos.index · /ong/eventos | #eventos-ong | /ong/eventos, 200 de disponibilidade |
| Candidatos | ong.candidatos · /ong/candidatos | #candidaturas-ong | /ong/candidatos, 200 de disponibilidade |
| Organizadores | ong.organizadores · /ong/organizadores | #organizadores-ong | /ong/organizadores, 200 de disponibilidade |
| Criar evento | ong.eventos.criar · /ong/eventos/criar | Ausente | /ong/eventos/criar, 200 de disponibilidade. POST 405 |
| Minha organização | ong.perfil · /ong/perfil | Mesmo | /ong/perfil, titular ONG ativa ou pendente, 200 |
| Notificações e sino móvel | ong.notificacoes · /ong/notificacoes | Ausente | /ong/notificacoes, titular ONG ativa ou pendente, 200 de disponibilidade |
| Configurações | ong.configuracoes · /ong/configuracoes | /conta | /ong/configuracoes, titular ONG ativa ou pendente, 200. Links para as páginas disponíveis de conta e privacidade |
| Privacidade e dados | ong.privacidade · /ong/privacidade | /privacidade pública pelo perfil | /ong/privacidade, titular ONG ativa ou pendente, 200. Retorno ao cadastro |
| Ajudae e Sair | Marca e ferramentas de conta | / e POST /logout | / público e POST /logout com CSRF |

## Sidebar Administração

Middleware `ResolveWebAccount:pessoa`, papel administrador real e capacidade correspondente calculada pelo servidor. Cada linha mantém o nome `admin.<área>` no modo integrado. Os GETs da coluna final que aguardam integração renderizam apenas a disponibilidade da própria área.

| Item | URI anterior 6.5 | Entrada 7.4 | URL e acesso corrigidos |
|---|---|---|---|
| Painel admin | /admin | Mesmo | /admin, papel e ao menos uma capacidade, 200 |
| Fila de eventos | /admin/fila-eventos | /admin#fila-eventos | /admin/fila-eventos · eventos.aprovar |
| Cadastros de ONG | /admin/cadastros-ong | /admin#cadastros-ong | /admin/cadastros-ong · ongs.validar |
| Promoções | /admin/upgrades-org | /admin#upgrades-org | /admin/upgrades-org · organizadores.validar |
| Validação de Pessoas | /admin/candidatos | /admin#candidatos | /admin/candidatos · usuarios.validar |
| Ocorrências | /admin/denuncias | Ausente | /admin/denuncias · usuarios.moderar |
| Solicitações de privacidade | /admin/solicitacoes-lgpd | Mesmo | /admin/solicitacoes-lgpd · lgpd.visualizar |
| Resposta LGPD | Área de fila | /admin#responder-lgpd | /admin/responder-lgpd · lgpd.responder. Uma pessoa com só essa capacidade recebe 403 na fila de leitura |
| Usuários | /admin/usuarios | /admin#usuarios | /admin/usuarios · usuarios.moderar, conforme o mapa do 7.4 integrado |
| Relatórios | /admin/relatorios | Ausente | /admin/relatorios · admin.logs.visualizar |
| Log de ações | /admin/log-acoes | /admin#log-acoes | /admin/log-acoes · admin.logs.visualizar |
| Configurações | /admin/configuracoes | Ausente | /admin/configuracoes · Master válido para a página informativa. Nenhuma regra de configuração ou comando foi criado |
| Campanhas de XP | Sem página própria no menu 6.5 | /admin#campanhas-xp | /admin/campanhas-xp · xp.campanhas.gerenciar |
| Notificações | /notificacoes | Ausente | /notificacoes, titular Pessoa. Quando administrador, mostra o shell e item ativo correspondente |
| Conta, marca e Sair | Destinos contextuais | /, /feed, /perfil, /conta, POST /logout | Mesmos destinos e guardas |

A API de LGPD continua exigindo `lgpd.visualizar` ou `lgpd.responder` conforme a operação e sua autorização por registro. As páginas informativas não carregam dados de casos, documentos ou registros administrativos.
