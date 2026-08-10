<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pessoa;
use App\Models\Recurso;
use Illuminate\Http\Request;

class PessoaRecursoController extends Controller
{
    public function index($id)
    {
        $pessoa = Pessoa::find($id);

        if (!$pessoa) {
            return response()->json([
                'message' => 'Pessoa não encontrada.'
            ], 404);
        }

        return response()->json([
            'data' => $pessoa->recursos()->get()
        ], 200);
    }

    public function store(Request $request, $id)
    {
        $pessoa = Pessoa::find($id);

        if (!$pessoa) {
            return response()->json([
                'message' => 'Pessoa não encontrada.'
            ], 404);
        }

        if ($request->user()->pessoa_id !== $pessoa->pessoa_id) {
            return response()->json([
                'message' => 'Você não tem permissão para alterar os recursos desta pessoa.'
            ], 403);
        }

        $validated = $request->validate([
            'recurso_id' => [
                'required',
                'integer',
                'exists:recurso,recurso_id'
            ],

            'detalhes_recurso' => [
                'required',
                'string',
                'max:255'
            ],
        ]);

        $jaPossui = $pessoa->recursos()
            ->where('recurso.recurso_id', $validated['recurso_id'])
            ->exists();

        if ($jaPossui) {
            return response()->json([
                'message' => 'Este recurso já está associado à pessoa.'
            ], 409);
        }

        $pessoa->recursos()->attach(
            $validated['recurso_id'],
            [
                'detalhes_recurso' => $validated['detalhes_recurso']
            ]
        );

        return response()->json([
            'message' => 'Recurso adicionado com sucesso.',
            'data' => $pessoa->recursos()->get()
        ], 201);
    }

    public function destroy(Request $request, $id, $rid)
    {
        $pessoa = Pessoa::find($id);

        if (!$pessoa) {
            return response()->json([
                'message' => 'Pessoa não encontrada.'
            ], 404);
        }

        if ($request->user()->pessoa_id !== $pessoa->pessoa_id) {
            return response()->json([
                'message' => 'Você não tem permissão para alterar os recursos desta pessoa.'
            ], 403);
        }

        $recurso = Recurso::find($rid);

        if (!$recurso) {
            return response()->json([
                'message' => 'Recurso não encontrado.'
            ], 404);
        }

        $existe = $pessoa->recursos()
            ->where('recurso.recurso_id', $rid)
            ->exists();

        if (!$existe) {
            return response()->json([
                'message' => 'Este recurso não está associado à pessoa.'
            ], 404);
        }

        $pessoa->recursos()->detach($rid);

        return response()->noContent();
    }
}