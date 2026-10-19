<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DocumentoPessoaResource;
use App\Models\AdminLog;
use App\Models\DocumentoPessoa;
use App\Models\Pessoa;
use App\Services\DocumentoStorage;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Throwable;

class DocumentoPessoaController extends Controller
{
    public function store(Request $request, string $tipo, DocumentoStorage $documents)
    {
        $pessoa = $request->user();
        if (! $pessoa instanceof Pessoa) {
            return ApiResponse::error('Acesso negado.', null, 403);
        }

        $request->merge(['tipo' => $tipo]);
        $validated = $request->validate([
            'tipo' => ['required', Rule::in(DocumentoPessoa::TIPOS)],
            'arquivo' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $arquivo = $validated['arquivo'];
        $stored = $documents->store($arquivo);

        try {
            $documento = DB::transaction(function () use ($pessoa, $tipo, $stored) {
                Pessoa::whereKey($pessoa->getKey())->lockForUpdate()->firstOrFail();
                DocumentoPessoa::where('pessoa_id', $pessoa->pessoa_id)
                    ->where('tipo', $tipo)
                    ->where('ativo', true)
                    ->lockForUpdate()
                    ->get()
                    ->each(fn (DocumentoPessoa $anterior) => $anterior->update([
                        'ativo' => false,
                        'delete_after' => $anterior->delete_after
                            ?? $anterior->validated_at?->copy()->addDays(30)
                            ?? now()->addDays(30),
                    ]));

                return DocumentoPessoa::create([
                    'pessoa_id' => $pessoa->pessoa_id,
                    'tipo' => $tipo,
                    'storage_disk' => $stored['disk'],
                    'storage_path' => $stored['path'],
                    'mime_type' => $stored['mime_type'],
                    'size_bytes' => $stored['size_bytes'],
                    'sha256' => $stored['sha256'],
                    'status' => 'pendente',
                    'ativo' => true,
                    'uploaded_at' => now(),
                ]);
            });
        } catch (Throwable $exception) {
            try {
                if (! Storage::disk($stored['disk'])->delete($stored['path'])) {
                    Log::warning('Falha na compensação do upload documental.');
                }
            } catch (Throwable) {
                Log::warning('Falha na compensação do upload documental.');
            }
            throw $exception;
        }

        return ApiResponse::success(new DocumentoPessoaResource($documento), 'Documento enviado com sucesso.', 201);
    }

    public function download(Request $request, DocumentoPessoa $documento, DocumentoStorage $documents)
    {
        $user = $request->user();
        $authorized = $user instanceof Pessoa && (
            $user->pessoa_id === $documento->pessoa_id
            || ($user->role_pessoa === 'administrador' && (
                $user->hasPermission('usuarios.validar')
                || $user->hasPermission('lgpd.visualizar')
            ))
        );

        if (! $authorized) {
            return ApiResponse::error('Acesso negado.', null, 403);
        }

        if (! $documents->safePath($documento->storage_path)) {
            $this->auditarDownload($user, $documento, 'arquivo_ausente');

            return ApiResponse::error('Arquivo não está mais disponível.', null, 404);
        }

        try {
            $disk = Storage::disk($documents->diskFor($documento));
            if (! $disk->exists($documento->storage_path)) {
                $this->auditarDownload($user, $documento, 'arquivo_ausente');

                return ApiResponse::error('Arquivo não está mais disponível.', null, 404);
            }

            $response = $disk->download(
                $documento->storage_path,
                $documento->tipo.'.'.pathinfo($documento->storage_path, PATHINFO_EXTENSION),
                [
                    'Content-Type' => $documento->mime_type,
                    'Cache-Control' => 'private, no-store',
                    'X-Content-Type-Options' => 'nosniff',
                ]
            );
        } catch (Throwable) {
            $this->auditarDownload($user, $documento, 'falha_storage');

            return ApiResponse::error('Arquivo temporariamente indisponível.', null, 503);
        }

        $this->auditarDownload($user, $documento, 'entrega_iniciada');

        return $response;
    }

    private function auditarDownload(Pessoa $ator, DocumentoPessoa $documento, string $resultado): void
    {
        if ($ator->pessoa_id === $documento->pessoa_id) {
            return;
        }

        AdminLog::create([
            'pessoa_id' => $ator->pessoa_id,
            'acao' => 'documento.download',
            'recurso_type' => 'documento_pessoa',
            'recurso_id' => $documento->getKey(),
            'detalhes' => ['titular_id' => $documento->pessoa_id, 'resultado' => $resultado],
        ]);
    }
}
