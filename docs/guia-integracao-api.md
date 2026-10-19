# Ajudae V1 — guia de integração da API

Este guia descreve as rotas existentes em `routes/api.php` (inventário local: `php artisan route:list --path=api`). Defina `API_BASE_URL` para a origem HTTPS da API homologada; os caminhos abaixo são relativos a ela. A homologação ainda não foi publicada.

## Convenções

- A API versionada usa `/api/v1`. `GET /api/health` é a verificação simples de disponibilidade. Há algumas rotas legadas `/api/ong/*`; não as confunda com o contrato versionado.
- Login e cadastro de Pessoa/ONG devolvem `token` e `token_type: Bearer`. Envie `Authorization: Bearer <token>` nos endpoints privados e descarte o token no logout. Nunca coloque tokens em URL, logs, repositório ou armazenamento legível por scripts de terceiros.
- Em frontend de outra origem, a origem exata precisa constar em `CORS_ALLOWED_ORIGINS`; o valor padrão é nenhum. CORS não concede autorização. Os fluxos de API usam Bearer, não cookies de sessão cross-site.
- Respostas de sucesso usam o envelope `success/data/message`; erros podem trazer `message/errors`. Trate `401` como sessão inválida, `403` como falta de permissão, `404` como recurso inexistente ou não público, `409` como conflito de estado e `422` como payload inválido. Não use `404` para inferir existência de registros privados.
- Coleções paginadas expõem os metadados do paginador Laravel; onde aceito, `per_page` é limitado a 100. Confira filtros específicos no endpoint antes de montar a interface.
- Uploads são `multipart/form-data`: campo `arquivo` para documentos, campo `imagem` para fotos/logos/banners. Envie um arquivo real, não um caminho local em JSON.

## Autenticação e perfis

| Perfil/uso | Método e caminho | Autenticação | Dados mínimos / resultado |
| --- | --- | --- | --- |
| Pessoa: cadastro | `POST /api/v1/pessoas/register` | Pública | Dados pessoais obrigatórios, `login_pessoa`, `senha_pessoa` e confirmação, `aceitou_termos`, `pfp_pessoa_link`; retorna token e perfil privado. CPF deve ser válido. |
| Pessoa: login/logout/perfil | `POST /api/v1/pessoas/login`, `POST /api/v1/pessoas/logout`, `GET /api/v1/pessoas/me` | Login público; demais Bearer Pessoa | Login: `login_pessoa`, `senha_pessoa`. Logout revoga o token atual. |
| ONG: cadastro | `POST /api/v1/ongs/register` | Pública | Dados da ONG, endereço, responsável, `login_ong`, `senha_ong` e confirmação, `aceitou_termos`, `pfp_ong_link`; retorna token. CNPJ/CPF devem ser válidos. |
| ONG: login/logout/perfil | `POST /api/v1/ongs/login`, `POST /api/v1/ongs/logout`, `GET /api/v1/ongs/me` | Login público; demais Bearer ONG | Login: `login_ong`, `senha_ong`. |
| Perfis públicos | `GET /api/v1/pessoas`, `GET /api/v1/pessoas/{id}`, `GET /api/v1/ongs`, `GET /api/v1/ongs/{id}` | Pública | Pessoa pública = voluntário aprovado; não espere campos privados ou documentos. |

## Validação, documentos e antecedentes

| Perfil/uso | Método e caminho | Autenticação | Dados mínimos / resultado |
| --- | --- | --- | --- |
| Estado da própria validação | `GET /api/v1/pessoas/me/validacao` | Pessoa | Mostra status e documentos permitidos ao titular. |
| Reenvio | `POST /api/v1/pessoas/me/validacao/reenviar` | Pessoa | Reenvia após correção/reprovação, respeitando bloqueio e workflow. |
| Upload documental | `POST /api/v1/pessoas/me/documentos/{tipo}` | Pessoa | `tipo`: `documento_identidade`, `antecedentes_criminais` ou `cnh`; `arquivo` PDF/JPEG/PNG até 10 MiB. O novo documento substitui logicamente a versão ativa. |
| Download documental | `GET /api/v1/documentos/{documento}/download` | Titular ou Admin autorizado | Bytes privados; não monte URL pública permanente. |
| Antecedentes próprios | `GET /api/v1/pessoas/me/antecedentes` | Pessoa | Situação dos antecedentes. Renovação se faz pelo upload de novo documento do tipo correspondente. |
| Fila e detalhe de validação | `GET /api/v1/admin/validacoes-pessoa`, `GET /api/v1/admin/validacoes-pessoa/{pessoa}` | Admin autorizado | A fila inclui voluntários; detalhe serve ao workflow administrativo. |
| Assumir e decidir validação | `POST /api/v1/admin/validacoes-pessoa/{pessoa}/assumir`, `/aprovar`, `/reprovar`, `/solicitar-correcao`, `/reprovar-definitivamente` | Admin com permissão | Assumir cria lock de 24 h; decisões exigem o dono do lock. Motivos são obrigatórios em decisões de correção/reprovação. Conflitos retornam `409`. Cada sufixo usa o mesmo prefixo completo antes de `{pessoa}`. |
| Analisar documento | `PATCH /api/v1/admin/documentos/{documento}/status` | Admin Master ou `usuarios.validar` | `status=aprovado` ou `rejeitado`; `motivo` obrigatório na rejeição. Documento histórico/inativo retorna `409`. Para Pessoa não aprovada exige análise assumida e lock válido; renovação de aprovada não reabre ciclo. |
| Renovação pendente | `GET /api/v1/admin/antecedentes/renovacoes` | Admin autorizado | Consulta administrativa, sem envio automático pelo frontend. |

## ONG, organizador, evento e agenda

| Perfil/uso | Método e caminho | Autenticação | Dados mínimos / resultado |
| --- | --- | --- | --- |
| Solicitar papel organizador | `POST /api/v1/pessoas/me/solicitacao-organizador`, `GET /api/v1/pessoas/me/solicitacao-organizador` | Pessoa elegível | POST exige `justificativa`; GET consulta o status. |
| Avaliar solicitação | `GET /api/v1/admin/solicitacoes-organizador`, `GET /api/v1/admin/solicitacoes-organizador/{solicitacao}`, `POST .../{iniciar-analise|aprovar|reprovar}` | Admin autorizado | Workflow administrativo e conflitos de estado. |
| Vínculo ONG-organizador | `POST /api/v1/ongs/me/convites-organizador`, `GET /api/v1/ongs/me/organizadores`, `DELETE /api/v1/ongs/me/organizadores/{vinculo}`; `GET /api/v1/pessoas/me/convites-ong`, `POST .../{vinculo}/{aceitar|recusar}` | ONG/Pessoa correspondente | Convite, aceite/recusa, listagem e encerramento. |
| Atribuir responsável | `PATCH /api/v1/ongs/me/eventos/{evento}/responsavel` | ONG gestora | Responsável organizador vinculado, conforme regras do evento. |
| Eventos | `GET /api/v1/eventos`, `GET /api/v1/eventos/{id}`, `POST /api/v1/eventos`, `PATCH /api/v1/eventos/{id}` | Leitura pública; escrita por ONG/organizador autorizado | Criação: `nm_evento`, `descricao_evento`, `imagem_evento_link`, `cat_evento_id`, `vagas_evento`, `modalidade_evento`; endereço é exigido para presencial/híbrido. Responsável vem da autenticação, não do payload. |
| Aprovação/finalização | `PATCH /api/v1/eventos/{id}/status`, `POST /api/v1/eventos/{id}/cancelar`, `POST /api/v1/eventos/{id}/finalizar` | Conforme perfil e política | Transições de estado preservam histórico; não representar cancelamento como DELETE. |
| Agendas | `GET /api/v1/eventos/{evento}/agendas`, `POST /api/v1/eventos/{evento}/agendas`, `GET /api/v1/agendas/{id}`, `PATCH /api/v1/agendas/{id}`, `POST /api/v1/agendas/{id}/cancelar`, `PATCH /api/v1/agendas/{id}/finalizar` | Leitura pública quando permitida; escrita pelo gestor | Criação: `data_inicio`, `data_fim`, `vagas_agenda`; finalização controla presença/XP. |
| Inscrição/candidatura | `POST /api/v1/agendas/{id}/inscrever`, `GET /api/v1/minhas-inscricoes`, `GET /api/v1/ongs/{id}/eventos` | Pessoa para candidatura; gestor para seleção | ONG consulta `GET /api/ong/eventos/{id}/candidatos` e decide pelas rotas `/api/ong/candidatos/*`. |
| Presença/desistência | `PATCH /api/v1/inscricoes/{id}/presenca`, `PATCH /api/v1/inscricoes/{id}/status` | Gestor/Pessoa conforme ação | Estado muda sem apagar o registro histórico. |
| Avaliações | `POST /api/v1/eventos/{id}/avaliacao-ong`, `POST /api/v1/eventos/{id}/avaliacoes/{pessoa}` | Participante/gestor elegível | Após os critérios de evento e participação; `409` quando não cabível. |

## XP, padrinhos, certificados e LGPD

| Perfil/uso | Método e caminho | Autenticação | Dados mínimos / resultado |
| --- | --- | --- | --- |
| Ranking e confiabilidade | `GET /api/v1/ranking`, `GET /api/v1/pessoas/{pessoa}/confiabilidade` | Pública conforme visibilidade | Resumos públicos, não dados documentais. |
| Campanhas XP | `GET/POST /api/v1/admin/campanhas-xp`, `PATCH /api/v1/admin/campanhas-xp/{campanha}`, `PATCH .../{campanha}/status` | Admin autorizado | Administração de campanhas; XP da agenda é consolidado por `POST /api/v1/agendas/{id}/consolidar-xp`. |
| Padrinhos | `GET /api/v1/padrinhos/elegiveis`, `POST /api/v1/padrinhos/solicitacoes`, `GET /api/v1/padrinhos/me`, `GET /api/v1/padrinhos/recebidas` | Pessoa elegível | Solicitar com `padrinho_id`; aceitar/recusar/cancelar por `POST .../solicitacoes/{pedido}/{aceitar|recusar|cancelar}`. |
| Acompanhamento | `GET /api/v1/padrinhos/vinculos/{vinculo}`, `POST .../{vinculo}/encerrar`, `POST .../{vinculo}/solicitar-conclusao` | Partes autorizadas | Histórico mantido. Admin consulta/aprova/reprova em `/api/v1/admin/padrinhos/conclusoes`. |
| Certificado | `POST /api/v1/eventos/{id}/certificado`, `GET /api/v1/eventos/{id}/certificado`, `GET /api/v1/certificados/{certificado}/download` | Titular/gestor conforme ação | PDF gerado sob demanda no download, sem arquivo persistido. Resposta privada sem cache público. |
| Verificação | `GET /api/v1/certificados/verificar/{codigo}` | Pública | Consulta por código de verificação; não é download do PDF privado. |
| Solicitação LGPD | `POST /api/v1/solicitacoes-lgpd`, `GET /api/v1/minhas-solicitacoes-lgpd` | Pessoa/ONG autenticada | Admin usa `GET /api/v1/admin/solicitacoes-lgpd` e `PATCH .../{id}/status`. |

## Imagens gerenciadas: nove endpoints

| Entidade | Upload (multipart `imagem`) | Remoção | Leitura pública |
| --- | --- | --- | --- |
| Pessoa | `POST /api/v1/pessoas/{id}/imagem` | `DELETE /api/v1/pessoas/{id}/imagem` | `GET /api/v1/pessoas/{id}/imagem` |
| ONG | `POST /api/v1/ongs/{id}/imagem` | `DELETE /api/v1/ongs/{id}/imagem` | `GET /api/v1/ongs/{id}/imagem` |
| Evento | `POST /api/v1/eventos/{id}/imagem` | `DELETE /api/v1/eventos/{id}/imagem` | `GET /api/v1/eventos/{id}/imagem` |

Upload e remoção exigem titular/gestor autorizado. O arquivo deve ser JPEG, PNG ou WebP íntegro, até 5 MiB, 8.000 px por lado e 25 MP; o backend grava uma versão sanitizada. A resposta de upload inclui `url`, `mime_type` e `size_bytes`. A leitura do objeto gerenciado usa chave controlada pela aplicação, `Cache-Control: public, max-age=300` e `ETag`; após substituição, a URL com `?v=` muda. O GET pode devolver `304` com `If-None-Match`. A visibilidade pública depende do perfil/estado da entidade. Os campos legados `pfp_pessoa_link`, `pfp_ong_link` e `imagem_evento_link` continuam como links fornecidos pelo cadastro; o servidor **não busca** esses URLs externos. Não use link legado como autorização para um recurso privado.

## Smoke test para o grupo, depois da homologação

1. Obter a URL HTTPS e origens CORS aprovadas; verificar `/api/health` e a ausência de stack trace em erros.
2. Com duas identidades fictícias de Pessoa e uma conta administrativa de teste, validar cadastro/login/logout, escopo dos tokens, `401/403/404/409/422` e visibilidade pública.
3. Seguir o fluxo de documentos e lock administrativo, sem compartilhar credenciais; tentar download por outro usuário e confirmar bloqueio.
4. Criar ONG/evento/agenda fictícios, candidatar-se, decidir, registrar presença, avaliar e conferir XP/ranking conforme as permissões reais.
5. Enviar imagem sintética com metadados, verificar leitura e `304`, substituir/remover; testar JPEG/PNG/WebP e tamanho inválido. Não usar fotos/documentos reais.
6. Verificar código público de certificado e baixar PDF apenas como autorizado; confirmar que não há PDF persistido no bucket.

Este checklist não autoriza limpar banco, disparar expurgo ou enviar dados reais à homologação.
