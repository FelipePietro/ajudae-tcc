<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Causa;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class CausaController extends Controller
{
    public function index(Request $request)
    {
        return ApiResponse::success(Causa::orderBy('nome_causa')->paginate(min(max($request->integer('per_page', 20), 1), 100)));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome_causa' => ['required', 'string', 'max:255'],
            'descricao_causa' => ['required', 'string', 'max:255'],
        ]);
        $causa = Causa::create($validated);

        return ApiResponse::success($causa, 'Causa criada com sucesso.', 201);
    }

    public function show(string $id)
    {
        return ApiResponse::success(Causa::findOrFail($id));
    }

    public function update(Request $request, string $id)
    {
        $causa = Causa::findOrFail($id);

        $validated = $request->validate([
            'nome_causa' => ['sometimes', 'string', 'max:255'],
            'descricao_causa' => ['sometimes', 'string', 'max:255'],
        ]);
        $causa->update($validated);

        return ApiResponse::success($causa, 'Causa atualizada com sucesso.');
    }

    public function destroy(string $id)
    {
        $causa = Causa::findOrFail($id);

        $causa->delete();

        return response()->noContent();
    }
}
