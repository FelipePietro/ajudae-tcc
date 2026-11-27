# Bloco 8.1 — Rotas Web e API

Web integrado usa sessão tipada e CSRF. Os endpoints privados em `/api/v1` usam Sanctum Bearer e abilities. As rotas Web usam o cliente `requestJson` para dados, com download PDF autorizado pela sessão. O catálogo completo do runtime está no inventário de rotas.

| Tela Web | Operações JSON Web | Endpoints de referência |
| --- | --- | --- |
| `/cadastro` | POST `/cadastro` | POST `/api/v1/pessoas/register`, `/api/v1/padrinhos/indicacao`, GET/POST/DELETE `/api/v1/padrinhos/codigo`, POST `/api/v1/admin/validacoes-pessoa/{pessoa}/exigir-padrinho` |
| `/candidatura?evento={id}` e `/minhas-candidaturas` | GET `/candidatura/{evento}/agendas`, POST `/candidatura/{evento}/enviar`, GET `/minhas-candidaturas/dados`, DELETE `/minhas-candidaturas/{inscricao}` | POST `/api/v1/agendas/{id}/inscricoes`, GET `/api/v1/minhas-inscricoes`, DELETE `/api/v1/inscricoes/{id}` |
| `/ong/organizadores`, `/organizador` | GET `/conta/vinculos-organizador/dados`, POST `/conta/vinculos-organizador/convites`, POST `/conta/vinculos-organizador/{vinculo}/resposta`, DELETE `/conta/vinculos-organizador/{vinculo}` | POST `/api/v1/ongs/me/convites-organizador` e respostas no namespace `/api/v1/pessoas/me/convites-ong` |
| `/admin/fila-eventos` | GET `/admin/fila-eventos/dados`, PATCH `/admin/fila-eventos/{evento}/decisao` | GET `/api/v1/eventos/admin/pendentes`, PATCH `/api/v1/eventos/{id}/status` |
| `/minhas-participacoes` | GET `/minhas-participacoes/dados`, POST `/minhas-participacoes/{evento}/avaliacao-ong` | GET/POST `/api/v1/eventos/{id}/avaliacao-ong`, GET/PATCH `/api/v1/inscricoes/{id}/monitoria`, GET/POST/PATCH `/api/v1/inscricoes/{id}/justificativa` |
| `/ranking`, `/meus-certificados`, `/certificados/verificar` | GET `/ranking/dados`, GET `/meus-certificados/dados`, POST `/meus-certificados/eventos/{evento}/emitir`, GET `/meus-certificados/{certificado}/pdf`, GET `/certificados/verificar/dados` | APIs de ranking, emissão, PDF privado e verificação por código preservadas |
| `/notificacoes`, `/ong/notificacoes`, `/perfil/privacidade`, `/ong/privacidade` | GET `/conta/notificacoes/dados`, PATCH `/conta/notificacoes/lidas` ou `/conta/notificacoes/{id}/lida`, GET/POST `/conta/privacidade/solicitacoes` | APIs existentes de notificações e solicitações LGPD |

## RN022 e D01

O Evento grava `finalizado_em` e o ator no fechamento. Para participação elegível, a avaliação facultativa da ONG tem nota inteira de 1 a 5 e unicidade por Pessoa e Evento. O servidor aceita o envio até o instante de sete dias corridos, inclusive. Depois, mantém a avaliação anterior consultável pelo titular. Eventos legados sem carimbo confiável não ganham prazo calculado a partir de edição ou de outra data.

## Pendências entre superfícies

Gestão operacional de criação/edição, seleção, presença, monitoria, justificativas e encerramento ainda depende da API. Cadastro ONG, uploads, editor de perfil, preferências e demais filas administrativas permanecem pendentes. Verificação pública Web minimiza dados. O contrato anterior da verificação pública API continua em vigor e requer homologação específica de exposição.
