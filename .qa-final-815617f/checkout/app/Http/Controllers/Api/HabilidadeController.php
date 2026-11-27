<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Habilidade;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class HabilidadeController extends Controller
{
    public function index(Request $request)
    {
        return ApiResponse::success(Habilidade::orderBy('nome_habilidade')->paginate(min(max($request->integer('per_page', 20), 1), 100)));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome_habilidade' => ['required', 'string', 'max:255'],
            'descricao_habilidade' => ['required', 'string', 'max:255'],
        ]);
        $Habilidade = Habilidade::create($validated);

        return ApiResponse::success($Habilidade, 'Habilidade criada com sucesso.', 201);
    }

    public function show(string $id)
    {
        return ApiResponse::success(Habilidade::findOrFail($id));
    }

    public function update(Request $request, string $id)
    {
        $Habilidade = Habilidade::findOrFail($id);

        $validated = $request->validate([
            'nome_habilidade' => ['sometimes', 'string', 'max:255'],
            'descricao_habilidade' => ['sometimes', 'string', 'max:255'],
        ]);
        $Habilidade->update($validated);

        return ApiResponse::success($Habilidade, 'Habilidade atualizada com sucesso.');
    }

    public function destroy(string $id)
    {
        $Habilidade = Habilidade::findOrFail($id);

        $Habilidade->delete();

        return response()->noContent();
    }
}
