<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Habilidade;
use App\Models\Pessoa;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class PessoaHabilidadeController extends Controller
{
    /**
     * Lista as habilidades de uma pessoa.
     */
    public function index($id)
    {
        $pessoa = Pessoa::query()
            ->where('role_pessoa', 'voluntario')
            ->where('status_validacao', 'aprovado')
            ->findOrFail($id);

        return ApiResponse::success($pessoa->habilidades()->get());
    }

    /**
     * Adiciona uma habilidade à pessoa autenticada.
     */
    public function store(Request $request, $id)
    {
        $pessoa = Pessoa::findOrFail($id);

        // A pessoa só pode alterar as próprias habilidades
        if ($request->user()->pessoa_id !== $pessoa->pessoa_id) {
            return ApiResponse::error('Você não tem permissão para alterar as habilidades desta pessoa.', null, 403);
        }

        $validated = $request->validate([
            'habilidade_id' => [
                'required',
                'integer',
                'exists:habilidade,habilidade_id',
            ],

            'nivel_habilidade' => [
                'required',
                'integer',
                'min:1',
                'max:5',
            ],
        ]);

        $jaPossui = $pessoa->habilidades()
            ->where('habilidade.habilidade_id', $validated['habilidade_id'])
            ->exists();

        if ($jaPossui) {
            return ApiResponse::error('Esta habilidade já está associada à pessoa.', null, 409);
        }

        $pessoa->habilidades()->attach(
            $validated['habilidade_id'],
            [
                'nivel_habilidade' => $validated['nivel_habilidade'],
            ]
        );

        return ApiResponse::success($pessoa->habilidades()->get(), 'Habilidade adicionada com sucesso.', 201);
    }

    /**
     * Remove uma habilidade da pessoa.
     */
    public function destroy(Request $request, $id, $hid)
    {
        $pessoa = Pessoa::findOrFail($id);

        if ($request->user()->pessoa_id !== $pessoa->pessoa_id) {
            return ApiResponse::error('Você não tem permissão para alterar as habilidades desta pessoa.', null, 403);
        }

        Habilidade::findOrFail($hid);

        $existe = $pessoa->habilidades()
            ->where('habilidade.habilidade_id', $hid)
            ->exists();

        if (! $existe) {
            return ApiResponse::error('Esta habilidade não está associada à pessoa.', null, 404);
        }

        $pessoa->habilidades()->detach($hid);

        return response()->noContent();
    }
}
