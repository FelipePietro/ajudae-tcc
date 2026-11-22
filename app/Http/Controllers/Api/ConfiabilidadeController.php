<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Pessoa;
use App\Services\ConfiabilidadeService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ConfiabilidadeController extends Controller
{
    public function show(Request $request, int $pessoa, ConfiabilidadeService $service)
    {
        $alvo = Pessoa::findOrFail($pessoa);
        $ator = $request->user();
        Gate::forUser($ator)->authorize('viewPrivateHistory', $alvo);

        if (! $ator->is($alvo)) {
            AdminLog::create([
                'pessoa_id' => $ator->getKey(),
                'acao' => 'confiabilidade.consulta_admin',
                'recurso_type' => $alvo->getMorphClass(),
                'recurso_id' => $alvo->getKey(),
                'detalhes' => ['finalidade' => 'validacao_cadastral'],
            ]);
        }

        return ApiResponse::success($service->detalhe($alvo));
    }
}
