<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentoPessoa;
use App\Models\Pessoa;
use App\Services\AntecedentesService;
use App\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

class AntecedentesController extends Controller
{
    public function meus(Request $request, AntecedentesService $service)
    {
        if (! $request->user() instanceof Pessoa) {
            throw new AuthorizationException();
        }

        return ApiResponse::success($service->situacao($request->user()));
    }

    public function renovacoes(Request $request)
    {
        $validated = $request->validate(['per_page' => ['sometimes', 'integer', 'min:1', 'max:100']]);
        $renovacoes = DocumentoPessoa::query()
            ->with('pessoa:pessoa_id,nm_pessoa')
            ->where('tipo', 'antecedentes_criminais')
            ->where('ativo', true)->where('status', 'pendente')
            ->whereHas('pessoa', fn ($query) => $query->where('status_validacao', 'aprovado'))
            ->orderBy('uploaded_at')
            ->paginate($validated['per_page'] ?? 20)
            ->through(fn (DocumentoPessoa $documento) => [
                'documento_pessoa_id' => $documento->documento_pessoa_id,
                'pessoa_id' => $documento->pessoa_id,
                'nome_pessoa' => $documento->pessoa?->nm_pessoa,
                'enviado_em' => $documento->uploaded_at?->toISOString(),
                'status' => $documento->status,
            ]);

        return ApiResponse::success($renovacoes);
    }
}
