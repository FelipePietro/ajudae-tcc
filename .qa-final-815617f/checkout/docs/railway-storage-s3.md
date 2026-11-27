# Railway Storage Bucket S3 privado — preparação Ajudae

O bucket já foi criado pelo responsável pelo projeto. **Este documento não autoriza deploy, criação de outro bucket, acesso a dados reais ou smoke test externo.** A integração local foi validada apenas com `Storage::fake()`.

## Conexão posterior no painel Railway

No futuro serviço Laravel, criar variáveis de ambiente usando referências de variáveis do bucket existente, sem copiar valores para GitHub, chat ou ZIP. Substituir `NOME_DO_SERVICO_BUCKET` pelo nome real do serviço no painel; não é o nome S3 do bucket. Conferir cada valor na aba Credentials do bucket antes de ativar:

| Variável lida pelo Laravel | Referência de variável Railway (exemplo estrutural) | Observação |
| --- | --- | --- |
| `AWS_ACCESS_KEY_ID` | `${{NOME_DO_SERVICO_BUCKET.ACCESS_KEY_ID}}` | Segredo de acesso, não expor |
| `AWS_SECRET_ACCESS_KEY` | `${{NOME_DO_SERVICO_BUCKET.SECRET_ACCESS_KEY}}` | Segredo, não expor |
| `AWS_DEFAULT_REGION` | `${{NOME_DO_SERVICO_BUCKET.REGION}}` | Usar a região informada, sem valor presumido |
| `AWS_BUCKET` | `${{NOME_DO_SERVICO_BUCKET.BUCKET}}` | Nome S3 real; não usar `RAILWAY_BUCKET_NAME` |
| `AWS_ENDPOINT` | `${{NOME_DO_SERVICO_BUCKET.ENDPOINT}}` | Endpoint HTTPS informado no painel |
| `AWS_USE_PATH_STYLE_ENDPOINT` | `false` ou `true`, conforme Credentials | Buckets recentes tendem a virtual-hosted; alguns antigos exigem path-style |
| `DOCUMENT_PRIVATE_DISK` | `railway_private` | Ativar explicitamente para novos documentos |
| `IMAGE_STORAGE_DISK` | `railway_images` | Ativar explicitamente para novas imagens |

Os exemplos de referência acima são placeholders: confirme a sintaxe aceita no painel e selecione o serviço de bucket correto. Mantenha `FILESYSTEM_DISK=local` para operações gerais. Não definir ACL pública, URL pública permanente, presigned PUT do navegador nem `AWS_URL` para os disks privados. O endpoint deve ser HTTPS; sem chave, segredo, bucket, região ou endpoint válido o serviço falha antes de gravar. Use credenciais com menor privilégio disponível para Put/Get/Head/Delete nos objetos necessários; Railway controla o acesso ao bucket, e a aplicação controla autorização dos usuários.

**Não confundir nomes de saída com nomes lidos pelo Laravel.** A documentação atual dos Buckets confirma as referências de serviço `ACCESS_KEY_ID`, `SECRET_ACCESS_KEY`, `REGION`, `BUCKET` e `ENDPOINT` usadas na tabela. Já a saída de credenciais do Railway CLI pode mostrar `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_S3_BUCKET_NAME`, `AWS_ENDPOINT_URL` e `AWS_S3_URL_STYLE`. Se o painel mostrar esta segunda forma, mapear `AWS_S3_BUCKET_NAME` para **`AWS_BUCKET`** e `AWS_ENDPOINT_URL` para **`AWS_ENDPOINT`** no serviço Laravel. `AWS_S3_URL_STYLE=virtual` corresponde a `AWS_USE_PATH_STYLE_ENDPOINT=false`; `path` corresponde a `true`. Conferir o estilo informado pelo bucket concreto, inclusive buckets antigos. Não copiar/exportar valores de credenciais nesta revisão nem presumir que a saída do CLI seja idêntica às referências de variável do painel.

`railway_private` usa a raiz `private/` do bucket e chaves `documentos/<40 caracteres aleatórios>.<extensão>`; `railway_images` usa `images/` com chaves `pessoas|ongs|eventos/<40 caracteres aleatórios>.<extensão>`. O bucket inteiro permanece privado. Imagens são lidas publicamente **somente** pelo GET controlado da API Laravel, com ETag/304, não por URL de S3. Documentos só passam por download autenticado/autorizado, com auditoria administrativa. O frontend segue usando `multipart/form-data` nos mesmos endpoints e campo `imagem`; não há upload direto para S3. Certificados PDF são renderizados sob demanda, sem objeto S3.

## Legado e rollback

`DOCUMENT_PRIVATE_DISK=local` e `IMAGE_STORAGE_DISK=image_local` são padrões seguros para desenvolvimento e testes. O `storage_disk`/`managed_image_disk` gravado em cada registro determina a leitura posterior: mudar uma variável **não migra bytes**. Documento com disk nulo ou `local` continua em `local`; imagens `image_local` continuam nesse disk. Eventuais referências `firebase_private` ou `firebase_images` permanecem intactas, mas não são lidas após remover o driver GCS; retornam indisponibilidade controlada. No ambiente acadêmico não há objetos GCS reais a migrar. Em Railway, filesystem local efêmero não serve como armazenamento persistente; inventariar/copiar/verificar bytes locais antes de mudar a implantação de dados reais.

Para rollback de configuração, interromper novas gravações, restaurar a seleção anterior de disks compatíveis com cada registro e verificar disponibilidade dos objetos, sem apagar objetos S3 nem atualizar referências em massa. Se registros já foram gravados em S3, voltar somente a seleção de novos uploads não os move para local; manter credenciais/acesso ao S3 para lê-los até uma migração inversa controlada. Nunca usar `migrate:fresh` em banco persistente.

## Smoke test externo — somente após autorização separada

Em homologação com MySQL novo e contas/arquivos **sintéticos**, configurar as referências do bucket existente, HTTPS, PHP 8.4+ e os dois disks. Enviar documento por API autenticada; conferir download pelo titular e admin autorizado, negar terceiro/ONG/anônimo e verificar auditoria. Enviar imagem sintética, conferir GET público/ETag/304, substituir e remover; conferir que objeto e credenciais não aparecem nas respostas nem há acesso anônimo ao bucket. Conferir falha remota, pendência de reconciliação e limpeza apenas das chaves sintéticas criadas para esse teste. Confirmar que PDF de certificado não produz objeto. Não usar dados pessoais reais e não executar este plano automaticamente na suíte.

## Operação e custos

Revisar limites, faturamento e permissões da conta Railway antes de habilitar. A API intermedeia upload e GET de imagem: além do armazenamento, tráfego do serviço pode gerar custo, especialmente leituras `200` de até 5 MiB. Ajustar memória do PHP para sanitização de imagens de até 25 MP. O scheduler de exclusão de contas e expurgo documental permanece `--dry-run`; reconciliação de imagens é **manual** e pode apagar apenas órfãos após verificação. Não há download público de documentos privados. Conferir política de backup/recuperação e métricas no ambiente real.

Referências: [Laravel 13 — S3/Flysystem](https://laravel.com/framework/docs/filesystem), [Railway — Storage Buckets](https://docs.railway.com/storage-buckets).
