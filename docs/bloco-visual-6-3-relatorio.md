# Bloco Visual 6.3 — estados, erros, feedback e linguagem

Execução 18–19/11/2026, branch `feat/ajustes-pre-deploy`, HEAD `1c028b311edcff2869f1d299dbbff5924425b450`. AGENTS.md lido integralmente; nenhum mais específico localizado. O anexo foi aplicado somente ao 6.3, incorporando o requisito posterior de linguagem e links de homologação exclusivamente na porta 8000. Nenhum módulo seguinte foi iniciado.

Baseline com SHA-256 de **1.465 arquivos** registra as alterações locais anteriores. O delta desta execução não é comparado apenas com o HEAD de outubro. Relatórios dos blocos anteriores, integração, 5.2/5.6 e 6.1/6.2 foram consultados. PDF original disponível, extraído localmente: 15 páginas, SHA-256 `cf36b76fd0061feaaac774a2f85ef9ddff70b3d9f1bf03f9d146b3a3d7117d48`. Extração textual foi usada para RN; não se afirma inspeção visual do PDF nesta execução.

## Alterações e causas

- **Login:** Enter era cancelado sem executar a validação. Agora Enter e clique usam `submit`, com foco na mensagem inválida e bloqueio durante a navegação. Retorno libera o controle. Credenciais continuam sendo descartadas; navegação não é prova de autenticação.
- **Editar cadastro Pessoa/ONG:** a revisão só marcava o primeiro inválido, sem limpar o erro após correção. Agora todos os campos são validados com texto inline e `aria-invalid`; primeiro erro recebe foco, valores permanecem e revisão válida limpa as marcações. Enter funciona também na revisão.
- **Eventos:** retry usava URL sem query e perdia a persona. Conserva contexto e demais parâmetros e troca somente o cenário sintético para disponível. Contagem e vazio são anunciados; busca tem label; filtros informam estado expandido e retornam foco com Escape/aplicação. Informações pessoais de posição/badges não aparecem para ONG/visitante.
- **HTTP:** mensagens antes desiguais foram centralizadas em `x-error-screen`, preservando 401/403/404/409/419/422/500/503. Início contextual, botões existentes, sem detalhes privados. 403 não promete liberação por login; retry GET não repete escrita. Uma rota local/testing de inspeção permite reproduzir cada status sem lançar falha real nem mudar renderer da API.
- **Diálogo de senha:** retirado Fechar redundante, preservando X acessível e ação de conferir. Foco/Escape compartilhados mantidos.
- **Admin:** gravação do histórico podia falhar e ainda produzir mensagem afirmativa. No caso normal restaura estado anterior e foca a falha. Quando um acompanhamento vinculado já foi salvo por outro mecanismo legado, informa falha do log e pede conferir situação, sem alegar rollback da decisão. Filtros continuam selecionados após decisões.
- **Linguagem:** revisão literal em notificações, cadastro, perfil, documentos, convites, admin, privacidade, certificados, ranking, XP e confiabilidade. “Atividade nesta aba” virou “Atividade recente”; origem histórica tem rótulo natural; botões técnicos de preenchimento/seleção sem arquivo e exemplos de acompanhamento ficam somente na inspeção explícita. Rodapé e texto do certificado deixam de expor detalhes de desenvolvimento/API. Não alterados IDs, stores, históricos ou mecanismos necessários aos testes.

Mensagens conservadas por veracidade: revisão não envia dados, cria conta, altera senha, registra aceite, responde legalmente ou efetiva exclusão. Uma revisão LGPD não vira protocolo de atendimento. O catálogo de textos e o patch mostram os pares antes/depois; nomes de identidades sintéticas permanecem, sem usar contas reais. Controles de inspeção não representam autorização.

## Evidência e limites

O **navegador interativo Codex** abriu a aplicação, reproduziu Enter sem efeito, verificou a correção com foco no erro, retry ONG mantendo contexto, vazio da busca, correção de e-mail, diálogo com X único/Escape/retorno de foco e páginas administrativa/HTTP em 360/768/1280. Os logs finais consultados não registraram erro JS. `getScreenshot()` falhou com `Unable to capture screenshot`: a superfície interativa forneceu AX/DOM e interação, mas não fotografia. Capturas e inspeção visual efetiva vieram do **Edge/Playwright complementar**. Não se declara uso de leitor de tela real ou inspeção individual de cada fotografia.

As evidências técnicas finais e totais são consolidados abaixo pelo inventário. Os testes são visuais/sintéticos, com caminhos Feature explicitamente selecionados, banco bloqueado pelas classes e HTTP externo impedido. Não houve suíte backend integral, migrations ou seeders nesta execução. A falha histórica no banco de testes narrada no 6.2 permanece registrada naquele relatório e não é reclassificada como teste deste módulo.

Ocorrências corrigidas durante a execução: dois textos e a nova rota exigiram atualização de expectativas Feature; condicionais Blade de documentação precisaram ficar em linhas separadas para conservar o botão de revisão fora do cadastro; seletores de navegador foram ajustados para campo editável real e controle visível; uma navegação GET recebeu `ERR_ABORTED`, tratada com repetição explícita da visita no teste. A expectativa do teste de storage inválido foi atualizada para a nova mensagem. Não foi removida cobertura por causa dessas ocorrências. Totais de repetições não são somados como testes inéditos.

Homologação humana final continua pendente. Complementações históricas 5.2 permanecem rastreadas; regressões atuais não constituem aceite humano retroativo. A revisão de linguagem deve continuar no 6.4/6.5 e na auditoria final.

## Arquivos, rotas e entrega

[Inventário de arquivos e URLs](bloco-visual-6-3-inventario.json), [matriz de regras](bloco-visual-6-3-matriz-regras.md), [catálogo de componentes](bloco-visual-6-3-catalogo-feedback.md), [textos antes/depois](bloco-visual-6-3-textos.md), [plano de integração](bloco-visual-6-3-plano-integracao.md) e [checklist clicável por jornada](bloco-visual-6-3-checklist-homologacao-manual.md).

Nova: `/inspecao/estados/{status}?inspecao=1`, somente local/testing. Alteradas: login, feed, edição cadastral, mensagens e estados compartilhados nas rotas existentes. Impactadas: páginas que usam componentes e scripts compartilhados (incluindo variantes por persona). Regressão: cadastros/perfis, Eventos/Agendas/candidaturas, convites/vínculos, Admin/apadrinhamento, presença/fechamento, avaliações, XP/confiabilidade/ranking e certificados. URLs reais visitadas e HTTP constam no inventário, sem deduzir URL de caminho Blade.

ZIP incremental em `C:/Users/felep/Desktop/Zips`, sem projeto inteiro, `.env`, segredos, dependências, build/cache, dump ou pacotes anteriores. Inclui manifesto, hashes por entrada, patch contra baseline e evidências. Validação de CRC e leitura independente dos bytes/hashes integra o empacotamento. SHA-256 externo entregue com o arquivo.

Backend de negócio, contratos API, auth, banco, migrations, jobs, S3 e Railway não foram alterados. AGENTS.md foi preservado; propõe-se registrar posteriormente o 6.3, o catálogo HTTP, a revisão transversal de linguagem e seus limites após aceite humano. Não houve git add, commit, push, merge, reset/clean ou deploy.

Decisões para você responder: **Nenhuma**. Próximo passo: homologar as jornadas do checklist neste mesmo chat; 6.4 é somente referência, sem início autorizado.

<!-- verificacao-consolidada -->

## Verificações consolidadas desta execução

- Feature: **95 testes / 4.179 asserções passaram** em `feature-final-seguro.txt`; repetição final dos três arquivos PHP de testes afetados: **16 testes / 496 asserções**, sem somar como casos inéditos (`feature-complementar-final.txt`).
- JavaScript: **65 testes passaram**, sem falha, cancelamento ou ignorado (`javascript-final.tap`). PHP 8.5.5, Node 22.14.0; versões e comandos adicionais estão em `verificacoes-finais.json`.
- Seleção Feature por caminhos explícitos `Visual*Test.php`, DemoFrontendTest, ContextualBrandNavigationTest e OngPublicNavigationTest; `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`. Classes visuais bloqueiam acesso a banco e HTTP real. Não foi usado filtro por nome genérico que alcance testes backend.

| Edge / Playwright — rodada final de cada suíte | Visitas | Ações | Checks | Capturas | Falhas / exceções JS |
| --- | --- | --- | --- | --- | --- |
| principal | 126 | 46 | 90 | 78 | 0 / 0 |
| admin | 7 | 23 | 7 | 7 | 0 / 0 |
| encerramento | 12 | 36 | 12 | 6 | 0 / 0 |
| regressao | 45 | 43 | 0 | 45 | 0 / 0 |
| acoes-anteriores | 40 | 49 | 12 | 13 | 0 / 0 |
| privacidade | 142 | 54 | 103 | 78 | 0 / 0 |
| certificados | 45 | 99 | 54 | 27 | 0 / 0 |

Somente os registros finais de cada suíte acima compõem os totais; rotas repetidas entre suítes não são jornadas inéditas. São **417 visitas, 350 ações, 278 checks e 254 capturas** nos recortes executados. HTTP esperados de erro podem produzir entradas de console/rede; fontes externas bloqueadas usam fallback. Nenhuma exceção JavaScript nos recortes finais. As suítes que monitoram API não registraram chamadas reais.

Build Vite, Pint no escopo, sintaxe PHP/JS, Blade e diff são registrados em `build-definitivo.txt` e `verificacoes-finais.json`. O primeiro Pint encontrou CRLF/operadores em três arquivos; somente esses arquivos foram formatados e a checagem foi repetida. Avisos de CRLF no diff global são distinguidos de erro de whitespace.

Amostras reais inspecionadas: HTTP 403 360/1280 (incluindo correção do rodapé), formulário institucional 768 e falha de gravação administrativa 1280; não se afirma inspeção visual individual de todas as capturas. Foco negativo de gravação foi corrigido para prevalecer após o fechamento nativo do diálogo. O teste de certificado passou a aguardar o carregamento após clicar no link, antes de ler o estado; a leitura imediata do loader não comprovava falha de emissão.

51 arquivos de implementação/testes/auditoria neste delta. 54 views catalogadas por marcadores de feedback/estado; 346 combinações registradas de URL/largura/suíte. O catálogo não substitui inspeção humana integral.
