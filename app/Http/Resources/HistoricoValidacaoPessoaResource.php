<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HistoricoValidacaoPessoaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'historico_validacao_pessoa_id' => $this->historico_validacao_pessoa_id,
            'admin_id' => $this->admin_id,
            'status_anterior' => $this->status_anterior,
            'status_novo' => $this->status_novo,
            'motivo' => $this->motivo,
            'numero_tentativa' => $this->numero_tentativa,
            'iniciada_em' => $this->iniciada_em?->toISOString(),
            'concluida_em' => $this->concluida_em?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
