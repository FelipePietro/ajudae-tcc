<?php

namespace App\Http\Controllers;

use App\Models\SolicitacaoLgpd;
use App\Services\PrivacyRequests;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class WebPrivacyRequestController extends Controller
{
    public function index(Request $request, PrivacyRequests $privacy): JsonResponse
    {
        $account = $privacy->owner($request->attributes->get('web_account'));
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['pendente', 'em_analise', 'concluida', 'recusada'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $history = $privacy->history($account);
        if (isset($filters['status'])) {
            $history->where('status', $filters['status']);
        }
        $page = $history->paginate(8, ['*'], 'page', $filters['page'] ?? 1);

        return ApiResponse::success([
            'items' => $page->getCollection()->map(fn (SolicitacaoLgpd $case) => [
                'id' => $case->getKey(),
                'type' => $case->tipo,
                'status' => $case->status,
                'description' => $case->descricao,
                'response' => $case->resposta,
                'created_at' => $case->created_at?->toISOString(),
                'responded_at' => $case->respondida_em?->toISOString(),
            ]),
            'page' => $page->currentPage(),
            'pages' => $page->lastPage(),
        ]);
    }

    public function store(Request $request, PrivacyRequests $privacy): JsonResponse
    {
        $account = $privacy->owner($request->attributes->get('web_account'));
        $case = $privacy->register($account, $request->validate(PrivacyRequests::RULES));

        return ApiResponse::success(['id' => $case->getKey()], 'Solicitação registrada com sucesso.', 201);
    }
}
