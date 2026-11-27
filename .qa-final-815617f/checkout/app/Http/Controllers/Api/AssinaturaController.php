<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assinatura;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class AssinaturaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return ApiResponse::success(
            $request->user()->assinaturas()->orderByDesc('aceito_em')->paginate(20)
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo_termo' => ['required', 'string', 'max:64'],
            'versao_termo' => ['required', 'string', 'max:32'],
            'aceite' => ['required', 'accepted'],
            'documento_hash' => ['nullable', 'string', 'size:64'],
        ]);

        $assinatura = $request->user()->assinaturas()->firstOrCreate([
            'tipo_termo' => $validated['tipo_termo'],
            'versao_termo' => $validated['versao_termo'],
        ], [
            'aceito_em' => now(),
            'ip_assinatura' => $request->ip(),
            'user_agent_assinatura' => mb_substr((string) $request->userAgent(), 0, 255),
            'documento_hash' => $validated['documento_hash'] ?? null,
        ]);

        return ApiResponse::success($assinatura, 'Aceite registrado com sucesso.', $assinatura->wasRecentlyCreated ? 201 : 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Assinatura $assinatura)
    {
        if (! $assinatura->assinante->is($request->user())) {
            return ApiResponse::error('Acesso negado.', null, 403);
        }

        return ApiResponse::success($assinatura);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Assinatura $assinatura)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Assinatura $assinatura)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Assinatura $assinatura)
    {
        //
    }
}
