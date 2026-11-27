# Correção do QA-06 — Ação de candidatura com Agenda indisponível — 27/11/2026

## Escopo

O [QA independente](QA_INDEPENDENTE_ENRICO_2026-11-27.md) reproduziu o link **Escolher uma Agenda** ativo quando a única Agenda estava lotada. O formulário seguinte bloqueava a seleção, com zero opções disponíveis. A correção usa os impedimentos existentes do contrato F2 e a regra de capacidade por Agenda. O PDF oficial estava indisponível no caminho indicado pelo projeto. O [catálogo Web](CATALOGO_WEB.md), o [contrato F2](CONTRATOS_F2_EVENTOS.md) e o resumo versionado RN013–RN015 forneceram as referências locais.

## Comportamento

- Em `GET /eventos/{id}`, o HTML inicial oferece o link somente a voluntário aprovado e operacional quando alguma Agenda está aberta a candidaturas. Sem Agenda aberta, mostra **Nenhuma Agenda disponível para candidatura.** e um botão desabilitado.
- A consulta pessoal existente, `GET /candidatura/{evento}/agendas`, atualiza a ação conforme as opções realmente selecionáveis para o voluntário. Uma Agenda pública com vaga pode estar indisponível para quem já se inscreveu. Se a consulta falhar, vale o estado inicial público.
- Quando existe uma opção selecionável, o link continua abrindo `GET /candidatura?evento={id}`. Horários, regras de gravação e validação de lotação continuam vindo dos mesmos serviços e rotas.

## Arquivos

- `resources/views/eventos/detalhe.blade.php` aplica a regra no HTML e usa o componente de botão existente.
- `resources/js/modules/managed-events.js` sincroniza o estado da ação com a projeção pessoal.
- `tests/Feature/Bloco9EventManagementTest.php` cobre a ação para visitante e para voluntário, antes e depois de esgotar a disponibilidade pública.

## Verificações

- PHPUnit integrado: 113 testes e 1.267 asserções, incluindo a regressão no HTML do detalhe. Modo demonstrativo: 9 testes e 778 asserções da classe `VisualTwoTest` com o manifesto Vite temporário.
- JavaScript: 35 testes focados passaram. Vite 8.4.0 compilou 38 módulos. Pint, `php -l`, `node --check` e `git diff --check` passaram.
- Chromium 158: no Evento lotado, o botão apareceu desabilitado e o link ficou fora do foco a 360, 768 e 1280 px. Com uma Agenda livre, Enter abriu o formulário com opção selecionável. Com impedimento pessoal em todas as opções, a ação ficou desabilitada. Duas respostas 503 simuladas preservaram os estados públicos, tanto com vaga quanto sem vaga. Visitante manteve o controle desabilitado.
- O primeiro ensaio no navegador atingiu um servidor anterior e recebeu o bundle antigo. O reteste usou porta exclusiva e confirmou o arquivo `identity-bwR4pFVH.js` antes de executar as verificações.

O banco efetivo foi conferido via Laravel e MySQL em `127.0.0.1:33429`, com a instância temporária `9e9f38a9-e9f9-11f1-b7ef-0250de5958d2`. A suíte usou o schema descartável `ajudae_qa9_audit_tests`. O Chromium usou os dados sintéticos da auditoria anterior no schema `ajudae_qa9_audit_web`. O `HEAD` local é `6a8aa17912eccc143dc6d6d670a820b2bdcdae08`. As alterações anteriores do checkout foram preservadas. O ZIP inclui o patch desta execução, calculado contra os três arquivos no início do trabalho.

A correção usa o contrato Web existente e dispensa alterações de backend, migrations ou dados de produção. Sem commit, push, merge ou deploy.

### Decisões para você responder

Nenhuma.
