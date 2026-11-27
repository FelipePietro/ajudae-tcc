<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RankingService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class RankingController extends Controller
{
    public function index(Request $request, RankingService $ranking)
    {
        $dados = $request->validate([
            'cidade' => ['sometimes', 'required', 'string', 'max:64'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);
        $cidade = $dados['cidade'] ?? null;

        return ApiResponse::success([
            'cidade' => $cidade,
            'ranking' => $ranking->listar($cidade, $dados['per_page'] ?? 20),
        ]);
    }
}
