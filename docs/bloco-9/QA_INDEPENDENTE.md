# QA independente do Bloco 9

## Ambiente e isolamento

A comparação usa o commit-base `c1ff91f2d92f26115ade7e7687c27ee7c2467fdd` e o delta local do Bloco 9. O QA retomou em 24/11/2026. O MySQL 8.4.3 temporário foi reiniciado com o diretório previamente isolado e conferido por UUID, em `127.0.0.1:33419`. O preflight consultou `DATABASE()`, `@@port`, `@@server_uuid` e `@@datadir` antes de iniciar migrações ou testes. A base arquivada usa o schema `ajudae_tcc_testing` dessa instância. O delta usa `ajudae_bloco9_final_testing` com credencial restrita ao schema, modo `testing` e armazenamento descartável. As duas suítes usaram PHP 8.5.5.

As tentativas de comparação arquivada que usaram o classmap do checkout ou a inferência automática de diretório do Laravel foram descartadas. A execução de referência usa cópia de `git archive`, prioridade de autoload para as classes da cópia, diretório de aplicação explícito e verificação por reflexão. O backend da aplicação principal, seu banco e credenciais de produção ficaram fora das consultas e migrações.

## Verificações

| Verificação | Comando ou procedimento | Resultado |
| --- | --- | --- |
| Suite demonstrativa do commit-base | `vendor/bin/phpunit` com filtro das classes demonstrativas, em snapshot e com modo Web integrado desligado | 91 testes e 3.631 asserções aprovadas na primeira rodada. Dois arquivos de teste demonstrativo locais e não rastreados no commit-base explicam a diferença em relação aos 102 casos históricos. |
| Comparação integrada em MySQL descartável | `vendor/bin/phpunit` no snapshot e no delta, com filtros apropriados ao modo integrado e validação da instância e do schema | Base com 244 aprovados e 2.819 asserções. Delta com 259 aprovados e 3.068 asserções. Um caso de atualização legada de certificados foi ignorado em cada execução porque exige instância reservada na porta 3307 e `RUN_CERT_UPGRADE_TEST=1`. |
| CSRF, sessão e fronteira Web/Bearer | `vendor/bin/phpunit Bloco9IndependentCsrfTest.php`, caso temporário de QA com sessão em arquivo e verificação de origem ativa | 1 teste e 11 asserções aprovadas no reteste. POST do cadastro institucional, POST de Evento e PATCH administrativo sem prova válida retornaram 419. Com token válido, cadastro incompleto chegou à validação 422 e chamadas anônimas protegidas retornaram 401. A consulta API sem Bearer retornou 401. |
| JavaScript | `node --test` para os testes JavaScript puros do projeto | 95 testes aprovados na primeira rodada. |
| Build | `npm.cmd run build -- --outDir <temporário>` | Vite 8.2.1 compilou 37 módulos. Manifesto e oito assets emitidos fora da árvore do projeto coincidem por SHA-256 com o build local usado no reteste de proxy. |
| Proxy e assets HTTPS | `vendor/bin/phpunit Bloco9BuiltProxyTest.php`, subclasse temporária que aponta o Vite para um marcador hot inexistente | 5 testes e 28 asserções aprovadas no reteste dedicado. O marcador de desenvolvimento `public/hot` presente na máquina foi preservado. |
| Sintaxe e whitespace | `php -l` sobre os PHP do delta e `git diff --check` | A revisão inicial verificou 54 arquivos PHP. O repasse final aprovou os 63 PHP do delta. Verificação de whitespace aprovada. |
| Inventário de rotas | `php artisan route:list --json`, comparação por método e URI no modo integrado | 254 entradas na base e 353 no delta, incluindo rotas de utilidade. As 99 adições são Web. As 174 assinaturas da API mantêm método e URI. |
| Concorrência no MySQL descartável | Três pares de requisições HTTP Bearer em processos PHP e conexões separados. Um bloqueio inicial por Evento permitiu liberar as duas operações juntas. | Última vaga: 200 e 409, com uma aprovação. Remoção simultânea: 204 e 409, com uma Agenda restante. Encerramento simultâneo: 200 e 409, com autor persistido. |

O ensaio de CSRF detectou que o binding acontecia antes da autenticação em uma mutação administrativa e retornava 404 para um identificador inexistente. Após a correção da prioridade dos middlewares, o reteste confirmou a resposta 401 da sessão anônima mesmo com token CSRF válido.

As verificações focadas da implementação, em schemas MySQL descartáveis, também passaram: Eventos e Agendas com 89 testes e 869 asserções, Participação com 82 testes e 1.053 asserções, e o reteste de cadastro institucional, contas, navegação, autorização e campanhas com 42 testes e 830 asserções. O repasse das classes novas e das fronteiras de identidade e histórico concluiu 32 testes e 375 asserções. Esses conjuntos se sobrepõem à regressão integrada.

O primeiro ensaio concorrente falhou por colisão do nome de um helper temporário com `event()` do Laravel. O reteste corrigiu apenas o script de QA e usou novas fixtures sintéticas no mesmo schema descartável validado.

## Limites

Casos HTTP e persistência usaram identidades e documentos sintéticos. Armazenamento de homologação S3, envio de mensagens externo e publicação ficaram fora do ambiente de QA. A interface não foi homologada visualmente neste bloco de backend.
