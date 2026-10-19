<?php

namespace App\Http\Resources;

use App\Services\ManagedImageStorage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventoResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'evento_id' => $this->evento_id,
            'ong_id' => $this->ong_id,
            'pessoa_id' => $this->pessoa_id,
            'nm_evento' => $this->nm_evento,
            'descricao_evento' => $this->descricao_evento,
            'vagas_evento' => $this->vagas_evento,
            'modalidade_evento' => $this->modalidade_evento,
            'status_evento' => $this->status_evento,
            'motivo_cancelamento' => $this->motivo_cancelamento,
            'cancelado_em' => $this->cancelado_em?->toISOString(),
            'cancelado_por_tipo' => $this->cancelado_por_tipo,
            'cancelado_por_id' => $this->cancelado_por_id,
            'imagem_evento_link' => app(ManagedImageStorage::class)->publicUrl($this->resource),
            'logradouro_evento' => $this->logradouro_evento,
            'cep_evento' => $this->cep_evento,
            'cidade_evento' => $this->cidade_evento,
            'bairro_evento' => $this->bairro_evento,
            'uf_evento' => $this->uf_evento,
            'compl_evento' => $this->compl_evento,
            'responsavel_pessoa_id' => $this->responsavel_pessoa_id,
            'responsavel' => $this->whenLoaded('responsavelPessoa', fn () => new PessoaPublicResource($this->responsavelPessoa)),
            'categoria' => $this->whenLoaded('categoria'),
            'ong' => $this->whenLoaded('ong', fn () => new OngPublicResource($this->ong)),
            'organizador' => $this->whenLoaded('pessoa', fn () => new PessoaPublicResource($this->pessoa)),
            'habilidades' => $this->whenLoaded('habilidades'),
            'agendas' => $this->whenLoaded('agendas'),
        ];
    }
}
