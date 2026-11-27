<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SolicitacaoOrganizador;
use App\Services\OrganizadorService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminOrganizadorController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'status' => ['sometimes', Rule::in(['pendente', 'em_analise', 'aprovada', 'reprovada'])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $query = SolicitacaoOrganizador::with('pessoa')->latest('solicitada_em');
        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        return ApiResponse::success($query->paginate($validated['per_page'] ?? 20));
    }

    public function show(SolicitacaoOrganizador $solicitacao)
    {
        return ApiResponse::success($solicitacao->load(['pessoa', 'admin']));
    }

    public function iniciarAnalise(Request $request, SolicitacaoOrganizador $solicitacao, OrganizadorService $service)
    {
        return ApiResponse::success(
            $service->iniciarAnalise($solicitacao, $request->user()),
            'Análise iniciada.'
        );
    }

    public function aprovar(Request $request, SolicitacaoOrganizador $solicitacao, OrganizadorService $service)
    {
        return ApiResponse::success(
            $service->aprovar($solicitacao, $request->user()),
            'Solicitação aprovada.'
        );
    }

    public function reprovar(Request $request, SolicitacaoOrganizador $solicitacao, OrganizadorService $service)
    {
        $validated = $request->validate([
            'motivo' => ['required', 'string', 'max:2000', 'not_regex:/^\s*$/'],
        ]);

        return ApiResponse::success(
            $service->reprovar($solicitacao, $request->user(), $validated['motivo']),
            'Solicitação reprovada.'
        );
    }
}
