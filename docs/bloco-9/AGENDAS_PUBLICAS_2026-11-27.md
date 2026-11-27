# Disponibilidade pública das Agendas — 27/11/2026

## Escopo

Esta execução fecha a pendência de consulta pública registrada no [reteste independente do Bloco 9](QA_INDEPENDENTE_ENRICO_VALIDACAO_QA_04_05_2026-11-26.md). A branch permaneceu em `feat/eventos-candidaturas`, HEAD `6a8aa17912eccc143dc6d6d670a820b2bdcdae08`, com as correções anteriores no checkout. A referência normativa usada foi o [catálogo Web de integração](CATALOGO_WEB.md), o contrato F2 e o resumo de regras versionado. O PDF oficial estava indisponível no caminho indicado.

## Comportamento

- `GET /eventos/{id}` exibe horários e vagas disponíveis por Agenda também para visitante, ONG, organizador e Pessoa pendente. As Agendas são renderizadas no HTML. O horário inclui indicação UTC no HTML inicial e usa o fuso do navegador com JavaScript.
- A disponibilidade é a capacidade da Agenda menos as inscrições que ocupam vaga, conforme o serviço existente. A página indica lotação, Agenda indisponível e prazo de candidatura encerrado. O total informativo do Evento não interfere no cálculo.
- A consulta de impedimentos pessoais continua em `GET /candidatura/{evento}/agendas`, com a autorização existente. O voluntário elegível vê seus impedimentos quando a consulta responde. Em falha dessa consulta, as informações públicas continuam visíveis. O envio de candidatura permanece em `POST /candidatura/{evento}/enviar`.
- Evento que sai de publicação deixa de ser consultável no detalhe. A projeção pública entrega horários e disponibilidade, sem cartas, motivos internos de cancelamento ou relações de Pessoas.

## Arquivos

| Área | Caminhos |
| --- | --- |
| Dados e renderização | `app/Http/Controllers/PublicEventPagesController.php`, `app/Services/CandidaturaService.php`, `resources/views/eventos/detalhe.blade.php` |
| Horários locais e consulta pessoal | `resources/js/modules/managed-events.js` |
| Regressões e contrato | `tests/Feature/Bloco9EventManagementTest.php`, `docs/bloco-9/CATALOGO_WEB.md` |

## Verificações

- PHPUnit, suíte Feature de integração selecionada: 113 testes aprovados e 1.260 asserções. A primeira execução detectou que a consulta do detalhe omitia `status_evento`. A consulta passou a selecioná-lo e a suíte inteira foi repetida com sucesso.
- JavaScript: 35 testes aprovados. Build Vite 8.2.1, Pint direcionado, `php -l`, `node --check` e `git diff --check` aprovados.
- HTTP local: Evento publicado apresentou as Agendas a visitante sem sessão. Evento aguardando publicação retornou 404. A consulta de impedimentos pessoais retornou 401 para visitante. Cartas e motivos internos de cancelamento ficaram restritos.
- Chromium: visitante a 360, 768 e 1280 px, além de ONG, organizador, Pessoa pendente e voluntário aprovado. Sem transbordamento horizontal nas larguras verificadas. A captura a 360 px foi inspecionada visualmente. As demais larguras foram verificadas pelo DOM.
- O voluntário aprovado recebeu o impedimento correspondente à candidatura existente. Uma resposta 503 simulada na consulta pessoal preservou os dados públicos.

A instância MySQL 8.4.3 usou `127.0.0.1:33429`, UUID `9e9f38a9-e9f9-11f1-b7ef-0250de5958d2` e diretório temporário. Os schemas `ajudae_qa9_public_tests` e `ajudae_qa9_public_web` eram novos e continham dados sintéticos. A aplicação foi exercitada localmente em modo Web integrado com PHP 8.5.9. S3 e Railway ficaram fora do teste.

## Preservação

O trabalho anterior de QA-04 e QA-05 foi preservado. O ZIP contém os arquivos tocados e um patch calculado contra o estado que existia antes desta execução. As rotas existentes foram reutilizadas. A implementação não inclui migration nova. Sem commit, push, merge ou deploy.

### Decisões para você responder

Nenhuma.
