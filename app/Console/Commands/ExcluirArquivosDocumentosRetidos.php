<?php

namespace App\Console\Commands;

use App\Models\DocumentoPessoa;
use App\Services\DocumentoStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ExcluirArquivosDocumentosRetidos extends Command
{
    protected $signature = 'documentos:excluir-retidos {--dry-run : Apenas contar documentos elegíveis} {--limit=100 : Máximo de documentos por execução}';

    protected $description = 'Exclui arquivos físicos de documentos após o prazo de retenção';

    public function handle(DocumentoStorage $documents): int
    {
        $limit = filter_var($this->option('limit'), FILTER_VALIDATE_INT);
        if ($limit === false || $limit < 1 || $limit > 1000) {
            $this->error('O limite deve estar entre 1 e 1000.');

            return self::FAILURE;
        }

        $query = DocumentoPessoa::query()
            ->whereNotNull('storage_path')->whereNotNull('validated_at')
            ->whereIn('status', ['aprovado', 'rejeitado'])
            ->where('delete_after', '<=', now())
            ->orderBy('documento_pessoa_id')->limit($limit);

        $elegiveis = $query->pluck('documento_pessoa_id');
        if ($this->option('dry-run')) {
            $this->info("Documentos elegíveis: {$elegiveis->count()} (máximo {$limit}). Nenhum arquivo foi lido ou apagado.");

            return self::SUCCESS;
        }

        $falhas = 0;
        foreach ($elegiveis as $id) {
            try {
                DB::transaction(function () use ($id, $documents) {
                    $documento = DocumentoPessoa::whereKey($id)->lockForUpdate()->first();
                    if (! $documento || ! $documento->storage_path || ! $documento->validated_at
                        || ! in_array($documento->status, ['aprovado', 'rejeitado'], true)
                        || ! $documento->delete_after?->lessThanOrEqualTo(now())) {
                        return;
                    }

                    $disk = $documents->diskFor($documento);
                    if (! $documents->safePath($documento->storage_path)) {
                        throw new \RuntimeException('Chave de documento inválida.');
                    }
                    $compartilhado = DocumentoPessoa::where('storage_path', $documento->storage_path)
                        ->whereKeyNot($id)
                        ->where(fn ($query) => $disk === 'local'
                            ? $query->whereNull('storage_disk')->orWhere('storage_disk', 'local')
                            : $query->where('storage_disk', $disk))
                        ->exists();
                    if ($compartilhado) {
                        throw new \RuntimeException('Objeto ainda referenciado.');
                    }

                    $storage = Storage::disk($disk);
                    if ($storage->exists($documento->storage_path)
                        && ! $storage->delete($documento->storage_path)) {
                        throw new \RuntimeException('Exclusão não confirmada pelo storage.');
                    }
                    $documento->update([
                        'storage_path' => null,
                        'arquivo_excluido_em' => $documento->arquivo_excluido_em ?? now(),
                    ]);
                }, 3);
            } catch (Throwable) {
                $falhas++;
                $this->warn("Falha no expurgo do documento {$id}; referência preservada para retry.");
            }
        }

        $this->info("Processados: {$elegiveis->count()}; falhas: {$falhas}.");

        return $falhas ? self::FAILURE : self::SUCCESS;
    }
}
