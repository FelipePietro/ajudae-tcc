# Bloco 9 — Contratos F2 de gestão de Eventos e Agendas

As rotas em `routes/web-event-management.php` usam sessão tipada Laravel em `AJUDAE_WEB_AUTH=true`. Envie `Accept: application/json` e `X-CSRF-TOKEN` nas escritas. O frontend pode consumir estes contratos sem Bearer. Os Services e Policies também atendem aos comandos centrais da API existente.

| Método e URI Web | Ator autorizado | Entrada e saída |
| --- | --- | --- |
| GET `/gestao/eventos/catalogos` | ONG ativa ou organizador aprovado e operacional | 200 com `data.categorias`, `data.habilidades`, `data.causas`, `data.recursos`. Cada lista contém ID e nome. |
| GET `/gestao/eventos` | Gestor, no próprio escopo | `per_page` de 1 a 100, padrão 20. 200 com paginação simples em `data`, registros em `data.data` e `data.next_page_url`. |
| GET `/gestao/eventos/{evento}` | Gestor do registro | 200 com `EventoResource`, `categoria`, `agendas`, `habilidades`, `causas`, `recursos` e IDs de proprietário e responsável. |
| POST `/gestao/eventos` | ONG ativa ou organizador independente aprovado e operacional | Campos e exemplo abaixo. 201 com Evento e Agendas persistidos em `data`. |
| PATCH `/gestao/eventos/{evento}` | Gestor do registro | Campos do Evento e listas de IDs opcionais. Cada lista enviada substitui as associações do grupo correspondente. 200 com dados atualizados. |
| POST `/gestao/eventos/{evento}/cancelar` | Gestor do registro | `motivo` obrigatório, até 2.000 caracteres. 200 com status `cancelado`. As Agendas não terminais são canceladas com histórico, autoria e notificações. |
| GET `/gestao/eventos/{evento}/imagem` | Gestor do registro | 200 com imagem gerenciada e cache privado, 304 com ETag válido. |
| POST `/gestao/eventos/{evento}/imagem` | Gestor do registro | Multipart com `imagem`. 201 no primeiro upload gerenciado, 200 na substituição. `data` contém `url`, `mime_type` e `size_bytes`. |
| DELETE `/gestao/eventos/{evento}/imagem` | Gestor do registro | 200. `url`, `mime_type` e `size_bytes` retornam nulos. |
| POST `/gestao/eventos/{evento}/agendas` | ONG proprietária ou proprietário independente | `data_inicio`, `data_fim`, `vagas_agenda`. 201 com a Agenda e reanálise do Evento quando aplicável. |
| PATCH `/gestao/agendas/{agenda}` | Gestor do Evento associado | `data_inicio`, `data_fim` e `vagas_agenda` são parciais. O par resultante deve durar pelo menos 30 minutos. 200 com dados persistidos. |
| DELETE `/gestao/agendas/{agenda}` | ONG proprietária ou proprietário independente | 204 para Agenda fora de estado terminal, sem inscrições e com outra Agenda remanescente. |
| POST `/gestao/agendas/{agenda}/cancelar` | Gestor do Evento associado | `motivo` obrigatório. 200 com estado `cancelado` e histórico preservado. |

Gestor é a ONG ativa proprietária, a Pessoa organizadora independente proprietária ou a Pessoa organizadora designada para aquele Evento com vínculo vigente à ONG proprietária ativa. As escritas revalidam a versão de autenticação, o vínculo e o estado aplicáveis. A criação institucional cabe à ONG. A criação independente cabe ao proprietário sem vínculo vigente. RN008 e RN011 previam criação institucional por organizador vinculado. A decisão D04/D05 desta execução revisa essa permissão.

## Criação conjunta

`nm_evento` até 64 caracteres, `descricao_evento` até 256, `modalidade_evento` em `presencial`, `online` ou `hibrido`, `cat_evento_id` de categoria existente e `vagas_evento` inteiro positivo são obrigatórios. Presencial e híbrido exigem `logradouro_evento`, `cep_evento` com oito dígitos, `cidade_evento`, `bairro_evento` e `uf_evento` com dois caracteres. `compl_evento` é opcional. `habilidades`, `causas` e `recursos` aceitam listas opcionais de IDs existentes e distintos. A titularidade vem da conta da sessão.

Cada Agenda exige `data_inicio`, `data_fim` e `vagas_agenda` inteiro positivo, com duração mínima de 30 minutos. Envie data e hora ISO 8601 com offset explícito. O backend normaliza os instantes para UTC. A Agenda controla a própria lotação e o total do Evento é informativo.

Exemplo de `POST /gestao/eventos` em JSON

```json
{
  "nm_evento": "Oficina de apoio escolar",
  "descricao_evento": "Apoio na preparação das atividades da turma.",
  "modalidade_evento": "online",
  "cat_evento_id": 1,
  "vagas_evento": 30,
  "habilidades": [],
  "agendas": [
    {
      "data_inicio": "2026-12-04T09:00:00-03:00",
      "data_fim": "2026-12-04T10:30:00-03:00",
      "vagas_agenda": 50
    }
  ]
}
```

Exemplo abreviado do 201 em sessão de ONG

```json
{
  "success": true,
  "message": "Evento criado com sucesso.",
  "data": {
    "evento_id": 100,
    "ong_id": 20,
    "pessoa_id": null,
    "responsavel_pessoa_id": null,
    "cat_evento_id": 1,
    "status_evento": "aguardando a confirmação",
    "imagem_evento_link": null,
    "vagas_evento": 30,
    "agendas": [
      {
        "agenda_id": 120,
        "evento_id": 100,
        "status_ativo": "ativo",
        "data_inicio": "2026-12-04T12:00:00.000000Z",
        "data_fim": "2026-12-04T13:30:00.000000Z",
        "vagas_agenda": 50
      }
    ]
  }
}
```

Para incluir banner na criação, use multipart com os mesmos campos, `agendas[0][data_inicio]`, `agendas[0][data_fim]`, `agendas[0][vagas_agenda]` e `imagem_evento`. Aceita JPEG, PNG ou WebP íntegros até 5 MiB, 8.000 px por lado e 25 megapixels. Na gestão, `imagem_evento_link` aponta à leitura privada enquanto a imagem estiver no armazenamento gerenciado. A leitura pública da imagem gerenciada é liberada com a publicação.

## Erros e compatibilidade

- 401 indica sessão ausente, expirada ou revogada. 403 indica ator inadequado ou registro fora do escopo. 404 indica recurso inexistente ou imagem sem referência gerenciada. 419 indica falha de CSRF.
- 422 indica validação por campo, inclusive `errors.agendas`, `errors["agendas.0.data_fim"]` e `errors.imagem_evento` no cadastro. 409 cobre estado incompatível, redução abaixo da ocupação ou exclusão da última Agenda. 503 cobre indisponibilidade de armazenamento na criação ou leitura.
- `POST /api/v1/eventos` e `POST /api/v1/pessoas/eventos` preservam o campo `imagem_evento_link` e exigem o array `agendas`. A API de Agenda avulsa continua disponível para um Evento existente.
- Campos de titularidade, designação e estado são definidos no servidor. No contrato Web, entradas indevidas nesses campos resultam em 422. O contrato Bearer mantém o comportamento anterior de ignorar campos extras protegidos.
- Uma falha de persistência reverte Evento, Agendas e associações. A imagem gerenciada gravada é removida ou tem a exclusão registrada para retentativa. Aprovar um Evento legado sem Agenda retorna 409. A regularização depende de cadastrar a Agenda real ou decidir o cancelamento.

## Conferência operacional

- Cadastre como ONG ativa ou organizador independente, confira a resposta 201 e consulte a gestão enquanto o Evento aguarda aprovação. Teste uma Agenda e duas Agendas cujas vagas somadas ultrapassem o valor informativo do Evento.
- Envie a segunda Agenda com intervalo de 29 minutos. A resposta deve apontar `agendas.1.data_fim` e a consulta de gestão deve continuar sem o novo Evento. Um banner inválido aponta `imagem_evento`.
- Tente excluir a única Agenda. O 409 orienta preservar o registro e cancelar a Agenda, quando aplicável.
- Confirme que o organizador formalmente designado consulta e edita seu Evento, e que recebe 403 ao criar uma Agenda ou abrir a gestão de outro Evento da ONG. A ONG proprietária pode operar os próprios registros.

A aplicação mantém os registros que já existiam. O catálogo e os detalhes públicos exigem estado ativo e pelo menos uma Agenda. Um órfão legado continua disponível na gestão autorizada para regularização ou cancelamento pelo titular. Em uma base autorizada, o inventário pode ser feito com a consulta abaixo. O banco principal ficou fora dessa conferência.

```sql
SELECT evento_id, status_evento
FROM evento
WHERE NOT EXISTS (SELECT 1 FROM agenda WHERE agenda.evento_id = evento.evento_id);
```

[EventoManagementService](../../app/Services/EventoManagementService.php) · [AgendaManagementService](../../app/Services/AgendaManagementService.php) · [EventoPolicy](../../app/Policies/EventoPolicy.php) · [Testes D02](../../tests/Feature/Bloco9EventManagementTest.php)

