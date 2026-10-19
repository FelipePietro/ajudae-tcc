<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Models\Pessoa;
use App\Services\AvaliacaoService;
use App\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

class AvaliacaoController extends Controller
{
    public function voluntario(Request $request, int $evento, int $pessoa, AvaliacaoService $service)
    {
        $validated = $request->validate($this->regrasNota());
        $avaliacao = $service->registrarVoluntario(
            Evento::findOrFail($evento),
            Pessoa::findOrFail($pessoa),
            $request->user(),
            $validated['nota']
        );

        return ApiResponse::success([
            'avaliacao_voluntario_id' => $avaliacao->avaliacao_voluntario_id,
            'evento_id' => $avaliacao->evento_id,
            'pessoa_id' => $avaliacao->pessoa_id,
            'nota' => $avaliacao->nota,
        ], 'Avaliação registrada.', 201);
    }

    public function ong(Request $request, int $evento, AvaliacaoService $service)
    {
        $validated = $request->validate($this->regrasNota());
        if (! ($request->user() instanceof Pessoa)) {
            throw new AuthorizationException('Somente voluntários podem avaliar a ONG.');
        }
        $avaliacao = $service->registrarOng(Evento::findOrFail($evento), $request->user(), $validated['nota']);

        return ApiResponse::success([
            'avaliacao_ong_id' => $avaliacao->avaliacao_ong_id,
            'evento_id' => $avaliacao->evento_id,
            'pessoa_id' => $avaliacao->pessoa_id,
            'nota' => $avaliacao->nota,
        ], 'Avaliação da ONG registrada.', 201);
    }

    private function regrasNota(): array
    {
        return [
            'nota' => ['required', 'integer', 'between:1,5'],
            'comentario' => ['prohibited'],
            'texto' => ['prohibited'],
            'justificativa' => ['prohibited'],
        ];
    }
}
