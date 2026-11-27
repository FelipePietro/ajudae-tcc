<?php

namespace App\Http\Resources;

use App\Models\Ong;
use App\Models\Pessoa;
use App\Policies\EventoPolicy;
use App\Support\AccountAccess;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InscricaoResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'inscricao_id' => $this->inscricao_id,
            'agenda_id' => $this->agenda_id,
            'pessoa_id' => $this->pessoa_id,
            'status_inscricao' => $this->status_inscricao,
            'dt_inscricao' => $this->dt_inscricao?->toISOString(),
            'desistiu_em' => $this->desistiu_em?->toISOString(),
            'desistencia_tardia' => $this->desistencia_tardia,
            'desistencia_sem_penalidade' => $this->desistencia_sem_penalidade,
            'desistencia_sem_penalidade_ate' => $this->desistencia_sem_penalidade_ate?->toISOString(),
            'presenca' => $this->presenca,
            'presenca_confirmada_em' => $this->presenca_confirmada_em?->toISOString(),
            'carga_horaria' => $this->carga_horaria,
            'declarou_estudante' => (bool) $this->declarou_estudante,
            'carta_apresentacao' => $this->when(
                $this->resource->getRawOriginal('carta_apresentacao') !== null && $this->podeLerCarta($request),
                fn () => $this->carta_apresentacao,
            ),
            'pessoa' => $this->whenLoaded('pessoa', fn () => new PessoaPublicResource($this->pessoa)),
            'agenda' => $this->whenLoaded('agenda'),
            'match' => $this->when(isset($this->match), $this->match),
            'confiabilidade' => $this->when(isset($this->confiabilidade), $this->confiabilidade),
        ];
    }

    private function podeLerCarta(Request $request): bool
    {
        $ator = $request->attributes->get('web_account') ?? $request->user();
        if ($ator instanceof Pessoa && $ator->getKey() === $this->pessoa_id
            && AccountAccess::hasPersonalJourney($ator) && AccountAccess::canOperate($ator)) {
            return true;
        }

        if (! ($ator instanceof Pessoa || $ator instanceof Ong)) {
            return false;
        }

        if (isset($this->carta_visivel)) {
            return $this->carta_visivel;
        }

        $agenda = $this->resource->relationLoaded('agenda') ? $this->agenda : null;

        return $agenda?->relationLoaded('evento') && $agenda->evento
            && app(EventoPolicy::class)->operate($ator, $agenda->evento);
    }
}
