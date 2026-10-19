<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recurso;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class RecursoController extends Controller
{
    public function index(Request $request)
    {
        return ApiResponse::success(Recurso::orderBy('nome_recurso')->paginate(min(max($request->integer('per_page', 20), 1), 100)));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome_recurso' => ['required', 'string', 'max:255'],
            'descricao_recurso' => ['required', 'string', 'max:255'],
        ]);
        $Recurso = Recurso::create($validated);

        return ApiResponse::success($Recurso, 'Recurso criado com sucesso.', 201);
    }

    public function show(string $id)
    {
        return ApiResponse::success(Recurso::findOrFail($id));
    }

    public function update(Request $request, string $id)
    {
        $Recurso = Recurso::findOrFail($id);

        $validated = $request->validate([
            'nome_recurso' => ['sometimes', 'string', 'max:255'],
            'descricao_recurso' => ['sometimes', 'string', 'max:255'],
        ]);
        $Recurso->update($validated);

        return ApiResponse::success($Recurso, 'Recurso atualizado com sucesso.');
    }

    public function destroy(string $id)
    {
        $Recurso = Recurso::findOrFail($id);

        $Recurso->delete();

        return response()->noContent();
    }
}
