<?php

namespace App\Http\Resources;

use App\Services\ManagedImageStorage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OngPublicResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'ong_id' => $this->ong_id,
            'nome_fantasia' => $this->nome_fantasia,
            'descricao_ong' => $this->descricao_ong,
            'pfp_ong_link' => app(ManagedImageStorage::class)->publicUrl($this->resource),
            'site_url_ong' => $this->site_url_ong,
            'cidade_ong' => $this->cidade_ong,
            'uf_ong' => $this->uf_ong,
            'status_ong' => $this->status_ong,
        ];
    }
}
