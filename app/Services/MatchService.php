<?php

namespace App\Services;

use App\Models\Evento;
use App\Models\Pessoa;

class MatchService
{
    public function calcular(Evento $evento, Pessoa $pessoa): array
    {
        $evento->loadMissing(['habilidades', 'causas', 'recursos']);
        $pessoa->loadMissing(['habilidades', 'causas', 'recursos']);

        $habilidades = $evento->habilidades->pluck('habilidade_id')->intersect($pessoa->habilidades->pluck('habilidade_id'))->count();
        $causas = $evento->causas->pluck('causa_id')->intersect($pessoa->causas->pluck('causa_id'))->count();
        $recursos = $evento->recursos->pluck('recurso_id')->intersect($pessoa->recursos->pluck('recurso_id'))->count();

        return [
            'habilidades' => $habilidades,
            'causas' => $causas,
            'recursos' => $recursos,
            'total' => $habilidades + $causas + $recursos,
        ];
    }
}
