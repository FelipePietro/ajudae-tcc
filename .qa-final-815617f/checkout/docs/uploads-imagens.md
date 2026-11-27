# Uploads de imagens — API V1

Fotos de Pessoa, logos de ONG e banners de Evento são arquivos gerenciados pelo Laravel; autenticação e metadados permanecem no MySQL. O Railway Storage Bucket S3 deve permanecer privado. Documentos pessoais usam outro disco e nunca passam pelos endpoints abaixo.

| Recurso | Upload/substituição | Remoção | Leitura pública |
| --- | --- | --- | --- |
| Pessoa | `POST /api/v1/pessoas/{id}/imagem` | `DELETE /api/v1/pessoas/{id}/imagem` | `GET /api/v1/pessoas/{id}/imagem` |
| ONG | `POST /api/v1/ongs/{id}/imagem` | `DELETE /api/v1/ongs/{id}/imagem` | `GET /api/v1/ongs/{id}/imagem` |
| Evento | `POST /api/v1/eventos/{id}/imagem` | `DELETE /api/v1/eventos/{id}/imagem` | `GET /api/v1/eventos/{id}/imagem` |

Upload aceita `multipart/form-data`, campo `imagem`, JPEG/JPG, PNG ou WebP de até 5 MiB (5.242.880 bytes). O servidor valida extensão, MIME detectado, imagem decodificável, fechamento do formato, no máximo 8000 px por lado e 25 megapixels. Antes de gravar, JPEG é reencodificado com GD após aplicar a orientação EXIF; PNG/APNG e WebP têm chunks de metadados removidos, preservando dados de imagem e animação. EXIF/GPS/XMP/texto do arquivo enviado não são gravados. A reencodificação JPEG pode alterar ligeiramente qualidade; a remoção de perfis de cor PNG/WebP pode alterar reprodução de cores. Links legados externos não passam por sanitização. Não há recorte nem thumbnail. Cabe configurar PHP/web server para admitir payload de pelo menos 5 MiB (`upload_max_filesize`, `post_max_size` e limite do proxy); rejeição antes do Laravel pode resultar em 413.

Upload e DELETE exigem Bearer token Sanctum. Pessoa só altera a própria foto; ONG só altera o próprio logo; banner usa a policy `EventoPolicy::update` (ONG proprietária, dono independente ou organizador vinculado responsável operacional), com evento `cancelado` ou `finalizado` bloqueado. Admin e registros de permissão não concedem esse acesso. 401 indica falta de autenticação, 403 falta de autorização, 404 recurso inexistente/não publicável, 409 evento terminal, 422 arquivo inválido ou ausente. O GET da Pessoa só é público para voluntário ou organizador aprovado (estes aparecem em eventos); o GET do Evento só para evento ativo.

O retorno de upload (`201` inicial, `200` substituição) e remoção (`200`) segue o envelope padrão `success`, `message`, `data`:

```json
{"success":true,"message":"Imagem salva.","data":{"url":"https://host/api/v1/ongs/1/imagem?v=abc123def456","mime_type":"image/png","size_bytes":12345}}
```

A URL também aparece nos campos legados `pfp_pessoa_link`, `pfp_ong_link` e `imagem_evento_link` dos resources. O parâmetro `v` muda a cada substituição para invalidar caches, sem divulgar a chave interna. Antes do primeiro upload, esses campos continuam exibindo o link legado externo. Ao substituir, a referência legada é limpa do banco, mas seu destino externo nunca é buscado nem excluído. DELETE repetido devolve `url: null` e não remove objetos externos.

```bash
curl -X POST 'https://host/api/v1/ongs/1/imagem' -H 'Authorization: Bearer TOKEN' -F 'imagem=@logo.png;type=image/png'
curl -X DELETE 'https://host/api/v1/ongs/1/imagem' -H 'Authorization: Bearer TOKEN'
curl -L 'https://host/api/v1/ongs/1/imagem' -o logo.png
```

```js
const body = new FormData();
body.append('imagem', file);
const response = await fetch('/api/v1/pessoas/1/imagem', {
  method: 'POST', headers: { Authorization: `Bearer ${token}` }, body
});
const { data } = await response.json();
imageElement.src = data.url;
```

Não defina `Content-Type` manualmente no `fetch` com `FormData`: o navegador inclui o boundary. A leitura pública retorna bytes com MIME, `nosniff` e cache de 5 minutos. Isso consome banda e CPU do Railway; use cache/CDN externo apenas após revisar privacidade e invalidação. Documentos privados continuam com `no-store` e jamais são servidos aqui.

Configure `IMAGE_STORAGE_DISK=image_local` em desenvolvimento/testes (diretório privado `storage/app/public-images`) ou `railway_images` no Railway, após configurar as variáveis S3 conforme [guia do bucket](railway-storage-s3.md). O disk S3 usa a raiz `images/` no mesmo bucket privado; documentos usam `railway_private`, raiz `private/`. Não enviar credenciais ao frontend nem ao repositório. Referências antigas `firebase_images` não são alteradas automaticamente; sem bytes migrados, o GET retorna indisponibilidade controlada.

Cada substituição grava em chave aleatória nova sob `pessoas/`, `ongs/` ou `eventos/`, atualiza o MySQL sob lock e agenda exclusão do objeto anterior após commit. Falhas de exclusão ficam em `pending_image_deletions`; o comando manual `php artisan imagens:reconciliar --limit=100` **não está agendado** enquanto a política operacional de limpeza não for aprovada. A reconciliação verifica todas as referências atuais antes de apagar. Em falha de banco após upload, tenta remover o novo objeto e, se não conseguir, registra a pendência. MySQL e S3 não possuem transação distribuída: uma falha extrema entre gravação remota e registro da pendência pode exigir inspeção operacional do prefixo de imagens. Não há teste com dois processos paralelos; o lock transacional de linha cobre concorrência no MySQL. Configure PHP com GD e EXIF, e memória suficiente: o pré-cálculo estima até cerca de 16 bytes por pixel mais buffers; o limite total de 25 MP pode exigir cerca de 450 MiB livres por request. Abaixo disso, uploads grandes retornam erro 503 controlado, sem gravação.
