# Ajudaê — Relatório de correções do Bloco 8.2

O trabalho partiu da branch `codex/baseline-ajudae-7-4`, HEAD `7bd046a4ad54411f662c1666d1d4d9000bcf963b`, com o delta 8.1 e outros arquivos locais já presentes. O anexo integral e as regras V1 orientaram o diagnóstico. O estágio prepara a integração da equipe e mantém em uso os consumidores Web existentes.

| Item | Resultado | Evidência |
| --- | --- | --- |
| P0.1 · Cadastro de Pessoa | **APROVADO** | Fluxo reproduzido antes. Bundle atualizado retestado em 360, 768 e 1280 px. Confirmação em 1280 após HTTP 201 do POST Web existente em SQLite descartável. |
| P0.2 · Cadastro de ONG | **BLOQUEADO** | GET público 200 com indisponibilidade e ausência do wizard integrado. A API de cadastro existe. Documentos institucionais e o mapeamento seguro do formulário continuam como dependências. |
| P0.3 · Criar Evento | **BLOQUEADO** | ONG ativa autenticada obteve GET 200 no editor, com aviso e botão desabilitado. As rotas Bearer de Evento e Agenda existem. A tela depende do fluxo definitivo de envio. |
| P0.4 · Candidatura | **BLOQUEADO** | A jornada de homologação depende de Evento publicado, Agenda elegível e Pessoa aprovada. O contrato e as regressões automatizadas foram revisados e executados separadamente. |

## P0.1 — Reprodução, causa e correção

1. Abra `/cadastro` em modo integrado com o manifesto antigo. Preencha os campos válidos da primeira etapa e acione **Continuar**. No Edge, a etapa permanece em **Passo 1 de 5** apesar da validação nativa passar.
2. Confirme o bundle usado. O manifesto local apontava para `identity-d20K-wOU.js`, sem o inicializador de `data-web-registration`. O `public/hot` remanescente também desviava os assets para um Vite IPv6 de loopback indisponível. O teste de controle com o build contemporâneo percorreu as etapas com as mesmas views.
3. Confira a nova inicialização. `app.js` e `identity.js` passam pelo bootstrap comum e mantêm entradas Vite distintas. Os handlers de administração demonstrativa e navegação Web ficam em módulos próprios. O formulário, seus seletores e validações continuam nos componentes existentes.
4. Faça o reteste com `identity-BhOLo7AU.js`. O QA independente percorreu as cinco etapas em três larguras, conferiu os bloqueios de dados obrigatórios e chegou à confirmação no cenário com banco descartável.

O build testado foi copiado para `public/build` local. O manifesto novo tem SHA-256 `4bc761153d8ad8fcffea109cef6520bd9c299830ccf7e17115675f1fef813c23`. A geração anterior e o indicador antigo foram preservados com os nomes `public-build-antigo` e `public-hot-antigo` em `%TEMP%\ajudae-b82-1504a3c6fb`. O indicador só foi retirado da origem que o Laravel consulta depois de confirmar o hash diagnóstico e a ausência de listener na porta 5173. O bundle segue a ordem de inicialização efetivamente testada, incluindo os handlers de cache da sessão. O ZIP contém fontes e evidências, e o build gerado continua sendo um artefato local.

[Diagnóstico F1](F1_CADASTRO.md) · [Inventário JS](INVENTARIO_JS.md) · [QA independente](QA_INDEPENDENTE.md)

## P0.2 — Cadastro de ONG

Abra `/cadastro/ong` como visitante. `routes/identity.php` retorna a view `auth/registration-unavailable.blade.php`. O wizard anterior em `ong/cadastro_ong.blade.php` pertence ao modo visual. `OngController::register` cria a conta aguardando aprovação no canal Bearer e exige, entre outros campos, referências de foto e de RG do responsável. Falta implementar e homologar o envio definitivo da interface e o tratamento seguro do documento institucional. A próxima ação fica com Identidade, Administração e Segurança. [Diagnóstico e dependências](F1_CADASTRO.md)

## P0.3 — Criação de Evento

Em sessão de ONG ativa, abra `/ong/eventos/criar`. A view `ong/criar.blade.php` renderiza `fieldset disabled` em modo integrado e mantém o aviso. A criação, Agendas, imagem e aprovação seguem comandos distintos. O editor da demonstração suporta revisão visual. Para habilitar envio real, Eventos precisa mapear as etapas existentes, as permissões e as respostas de conflito do canal API definitivo. [Diagnóstico e contratos](F2_EVENTOS.md)

## P0.4 — Candidatura

O código existente cobre quatro etapas, carta opcional privada, vagas e seleção por Agenda, janela e elegibilidade no servidor. A auditoria corrigiu a documentação do 409 para ambas as rotas de candidatura. As suites de Evento e inscrição passaram em MySQL descartável com fixtures próprias. O resultado do ambiente de homologação segue pendente até a existência de um cenário publicado e autorizado. [Diagnóstico e matriz](F2_EVENTOS.md) · [Catálogo versionado](CONTRATOS_API.md)

## Verificações executadas

| Verificação | Resultado |
| --- | --- |
| Backend em MySQL descartável | 104 testes aprovados, 1.064 asserções. Incluiu Agenda, Evento, presença, avaliação, documentos, autorização, autenticação e organizadores. O runner verificou o schema, o UUID e o diretório de dados antes de rodar. A instância descartável foi encerrada. |
| Fronteiras de autenticação, navegação e privacidade em SQLite em memória | 30 testes aprovados, 717 asserções no reteste. A primeira execução aprovou 28 de 30. Foram atualizadas as expectativas de `IdentityBoundaryTest` para as rotas integradas e os estados dos usuários sintéticos. Os 19 testes de recurso validados na rodada independente também compõem este conjunto. |
| CSRF em sessão sintética | Quatro casos aprovados. POST sem token ou origem retornou 419, PATCH com token retornou 204, POST de mesma origem retornou 204 e POST entre sites sem token retornou 419. |
| JavaScript | 95 testes aprovados, incluindo 25 do cliente HTTP. `node --check` aprovado nos arquivos JavaScript alterados. |
| Build e templates | Vite compilou 37 módulos. Blade compilou 192 arquivos em cache isolado. `git diff --check` passou. |
| Edge e catálogo | Seis casos executados por QA independente, com capturas e resultado por P0. O QA comparou 173 entradas de método e URI com o runtime e confirmou a atualização dos dois contratos de candidatura. |

A consulta pública legada de certificados na API ainda devolve nomes. A revisão registrou a divergência em relação à projeção pública Web para análise antes da integração. O S3 externo permanece pendente de homologação.

[QA independente](QA_INDEPENDENTE.md) · [Revisão de segurança](F5_SEGURANCA.md) · [Checklist de prontidão](CHECKLIST_PRONTIDAO.md) · [Decisões e pendências](DECISOES_E_PENDENCIAS.md)

Não houve commit, push, deploy, alteração do backend da aplicação ou operação no banco principal. O cliente HTTP preparado nesta etapa é usado apenas por seus testes isolados. A homologação do frontend definitivo continua pendente.
