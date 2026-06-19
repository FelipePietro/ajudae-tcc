<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assinatura;
use App\Services\AssinaturaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssinaturaController extends Controller
{
    public function __construct(
        private readonly AssinaturaService $assinaturaService
    ) {
    }

    public function store(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'tipo' => 'required|in:pessoa,ong',
            'id_entidade' => 'required|integer|min:1',
            'aceite_termos' => 'required|accepted',
            'dispositivo' => 'nullable|string|max:100',
            'geoloc_assinatura' => 'nullable|string|max:100',
        ]);

        $assinatura = $this->assinaturaService->registrar(
            $request,
            $dados['tipo'],
            (int) $dados['id_entidade']
        );

        return response()->json([
            'message' => 'Assinatura registrada com sucesso.',
            'data' => $assinatura,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $assinatura = Assinatura::query()
            ->with(['pessoas', 'ongs'])
            ->findOrFail($id);

        return response()->json($assinatura);
    }
}
