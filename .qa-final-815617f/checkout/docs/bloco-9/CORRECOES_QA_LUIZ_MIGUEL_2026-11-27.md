# Correções do QA independente · Luiz Miguel · Bloco 9

27/11/2026 · `feat/participacao-administracao`

A correção parte do [QA independente de 26/11](QA_INDEPENDENTE_LUIZ_MIGUEL_2026-11-26.md) e do manual de quatro integrantes. O PDF oficial indicado no AGENTS.md estava indisponível. As regras foram conferidas no [texto local de RN V1](../../Ajudae_Regras_de_Negocio_V1.md), no manual e nos contratos executáveis.

## Correções e delimitações

| Achado | Resultado |
| --- | --- |
| QA-01 · Git | A entrega inclui o delta local das nove frentes, as dependências de Blade e JavaScript, os testes e a documentação. A remoção preexistente de `.env.example` fica fora do commit. A referência inicial local de `origin/dev` era `74609bfd03cfbdbfa8dbfdcaf603c5dabd91794e`. A consulta ao remoto falhou na conexão à porta 443. |
| QA-02 · Sidebar | **Conclusões de apadrinhamento** aparece em **Moderação** quando a conta tem `padrinhos.validar_conclusao`. O item ativo usa `aria-current="page"`. Uma conta com a capacidade exclusiva pode abrir a fila sem ganhar acesso à validação de Pessoas. |
| QA-03 · Diretório de Pessoas | A validação de cadastros e documentos é atendida em `/admin/candidatos`. O diretório de moderação `/admin/usuarios` tem outra capacidade, `usuarios.moderar`, e permanece pendente de contrato Web próprio. O catálogo registra esse limite. |
| QA-04 · Autoria | A gestão de participações mostra o nome de exibição de quem decidiu a justificativa. O gestor recebe tipo e nome na leitura e na resposta à decisão Web. O titular continua recebendo situação, motivo e data. Os identificadores internos do decisor ficam na trilha de auditoria. |
| QA-04 · Reprovação | A reprovação Web de apadrinhamento requer motivo de até 2.000 caracteres, retorna 422 antes de gravar se faltar e mantém o 409 para pedido decidido. A decisão institucional sobre ONG recebe `status_ong`. O contrato de moderação de Evento exige motivo na reprovação. |

As rotas existentes permanecem como pontos de entrada. As respostas Bearer anteriores de justificativa e reprovação de apadrinhamento são preservadas. Detalhes em [Catálogo Web](CATALOGO_WEB.md) e [Contratos de participação](CONTRATOS_PARTICIPACAO.md).

## Rotas para revalidar

| Página | Percurso |
| --- | --- |
| `/admin/conclusoes-padrinho` | Abrir pela sidebar de uma conta com `padrinhos.validar_conclusao`. Conferir item ativo, enviar reprovação com motivo e abrir a lista novamente. |
| `/ong/eventos#gestao-participacoes` ou `/organizador/eventos#gestao-participacoes` | Abrir justificativa decidida com o responsável autorizado. Conferir autoria depois do PATCH e depois do GET de recarga. |
| `/minhas-participacoes` | Como titular, abrir a mesma ocorrência. Conferir situação, motivo e data, conforme a projeção própria. |

## Verificações

- 95 testes JavaScript, build Vite de 40 módulos, sintaxe PHP/Blade e JavaScript, Pint e `git diff --check`.
- 18 testes Feature em modo demonstrativo, com 309 asserções.
- O catálogo executado confirmou a proteção das rotas de apadrinhamento por sessão e capacidade, a validação específica da rota Web e a manutenção da ação Bearer anterior.
- 139 testes PHP integrados com 2.023 asserções aprovadas. O processo Laravel conferiu o MySQL 8.4.3 descartável em `127.0.0.1:34643` e o schema `ajudae_qa9_independente` antes da suíte. Os casos de navegação usam seu SQLite em memória do próprio teste.
- A tentativa inicial executou Feature demonstrativas com o modo Web integrado e comparou whitespace no HTML da sidebar. A execução final separou os modos e passou a verificar o link ativo pelo DOM.

## Etapas seguintes do plano

O QA complementar independente cobre as gravações no navegador, a revalidação completa de autorização e as corridas ainda listadas no relatório de 26/11. A revisão coletiva valida o commit publicado com as demais branches. A homologação no Railway e a prova de upload/download S3 pertencem ao ambiente de integração.
