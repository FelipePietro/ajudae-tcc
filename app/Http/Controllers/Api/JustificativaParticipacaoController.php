<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inscricao;
use App\Models\JustificativaParticipacao;
use App\Services\JustificativaParticipacaoService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class JustificativaParticipacaoController extends Controller
{
    public function show(Request $request, int $id, JustificativaParticipacaoService $service)
    {
        $inscricao = Inscricao::with('agenda.evento')->findOrFail($id);

        return ApiResponse::success($this->dados($service->consultar($inscricao, $request->user())));
    }

    public function store(Request $request, int $id, JustificativaParticipacaoService $service)
    {
        $validated = $request->validate(['motivo' => ['required', 'string', 'max:2000']]);
        $inscricao = Inscricao::with('agenda')->findOrFail($id);
        $justificativa = $service->submeter($inscricao, $request->user(), $validated['motivo']);

        return ApiResponse::success($this->dados($justificativa), 'Justificativa enviada.', 201);
    }

    public function update(Request $request, int $id, JustificativaParticipacaoService $service)
    {
        $validated = $request->validate([
            'deferida' => ['required', 'boolean'],
            'motivo' => ['required', 'string', 'max:2000'],
        ]);
        $inscricao = Inscricao::with('agenda')->findOrFail($id);
        $justificativa = $service->decidir($inscricao, $request->user(), (bool) $validated['deferida'], $validated['motivo']);

        return ApiResponse::success($this->dados($justificativa), 'Decisão da justificativa registrada.');
    }

    private function dados(JustificativaParticipacao $justificativa): array
    {
        return [
            'justificativa_id' => $justificativa->justificativa_id,
            'inscricao_id' => $justificativa->inscricao_id,
            'status' => $justificativa->status,
            'motivo' => $justificativa->motivo,
            'motivo_decisao' => $justificativa->motivo_decisao,
            'enviada_em' => $justificativa->created_at?->toISOString(),
            'decidido_em' => $justificativa->decidido_em?->toISOString(),
        ];
    }
}
