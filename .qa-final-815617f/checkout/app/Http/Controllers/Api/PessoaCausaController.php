<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Causa;
use App\Models\Pessoa;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class PessoaCausaController extends Controller
{
    public function index($id)
    {
        $pessoa = Pessoa::query()
            ->where('role_pessoa', 'voluntario')
            ->where('status_validacao', 'aprovado')
            ->findOrFail($id);

        return ApiResponse::success($pessoa->causas()->get());
    }

    public function store(Request $request, $id)
    {
        $pessoa = Pessoa::findOrFail($id);

        if ($request->user()->pessoa_id !== $pessoa->pessoa_id) {
            return ApiResponse::error('Você não tem permissão para alterar as causas desta pessoa.', null, 403);
        }

        $validated = $request->validate([
            'causa_id' => [
                'required',
                'integer',
                'exists:causa,causa_id',
            ],
        ]);

        $jaPossui = $pessoa->causas()
            ->where('causa.causa_id', $validated['causa_id'])
            ->exists();

        if ($jaPossui) {
            return ApiResponse::error('Esta causa já está associada à pessoa.', null, 409);
        }

        $pessoa->causas()->attach(
            $validated['causa_id']
        );

        return ApiResponse::success($pessoa->causas()->get(), 'Causa adicionada com sucesso.', 201);
    }

    public function destroy(Request $request, $id, $cid)
    {
        $pessoa = Pessoa::findOrFail($id);

        if ($request->user()->pessoa_id !== $pessoa->pessoa_id) {
            return ApiResponse::error('Você não tem permissão para alterar as causas desta pessoa.', null, 403);
        }

        Causa::findOrFail($cid);

        $existe = $pessoa->causas()
            ->where('causa.causa_id', $cid)
            ->exists();

        if (! $existe) {
            return ApiResponse::error('Esta causa não está associada à pessoa.', null, 404);
        }

        $pessoa->causas()->detach($cid);

        return response()->noContent();
    }
}
