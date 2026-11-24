# Bloco 8.2 — Inventário JavaScript

## Estrutura na entrada

O Vite entregava duas entradas escolhidas pelas views Blade. `resources/js/app.js` carregava scripts legados e declarava diretamente os controles de filtro, busca e abas administrativas. `resources/js/identity.js` registrava o próprio listener de DOM e agrupava navegação privada, inicializadores das funções Web e proteção de restauração de sessão. Os módulos usavam o marcador da tela para decidir quais controles inicializar.

No baseline observado pelo QA, o marcador de desenvolvimento apontava para `[::1]:5173` sem servidor acessível. Com a cópia do manifest antigo, a view atual de cadastro carregava `identity-d20K-wOU.js`. O bundle anterior ao componente Web vigente deixou o botão Continuar sem o inicializador correspondente. [Diagnóstico de cadastro](F1_CADASTRO.md)

## Inicialização após a organização

| Arquivo | Responsabilidade |
| --- | --- |
| `resources/js/app.js` | Entrada Vite do legado. Importa módulos demonstrativos e despacha os controles legados de administração pelo inicializador comum. |
| `resources/js/identity.js` | Entrada Vite Web. Registra a proteção de restauração de sessão e despacha navegação e inicializadores das telas Web na ordem existente. |
| `resources/js/bootstrap/app.js` | Ponto comum. Registra uma execução por modo, agenda o callback em `DOMContentLoaded` enquanto o documento carrega e inicializa imediatamente com o DOM disponível. |
| `resources/js/modules/demo-admin-controls.js` | Chips, atalhos de filas, busca e links inertes já usados pelo legado. A lógica e os seletores vieram da entrada original. |
| `resources/js/modules/identity-navigation.js` | Estado das abas e seções por URL. Um painel ausente não interrompe a montagem dos demais módulos Web. |

As chaves de entrada Vite e as chamadas `@vite` foram preservadas. A seleção do bundle segue as views existentes. Os módulos Web continuam reunidos no bundle Web e inicializam controles apenas quando seus pontos de montagem estão presentes. Os módulos legados com registro próprio de DOM conservam o mecanismo existente. O inicializador comum não importa entradas, módulos de tela ou clientes HTTP.

## Módulos Web por domínio

| Frente | Módulo | Ponto de montagem e responsabilidade |
| --- | --- | --- |
| F1 | `pessoa-registration.js` | `[data-web-registration]`. Etapas, validações anteriores ao envio, revisão e conclusão do cadastro Web existente. |
| F1 · transversal | `identity-navigation.js` | `[data-own-profile]` e `[data-panel-section-link]`. Ativa abas ou seções existentes e atualiza foco e estado da navegação. |
| F2 | `public-feed.js` | `[data-public-feed-filters]`. Filtros da listagem pública. |
| F2 | `application.js` | `[data-web-application]` e `[data-application-history]`. Agendas, candidatura e ações de histórico já conectadas no Web. |
| F2/F3 | `organizer-links.js` | `[data-web-organizer-links]`. Vínculos e convites Web existentes. |
| F3 | `event-moderation.js` | `[data-event-moderation]`. Moderação de Evento já presente na entrada Web. |
| F4 | `participation.js` | `[data-web-participation]`. Histórico e avaliação Web. |
| F5 | `ranking.js` | `[data-web-ranking]`. Filtros e paginação da classificação. |
| F5 | `certificates.js` | `[data-web-certificates]` e `[data-web-certificate-verification]`. Histórico, emissão, PDF e consulta existentes. |
| F6 | `notifications.js` | `[data-identity-inbox]`. Leitura e marcação das notificações Web. |
| F6 | `privacy-requests.js` | `[data-identity-privacy]`. Solicitações do titular e seus estados. |

Os módulos desta tabela residem em `resources/js/modules`. Os módulos Web que já consultam o servidor importam `core/http.js`, com sessão/CSRF e mesma origem. O filtro público e a navegação usam mecanismos próprios. O cliente futuro `core/api-client.js` permanece isolado e é importado pela sua suíte. [Cliente futuro](CLIENTE_HTTP.md)

## Módulos e auxiliares legados

| Arquivo em `resources/js` | Responsabilidade |
| --- | --- |
| `account-demo.js`, `sponsorship-demo.js` | Cadastro, conta e apadrinhamento das jornadas demonstrativas. |
| `events-demo.js`, `organizer-demo.js` | Eventos, Agendas, candidatura e atuação/convites nas jornadas demonstrativas. |
| `admin-demo.js`, `modules/demo-admin-controls.js` | Filas, decisões simuladas e controles de navegação legados. |
| `participation-demo.js`, `participation-xp.js` | Participação demonstrativa e operações auxiliares de XP. |
| `participation-reliability.js`, `participation-reliability-core.js`, `participation-certificates.js` | Apresentação de confiabilidade, ranking e certificado demonstrativos. |
| `notifications-demo.js`, `privacy-demo.js` | Notificações e privacidade das jornadas demonstrativas. |
| `dialog-accessibility.js`, `navigation-accessibility.js` | Foco e ergonomia dos diálogos e navegação. Navegação também é carregada na entrada Web. |

## Reteste de inicialização

O diagnóstico de assets se aplica a qualquer checkout que sirva um bundle anterior ao código Blade. Conferir o manifest do build, o entrypoint selecionado pela view e o ponto de montagem antes de refazer a lógica do componente. As verificações de código e navegador do Bloco 8.2 estão no relatório consolidado e no relatório de QA independente.
