# F1 — Identidade, cadastro e apadrinhamento

## Cadastro Web

`GET /cadastro` continua usando a view e o componente de etapas do cadastro visual. No modo integrado, as etapas cobrem identificação, contato e endereço, indicação e orientação documental, acesso e revisão dos termos. O formulário usa apenas os campos suportados pelo cadastro de Pessoa. A operação envia JSON esperado pelo cliente de mesma origem, com corpo de formulário e CSRF em `POST /cadastro`. A tela retorna para o primeiro campo rejeitado e confirma a criação somente após a resposta 201.

`WebPessoaRegistrationRequest` reutiliza as regras do cadastro já validadas pela API e fixa a imagem padrão local. `PessoaRegistrationService` grava Pessoa, aceite dos termos e, se informado, pedido de apadrinhamento na mesma transação. A situação inicial é `aguardando_validacao`. A resposta Web não inclui dados pessoais ou token e a confirmação aponta para o login separado. Uma sessão existente não pode enviar o cadastro.

## Apadrinhamento e validação

- `POST /api/v1/pessoas/register` e `POST /cadastro` aceitam `codigo_padrinho` opcional. O código é conferido e o pedido pendente é criado de forma transacional. A API existente mantém sua resposta Bearer.
- `GET/POST/DELETE /api/v1/padrinhos/codigo` consultam, emitem e revogam o código do titular. `POST /api/v1/padrinhos/indicacao` admite correção nos estados previstos. O código revogado não desfaz pedido já protocolado.
- `POST /api/v1/admin/validacoes-pessoa/{pessoa}/exigir-padrinho` registra exigência fundamentada durante análise ativa. A aprovação consulta aceitação, vínculo vigente e elegibilidade do padrinho. A decisão humana e a aprovação dos documentos seguem obrigatórias.
- A migration incremental `2026_11_23_081100_add_padrinho_indicacao_to_pessoa_validation.php` grava os campos de exigência e a tabela de códigos protegidos. A integração não altera migrations históricas.

## Verificação

- A segunda rodada MySQL descartável aprovou os 7 casos de `PadrinhoWorkflowTest` e os 5 de `PessoaAuthTest`, com 117 assertivas ao todo. Esses resultados antecedem a conexão do formulário Web.
- A sintaxe PHP e o Pint passaram nos dois arquivos Web e no teste novo. `node --check` passou no módulo do formulário. `git diff --check` dos dois templates passou.
- `Bloco81WebPessoaRegistrationTest` cobre renderização integrada, criação pendente sem Bearer, descarte de papel e imagem manipulados, duplicidade, rejeição por idade ou sintaxe do código e ausência de gravação. Os resultados de execução em MySQL ficam no relatório de validação do bloco.

## Cobertura restante

O upload de documentos e a escolha de foto ficaram fora da criação Web. O perfil integrado ainda depende da conexão dos documentos, preferências, correção de dados e acompanhamento de apadrinhamento. A aprovação continua condicionada aos documentos obrigatórios. A prova de concorrência real da última vaga de padrinho, a renovação de documentos no Web e a homologação do armazenamento privado remoto permanecem pendentes.
