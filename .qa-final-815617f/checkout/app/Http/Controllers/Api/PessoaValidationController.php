<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PessoaPrivateResource;
use App\Models\Pessoa;
use App\Services\AvaliacaoService;
use App\Services\ConfiabilidadeService;
use App\Services\PessoaValidationService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class PessoaValidationController extends Controller
{
    public function show(Request $request)
    {
        $pessoa = $request->user();
        $pessoa->load(['documentos' => fn ($query) => $query->where('ativo', true)->latest()]);

        return ApiResponse::success(new PessoaPrivateResource($this->prepararMetricas($pessoa)));
    }

    public function reenviar(Request $request, PessoaValidationService $service)
    {
        $pessoa = $service->reenviar($request->user());

        return ApiResponse::success(new PessoaPrivateResource($this->prepararMetricas($pessoa)), 'Validação reenviada com sucesso.');
    }

    private function prepararMetricas(Pessoa $pessoa): Pessoa
    {
        $pessoa->setAttribute('media_avaliacao_calculada', app(AvaliacaoService::class)->mediaVoluntario($pessoa));
        $pessoa->setAttribute('confiabilidade_resumo', app(ConfiabilidadeService::class)->resumoPublico($pessoa));

        return $pessoa;
    }
}
