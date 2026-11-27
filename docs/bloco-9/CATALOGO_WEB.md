# Catálogo Web para integração

Ative o modo de identidade integrada com `AJUDAE_WEB_AUTH=true` no ambiente próprio. Use `resources/js/core/http.js` nas chamadas JSON de mesma origem. Para uploads, passe `FormData` e deixe o navegador definir o separador multipart. Envie `Accept: application/json` e `X-CSRF-TOKEN` nas escritas. Submissões de login, senha e exclusão existentes usam formulário Web com `_token` e redirecionamento. Downloads usam navegação comum na mesma sessão.

Sessões `web_pessoa` e `web_ong` são determinadas pelo login e pela versão de autenticação da conta. Os grupos **Pessoa**, **ONG**, **Jornada** (Pessoa voluntária ou organizadora) e **Conta** (qualquer um dos dois guards) partem dessa identidade. As escritas administrativas exigem a capacidade indicada no cadastro do administrador. A API segue em `/api/v1` com Sanctum Bearer. Este catálogo descreve o canal Web.

Salvo indicação, sucessos `200` usam `{success:true,message,data}`. `Paginado` indica as chaves Laravel (`data`, `current_page`, `last_page`, `total`) dentro de `data`. `Paginação simples` tem `data`, `current_page`, `next_page_url` e `prev_page_url`. Algumas coleções de Resource retornam lista direta. Os códigos comuns são `401` sem sessão válida, `403` por ator/capacidade, `404` por recurso inexistente ou indisponível, `409` por conflito de negócio, `419` por CSRF e `422` por validação. A validação Laravel retorna `{message,errors:{campo:[mensagem]}}`. O conflito usa `{success:false,message,errors:null}`. Uploads podem devolver `503` por falha de storage, e rotas com limite retornam `429`.

## F1 — Identidade, cadastro e documentos

| Operação e tela | Método e URI | Acesso e entrada | Resposta e implementação |
| --- | --- | --- | --- |
| Cadastro Pessoa | `POST /cadastro` | Visitante, JSON. Obrigatórios `nm_pessoa` até 64, `genero_pessoa`, `cpf_pessoa`, `dt_nasc` de maior de idade, `rg_pessoa`, `email_pessoa`, `tele_pessoa`, `cep_pessoa`, `logradouro_pessoa`, `cidade_pessoa`, `bairro_pessoa`, `uf_pessoa`, `login_pessoa`, `senha_pessoa`, `senha_pessoa_confirmation`, `aceitou_termos`. Opcionais `compl_pessoa`, `codigo_padrinho` hexadecimal de 64 caracteres. | `201`, `data:null`. Conta pendente e aceite em `PessoaRegistrationService` (RN001–RN005). A documentação é enviada depois, pelo titular autenticado. |
| Cadastro ONG | `POST /cadastro/ong` | Visitante, multipart com `Accept: application/json`. Campos na seção abaixo. | `201`, `data.ong` é `OngPrivateResource`, status `aguardando aprovação`. `OngRegistrationService`, RN009/RN032/RN034. API correspondente usa o mesmo arquivo e continua retornando Bearer. |
| Login e saída | `POST /login`, `POST /logout` | Login usa `tipo_login: pessoa\|ong`, `login` e `senha`. Logout usa a sessão atual. Ambos recebem `_token`. | `302`, destino escolhido pela identidade, ou fluxo de erro da página. `SessionAccountController`, `AccountCredentials`. Formulário de login recusa excesso com `429`. |
| Minha conta | `GET /conta`, `POST /conta/senha` | Conta. Mudança recebe `senha_atual`, `senha`, `senha_confirmation`. | HTML ou `302 /login` após trocar senha, com revogação de tokens e sessões pela versão. `AccountCredentials`. |
| Recuperação de senha | Sem rota | O sistema não tem fluxo de recuperação para as identidades Pessoa/ONG nem canal confirmado de entrega e verificação de titular. | **BLOQUEADO** para a integração de “Esqueceu a senha?”. O broker padrão em `config/auth.php` não é um contrato dessas duas contas. |
| Minha validação | `GET /conta/pessoa/validacao`, `POST /conta/pessoa/validacao/reenviar` | Jornada, sem body. Reenvio depende do estado, prazo e documentos. | `PessoaPrivateResource` com `documentos`, status e informações da análise. `PessoaValidationService` (RN002–RN004). |
| Renovação de antecedentes | `GET /conta/pessoa/antecedentes` | Jornada. | Projeção privada de renovação, validade e documento atual em `AntecedentesService`. |
| Enviar documento de Pessoa | `POST /conta/pessoa/documentos/{tipo}` | Jornada. Multipart `arquivo` PDF/JPG/PNG até 10 MiB. `tipo`: `documento_identidade`, `antecedentes_criminais` ou `cnh`. | `201`, `DocumentoPessoaResource` com id, tipo, status, MIME, tamanho e datas. `DocumentoStorage`, RN003/RN032. |
| Baixar documento de Pessoa | `GET /conta/documentos/{documento}/download` | Titular, administrador com análise cadastral vigente atribuída a ele ou operador de renovação com `usuarios.validar`. Na renovação, apenas antecedentes ativos e pendentes de voluntário aprovado sem exclusão pendente e arquivo dentro da retenção. | Arquivo privado com `Content-Disposition: attachment` e `no-store`. `404` se indisponível, `503` se storage indisponível. |
| Baixar identidade do responsável ONG | `GET /conta/ong/{ong}/documento-identidade` | ONG titular ou Pessoa administradora com `ongs.validar`. | Arquivo privado e download administrativo auditado. `404` sem arquivo gerenciado, após prazo de acesso ou por referência inválida. `503` em falha de storage. |
| Código de padrinho | `GET`, `POST`, `DELETE /conta/pessoa/padrinhos/codigo` | Jornada de voluntário, elegibilidade no `PadrinhoService`. Sem body. | Leitura/emissão `data.codigo`, `data.emitido_em`. Revogação `data:null`. |
| Indicação, pedido e aceites | `POST /conta/pessoa/padrinhos/indicacao`, `GET /conta/pessoa/padrinhos/elegiveis`, `POST /conta/pessoa/padrinhos/solicitacoes`, `POST /conta/pessoa/padrinhos/solicitacoes/{pedido}/aceitar`, `/recusar` ou `/cancelar` | Voluntário autorizado conforme RN004/RN005. `codigo_padrinho` hexadecimal de 64, `per_page` opcional ou `padrinho_id` existente na criação, conforme a rota. Decisões sem body. | `201` na solicitação. Pedido com ids, status e datas, ou lista de elegíveis paginada. `PadrinhoService`. |
| Histórico e conclusão de apadrinhamento | `GET /conta/pessoa/padrinhos/me`, `GET /conta/pessoa/padrinhos/recebidas`, `GET /conta/pessoa/padrinhos/vinculos/{vinculo}`, `POST /conta/pessoa/padrinhos/vinculos/{vinculo}/solicitar-conclusao` ou `/encerrar` | Voluntário e partes autorizadas. `per_page` opcional nas recebidas. Encerramento: `motivo` obrigatório até 2.000. | Listas paginadas, detalhe com `progresso`, `201` com protocolo de conclusão ou vínculo encerrado. `PadrinhoService`. |

### Multipart de cadastro ONG

- Instituição: `nome_fantasia` até 100, `cnpj_ong` válido e único, `descricao_ong` até 256, `email_ong` válido e único até 100, `tel_ong` com 10–15 dígitos. `site_url_ong` HTTP(S) e `pfp_ong_link` são opcionais, até 255.
- Endereço: `cep_ong` com oito dígitos, `logradouro_ong`, `bairro_ong` e `cidade_ong` até 100, `uf_ong` com dois caracteres.
- Responsável: `nome_responsavel_ong`, `cpf_responsavel_ong` válido, `email_responsavel_ong`, `tel_responsavel_ong` com 10–15 dígitos e arquivo `documento_identidade` PDF/JPEG/PNG até 10 MiB. O documento de identidade é o item obrigatório comprovado pelo wizard e pelo contrato existente. O campo legado `rg_responsavel_ong_link` é recusado. O CNPJ e CPF aceitam a pontuação convencional.
- Acesso: `login_ong` único até 64, `senha_ong` de pelo menos oito caracteres e até 72 bytes, `senha_ong_confirmation`, `aceitou_termos` com valor aceito pelo Laravel. Nenhum estado enviado pelo cliente atribui aprovação.

No sucesso, `data.ong` inclui o identificador e estado, dados institucionais e do responsável necessários ao titular, sem senha, CPF ou referência física. Exemplo de núcleo da resposta `201`:

```json
{"success":true,"message":"ONG cadastrada com sucesso.","data":{"ong":{"ong_id":12,"nome_fantasia":"Instituição sintética","status_ong":"aguardando aprovação"}}}
```

Uploads usam `local` privado ou `railway_private`, guardam disco, chave interna, MIME, tamanho e SHA-256. A conta e o aceite são revertidos se a gravação falhar. O objeto é compensado em falha após upload. A primeira aprovação ou reprovação agenda o fim do acesso em 30 dias corridos. O expurgo físico institucional ainda requer autorização explícita e homologação. Referências legadas ficam sem download quando falta metadado do armazenamento gerenciado. Para homologar o limite da aplicação, confira `upload_max_filesize` e `post_max_size` no runtime e o limite total do proxy.

## F2 — Eventos, Agendas e candidaturas

[Gestão de Eventos e Agendas, contrato multipart, estados e D02](CONTRATOS_F2_EVENTOS.md). [Seleção de candidatos e autorização](CONTRATOS_PARTICIPACAO.md).

| Operação e tela | Método e URI | Acesso e entrada | Resposta e implementação |
| --- | --- | --- | --- |
| Feed e detalhe | `GET /feed`, `GET /eventos/{id}` | Público, apenas Evento publicado no detalhe público. Filtros do feed no GET. | HTML com projeção pública de `PublicEventPagesController`, RN011–RN016. |
| Escolher Agenda | `GET /candidatura/{evento}/agendas` | Voluntário aprovado, Evento público. | `data` lista com Agenda, janela, vagas e motivo de indisponibilidade, `CandidaturaService`. |
| Enviar candidatura | `POST /candidatura/{evento}/enviar` | Voluntário aprovado. `agenda_id` positivo, `declarou_estudante` booleano opcional e `carta_apresentacao` opcional até 2.000 caracteres. | `201`, `InscricaoResource`. `409` se inelegível, duplicada, vencida ou lotada. RN014–RN017. |
| Minhas candidaturas e desistência | `GET /minhas-candidaturas/dados`, `DELETE /minhas-candidaturas/{inscricao}` | Titular voluntário. `page` na lista, sem body na desistência. | Paginação simples com `evento`, `inicio`, `status`, `carta`, `desistir_url`. Desistência `data:null`, preserva histórico, RN017/RN018. |

## F3 — Administração, organizadores e ONG

| Operação e tela | Método e URI | Acesso e entrada | Resposta e implementação |
| --- | --- | --- | --- |
| Fila de Pessoas | `GET /admin/validacoes-pessoa/dados`, `GET /admin/validacoes-pessoa/{pessoa}` | `usuarios.validar`, filtros `status` e `per_page` na lista. | Lista ou `PessoaAdminResource` com status, documentos e histórico no detalhe. No detalhe Web, `download_url` depende da autorização vigente e `download_requires_assignment` indica a necessidade de posse da análise. RN002/RN006. |
| Revisar renovação de antecedentes | `GET /admin/antecedentes/renovacoes`, download pela URL autorizada e `PATCH /admin/documentos/{documento}/status` | `usuarios.validar`. O download da renovação segue a autorização por documento, sem assumir o cadastro aprovado. | Fila com `download_url` quando disponível. O download registra `documento.download`. A decisão persiste estado, operador e data e retira o item da fila de pendentes. RN003/RN032/RN034. |
| Decidir Pessoa | `POST /admin/validacoes-pessoa/{pessoa}/assumir`, `/solicitar-correcao`, `/exigir-padrinho`, `/aprovar`, `/reprovar`, `/reprovar-definitivamente` | `usuarios.validar` e, na definitiva, Master. `motivo` obrigatório até 2.000 em correção, exigência de padrinho e reprovações. | `PessoaAdminResource`, `PessoaValidationService` com validações de estado e documentos. |
| Decidir documento Pessoa | `PATCH /admin/documentos/{documento}/status` | `usuarios.validar`, `status=aprovado\|rejeitado`, `motivo` obrigatório na rejeição. | `DocumentoPessoaResource`, `PessoaValidationService`. |
| Fila e decisão ONG | `GET /admin/validacoes-ong/dados`, `PATCH /admin/validacoes-ong/{ong}/status` | `ongs.validar`, `per_page` na lista. A decisão recebe apenas `status_ong` (`ativo`, `aguardando aprovação` ou `reprovado`). | Lista de pendentes ou ONG atualizada, com metadados e link Web autorizado se disponível. `OngValidationService`, RN009/RN034. |
| Fila e decisão Evento | `GET /admin/fila-eventos/dados`, `PATCH /admin/fila-eventos/{evento}/decisao` | `eventos.aprovar`, `q` opcional até 100. `status_evento=ativo\|reprovado`, `motivo` obrigatório na reprovação. | Paginação simples com título, descrição, local, modalidade, Agenda e `decisao_url`, ou `data.status_evento` na decisão. `EventoModerationService`, RN011/RN012. |
| Solicitar promoção | `GET`, `POST /conta/pessoa/solicitacao-organizador` | Jornada. POST por voluntário elegível, com `justificativa` obrigatória até 2.000. | Pedido mais recente ou `null`, POST `201`, `OrganizadorService`, RN007. |
| Fila e decisão de promoção | `GET /admin/solicitacoes-organizador/dados`, `GET /admin/solicitacoes-organizador/{solicitacao}`, `POST /admin/solicitacoes-organizador/{solicitacao}/iniciar-analise`, `/aprovar` ou `/reprovar` | `organizadores.validar`, `status` e `per_page` na lista, `motivo` obrigatório até 2.000 na reprovação. | Pedido e estado, lista paginada, `OrganizadorService`. |
| Vínculos | `GET /conta/vinculos-organizador/dados`, `POST /conta/vinculos-organizador/convites`, `POST /conta/vinculos-organizador/{vinculo}/resposta`, `DELETE /conta/vinculos-organizador/{vinculo}` | ONG ativa convida com `identificador` (e-mail ou login, até 128). Organizador elegível responde com `aceitar` booleano. Partes autorizadas encerram. | Paginação simples com `id`, `nome`, `status`, ações e `pessoa_id` na ONG para vínculo ativo. Convite `202` com resposta uniforme. Demais escritas `data:null`. `OrganizadorService`, RN008. |
| Designar responsável | `PATCH /conta/ong/eventos/{evento}/responsavel` | ONG proprietária ativa. `pessoa_id` de organizador vinculado ativo ou `null` explícito. | Evento atualizado. Conflito em Evento iniciado, terminal ou vínculo inválido. `OrganizadorService`, RN008/RN010/RN012. |
| Conclusão de padrinho | `GET /admin/padrinhos/conclusoes/dados`, `POST /admin/padrinhos/conclusoes/{pedido}/aprovar` ou `/reprovar` | `padrinhos.validar_conclusao`. Reprovação Web exige `motivo` textual até 2.000 caracteres. | Lista paginada, decisão com ID e status. Retorna 422 sem motivo na reprovação Web e 409 se o pedido já foi decidido. A decisão e o log `padrinho.conclusao_decidida` são gravados juntos, com ator, transição e motivo, quando fornecido. O contrato de fila pendente e o Bearer legado foram preservados. `PadrinhoService`, RN005/RN034. |

A validação em `/admin/candidatos` é a jornada de `usuarios.validar`. A moderação de contas em `/admin/usuarios` tem a capacidade `usuarios.moderar` e depende de um contrato de diretório próprio. A exigência de motivo de rejeição do Evento está na rota de moderação de Eventos. Na reprovação de apadrinhamento, o endpoint Bearer legado continua aceitando motivo opcional.

## F4 — Participação e encerramento

[Matriz por ator, contrato por rota, D01/D04/D05 e exemplos](CONTRATOS_PARTICIPACAO.md).

## F5 — Perfis, preferências e gamificação

| Operação e tela | Método e URI | Acesso e entrada | Resposta e implementação |
| --- | --- | --- | --- |
| Perfil público | `GET /pessoas/{id}/perfil`, `GET /ongs/{id}/perfil` | Público. Pessoa voluntária aprovada e ONG ativa, sem exclusão pendente. | HTML de perfil público, `SessionPagesController`, RN032. |
| Perfil privado | `GET /conta/pessoa/dados`, `GET /conta/ong/dados` | Respectivamente Jornada e ONG titular. | `PessoaPrivateResource` ou `OngPrivateResource`. Dados de XP e confiança resumida no Resource Pessoa. |
| Editar perfil | `PATCH /conta/pessoa/{id}/dados`, `PATCH /conta/ong/{id}/dados` | Titular. Pessoa: e-mail, telefone, CEP, logradouro, complemento, cidade, bairro, UF e `pfp_pessoa_link`. ONG: descrição, e-mail, telefone, endereço, site, `pfp_ong_link` e login. As chaves seguem `PessoaController::update` e `OngController::update`, todos opcionais. | Resource privado atualizado, `AccountCredentials`. Para troca de senha na Web, use a rota de Minha conta. |
| Foto de perfil | `POST` ou `DELETE /conta/pessoa/{id}/imagem`, `/conta/ong/{id}/imagem` | Titular. POST multipart `imagem`, JPEG/PNG/WebP até 5 MiB, validação estrutural. | `201` primeiro upload, `200` mudança ou remoção. `data.url`, `mime_type`, `size_bytes`, `ManagedImageStorage`. |
| Catálogos e preferências | `GET /catalogos/habilidades`, `/recursos`, `/causas` e `/categorias-evento`. `GET /conta/pessoa/preferencias` | Catálogos públicos, preferências da Jornada autenticada. | Catálogo paginado. Preferências retornam `{habilidades,recursos,causas}` com pivot existente. |
| Alterar preferências | `POST /conta/pessoa/{id}/habilidades`, `/recursos`, `/causas`. `DELETE` no mesmo caminho com id do item. | Titular. POST habilidade `{habilidade_id,nivel_habilidade}` de 1 a 5, recurso `{recurso_id,detalhes_recurso}` até 255, causa `{causa_id}`. | `201` com lista atualizada, `200` ou `404` na remoção, `409` para associação duplicada. |
| Ranking e confiança | `GET /ranking/dados`, `GET /conta/pessoa/{pessoa}/confiabilidade` | Ranking público, query `cidade`, `busca`, `page`. Confiança detalhada para titular autorizado pela policy. | `data.cidades`, `podio`, `ranking`. Detalhe via `ConfiabilidadeService`. RN027/RN028. |
| Campanhas de XP | `GET /admin/campanhas-xp/dados`, `POST /admin/campanhas-xp`, `PATCH /admin/campanhas-xp/{campanha}` e `PATCH /admin/campanhas-xp/{campanha}/status` | `xp.campanhas.gerenciar`. Criação `nome`, `multiplicador` positivo, `inicio`, `fim`, `ativa`, `cat_evento_id`. PATCH parcial ou só `ativa` para status. | Paginador, `201` ou campanha atualizada. `CampanhaXpManagement`, log antes/depois, RN026/RN034. |
| Certificados | `GET /meus-certificados/dados`, `POST /meus-certificados/eventos/{evento}/emitir`, `GET /meus-certificados/{certificado}/pdf` | Jornada titular. Emissão depende de Evento finalizado, presença, avaliação e monitoria, quando aplicável. | Paginação simples com `evento`, `situacao`, `emitir_url`, `certificado`. Emissão idempotente com `pdf_url`, `verificar_url`; PDF privado. `CertificadoService`, RN029. |
| Verificação | `GET /certificados/verificar/dados?codigo={codigo}` | Público. Código de até 64 caracteres alfanuméricos ou hífen. | `data.valido`, `codigo_verificacao`, `nome_evento`, `total_minutos`, `emitido_em`. Web usa projeção reduzida. |
| Níveis, badges e extrato de XP | Sem rota Web de extrato ou nível | Total de XP, ranking, concessões e campanhas já têm regras executáveis. | **PARCIAL**. Níveis e badges sem regra V1 aprovada e sem contrato deferido. Extrato próprio segue expansão futura, conforme backlog. |

## F6 — Notificações, privacidade e auditoria

| Operação e tela | Método e URI | Acesso e entrada | Resposta e implementação |
| --- | --- | --- | --- |
| Central, leitura e remoção | `GET /conta/notificacoes/dados`, `GET` ou `DELETE /conta/notificacoes/{id}`, `PATCH /conta/notificacoes/{id}/lida`, `PATCH /conta/notificacoes/lidas` | Conta destinatária. Filtros `busca`, `tipo`, `leitura`, `page` na lista. | Lista `{items,total,unread,page,pages,types}`, detalhe do destinatário, `204` na remoção e `data:null` na leitura. RN031/RN032. |
| Meu protocolo LGPD | `GET`, `POST /conta/privacidade/solicitacoes` | ONG ou Jornada titular. GET `status` e `page` opcionais. POST `tipo=acesso\|correcao\|exclusao\|portabilidade\|informacao` e `descricao` opcional até 2.000. | `{items,page,pages}`, ou `201` com `data.id`. `PrivacyRequests`, RN032. |
| Atendimento LGPD | `GET /admin/solicitacoes-lgpd/dados`, `PATCH /admin/solicitacoes-lgpd/{id}/status` | `lgpd.visualizar` ou `lgpd.responder`, respectivamente. GET `status`, `per_page`. PATCH `status=pendente\|em_analise\|concluida\|recusada`, `resposta` obrigatória nos dois últimos. | Paginador com dados reduzidos para analista sem atribuição, ou caso atualizado. Analista precisa assumir antes de decidir, salvo Master. Auditoria e notificação. |
| Exclusão reversível | `POST /conta/exclusao`, `PATCH /conta/exclusao` | Titular elegível de Pessoa/ONG. POST `confirmar_exclusao` aceito. PATCH cancela antes do prazo. | `302 /login`, revoga sessões e tokens. Indeferimento por pendências ou conta administrativa. `AccountDeletion`, RN033. |
| Auditoria administrativa | `GET /admin/log-acoes/dados` | `admin.logs.visualizar`. `acao` e `per_page` opcionais. | Paginador com id, ação, ator, recurso e data. O log armazenado mantém detalhes conforme o domínio. RN034. |

Exemplo de candidatura com sessão e CSRF. `POST /candidatura/18/enviar` com `{"agenda_id":39,"declarou_estudante":false}` retorna `201` com `data.inscricao_id` e `data.status_inscricao` igual a `pendente`. `PATCH /conta/notificacoes/42/lida` retorna `{"success":true,"message":"Notificação marcada como lida.","data":null}`.

## Evidências e status

Operações disponíveis no runtime integrado. A [matriz de prontidão](PRONTIDAO.md) registra o status de cada grupo e as pendências. Referências de teste por operação nos catálogos F2/F4 e no [relatório independente](QA_INDEPENDENTE.md). Os contratos de cadastro e autorização têm cobertura focal em `Bloco9WebOngRegistrationTest`, `Bloco9WebAccountOperationsTest`, `OngAuthAndEventoTest`, `Bloco81WebPessoaRegistrationTest`, `PessoaValidationWorkflowTest`, `PadrinhoWorkflowTest`, `OrganizadorWorkflowTest`, `Bloco81PrivacyNotificationsTest` e `XpCampanhaRankingTest`.
