<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pessoa;
use App\Services\ConfiabilidadeService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class ConfiabilidadeController extends Controller
{
    public function show(Request $request, int $pessoa, ConfiabilidadeService $service)
    {
        $alvo = Pessoa::findOrFail($pessoa);
        $ator = $request->user();
        if (! ($ator instanceof Pessoa)
            || ($ator->pessoa_id !== $alvo->pessoa_id && $ator->role_pessoa !== 'administrador')) {
            return ApiResponse::error('Você não pode consultar este histórico.', null, 403);
        }

        return ApiResponse::success($service->detalhe($alvo));
    }
}
