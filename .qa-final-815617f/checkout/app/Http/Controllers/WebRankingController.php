<?php

namespace App\Http\Controllers;

use App\Services\RankingService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

final class WebRankingController extends Controller
{
    public function index(Request $request, RankingService $ranking)
    {
        $input = $request->validate([
            'cidade' => ['nullable', 'string', 'max:64'],
            'busca' => ['nullable', 'string', 'max:80'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        return ApiResponse::success($ranking->web($input['cidade'] ?? null, $input['busca'] ?? null));
    }
}
