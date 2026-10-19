<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CertificadoResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'certificado_id' => $this->certificado_id,
            'codigo_verificacao' => $this->codigo_verificacao,
            'evento_id' => $this->evento_id,
            'pessoa_id' => $this->pessoa_id,
            'nome_participante' => $this->nome_participante,
            'nome_evento' => $this->nome_evento,
            'nome_responsavel' => $this->nome_responsavel,
            'total_minutos' => $this->total_minutos,
            'horas' => round($this->total_minutos / 60, 4),
            'emitido_em' => $this->emitido_em->toISOString(),
        ];
    }
}
