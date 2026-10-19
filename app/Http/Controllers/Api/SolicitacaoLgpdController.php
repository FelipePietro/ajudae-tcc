<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\SolicitacaoLgpd;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SolicitacaoLgpdController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo' => ['required', Rule::in(['acesso', 'correcao', 'exclusao', 'portabilidade', 'informacao'])],
            'descricao' => ['nullable', 'string', 'max:2000'],
        ]);

        $solicitacao = $request->user()->morphMany(SolicitacaoLgpd::class, 'solicitante')->create($validated);

        return ApiResponse::success($solicitacao, 'Solicitação registrada com sucesso.', 201);
    }

    public function minhas(Request $request)
    {
        $solicitacoes = $request->user()
            ->morphMany(SolicitacaoLgpd::class, 'solicitante')
            ->orderByDesc('created_at')
            ->paginate(min(max($request->integer('per_page', 20), 1), 100));

        return ApiResponse::success($solicitacoes);
    }

    public function index(Request $request)
    {
        $query = SolicitacaoLgpd::query()->orderByDesc('created_at');
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return ApiResponse::success($query->paginate(min(max($request->integer('per_page', 20), 1), 100)));
    }

    public function updateStatus(Request $request, int $id)
    {
        $solicitacao = SolicitacaoLgpd::findOrFail($id);
        $validated = $request->validate([
            'status' => ['required', Rule::in(['pendente', 'em_analise', 'concluida', 'recusada'])],
            'resposta' => ['nullable', 'string', 'max:4000', Rule::requiredIf(in_array($request->input('status'), ['concluida', 'recusada'], true))],
        ]);

        DB::transaction(function () use ($solicitacao, $validated, $request) {
            $solicitacao->update([
                ...$validated,
                'respondida_por' => $request->user()->pessoa_id,
                'respondida_em' => now(),
            ]);
            AdminLog::create([
                'pessoa_id' => $request->user()->pessoa_id,
                'acao' => 'lgpd.status_atualizado',
                'recurso_type' => SolicitacaoLgpd::class,
                'recurso_id' => $solicitacao->solicitacao_lgpd_id,
                'detalhes' => ['status' => $validated['status']],
            ]);
        });

        return ApiResponse::success($solicitacao->fresh(), 'Solicitação atualizada com sucesso.');
    }
}
