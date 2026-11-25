<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JustificativaParticipacaoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'justificativa_id' => $this->justificativa_id,
            'inscricao_id' => $this->inscricao_id,
            'status' => $this->status,
            'motivo' => $this->motivo,
            'motivo_decisao' => $this->motivo_decisao,
            'enviada_em' => $this->created_at?->toISOString(),
            'decidido_em' => $this->decidido_em?->toISOString(),
        ];
    }
}
