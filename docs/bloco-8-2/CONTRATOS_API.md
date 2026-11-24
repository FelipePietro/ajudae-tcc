# Bloco 8.2 — Contratos versionados

O [catálogo CSV](CATALOGO_API_V1.csv) reúne as 173 entradas `/api/v1` registradas no Laravel deste checkout. Cada linha indica o método registrado, caminho, origem de código com linha, middleware, autorização explícita visível, path, campos lidos, validação, forma de resposta, erros observados ou delegados, RN e módulo previsto. `GET|HEAD` e `PUT|PATCH` estão agrupados quando o router os registra numa única entrada. O CSV descreve o contrato de código. As execuções observadas constam do relatório de QA.

A coluna de validação contém trechos compactados de Form Requests e validações dos controllers. Quando uma operação delega as regras, o trecho identifica a chamada. A expressão de resposta aponta para o Resource, o paginador ou os campos efetivamente montados na ação. Para campos condicionais e autorização contextual, consultar o método e o Resource indicados. A coluna de erros reúne status extraídos ou esperados pela camada comum e pela rota. Serviços e regras por campo podem acrescentar condições. [Rotas de origem](../../routes/api.php) · [Guia de base](../guia-integracao-api.md)

## Sessão e Bearer

- No Web, os guards tipados distinguem Pessoa e ONG. Escritas Web usam a proteção CSRF do Laravel. O HTTP `core/http.js` já atende as páginas Web do 8.1.
- Na API, endpoints protegidos usam `Authorization: Bearer <token>`, `auth:sanctum` e `bearer.account`. `abilities:pessoa` e `abilities:ong` exigem a capacidade indicada. `ability:pessoa,ong` aceita um dos tipos, sujeito à autorização da ação. A UI precisa verificar a capacidade administrativa e a titularidade.
- O cliente futuro `api-client.js` tem configuração explícita para cada modo e testes isolados. A equipe ainda precisa escolher o ponto de consumo em cada módulo e validar a origem aprovada. A API não passa a aceitar a sessão Blade por meio do cliente. [Cliente HTTP](CLIENTE_HTTP.md)
- O padrão de sucesso é `{ success, data, message }`. Erros de API usam `success: false`, `message` e `errors` quando aplicável. Tratar `errors` por campo no 422. Não presumir que todos os erros trazem a mesma mensagem ou shape de corpo. 401, 403, 404 e 409 distinguem autenticação, autorização, ausência e conflito. 429 exige respeitar o limite. 419 pertence à proteção Web. Downloads PDF e arquivos, 204 e 304 têm tratamento próprio. [Envelope atual](../../app/Support/ApiResponse.php) · [Tratamento Laravel](../../bootstrap/app.php)

## F1 — Identidade, opções e documentos

- Cadastro de Pessoa em `POST /api/v1/pessoas/register` usa `PessoaRegistrationRequest`. O campo `cpf_pessoa` é normalizado e validado. `pfp_pessoa_link` é exigido no contrato API, e `codigo_padrinho` é opcional. O 201 devolve `data.user`, `data.token` e `data.token_type`. A conta começa pendente. Documentos de Pessoa seguem o fluxo privado separado.
- Cadastro de ONG em `POST /api/v1/ongs/register` exige dados da organização, endereço, responsável, termos, confirmação de senha, e campos string `pfp_ong_link` e `rg_responsavel_ong_link`. O 201 retorna `data.ong`, `token` e tipo `Bearer`. O campo de RG organizacional não transforma a rota em upload institucional privado.
- `/api/v1/pessoas/login` e `/api/v1/ongs/login` recebem seus próprios campos de login e senha. `/me` e `/logout` exigem Bearer do tipo correspondente. Não há rota versionada de recuperação pública identificada. Perfis públicos, atualização de titular, habilidades, recursos, causas e documentos estão nas linhas F1 do catálogo. [Cadastro de Pessoa e ONG](F1_CADASTRO.md)

## F2 — Evento, Agenda, candidaturas e vínculos

- O catálogo público `GET /api/v1/eventos` filtra por parâmetros como `categoria`, `ong`, `modalidade`, `data_inicio`, `data_fim`, `q` e `per_page`, conforme validação do controller. `POST /api/v1/eventos` exige ator operacional e devolve 201 antes de publicação. A criação de Agenda tem comando próprio. Localidade é condicional à modalidade.
- `POST /api/v1/agendas/{id}/inscricoes` recebe declaração estudantil e carta opcional de até 2.000 caracteres. Exige Pessoa voluntária aprovada, Evento ativo e Agenda elegível. A carta é privada. `PATCH /api/v1/inscricoes/{id}/status` decide a candidatura no escopo da Agenda, com capacidade transacional. `DELETE /api/v1/inscricoes/{id}` preserva o histórico de desistência.
- `POST /api/v1/ongs/me/convites-organizador` usa `pessoa_id`. Listagem, resposta, encerramento de vínculo e atribuição têm entradas próprias. A forma de busca da tela Web não estabelece contrato API por e-mail/login. [Diagnóstico dos Eventos](F2_EVENTOS.md)

## F3 — Administração

- A fila de Eventos está em `GET /api/v1/eventos/admin/pendentes`. `PATCH /api/v1/eventos/{id}/status` recebe `status_evento` com `ativo` ou `reprovado`. Autoriza `eventos.aprovar` e aplica decisão humana. O handler Bearer não ganha o requisito adicional do formulário Web de reprovação.
- A fila de validação Pessoa e as decisões de promoção usam os próprios namespaces `/api/v1/admin/validacoes-pessoa` e `/api/v1/admin/solicitacoes-organizador`. O catálogo distingue decisões reprovadas, etapas, permissões e ações homologáveis para ONG.
- Download privado de documento administrativo exige caso atribuído e análise válida. A presença de `admin` na rota ou capacidade de consulta não concede revisão, resposta ou acesso a todo registro. [Segurança](F5_SEGURANCA.md)

## F4 — Participação

- Presença, justificativa e monitoria pertencem à inscrição. A avaliação da ONG tem POST e consulta GET privados em `/api/v1/eventos/{id}/avaliacao-ong`. Apenas nota inteira de 1 a 5 no POST atual. A consulta do titular tem autorização própria e a janela de envio é calculada no servidor.
- Fechar Agenda ou Evento preserva a autoridade e restrições de estado. A monitoria pendente pode ser regularizada após a conclusão operacional, nos termos da D03. [Contratos F4 detalhados](CONTRATOS_F4_PARTICIPACAO.md)

## F5 — XP, confiabilidade, ranking e certificados

- A consolidação de XP e campanhas tem rotas próprias. Ranking público recebe cidade e paginação e devolve `data.cidade` e `data.ranking`. Nível ou extrato individual precisam de contrato específico antes de uso na UI.
- `POST /api/v1/eventos/{id}/certificado` emite para a Pessoa titular elegível. A consulta do registro do titular e a rota de PDF privado são distintas. A verificação pública `GET /api/v1/certificados/verificar/{codigo}` ainda retorna os nomes de participante e responsável no contrato legado. A versão Web 8.1 é minimizada e não define por si só os dados públicos da API.

## F6 — Cancelamento, notificações, exclusão e LGPD

- Cancelamento de Evento e Agenda usa comando e ator próprios. Notificações pela API pertencem ao destinatário e têm contratos separados de leitura, marcação e exclusão. Exclusão reversível usa os endpoints da entidade titular, com cancelamento próprio e regras de estado.
- Titular abre pedido em `POST /api/v1/solicitacoes-lgpd` e lê em `GET /api/v1/minhas-solicitacoes-lgpd`. A equipe deve conferir `PrivacyRequests::RULES` para tipo e descrição. Admin lista com capacidade de visualização e responde com capacidade própria e escopo de caso. Auditoria registra as transições no domínio. Qualquer acesso administrativo a logs deve usar a rota real e a política correspondente.

[Matriz para divisão do trabalho](MATRIZ_INTEGRACAO.md) · [Pendências e divergências](DECISOES_E_PENDENCIAS.md)
