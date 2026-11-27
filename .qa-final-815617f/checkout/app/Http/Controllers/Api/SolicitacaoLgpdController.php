<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Ong;
use App\Models\Pessoa;
use App\Models\SolicitacaoLgpd;
use App\Services\PrivacyRequests;
use App\Support\AccountAccess;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SolicitacaoLgpdController extends Controller
{
    public function store(Request $request, PrivacyRequests $privacy)
    {
        $titular = $privacy->owner($request->user());
        $solicitacao = $privacy->register($titular, $request->validate(PrivacyRequests::RULES));

        return ApiResponse::success($solicitacao, 'Solicitação registrada com sucesso.', 201);
    }

    public function minhas(Request $request, PrivacyRequests $privacy)
    {
        $solicitacoes = $privacy->history($privacy->owner($request->user()))
            ->paginate(min(max($request->integer('per_page', 20), 1), 100));

        return ApiResponse::success($solicitacoes);
    }

    public function index(Request $request)
    {
        $actor = $request->user();
        $query = SolicitacaoLgpd::query()->orderByDesc('created_at')->orderByDesc('solicitacao_lgpd_id');
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $solicitacoes = $query->paginate(min(max($request->integer('per_page', 20), 1), 100))
            ->through(static function (SolicitacaoLgpd $caso) use ($actor): SolicitacaoLgpd {
                if (! $actor->admin_master && (int) $caso->respondida_por !== $actor->getKey()) {
                    $caso->forceFill(['solicitante_id' => null, 'solicitante_type' => null, 'descricao' => null, 'resposta' => null]);
                }

                return $caso;
            });

        return ApiResponse::success($solicitacoes);
    }

    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['pendente', 'em_analise', 'concluida', 'recusada'])],
            'resposta' => ['nullable', 'string', 'max:4000', Rule::requiredIf(in_array($request->input('status'), ['concluida', 'recusada'], true))],
        ]);

        $solicitacao = DB::transaction(function () use ($id, $validated, $request) {
            $identity = $request->user();
            $actor = Pessoa::whereKey($identity->getKey())->lockForUpdate()->firstOrFail();
            abort_unless(AccountAccess::canOperate($actor) && (int) $actor->auth_version === (int) $identity->auth_version
                && $actor->hasPermission('lgpd.responder'), 403);

            $solicitacao = SolicitacaoLgpd::whereKey($id)->lockForUpdate()->firstOrFail();
            if (! $actor->admin_master) {
                if ($solicitacao->respondida_por === null) {
                    abort_unless($validated['status'] === 'em_analise', 403);
                } else {
                    abort_unless((int) $solicitacao->respondida_por === $actor->getKey(), 403);
                }
            }
            $statusAnterior = $solicitacao->status;
            $solicitacao->fill([
                ...$validated,
                'respondida_por' => $actor->getKey(),
            ]);
            if (! $solicitacao->isDirty()) {
                return $solicitacao;
            }
            $solicitacao->forceFill(['respondida_em' => now()])->save();
            AdminLog::create([
                'pessoa_id' => $actor->getKey(),
                'acao' => 'lgpd.status_atualizado',
                'recurso_type' => SolicitacaoLgpd::class,
                'recurso_id' => $solicitacao->solicitacao_lgpd_id,
                'detalhes' => ['de' => $statusAnterior, 'para' => $validated['status']],
            ]);
            if ($statusAnterior !== $validated['status']) {
                $titular = $solicitacao->solicitante()->first();
                if ($titular instanceof Pessoa || $titular instanceof Ong) {
                    $titular->notificacoes()->create([
                        'tipo_notificacao' => 'lgpd.status_atualizado',
                        'mensagem_notificacao' => 'O status de uma solicitação de privacidade da sua conta foi atualizado.',
                    ]);
                }
            }

            return $solicitacao;
        }, 3);

        return ApiResponse::success($solicitacao, 'Solicitação atualizada com sucesso.');
    }
}
