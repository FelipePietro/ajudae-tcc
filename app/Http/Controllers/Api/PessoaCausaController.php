<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pessoa;
use App\Models\Causa;
use Illuminate\Http\Request;

class PessoaCausaController extends Controller
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
            'data' => $pessoa->causas()->get()
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
                'message' => 'Você não tem permissão para alterar as causas desta pessoa.'
            ], 403);
        }

        $validated = $request->validate([
            'causa_id' => [
                'required',
                'integer',
                'exists:causa,causa_id'
            ]
        ]);

        $jaPossui = $pessoa->causas()
            ->where('causa.causa_id', $validated['causa_id'])
            ->exists();

        if ($jaPossui) {
            return response()->json([
                'message' => 'Esta causa já está associada à pessoa.'
            ], 409);
        }

        $pessoa->causas()->attach(
            $validated['causa_id']
        );

        return response()->json([
            'message' => 'Causa adicionada com sucesso.',
            'data' => $pessoa->causas()->get()
        ], 201);
    }

    public function destroy(Request $request, $id, $cid)
    {
        $pessoa = Pessoa::find($id);

        if (!$pessoa) {
            return response()->json([
                'message' => 'Pessoa não encontrada.'
            ], 404);
        }

        if ($request->user()->pessoa_id !== $pessoa->pessoa_id) {
            return response()->json([
                'message' => 'Você não tem permissão para alterar as causas desta pessoa.'
            ], 403);
        }

        $causa = Causa::find($cid);

        if (!$causa) {
            return response()->json([
                'message' => 'Causa não encontrada.'
            ], 404);
        }

        $existe = $pessoa->causas()
            ->where('causa.causa_id', $cid)
            ->exists();

        if (!$existe) {
            return response()->json([
                'message' => 'Esta causa não está associada à pessoa.'
            ], 404);
        }

        $pessoa->causas()->detach($cid);

        return response()->noContent();
    }
}