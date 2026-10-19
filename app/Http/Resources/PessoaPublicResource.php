<?php

namespace App\Http\Resources;

use App\Services\ManagedImageStorage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PessoaPublicResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'pessoa_id' => $this->pessoa_id,
            'nm_pessoa' => $this->nm_pessoa,
            'pfp_pessoa_link' => app(ManagedImageStorage::class)->publicUrl($this->resource),
            'xp_pessoa' => $this->xp_pessoa,
            'media_avaliacao' => $this->resource->getAttribute('media_avaliacao_calculada'),
            'confiabilidade' => $this->resource->getAttribute('confiabilidade_resumo'),
        ];
    }
}
