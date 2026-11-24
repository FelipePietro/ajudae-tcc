<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inscricao;
use App\Services\MonitoriaService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class MonitoriaController extends Controller
{
    public function show(Request $request, int $id, MonitoriaService $monitoria)
    {
        $inscricao = Inscricao::with('agenda.evento', 'monitoria')->findOrFail($id);

        return ApiResponse::success($monitoria->consultar($inscricao, $request->user()));
    }

    public function update(Request $request, int $id, MonitoriaService $monitoria)
    {
        $validated = $request->validate([
            'confirmada' => ['required', 'boolean'],
            'justificativa' => ['nullable', 'string', 'max:2000'],
        ]);

        $inscricao = Inscricao::with('agenda')->findOrFail($id);
        $resultado = $monitoria->decidir($inscricao, $request->user(), (bool) $validated['confirmada'], $validated['justificativa'] ?? null);

        return ApiResponse::success($resultado, 'Decisão de monitoria registrada.');
    }
}
