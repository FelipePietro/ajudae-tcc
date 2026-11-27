# QA independente do Bloco 8.2

## Escopo e ambiente

Executei o roteiro local sem editar o código da aplicação. Usei Edge 158.0.4370.33 por Playwright, servidor PHP em loopback, SQLite descartável de arquivo com schema de teste, sessão de arquivo isolada e contas sintéticas. Os testes Laravel independentes usaram SQLite em memória. O navegador permitiu somente os dois endereços locais necessários à verificação do build e do Vite. A cópia de baseline incluiu as views e os assets encontrados antes das edições da frente de cadastro. A versão de referência e o estado modificado do checkout foram inspecionados antes dos testes.

## P0.1 Cadastro de Pessoa

1. Abra `/cadastro` com o bundle antigo `identity-d20K-wOU.js`.
2. Preencha os cinco campos obrigatórios de Identificação com valores de teste válidos.
3. Clique em **Continuar**. O Edge confirmou zero campos inválidos pela validação nativa e o fluxo permaneceu em **Passo 1 de 5**.
4. Repita com `identity-pEe2wFsW.js`. As mesmas views e a mesma entrada percorreram os cinco passos até a revisão. A compilação Vite das fontes de entrada gerou o mesmo nome de asset do build 8.1 usado como controle. A revisão ocultou identificadores e senhas e o navegador não registrou exceções JavaScript de página.

O bundle antigo não contém os seletores do inicializador integrado de cadastro. A sondagem do caminho inteiramente servido por `public/hot` expirou durante a navegação com o registro antigo apontando para `http://[::1]:5173`. A sondagem isolada com manifest foi conclusiva para reproduzir o clique sem ação. Os resultados distinguem o defeito dos artefatos locais de problemas de validação de formulário.

[Reprodução original](evidencias/qa/browser-antes.json) · [Captura original](evidencias/qa/p01-baseline-bundle-antigo.png) · [Controle com bundle 8.1](evidencias/qa/browser-controle.json)

### Reteste com o build final

Comparei SHA-256 do manifesto servido pelo harness ao manifesto do build final da coordenação. Ambos tinham hash `4bc761153d8ad8fcffea109cef6520bd9c299830ccf7e17115675f1fef813c23`. No Edge, clicar com campo obrigatório vazio deixou a Pessoa na primeira etapa e mostrou o erro. Com os campos válidos, percorri as cinco etapas em 360, 768 e 1280 px, sem transbordamento horizontal. O resumo não apresentou senha nem CPF. Em 1280 px, aceitei os termos, observei HTTP 201 na rota Web já existente `POST /cadastro` e confirmei visualmente **Conta criada**. O único cadastro feito neste ensaio ficou na fixture SQLite descartável.

Inspecionei as capturas das revisões e da confirmação. O cabeçalho, o indicador de etapas, o formulário e as ações do wizard estão presentes. O reteste comprova o build final servido localmente. A promoção desse mesmo conjunto de assets ao checkout de uso normal é verificada separadamente pela coordenação.

[Reteste de navegador](evidencias/qa/browser-depois.json) · [Revisão 360 px](evidencias/qa/p01-reteste-revisao-360.png) · [Revisão 768 px](evidencias/qa/p01-reteste-revisao-768.png) · [Confirmação 1280 px](evidencias/qa/p01-reteste-conclusao-1280.png)

## P0.2 Cadastro de ONG

Abri `/cadastro/ong` como visitante e observei HTTP 200, aviso de indisponibilidade e ausência do wizard. A rota integrada em `routes/identity.php` direciona a `resources/views/auth/registration-unavailable.blade.php`. A versão demonstrativa tem passos em `resources/views/ong/cadastro_ong.blade.php`, sem persistência. `POST /api/v1/ongs/register` existe em `OngController::register` e trata cadastro, documentos informados e termos no contrato Bearer. Para disponibilizar a interface, a equipe de identidade precisa ligar o wizard e as validações ao contrato definido de conta, upload e aprovação. O reteste com o build final repetiu o comportamento observado. [Captura original](evidencias/qa/p02-cadastro-ong-antes.png) · [Captura final](evidencias/qa/p02-cadastro-ong-depois.png)

## P0.3 Criação de Evento

Autentiquei uma ONG ativa sintética pelo formulário Web e abri `/ong/eventos/criar`. A página retornou HTTP 200, exibiu o aviso de indisponibilidade e o botão **Enviar para aprovação** estava desabilitado. O tratamento de `ong.eventos.criar` em `SessionPagesController::institutionalArea` exige ONG ativa e renderiza `resources/views/ong/criar.blade.php`, cujo modo integrado coloca o editor em `fieldset disabled`. O editor anterior suporta a revisão visual. A criação real usa `POST /api/v1/eventos` em `EventoController::store`, Agendas usam endpoints próprios e a publicação exige avaliação administrativa.

A frente futura de Eventos precisa conectar os dados do editor, imagem, Agendas e estados de aprovação aos endpoints reais. O editor permanece disponível para consulta com a submissão bloqueada. Na mesma sessão, `GET /api/v1/ongs/me` retornou 401 sem Bearer e `GET /perfil` retornou 403 para a identidade ONG. O build final manteve esses resultados. [Evidência de navegador](evidencias/qa/browser-controle.json) · [Captura do editor original](evidencias/qa/p03-criar-evento-controle.png) · [Captura final](evidencias/qa/p03-criar-evento-depois.png)

## P0.4 Candidatura

O contrato existente exige Pessoa voluntária aprovada, Evento publicado, Agenda ativa com vaga e janela superior a uma hora. `CandidaturaService` valida a carta opcional privada com limite de 2.000 caracteres, usa a ocupação da própria Agenda e impede inscrição duplicada. O contrato documentado em `POST /api/v1/agendas/{id}/inscricoes` e no alias `/inscrever` deve incluir HTTP 409 em conflitos. A suíte `IntegratedNavigationTest` verificou o catálogo, acesso à candidatura e contextos usando fixtures efêmeras. A jornada real segue dependente de Evento publicado com Agenda, vaga e ator de homologação autorizado. A preparação desse cenário pertence à frente de Eventos.

## Regressões e catálogo

- Executei `IntegratedNavigationTest|Bloco81PrivacyNotificationsTest` em SQLite em memória. Resultado de 19 testes e 597 assertivas, sem falhas, erros ou ignorados. Inclui guardas de Pessoa e ONG, escopos administrativos, expiração, invalidação de identidade, privacidade e notificações.
- Executei `node --test` nos 14 arquivos `.test.mjs` de `tests/Frontend`, com ambiente de demonstração isolado. Resultado de 95 testes aprovados, sem falhas ou ignorados, incluindo os testes isolados do cliente HTTP proposto.
- No bundle final, uma Pessoa aprovada sintética entrou pelo formulário Web, consultou `/perfil` e viu o catálogo vazio em `/feed` com HTTP 200. Aquela sessão recebeu 403 em `/ong/perfil` e 401 em `/api/v1/pessoas/me` sem Bearer. O runner de navegador final concluiu seis casos, sem falhas de assertiva ou exceções JavaScript de página. [Evidência de navegador](evidencias/qa/browser-depois.json)
- Comparei método e URI das 173 rotas retornadas por `artisan route:list --path=api/v1 --json` com as 173 entradas CSV. Não houve ausências, linhas extras ou duplicatas. Revisei semanticamente por amostragem, com divergência de erro de candidatura encaminhada ao coordenador. Esta comparação não atesta todos os payloads. [Contagem e diferenças](evidencias/qa/catalogo-runtime.json)

## Classificação final

| Caso | Classificação | Resultado ou ação restante |
| --- | --- | --- |
| P0.1 Cadastro de Pessoa | **APROVADO** | Build final reproduziu cinco passos, rejeição de campo obrigatório vazio e confirmação após HTTP 201 em fixture descartável. |
| P0.2 Cadastro de ONG | **BLOQUEADO** | O wizard integrado não está ligado à API de cadastro. Frente de identidade deve preparar contrato com upload e aprovação. |
| P0.3 Criação de Evento | **BLOQUEADO** | ONG ativa abriu o formulário, cuja submissão integrada está indisponível. Frente de Eventos deve conectar editor, Agendas, imagem e aprovação conforme a API real. |
| P0.4 Candidatura de ponta a ponta | **BLOQUEADO** | Depende de Evento publicado, Agenda elegível, vaga e ator de teste autorizados. O recorte funcional com fixtures sintéticas testa as guardas de acesso e catálogo, sem substituir a sessão de homologação. |
| Regressões de sessão, notificações e privacidade | **APROVADO** | SQLite integrado com 19 testes e verificações de isolamento em navegador. |
| Regressões JavaScript | **APROVADO** | 95 testes, incluindo o cliente futuro isolado. |
| Catálogo de rotas `/api/v1` por método e URI | **APROVADO** | 173 entradas de cada lado. A coordenação incorporou o 409 no contrato de candidatura após o apontamento. |
| Validação exaustiva de payload de 173 endpoints | **NÃO EXECUTADO** | O review de semântica desta frente foi amostral. |

## Limites desta rodada

As primeiras tentativas reproduziram P0.1 e P0.2 e esbarraram no tempo do build nos demais cenários. O segundo ensaio corrigiu o setup e reproduziu P0.3. Nas evidências, `ERR_FAILED` de recursos pode refletir fontes externas bloqueadas pelo harness. Os campos `networkErrors` enumeram as falhas locais e `jsErrors` as exceções JavaScript de página.

A auditoria de contratos registra divergências de redação do novo anexo para D01, D02 e D04 em relação às decisões aprovadas e à implementação 8.1. Esses casos não autorizam ampliar o escopo do cliente futuro. Produção, arquitetura de autenticação real, armazenamento externo e publicação ficaram fora da execução. As demais jornadas de presença, monitoria, certificado, ranking e recuperação de senha no navegador permanecem **NÃO EXECUTADAS** nesta rodada independente.
