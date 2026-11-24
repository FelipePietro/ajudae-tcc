# F2 — Eventos, Agendas e candidaturas

O backend aplica capacidade por Agenda, prazo de candidatura, elegibilidade e duplicidade no serviço usado por Web e API. A seleção existente usa locks e responde a conflitos de estado e lotação. A RN012 e a regra de total informativo do Evento foram reconciliadas na documentação. Durante a reanálise, candidatura e seleção ficam bloqueadas. A desistência do titular conserva o histórico.

`2026_11_23_081200_add_carta_apresentacao_to_inscricao.php` adiciona uma coluna para a carta facultativa, limitada a 2.000 caracteres e com cast criptografado. O Resource entrega a carta ao titular ou ao responsável operacional autorizado. Consultas públicas e notificações não levam o texto.

- `GET /candidatura?evento={id}` mantém o stepper. A leitura de Agendas e o envio usam `GET /candidatura/{evento}/agendas` e `POST /candidatura/{evento}/enviar`. A tela só confirma depois do HTTP 201 e oferece a escolha de outra Agenda.
- `GET /minhas-candidaturas/dados` pagina o histórico do titular. `DELETE /minhas-candidaturas/{inscricao}` usa o mesmo serviço da API e exige sessão e CSRF.
- O controlador de candidatura valida `agenda_id` no Evento indicado e mantém `declarou_estudante` na inscrição. Alteração de titularidade, lotação ou publicação entre leitura e escrita é verificada no servidor.

A gestão Web de Eventos, Agendas, seleção e filas administrativas permanece parcial. O catálogo Blade continua usando os filtros já integrados no 7.5. As evidências de persistência e a jornada mista estão no relatório consolidado.
