<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CampanhaXp;
use App\Services\CampanhaXpManagement;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class CampanhaXpController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate(['per_page' => ['sometimes', 'integer', 'min:1', 'max:100']]);

        return ApiResponse::success(CampanhaXp::orderByDesc('campanha_xp_id')->paginate($validated['per_page'] ?? 20));
    }

    public function store(Request $request, CampanhaXpManagement $management)
    {
        return ApiResponse::success($management->save($request->user(), $request->all()), 'Campanha criada.', 201);
    }

    public function update(Request $request, CampanhaXp $campanha, CampanhaXpManagement $management)
    {
        return ApiResponse::success($management->save($request->user(), $request->all(), $campanha), 'Campanha atualizada.');
    }

    public function status(Request $request, CampanhaXp $campanha, CampanhaXpManagement $management)
    {
        $dados = $request->validate(['ativa' => ['required', 'boolean']]);

        return ApiResponse::success($management->save($request->user(), $dados, $campanha, statusOnly: true), 'Status da campanha atualizado.');
    }
}
