<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConclusaoPadrinho;
use App\Services\PadrinhoService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class AdminPadrinhoController extends Controller
{
    public function conclusoes(Request $request)
    {
        $validated = $request->validate(['per_page' => ['sometimes', 'integer', 'min:1', 'max:100']]);
        $pedidos = ConclusaoPadrinho::query()->with('acompanhamento')
            ->where('status', 'pendente')->orderBy('solicitada_em')
            ->paginate($validated['per_page'] ?? 20)
            ->through(fn ($pedido) => [
                'conclusao_padrinho_id' => $pedido->getKey(),
                'acompanhamento_id' => $pedido->acompanhamento_id,
                'padrinho_id' => $pedido->acompanhamento->padrinho_id,
                'apadrinhado_id' => $pedido->apadrinhado_id,
                'status' => $pedido->status,
                'solicitada_em' => $pedido->solicitada_em?->toISOString(),
            ]);

        return ApiResponse::success($pedidos);
    }

    public function aprovar(Request $request, ConclusaoPadrinho $pedido, PadrinhoService $service)
    {
        $pedido = $service->decidirConclusao($pedido, $request->user(), true, null);

        return ApiResponse::success(['conclusao_padrinho_id' => $pedido->getKey(), 'status' => $pedido->status], 'Conclusão aprovada.');
    }

    public function reprovar(Request $request, ConclusaoPadrinho $pedido, PadrinhoService $service)
    {
        $validated = $request->validate(['motivo' => ['nullable', 'string', 'max:2000']]);
        $pedido = $service->decidirConclusao($pedido, $request->user(), false, $validated['motivo'] ?? null);

        return ApiResponse::success(['conclusao_padrinho_id' => $pedido->getKey(), 'status' => $pedido->status], 'Conclusão rejeitada.');
    }
}
