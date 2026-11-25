<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\JustificativaParticipacaoResource;
use App\Models\Inscricao;
use App\Services\JustificativaParticipacaoService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class JustificativaParticipacaoController extends Controller
{
    public function show(Request $request, int $id, JustificativaParticipacaoService $service)
    {
        $inscricao = Inscricao::with('agenda.evento')->findOrFail($id);

        return ApiResponse::success(new JustificativaParticipacaoResource($service->consultar($inscricao, $request->user())));
    }

    public function store(Request $request, int $id, JustificativaParticipacaoService $service)
    {
        $validated = $request->validate(JustificativaParticipacaoService::regrasEnvio());
        $inscricao = Inscricao::with('agenda')->findOrFail($id);
        $justificativa = $service->submeter($inscricao, $request->user(), $validated['motivo']);

        return ApiResponse::success(new JustificativaParticipacaoResource($justificativa), 'Justificativa enviada.', 201);
    }

    public function update(Request $request, int $id, JustificativaParticipacaoService $service)
    {
        $validated = $request->validate(JustificativaParticipacaoService::regrasDecisao());
        $inscricao = Inscricao::with('agenda')->findOrFail($id);
        $justificativa = $service->decidir($inscricao, $request->user(), (bool) $validated['deferida'], $validated['motivo']);

        return ApiResponse::success(new JustificativaParticipacaoResource($justificativa), 'Decisão da justificativa registrada.');
    }
}
