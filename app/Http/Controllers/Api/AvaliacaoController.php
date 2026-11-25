<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AvaliacaoOng;
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
        $validated = $request->validate(AvaliacaoService::regrasNota());
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
        $validated = $request->validate(AvaliacaoService::regrasNota());
        if (! ($request->user() instanceof Pessoa)) {
            throw new AuthorizationException('Somente voluntários podem avaliar a ONG.');
        }
        $avaliacao = $service->registrarOng(Evento::findOrFail($evento), $request->user(), $validated['nota']);

        return ApiResponse::success($this->dadosAvaliacaoOng($avaliacao), 'Avaliação da ONG registrada.', 201);
    }

    public function consultarOng(Request $request, int $evento, AvaliacaoService $service)
    {
        if (! ($request->user() instanceof Pessoa)) {
            throw new AuthorizationException('Somente a própria Pessoa pode consultar a avaliação enviada.');
        }

        $avaliacao = $service->consultarOng(Evento::findOrFail($evento), $request->user());

        return ApiResponse::success($this->dadosAvaliacaoOng($avaliacao));
    }

    private function dadosAvaliacaoOng(AvaliacaoOng $avaliacao): array
    {
        return [
            'avaliacao_ong_id' => $avaliacao->avaliacao_ong_id,
            'evento_id' => $avaliacao->evento_id,
            'pessoa_id' => $avaliacao->pessoa_id,
            'nota' => $avaliacao->nota,
        ];
    }
}
