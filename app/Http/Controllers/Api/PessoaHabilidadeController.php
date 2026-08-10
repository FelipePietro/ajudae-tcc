<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pessoa;
use App\Models\Habilidade;
use Illuminate\Http\Request;

class PessoaHabilidadeController extends Controller
{
    /**
     * Lista as habilidades de uma pessoa.
     */
    public function index($id)
    {
        $pessoa = Pessoa::find($id);

        if (!$pessoa) {
            return response()->json([
                'message' => 'Pessoa não encontrada.'
            ], 404);
        }

        return response()->json([
            'data' => $pessoa->habilidades()->get()
        ], 200);
    }

    /**
     * Adiciona uma habilidade à pessoa autenticada.
     */
    public function store(Request $request, $id)
    {
        $pessoa = Pessoa::find($id);

        if (!$pessoa) {
            return response()->json([
                'message' => 'Pessoa não encontrada.'
            ], 404);
        }

        // A pessoa só pode alterar as próprias habilidades
        if ($request->user()->pessoa_id !== $pessoa->pessoa_id) {
            return response()->json([
                'message' => 'Você não tem permissão para alterar as habilidades desta pessoa.'
            ], 403);
        }

        $validated = $request->validate([
            'habilidade_id' => [
                'required',
                'integer',
                'exists:habilidade,habilidade_id'
            ],

            'nivel_habilidade' => [
                'required',
                'integer',
                'min:1',
                'max:5'
            ],
        ]);

        $jaPossui = $pessoa->habilidades()
            ->where('habilidade.habilidade_id', $validated['habilidade_id'])
            ->exists();

        if ($jaPossui) {
            return response()->json([
                'message' => 'Esta habilidade já está associada à pessoa.'
            ], 409);
        }

        $pessoa->habilidades()->attach(
            $validated['habilidade_id'],
            [
                'nivel_habilidade' => $validated['nivel_habilidade']
            ]
        );

        return response()->json([
            'message' => 'Habilidade adicionada com sucesso.',
            'data' => $pessoa->habilidades()->get()
        ], 201);
    }

    /**
     * Remove uma habilidade da pessoa.
     */
    public function destroy(Request $request, $id, $hid)
    {
        $pessoa = Pessoa::find($id);

        if (!$pessoa) {
            return response()->json([
                'message' => 'Pessoa não encontrada.'
            ], 404);
        }

        if ($request->user()->pessoa_id !== $pessoa->pessoa_id) {
            return response()->json([
                'message' => 'Você não tem permissão para alterar as habilidades desta pessoa.'
            ], 403);
        }

        $habilidade = Habilidade::find($hid);

        if (!$habilidade) {
            return response()->json([
                'message' => 'Habilidade não encontrada.'
            ], 404);
        }

        $existe = $pessoa->habilidades()
            ->where('habilidade.habilidade_id', $hid)
            ->exists();

        if (!$existe) {
            return response()->json([
                'message' => 'Esta habilidade não está associada à pessoa.'
            ], 404);
        }

        $pessoa->habilidades()->detach($hid);

        return response()->noContent();
    }
}