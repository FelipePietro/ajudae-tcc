<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CatEvento;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CatEventoController extends Controller
{
    public function index(Request $request)
    {
        return ApiResponse::success(
            CatEvento::orderBy('nome_categoria')->paginate(min(max($request->integer('per_page', 20), 1), 100))
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['nome_categoria' => ['required', 'string', 'max:64', 'unique:cat_evento,nome_categoria']]);
        $CatEvento = CatEvento::create($validated);

        return ApiResponse::success($CatEvento, 'Categoria criada com sucesso.', 201);
    }

    public function show(string $id)
    {
        return ApiResponse::success(CatEvento::findOrFail($id));
    }

    public function update(Request $request, string $id)
    {
        $CatEvento = CatEvento::findOrFail($id);
        $validated = $request->validate([
            'nome_categoria' => ['required', 'string', 'max:64', Rule::unique('cat_evento', 'nome_categoria')->ignore($CatEvento->cat_evento_id, 'cat_evento_id')],
        ]);
        $CatEvento->update($validated);

        return ApiResponse::success($CatEvento, 'Categoria atualizada com sucesso.');
    }

    public function destroy(string $id)
    {
        $CatEvento = CatEvento::findOrFail($id);

        if ($CatEvento->eventos()->exists()) {
            return ApiResponse::error('A categoria está em uso e não pode ser removida.', null, 409);
        }

        $CatEvento->delete();

        return response()->noContent();
    }
}
