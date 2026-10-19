<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CampanhaXp;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CampanhaXpController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate(['per_page' => ['sometimes', 'integer', 'min:1', 'max:100']]);

        return ApiResponse::success(CampanhaXp::orderByDesc('campanha_xp_id')->paginate($validated['per_page'] ?? 20));
    }

    public function store(Request $request)
    {
        $dados = $this->validar($request->all());

        return ApiResponse::success(CampanhaXp::create($dados), 'Campanha criada.', 201);
    }

    public function update(Request $request, CampanhaXp $campanha)
    {
        $dados = $this->validar(array_merge($campanha->only([
            'nome', 'multiplicador', 'inicio', 'fim', 'ativa', 'cat_evento_id',
        ]), $request->all()));
        $campanha->update($dados);

        return ApiResponse::success($campanha->refresh(), 'Campanha atualizada.');
    }

    public function status(Request $request, CampanhaXp $campanha)
    {
        $dados = $request->validate(['ativa' => ['required', 'boolean']]);
        $campanha->update($dados);

        return ApiResponse::success($campanha->refresh(), 'Status da campanha atualizado.');
    }

    private function validar(array $dados): array
    {
        return Validator::make($dados, [
            'nome' => ['required', 'string', 'max:120'],
            'multiplicador' => ['required', 'numeric', 'gt:0'],
            'inicio' => ['required', 'date'],
            'fim' => ['required', 'date', 'after_or_equal:inicio'],
            'ativa' => ['required', 'boolean'],
            'cat_evento_id' => ['required', 'integer', 'exists:cat_evento,cat_evento_id'],
        ])->validate();
    }
}
