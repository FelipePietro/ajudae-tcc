<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class OngAdminResource extends OngPrivateResource
{
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'cpf_responsavel_ong' => $this->cpf_responsavel_ong,
            'documento_identidade' => $this->rg_responsavel_storage_disk === null ? null : [
                'mime_type' => $this->rg_responsavel_mime_type,
                'size_bytes' => $this->rg_responsavel_size_bytes,
                'download' => $this->rg_responsavel_excluir_em?->lte(now())
                    ? null : route('identity.ong.document', $this->resource, absolute: false),
            ],
        ]);
    }
}
