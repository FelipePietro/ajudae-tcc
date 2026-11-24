# Ajudae — Regras de Negócio — V1

**Especificação funcional consolidada para implementação**

- **Projeto:** Ajudae - Plataforma de Voluntariado
- **Versão:** V1 - regras consolidadas para a entrega
- **Data:** 09 de outubro de 2026
- **Finalidade:** Documento de referência para backend, frontend, testes e validação da pré-banca.

> **Nota de transcrição:** o conteúdo foi convertido para Markdown mantendo as decisões, valores, estados e terminologia do PDF original. Foram retirados apenas cabeçalhos/rodapés repetitivos e quebras artificiais de linha. A ortografia do PDF foi em geral preservada.

## 1. Proposito e principios da V1

Este documento consolida as regras de negocio acordadas para o Ajudae. A V1 prioriza uma entrega solida, auditavel e implementavel dentro do prazo, evitando recursos que ampliem demais o escopo. Quando uma regra anterior entrou em conflito com uma decisao posterior, prevalece a decisao mais recente.

**Principio de escopo:** a V1 deve resolver o fluxo principal de voluntariado de ponta a ponta. Funcionalidades que exigem moderacao sofisticada, reputacao adicional, multiplos papeis simultaneos ou automacoes complexas ficam para evolucao posterior.

## 2. Atores do sistema

| Ator | Papel principal | Restricoes-chave |
| --- | --- | --- |
| Voluntario | Participar de Agendas e construir historico no Ajudae. | Precisa estar aprovado para candidatar-se. |
| Organizador | Criar e operar Eventos proprios ou da ONG a que estiver vinculado. | Nao participa como voluntario; promocao e definitiva. |
| ONG | Entidade autenticavel que cria e gerencia Eventos e pode convidar organizadores. | A conta da ONG representa o responsavel da entidade. |
| Administrador | Moderar processos conforme permissoes explicitas. | Todo admin tem role administrador; permissoes sao granulares. |
| Admin Master | Administracao total e delegacao de permissoes. | Pode executar todas as funcoes administrativas. |
| Monitor | Validar participacao estudantil quando aplicavel. | A elegibilidade fisica do monitor nao e controlada pela V1. |

## 3. Mapa das regras

- RN001-RN005: cadastro, validacao, documentos, antecedentes e padrinho.
- RN006-RN009: administracao, organizador, vinculo com ONG e regras da entidade.
- RN010-RN017: Evento, Agenda, publicacao, edicao, candidaturas e match.
- RN018-RN024: desistencias, presenca, monitoria, avaliacoes e finalizacao.
- RN025-RN029: XP, campanhas, confiabilidade, ranking e certificados.
- RN030-RN034: cancelamentos, notificacoes, LGPD, exclusao e auditoria.

### RN001 — Cadastro de Pessoa

*Definir quem pode criar conta como Pessoa no Ajudae.*

- O Ajudae e exclusivo para pessoas com 18 anos ou mais; nao ha fluxo para menores nem autorizacao de responsavel.
- O cadastro deve validar CPF, formato e unicidade de e-mail, duplicidades e data de nascimento.
- O cadastro bem-sucedido cria a conta, mas nao torna a Pessoa automaticamente um voluntario aprovado.
- A Pessoa pode autenticar-se e acompanhar o proprio processo enquanto sua validacao estiver pendente.

### RN002 — Validacao do voluntario

*Separar cadastro de conta e autorizacao para atuar como voluntario.*

- Estados previstos: aguardando_validacao, em_analise, correcao_solicitada, aprovado, reprovado e, exclusivamente por decisao do Admin Master, reprovado_definitivamente.
- Enquanto pendente, a Pessoa pode entrar no sistema, navegar por Eventos, ver o proprio perfil, consultar o ranking, corrigir cadastro/documentos e acompanhar o status.
- Enquanto pendente, nao pode candidatar-se, atuar como voluntario validado, aparecer em listagens publicas de voluntarios, no ranking publico ou ter perfil publico acessivel a terceiros.
- Somente Pessoas aprovadas aparecem publicamente como voluntarias.
- Um Admin pode assumir uma analise; o registro deve guardar admin_analisando_id e analise_assumida_em. O bloqueio de analise expira apos 24 horas sem conclusao.
- correcao_solicitada exige motivo e permite novo envio sem contar como reprovacao.
- Tres reprovacoes em uma janela de 30 dias geram bloqueio de 7 dias para novo envio. Nao ha escalonamento para 15 dias.
- Uma aprovacao posterior zera a contagem operacional de reprovacoes.
- Reprovacao definitiva exige Admin Master, motivo, autor, data/hora e auditoria.

### RN003 — Documentos do voluntario

*Definir os documentos exigidos e como sao validados.*

- Documento de identidade (RG ou equivalente) e certidao de antecedentes criminais sao obrigatorios.
- CNH e opcional e pode ser solicitada apenas quando uma atividade de direcao realmente exigir esse dado.
- Validacoes deterministicas incluem CPF, maioridade, e-mail, duplicidades e presenca/tipo/tamanho do upload.
- Conteudo de identidade e autenticidade que nao possam ser confirmados automaticamente seguem para revisao humana.
- Arquivos devem ficar em armazenamento privado de objetos; o MySQL guarda metadados e nunca os bytes/base64 do documento.
- Nao devem existir URLs publicas permanentes para documentos pessoais.
- O arquivo fisico do documento e apagado 30 dias apos a validacao, mantendo-se apenas metadados e trilha de auditoria necessarios.
- A certidao de antecedentes deve ser renovada a cada 12 meses; o vencimento gera pendencia/lembrete cadastral, mas nao revoga automaticamente permissoes ja concedidas.
> **Observacao:** Se for utilizado Firebase, a opcao adequada para arquivos e Cloud Storage for Firebase / Google Cloud Storage, nao Firestore nem Realtime Database.


### RN004 — Antecedentes criminais e padrinho

*Permitir analise humana de voluntarios com antecedentes sem exclusao automatica.*

- A existencia de antecedente nao reprova automaticamente a Pessoa.
- Quando houver antecedente, a aprovacao depende de padrinho valido e analise administrativa do conjunto.
- Sem padrinho valido, a Pessoa com antecedente nao pode ser aprovada.
- O sistema nao decide autonomamente a aceitabilidade juridica ou moral do antecedente; encaminha para revisao humana.
- O convite de apadrinhamento precisa ser aceito explicitamente pelo padrinho.

### RN005 — Elegibilidade e encerramento do padrinho

*Definir quem pode apadrinhar e quando o acompanhamento pode terminar.*

- Padrinho precisa ser voluntario aprovado e nao pode depender de padrinho.
- Deve possuir conta aprovada ha pelo menos 60 dias, no minimo 10 participacoes confirmadas e avaliacao media >= 4,5/5.
- Nao pode ter ausencia injustificada nos ultimos 90 dias e pode ter no maximo 1 desistência tardia nos ultimos 90 dias.
- Cada padrinho pode manter no maximo 2 apadrinhados ativos.
- Para encerrar o acompanhamento: minimo de 6 meses, pelo menos 5 participacoes confirmadas do apadrinhado nesse periodo, nenhuma ocorrencia relevante, nenhuma desistência tardia e nenhuma desistência injustificada.
- O encerramento elegivel depende de confirmacao administrativa; nao e automatico.
- Um voluntario com apadrinhados ativos nao pode solicitar promocao para organizador.

### RN006 — Administracao e permissoes

*Garantir privilegios administrativos granulares.*

- Todo operador administrativo possui role_pessoa = administrador.
- O Admin Master possui todas as permissoes e pode conceder/remover permissoes de outros admins.
- Admin comum somente executa acoes para as quais recebeu permissao explicita.
- Uma Pessoa que nao seja administrador nunca ganha poder administrativo apenas porque uma permissao foi vinculada por engano.
- Permissoes previstas incluem, entre outras: usuarios.validar, ongs.validar, eventos.aprovar, usuarios.moderar, lgpd.visualizar, lgpd.responder, admin.logs.visualizar, organizadores.validar e xp.campanhas.gerenciar.
- O Admin nao assume a finalizacao de Evento abandonado; o sistema apenas notifica o responsavel sobre a pendencia.
> **Observacao:** Regra de autorizacao conceitual: se role != administrador, negar; se Admin Master, permitir; caso contrario, verificar permissao explicita.


### RN007 — Promocao para organizador

*Definir a mudanca definitiva de voluntario para organizador.*

- Para solicitar: Pessoa aprovada, pelo menos 5 participacoes confirmadas, confiabilidade >= 80, avaliacao >= 4,0, sem bloqueio ativo e sem apadrinhados ativos.
- A justificativa para a solicitacao e obrigatoria.
- Somente uma solicitacao pode ficar aberta por vez.
- Se reprovada, nova solicitacao somente apos 30 dias.
- A analise exige a permissao organizadores.validar (ou Admin Master).
- A aprovacao altera a funcao da Pessoa para organizador e a mudanca e definitiva.
- O historico anterior de XP, avaliacoes, confiabilidade e participacoes e preservado.
- Depois da promocao, o organizador nao pode candidatar-se nem participar de novas Agendas como voluntario.

### RN008 — Vinculo entre organizador e ONG

*Permitir que uma ONG trabalhe com organizadores sem criar sistema de funcionarios.*

- Um organizador pode estar vinculado a apenas uma ONG por vez.
- Uma ONG pode ter qualquer quantidade de organizadores vinculados.
- Somente a conta da propria ONG pode enviar convite de vinculo.
- O convite precisa ser aceito pelo organizador; enquanto estiver pendente, ele continua independente.
- Quando o vinculo fica ativo, o organizador deixa de criar Eventos independentes e passa a criar/gerenciar Eventos da ONG.
- O organizador vinculado gerencia apenas Eventos em que foi explicitamente definido como responsavel.
- ONG e organizador podem encerrar o vinculo, desde que o organizador nao seja responsavel por Evento ativo.
- Nao existe transferencia de responsavel em Evento ja iniciado na V1.

### RN009 — Conta e status da ONG

*Definir a entidade ONG como ator autenticavel.*

- A ONG possui conta propria e a autenticacao da entidade representa seu responsavel institucional.
- Nao e necessario criar uma conta Pessoa separada para o responsavel da ONG.
- ONG pendente ou nao aprovada nao pode operar Eventos protegidos.
- A validacao administrativa da ONG deve manter trilha de status e auditoria.
- A ONG pode gerenciar os Eventos que lhe pertencem, inclusive quando um organizador vinculado estiver definido como responsavel.

### RN010 — Propriedade e responsabilidade do Evento

*Distinguir quem e dono e quem opera cada Evento.*

- Um Evento pertence exatamente a uma ONG ou a um organizador independente.
- Evento independente: o organizador e simultaneamente proprietario e responsavel operacional.
- Evento de ONG sem organizador atribuido: a propria conta da ONG atua como responsavel.
- Evento de ONG com organizador: a ONG continua proprietaria e a Pessoa organizadora vinculada e o responsavel operacional.
- O responsavel precisa possuir conta no Ajudae.
- O responsavel pode ser definido/alterado antes do inicio; depois que o Evento comeca, nao pode ser trocado.

### RN011 — Criacao, aprovacao e publicacao do Evento

*Controlar quando um Evento pode ficar visivel.*

- Eventos criados por ONG ou organizador independente sempre passam por aprovacao administrativa antes da publicacao.
- Criar Evento nao equivale a publicar Evento.
- Enquanto estiver aguardando aprovacao, o Evento nao aparece nas paginas publicas e nao aceita candidaturas.
- Um organizador vinculado a ONG cria Eventos em nome da ONG, nao Eventos independentes.

### RN012 — Edicao e reprovacao do Evento

*Definir quando uma edicao invalida a aprovacao anterior.*

- Alteracoes sensiveis fazem o Evento voltar para aguardando_aprovacao, removem-no temporariamente das paginas e bloqueiam novas candidaturas.
- Sao sensiveis: descricao, data, horario, duracao, local/endereco, capacidade operacional de Agenda, estrutura de Agendas, responsavel, causas, habilidades e recursos. O total de vagas do Evento e informativo.
- Titulo e banner/imagem sao alteracoes simples e nao exigem nova aprovacao.
- Qualquer alteracao, simples ou sensivel, gera notificacao aos voluntarios ja inscritos.
- Inscricoes existentes sao mantidas durante nova analise.
- Durante aprovacao pendente ou reanalise, preservar consultas autorizadas e moderacao. Bloquear novas candidaturas, selecao, presenca, avaliacoes e finalizacao. Cancelamentos, correcoes e medidas legais ou de seguranca seguem suas regras proprias. A nova decisao revalida os estados e prazos vigentes.
- Quando houver mudanca de data, horario ou local, o inscrito pode desistir sem penalizacao ate o inicio da respectiva Agenda.

### RN013 — Agenda, horarios e vagas

*Tratar cada horario como unidade operacional de participacao.*

- Um Evento pode possuir varias Agendas.
- Vagas sao definidas e controladas por Agenda, nao apenas pelo Evento.
- A mesma Pessoa pode se inscrever em varias Agendas do mesmo Evento.
- A duracao minima permitida para uma Agenda e 30 minutos.
- A duracao declarada na criacao e a unica considerada para XP e certificado; horas extras reais nao sao computadas na V1.
- Uma Agenda sem inscritos pode ser finalizada normalmente.
- Estados terminais de Agenda incluem finalizada e cancelada.

### RN014 — Candidatura

*Definir quem pode se candidatar e ate quando.*

- Somente voluntario aprovado pode candidatar-se.
- Organizador nao pode candidatar-se como voluntario.
- Candidatura e feita para uma Agenda especifica.
- A carta de apresentacao e opcional e privada ao titular e ao responsavel autorizado. Nao e criterio automatico de selecao.
- Novas candidaturas sao aceitas ate 1 hora antes do inicio da Agenda.
- Nao existe lista de espera na V1.
- Quando a quantidade de aprovados atinge o numero de vagas, novas candidaturas sao bloqueadas (resposta recomendada: HTTP 409).
- Se um aprovado desistir antes do fechamento da janela, a vaga e liberada e novas candidaturas podem voltar a ser aceitas.
- Habilidades sugeridas nao sao requisitos obrigatorios; qualquer voluntario aprovado pode candidatar-se mesmo sem correspondencias.

### RN015 — Selecao de candidatos

*Manter decisao humana apoiada por dados.*

- O responsavel escolhe livremente entre os inscritos, respeitando o limite de vagas da Agenda.
- O sistema nao aprova automaticamente por score ou algoritmo.
- Candidatos com mais caracteristicas em comum podem ser destacados visualmente.
- A selecao precisa ser transacional para impedir que aprovacoes concorrentes ultrapassem o limite de vagas.

### RN016 — Compatibilidade (match)

*Destacar afinidade sem transformar a recomendacao em barreira.*

- O match considera apenas habilidades, causas de interesse e recursos declarados pelo voluntario que coincidam com o Evento.
- O sistema mostra quantidade/destaque de correspondencias, nao porcentagem.
- Confiabilidade e exibida separadamente e nao entra no calculo do match.
- Disponibilidade nao entra no match porque a candidatura ja ocorre para uma Agenda especifica.
- O match e informativo e nunca impede candidatura ou obriga aprovacao.

### RN017 — Status historico da inscricao

*Preservar historico em vez de apagar candidaturas.*

- Inscricoes nao devem ser deletadas para representar desistência, ausencia ou conclusao.
- O fluxo precisa manter estados historicos como pendente, aprovada, recusada, desistente, presente, ausente e cancelada pela Agenda/Evento.
- Eventos de negocio relevantes devem guardar data/hora e autor quando aplicavel.

### RN018 — Desistencia e justificativa

*Diferenciar desistência normal, tardia e justificada.*

- Desistencia com pelo menos 24 horas de antecedencia e classificada como normal.
- Desistencia com menos de 24 horas e classificada como tardia.
- A classificacao temporal permanece verdadeira mesmo que a justificativa seja aceita depois.
- O voluntario pode enviar justificativa; o responsavel pelo Evento aceita ou rejeita.
- Justificativa aceita remove a penalizacao de confiabilidade correspondente, sem apagar o registro historico.
- Mudanca de data, horario ou local feita pelo organizador permite desistência sem penalizacao ate o inicio da Agenda.

### RN019 — Ausencia e presenca

*Registrar participacao real por Agenda.*

- Presenca e confirmada por Agenda.
- Pessoa aprovada que nao desistiu e nao compareceu recebe status de ausencia.
- A ausencia injustificada e ocorrencia negativa de confiabilidade.
- Uma ausencia posteriormente justificada e aceita permanece historicamente registrada, mas sua penalizacao e revertida.
- XP so pode ser concedido para Agenda encerrada com presenca confirmada.

### RN020 — Monitoria de estudante

*Atender o requisito de comprovacao estudantil sem ampliar o escopo operacional.*

- Monitoria so e exigida quando o voluntario informa que e estudante.
- A validacao de monitoria ocorre por Agenda.
- Qualquer pessoa presente no Evento pode exercer fisicamente a funcao de monitor; a V1 nao tenta validar quem estava fisicamente acompanhando o estudante.
- O sistema deve apenas registrar a confirmacao de monitoria necessaria ao certificado estudantil.
- Participacoes de estudante sem monitoria confirmada nao liberam o certificado correspondente.
- O responsavel autorizado pode regularizar a monitoria apos o encerramento da Agenda. Registrar autoria e data, preservar a presenca e exigir justificativa para a decisao posterior ao encerramento.
> **Observacao:** A V1 deliberadamente nao implementa uma gestao complexa de credenciamento de monitores.


### RN021 — Avaliacao do voluntario

*Medir qualidade de atuacao sem confundir com confiabilidade.*

- O responsavel pelo Evento avalia o voluntario em escala de 1 a 5.
- A avaliacao e obrigatoria para cada voluntario que efetivamente participou do Evento.
- Existe apenas uma avaliacao por Pessoa + Evento; se ela participou de varias Agendas, a avaliacao e reaproveitada e nao aparece novamente como pendente.
- O responsavel espera o conjunto da participacao no Evento para emitir a avaliacao unica.
- Evento independente: o proprio organizador avalia.
- Evento de ONG com organizador responsavel: o organizador avalia.
- Evento de ONG sem organizador: a conta ONG avalia.
- A avaliacao mede qualidade; nao e usada como a mesma metrica de confiabilidade.

### RN022 — Avaliacao da ONG

*Permitir feedback do voluntario sem criar obrigacao de fechamento.*

- O voluntario pode avaliar a ONG uma unica vez por Evento.
- A escala e de 1 a 5.
- A avaliacao da ONG e opcional e nao bloqueia XP, certificado ou finalizacao.
- Somente a Pessoa com participacao elegivel pode enviar a propria avaliacao. O envio apos a finalizacao e permitido ate sete dias corridos, inclusive, contados da data e hora efetivas registradas pelo servidor. A avaliacao ja registrada permanece consultavel conforme as permissoes depois do prazo.

### RN023 — Finalizacao de Agenda

*Definir quando um horario deixa de estar operacional.*

- Agenda sem inscritos pode ser finalizada diretamente.
- Agenda com inscritos deve ter o estado de participacao resolvido para cada aprovado (presente, ausente, desistente ou equivalente).
- Monitoria estudantil pendente permite fechar a Agenda quando a participacao ja estiver resolvida. A emissao de certificado estudantil continua condicionada a confirmacao das monitorias aplicaveis.
- Uma Agenda cancelada e considerada terminal para permitir o fechamento do Evento.
- A avaliacao unica do voluntario pertence ao fechamento do Evento e nao precisa ser repetida por Agenda.

### RN024 — Finalizacao do Evento

*Consolidar as varias Agendas em um unico Evento.*

- O Evento so pode ser finalizado quando tiver pelo menos uma Agenda e todas estiverem em estado terminal: finalizada ou cancelada.
- A ONG proprietaria ou o organizador designado com vinculo e atribuicao vigentes pode finalizar. No Evento independente, pode finalizar o organizador proprietario autorizado. Registrar o ator e o instante efetivo da finalizacao.
- Todos os voluntarios que efetivamente participaram precisam possuir a avaliacao obrigatoria do responsavel antes da finalizacao.
- Nao existe prazo maximo que autorize um Admin a finalizar o Evento no lugar do responsavel.
- Se houver pendencia de finalizacao/avaliacao, o sistema deve notificar o organizador ou a ONG responsavel.
- O certificado e consolidado somente apos o Evento atingir a finalizacao adequada.

### RN025 — Calculo de XP

*Gamificar participacoes confirmadas por Agenda.*

- XP base de uma participacao concluida e 100.
- O XP e calculado por Agenda e somado ao total da Pessoa.
- O XP nao depende da avaliacao do voluntario; depende da Agenda encerrada e da presenca confirmada.
- A duracao declarada da Agenda define o multiplicador: 30 min = x0,50; entre 30 e 60 min o valor e proporcional; 1h = x1,00.
- Acima de 1h, o multiplicador cresce proporcionalmente em +0,10 por hora adicional. Ex.: 1h30 = x1,05; 2h = x1,10; 2h30 = x1,15.
- Formula: XP = 100 x multiplicador_duracao x multiplicador_tematico.
- O multiplicador de duracao fica fora do teto tematico, portanto o XP final pode ultrapassar 300.
- Cada concessao precisa ser idempotente: uma unica transacao de XP para a mesma inscricao/Agenda.
- Alteracoes futuras nas regras nao recalculam XP historico; cada transacao guarda base, multiplicadores, resultado e motivo.

### RN026 — Campanhas e multiplicadores de XP

*Controlar bonus sazonais sem espalhar constantes no codigo.*

- Bonus de nicho, semana, campanha ou periodo tematico podem acumular-se multiplicativamente.
- O produto dos bonus tematicos e limitado a x3,00.
- O multiplicador de duracao e aplicado alem desse teto e nao consome o limite de x3.
- Campanhas devem ser configuraveis e auditaveis, preferencialmente por estruturas proprias de regra/campanha.
- Somente Admin Master ou Admin com permissao xp.campanhas.gerenciar pode criar/alterar campanhas.

### RN027 — Confiabilidade do voluntario

*Representar compromisso e comparecimento separadamente da avaliacao.*

- A confiabilidade so passa a ser exibida/calculada apos 3 participacoes confirmadas.
- A base inicial e 100 pontos, limitada ao intervalo de 0 a 100.
- Ausencia injustificada: -20 pontos.
- Desistencia tardia injustificada: -10 pontos.
- Desistencia normal sem justificativa: -5 pontos.
- Participacao concluida: +3 pontos, ate retornar ao maximo de 100.
- Justificativa aceita remove/reverte a penalizacao da ocorrencia, mas nao apaga o historico.
- Publicamente e mostrado apenas o resumo, por exemplo 94/100.
- O proprio voluntario e a administracao podem consultar o detalhamento; o responsavel pelo Evento ve apenas informacoes relevantes, sem dados sensiveis da justificativa.
- A confiabilidade nao entra no ranking nem no calculo do match.

### RN028 — Ranking

*Definir uma classificacao simples e objetiva.*

- O ranking utiliza exclusivamente o XP acumulado.
- Avaliacao e confiabilidade sao exibidas separadamente e nao alteram a posicao.
- Pessoa ainda nao aprovada nao aparece no ranking publico.
- Historico de XP conquistado antes de uma promocao para organizador e preservado.

### RN029 — Certificados

*Emitir comprovacao consolidada da participacao.*

- Existe um certificado por Pessoa + Evento, e nao um certificado por Agenda.
- Participacao confirmada em pelo menos uma Agenda ja gera direito ao certificado, desde que o Evento seja finalizado e os demais requisitos estejam resolvidos.
- A carga horaria do certificado e a soma das duracoes declaradas das Agendas em que a Pessoa teve presenca confirmada.
- Agendas canceladas, ausencias e horas extras reais nao entram na carga horaria.
- Quando a Pessoa se declarou estudante, revalidar a confirmacao da monitoria das Agendas correspondentes no momento de emitir o certificado.
- O certificado deve possuir codigo unico de verificacao publica, no padrao conceitual AJD-AAAA-NNNNNN ou equivalente.
- O certificado funciona como comprovacao formal; nao e liberado apenas por passagem de tempo ou por ausencia de avaliacao.

### RN030 — Cancelamento de Agenda ou Evento

*Permitir cancelamento direto sem punir voluntarios.*

- ONG ou responsavel autorizado pode cancelar Evento ou Agenda diretamente; nao e necessaria aprovacao do Admin.
- O motivo do cancelamento e obrigatorio.
- Todos os inscritos/aprovados afetados devem ser notificados.
- Cancelamento pelo organizador nunca gera ausencia, desistência ou penalizacao de confiabilidade para o voluntario.
- Agenda cancelada nao gera XP nem horas de certificado.
- Um Evento com algumas Agendas canceladas ainda pode ser concluido se todas as demais forem finalizadas.
- Cancelamento tardio pelo organizador deve ser registrado como ocorrencia da ONG/organizador para uso futuro, mas nao gera automaticamente uma nota de reputacao na V1.

### RN031 — Notificacoes obrigatorias

*Garantir comunicacao nos pontos criticos do fluxo.*

- Inscritos devem ser notificados sobre qualquer alteracao de Evento, inclusive alteracoes simples de titulo ou banner.
- Cancelamentos de Agenda ou Evento notificam todos os afetados.
- Alteracoes de data, horario ou local devem informar explicitamente o direito de desistir sem penalizacao ate o inicio.
- Organizador/ONG deve receber lembretes quando houver Evento pendente de avaliacao ou finalizacao.
- Voluntario deve receber avisos de status de validacao, correcao solicitada, convite/estado de padrinho quando aplicavel e vencimento proximo da certidao de antecedentes.
- Convites de ONG para organizador e mudancas relevantes no vinculo devem gerar notificacao.

### RN032 — LGPD, privacidade e acesso a dados

*Minimizar exposicao e separar dados publicos de dados sensiveis.*

- Documentos pessoais ficam privados e acessiveis somente a quem realmente precisa analisar o processo.
- Responsavel por Evento nao recebe detalhes sensiveis de justificativas, documentos ou antecedentes alem do necessario para sua funcao.
- Arquivos validados sao eliminados fisicamente apos 30 dias; metadados e auditoria permanecem somente na medida necessaria para integridade e rastreabilidade.
- Nao utilizar links publicos permanentes para documentos.
- Dados publicos do perfil devem limitar-se a informacoes funcionais, como XP, avaliacao, confiabilidade resumida, participacoes e demais campos expressamente publicos.
- O desenho tecnico deve possibilitar anonimizar historico quando a conta for excluida, preservando registros operacionais que nao precisem continuar associados a dados pessoais identificaveis.

### RN033 — Exclusao de conta e preservacao historica

*Conciliar direito de exclusao com integridade do historico.*

- A exclusao solicitada entra em estado pendente por 7 dias, permitindo cancelamento da solicitacao durante essa janela.
- Contas nao devem ser apagadas de modo que corrompa participacoes, Eventos, certificados ou auditoria.
- Ao concluir a exclusao, dados pessoais devem ser removidos ou anonimizados conforme sua finalidade e necessidade de retencao.
- Registros historicos essenciais podem permanecer anonimizados, em vez de bloquear indefinidamente o direito de exclusao.
- Processos ativos que exigem a identidade da Pessoa devem ser resolvidos antes da exclusao definitiva.

### RN034 — Auditoria e integridade

*Manter rastreabilidade das decisoes relevantes.*

- Alteracoes administrativas de status, validacoes, reprovacoes, aprovacoes de ONG, promocoes para organizador, campanhas de XP e decisoes permanentes devem gerar log administrativo.
- Historicos de validacao nao devem ser sobrescritos; cada mudanca precisa registrar status anterior, novo status, responsavel, motivo e data/hora quando aplicavel.
- Relacoes historicas relevantes devem preferir restricao/estado terminal a exclusoes em cascata que destruam evidencias de participacao.
- Transacoes de XP, ocorrencias de confiabilidade, avaliacoes e certificados devem possuir chaves/restricoes que evitem duplicacao logica.
- Erros de autorizacao ou concorrencia nao podem permitir ultrapassar vagas, conceder XP duplicado ou acessar documentos privados.

## 4. Fluxos de referencia

### 4.1 Fluxo do voluntario

1. Pessoa maior de 18 anos cria conta e envia dados/documentos obrigatorios.
2. Sistema executa validacoes automaticas e o processo segue para analise humana quando necessario.
3. Apos aprovacao, a Pessoa torna-se voluntario apto a candidatar-se.
4. O voluntario escolhe uma Agenda e envia candidatura ate 1 hora antes do inicio.
5. O responsavel seleciona candidatos respeitando vagas; o sistema apenas destaca match por habilidades, causas e recursos.
6. Apos a Agenda, presenca/ausencia/desistencia sao consolidadas. Se estudante, registra-se monitoria.
7. Presenca confirmada gera XP da Agenda. Confiabilidade e atualizada conforme as ocorrencias.
8. Ao final do Evento, o responsavel emite uma avaliacao unica do voluntario; todas as Agendas precisam estar encerradas/canceladas.
9. O Evento finalizado libera certificado consolidado com a carga horaria das Agendas efetivamente cumpridas.

### 4.2 Fluxo do organizador

10. Voluntario elegivel solicita promocao e informa justificativa.
11. Admin com organizadores.validar (ou Master) analisa. Se aprovado, a promocao e definitiva.
12. Organizador independente pode criar Eventos proprios, sempre sujeitos a aprovacao administrativa.
13. ONG pode convidar o organizador; apos aceite, ele fica vinculado exclusivamente aquela ONG.
14. Organizador vinculado cria/gerencia apenas Eventos da ONG pelos quais e responsavel.
15. Apos o inicio de um Evento, o responsavel nao pode ser trocado na V1.
16. Para sair da ONG, o organizador nao pode manter Evento ativo sob sua responsabilidade.

### 4.3 Fluxo de Evento com multiplas Agendas

17. Evento e aprovado e publicado.
18. Cada Agenda possui vagas, candidaturas, presenca e XP proprios.
19. Pessoa pode participar de uma ou varias Agendas.
20. Agenda cancelada e terminal e nao gera penalidade ou recompensa.
21. Evento so finaliza quando todas as Agendas estiverem finalizadas ou canceladas e as avaliacoes obrigatorias estiverem concluidas.
22. Certificado e unico por Evento e soma somente as horas declaradas das Agendas com presenca confirmada.

## 5. Estruturas de dominio recomendadas

Os nomes abaixo sao recomendacoes de modelagem para manter as regras auditaveis. Nao obrigam o uso exato do nome da tabela, mas indicam quais conceitos nao devem ser reduzidos a um unico campo agregado.

| Estrutura | Responsabilidade |
| --- | --- |
| `validacao_pessoa` | Historico de analise, responsavel, motivo, tentativa e datas. |
| `pessoa_documento` | Metadados privados de documentos e ciclo de validacao/exclusao fisica. |
| `acompanhamento/padrinho` | Convite, aceite, vigencia e encerramento do apadrinhamento. |
| `solicitacao_organizador` | Pedido, justificativa, status, admin e intervalo de nova tentativa. |
| `vinculo_ong_organizador` | Convite, aceite, vinculo ativo e encerramento; um vinculo ativo por organizador. |
| `evento + responsavel_pessoa_id` | Proprietario (ONG/Pessoa) separado do responsavel operacional quando necessario. |
| `agenda` | Horario, duracao declarada, vagas, status. |
| `inscricao` | Candidatura historica por Agenda, sem apagar desistencias/ausencias. |
| `avaliacao_voluntario` | Fonte de verdade da nota unica Pessoa + Evento. |
| `avaliacao_ong` | Feedback opcional Pessoa + Evento. |
| `xp_transacao` | Base, duracao, bonus, XP final e idempotencia por participacao. |
| `campanha_xp` | Bonus tematicos configuraveis e auditaveis. |
| `ocorrencia_confiabilidade` | Eventos que aumentam/reduzem/revertem a confiabilidade. |
| `certificado` | Codigo verificavel, Evento, Pessoa, carga horaria e emissao. |
| `admin_log` | Trilha de decisoes administrativas relevantes. |

## 6. Escopo de entrega e pos-pre-banca

Para proteger o prazo e a qualidade da entrega, o que esta abaixo deve ser tratado como evolucao posterior, salvo se alguma parte ja for estritamente necessaria para o fluxo principal funcionar.

| Pos-pre-banca | Diretriz |
| --- | --- |
| Reputacao da ONG/organizador | Usar ocorrencias de cancelamento tardio e outros sinais para criar um indice proprio. Na V1 apenas registrar o historico. |
| Automacao avancada de documentos | OCR, verificacao externa de autenticidade e integracoes com provedores oficiais. Na V1 ficam validacoes deterministicas + revisao humana. |
| Regras juridicas automaticas de antecedentes | Nao automatizar decisao sobre antecedentes. Manter fluxo de padrinho e analise humana. |
| Sistema completo de funcionarios da ONG | Nao criar multiplos membros, cargos internos, coorganizadores ou hierarquia de equipe. A conta ONG representa a entidade. |
| Multiplos vinculos de organizador | Organizador permanece limitado a uma ONG ativa por vez. |
| Transferencia de responsavel durante Evento | Nao implementar na V1; responsavel fica bloqueado depois do inicio. |
| Lista de espera | Fora do escopo; candidaturas bloqueiam quando as vagas aprovadas atingem a capacidade. |
| IA/score complexo de match | Nao implementar. O destaque usa apenas contagem de habilidades, causas e recursos coincidentes. |
| Pontuacao de confiabilidade da ONG | Fora da V1. Registrar ocorrencias agora para permitir formula futura. |
| Intervencao administrativa na finalizacao | Admin nao finaliza Evento abandonado. Apenas notificacoes/lembretes ao responsavel. |
| Credenciamento formal de monitores | A V1 nao controla quem estava fisicamente acompanhando estudante; apenas registra a validacao por Agenda. |
| Horas reais e horas extras | Nao computar. XP/certificado usam somente a duracao declarada. |
| Regras especiais de recuperacao de XP por desistência tardia | A antiga ideia de bonus de recuperacao (ex.: 1,3x) nao foi consolidada na V1 e fica fora do escopo. |
| Papeis acumulados | Organizador nao atua simultaneamente como voluntario; nao criar alternancia de papel na V1. |
| Canais externos sofisticados de notificacao | Priorizar notificacao interna. E-mail/push/SMS podem ser adicionados conforme tempo e infraestrutura. |
| Painel analitico avancado | Relatorios preditivos, dashboards de reputacao e metricas sofisticadas ficam para fase posterior. |

## 7. Checklist de fechamento da V1

- [ ] Validacao 18+ aplicada no cadastro e testes.
- [ ] Fluxo de validacao de voluntario implementado com historico, bloqueios e correcao solicitada.
- [ ] Documentos privados e retencao de 30 dias implementados ou preparados de forma segura.
- [ ] Padrinho integrado ao caso de antecedente e aos criterios de elegibilidade.
- [ ] Admin Master + permissoes granulares corrigidos.
- [ ] Promocao definitiva para organizador e vinculo unico com ONG implementados.
- [ ] Eventos aprovados/reprovados/editados com reanalise de campos sensiveis.
- [ ] Agendas com vagas proprias, candidatura ate 1h antes e sem lista de espera.
- [ ] Match simples por habilidades, causas e recursos.
- [ ] Presenca/desistencia/ausencia com justificativa e historico.
- [ ] Avaliacao unica do voluntario por Evento; avaliacao opcional da ONG.
- [ ] XP por Agenda, campanhas com teto tematico x3 e historico idempotente.
- [ ] Confiabilidade 0-100 a partir de 3 participacoes.
- [ ] Ranking somente por XP.
- [ ] Monitoria estudantil por Agenda e certificado consolidado por Evento com codigo verificavel.
- [ ] Cancelamentos e alteracoes notificam inscritos sem penalizacao indevida.
- [ ] Exclusao/anomizacao e auditoria preservam integridade sem manter dados pessoais desnecessariamente.
- [ ] Rotas mock/temporarias removidas ou substituidas antes do deploy final.
- [ ] Frontend revisado para refletir permissoes, estados, erros, loading e contratos reais da API.
- [ ] Testes de integridade, autorizacao e fluxos principais executados em MySQL antes do deploy.

> **Regra de congelamento da V1:** apos a aprovacao deste documento, novas ideias que nao sejam correcoes de falha do fluxo principal devem ser classificadas como pos-pre-banca para evitar aumento de escopo.

**Ajudae - conectar pessoas, causas e oportunidades de voluntariado com seguranca e rastreabilidade.**
