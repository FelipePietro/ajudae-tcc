<?php

namespace App\Http\Resources;

use App\Models\DocumentoPessoa;
use App\Services\DocumentoStorage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;

class DocumentoPessoaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'documento_pessoa_id' => $this->documento_pessoa_id,
            'pessoa_id' => $this->pessoa_id,
            'tipo' => $this->tipo,
            'mime_type' => $this->mime_type,
            'size_bytes' => $this->size_bytes,
            'status' => $this->status,
            'ativo' => $this->ativo,
            'uploaded_at' => $this->uploaded_at?->toISOString(),
            'validated_at' => $this->validated_at?->toISOString(),
            'validated_by_admin_id' => $this->validated_by_admin_id,
            'rejection_reason' => $this->rejection_reason,
            'expires_at' => $this->expires_at?->toISOString(),
            'delete_after' => $this->delete_after?->toISOString(),
            'arquivo_excluido_em' => $this->arquivo_excluido_em?->toISOString(),
            ...($request->routeIs('identity.admin.person-review') ? self::webReviewFields($request, $this->resource) : []),
        ];
    }

    public static function webReviewFields(Request $request, DocumentoPessoa $document): array
    {
        $available = $document->hasRetainedFile() && app(DocumentoStorage::class)->safePath($document->storage_path);
        $allowed = $available && Gate::forUser($request->user())->allows('downloadWeb', $document);

        return [
            'download_url' => $allowed ? route('identity.documents.download', $document, absolute: false) : null,
            'download_requires_assignment' => $available && ! $allowed
                && in_array($document->pessoa->status_validacao, ['aguardando_validacao', 'em_analise'], true),
        ];
    }
}
