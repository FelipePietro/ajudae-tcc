<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pessoa;
use App\Models\Recurso;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class PessoaRecursoController extends Controller
{
    public function index($id)
    {
        $pessoa = Pessoa::query()
            ->where('role_pessoa', 'voluntario')
            ->where('status_validacao', 'aprovado')
            ->findOrFail($id);

        return ApiResponse::success($pessoa->recursos()->get());
    }

    public function store(Request $request, $id)
    {
        $pessoa = Pessoa::findOrFail($id);

        if ($request->user()->pessoa_id !== $pessoa->pessoa_id) {
            return ApiResponse::error('Você não tem permissão para alterar os recursos desta pessoa.', null, 403);
        }

        $validated = $request->validate([
            'recurso_id' => [
                'required',
                'integer',
                'exists:recurso,recurso_id',
            ],

            'detalhes_recurso' => [
                'required',
                'string',
                'max:255',
            ],
        ]);

        $jaPossui = $pessoa->recursos()
            ->where('recurso.recurso_id', $validated['recurso_id'])
            ->exists();

        if ($jaPossui) {
            return ApiResponse::error('Este recurso já está associado à pessoa.', null, 409);
        }

        $pessoa->recursos()->attach(
            $validated['recurso_id'],
            [
                'detalhes_recurso' => $validated['detalhes_recurso'],
            ]
        );

        return ApiResponse::success($pessoa->recursos()->get(), 'Recurso adicionado com sucesso.', 201);
    }

    public function destroy(Request $request, $id, $rid)
    {
        $pessoa = Pessoa::findOrFail($id);

        if ($request->user()->pessoa_id !== $pessoa->pessoa_id) {
            return ApiResponse::error('Você não tem permissão para alterar os recursos desta pessoa.', null, 403);
        }

        Recurso::findOrFail($rid);

        $existe = $pessoa->recursos()
            ->where('recurso.recurso_id', $rid)
            ->exists();

        if (! $existe) {
            return ApiResponse::error('Este recurso não está associado à pessoa.', null, 404);
        }

        $pessoa->recursos()->detach($rid);

        return response()->noContent();
    }
}
