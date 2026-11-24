# Bloco 8.1 — Verificação

## Ambiente

PHP 8.5.5, Laravel do checkout, Node 22, Vite 8.2.1 e MySQL 8.4.3 descartável. O preflight Laravel conferiu o schema, a porta 3318, o UUID e o diretório da instância antes dos testes. A jornada de navegador usou outro schema descartável, dois servidores HTTP locais e relógio controlado. A compilação Vite e Blade usou diretórios temporários. O `public/hot` preexistente do desenvolvedor foi preservado.

## Jornada funcional e visual

A execução Edge/Playwright criou Evento e duas Agendas por API, bloqueou candidatura antes da publicação, publicou por admin, inscreveu por quatro etapas Web, validou carta privada e concorrência na última vaga, cancelou uma Agenda e finalizou a outra após presença. A avaliação obrigatória foi registrada e o fechamento do Evento respondeu 200/409 para dois comandos enviados em paralelo por servidores que usam conexões MySQL distintas. A disputa da última vaga também respondeu 200/409.

O certificado estudantil respondeu 409 antes da confirmação de monitoria e foi emitido depois de confirmação auditada após o encerramento. Repetir a emissão devolveu o mesmo código. O ranking refletiu o incremento de 100 XP. A Pessoa avaliou a ONG pela Web após finalizar. O teste Web confirmou 419 sem token ou cabeçalho confiável de origem. A central marcou notificações como lidas e a verificação pública Web devolveu somente a projeção reduzida.

A inspeção incluiu as capturas de candidatura em 360, participações em 768 e ranking em 1280, além de medidas de transbordamento. O link de pular para o conteúdo foi corrigido depois da primeira inspeção de captura. O roteiro foi repetido com o build corrigido. A evidência de browser é específica aos percursos e larguras registrados.

[Resultados do navegador](evidencias/jornada-web.json) · [Cadastro, histórico e LGPD](evidencias/web-complementar.json) · [Candidatura 360 px](evidencias/candidatura-360.png) · [Participações 768 px](evidencias/participacoes-768.png) · [Ranking 1280 px](evidencias/ranking-1280.png)

## Resultados finais

| Execução | Aprovados | Asserções | Falhas e erros | Ignorados |
| --- | ---: | ---: | ---: | ---: |
| Backend e Web integrado em MySQL descartável | 204 | 2.089 | 0 | 1 |
| Imagem e EXIF, MySQL descartável | 16 | 180 | 0 | 0 |
| Upgrade legado de certificado, schema descartável separado | 1 | 8 | 0 | 0 |
| Navegação integrada e LGPD, SQLite em memória | 19 | 597 | 0 | 0 |
| Navegação e fluxos históricos em modo demonstrativo | 102 | 4.295 | 0 | 0 |
| JavaScript Node | 70 | — | 0 | 0 |

O upgrade legado foi ignorado no filtro amplo por exigir porta 3307. A mesma verificação foi executada em cópia temporária com a guarda usando porta 3318, sem editar o teste do repositório. O resultado suplementar usa schema dedicado e não foi contado duas vezes.

[Contagens estruturadas](evidencias/testes-resumo.json)

## Regressões e infraestrutura

- O inventário integrado registra 254 entradas, das quais 174 API. Em relação ao 8.0, 34 entradas foram adicionadas, o handler de cadastro de Pessoa mudou e 219 foram preservadas. Nenhuma entrada foi removida. [Comparação](evidencias/rotas-comparacao.csv)
- Vite compilou o bundle integrado. Blade compilou 151 arquivos na área temporária. PHP lint, Pint nos arquivos novos selecionados, `node --check` e `git diff --check` passaram. Os avisos CRLF do Git não alteraram esse resultado. Controllers compartilhados mantêm apontamentos de estilo preexistentes.
- Os cinco desvios a partir do 8.0 foram triados. A leitura documental exige caso atribuído inclusive para Master. Pessoa definitivamente reprovada recebe 403 em reenvio por Bearer. ONG com organizador designado não registra presença sob D05, que trata de fechamento do Evento. Os testes foram adaptados para provar as negativas e os atores permitidos.
- Na rodada intermediária, testes de demonstração rodados no modo integrado falharam por configuração. As regressões de demonstração passaram no modo próprio. A seleção SQLite ampla encontrou uma migration histórica não portátil ao incluir o teste de imagem, que passou na repetição em MySQL. O teste HTTPS de assets de produção ficou fora da seleção final porque o checkout tem `public/hot`.

Ambiente de banca, storage externo, homologação humana e produção continuam sem validação nesta execução. A distribuição entre subagentes teve interrupção por limite de uso. A coordenação retomou F2 e a integração transversal, e as execuções finais refletem os arquivos consolidados.
