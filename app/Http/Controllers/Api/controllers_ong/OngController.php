<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ong;
use App\Models\Assinatura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class OngController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([

            // Dados da ONG

            'login_ong' => ['required', 'string', 'max:64', 'unique:ong,login_ong'],
            'nome_fantasia' => ['required', 'string', 'max:255'],
            'cnpj_ong' => ['required', 'string', 'size:14', 'unique:ong,cnpj_ong'],
            'email_ong' => ['required', 'email', 'unique:ong,email_ong'],
            'senha_ong' => ['required', 'string', 'min:8', 'confirmed'],
            'tel_ong' => ['required', 'string', 'size:11'],

            // Endereço
            'cep_ong' => ['required', 'string', 'size:8'],
            'logradouro_ong' => ['required', 'string'],
            'bairro_ong' => ['required', 'string'],
            'cidade_ong' => ['required', 'string'],
            'uf_ong' => ['required', 'string', 'size:2'],

            // Informações
            'descricao_ong' => ['required', 'string'],
            'site_url_ong' => ['required', 'string'],
            'pfp_ong_link' => ['required', 'string'],

            // Responsável
            'nome_responsavel_ong' => ['required', 'string'],
            'cpf_responsavel_ong' => ['required', 'string'],
            'rg_responsavel_ong_link' => ['required', 'string'],
            'email_responsavel_ong' => ['required', 'email'],
            'tel_responsavel_ong' => ['required', 'string'],

            // Termos
            'aceitou_termos' => ['required', 'accepted'],
        ]);

        // Criptografa senha
        $validated['senha_ong'] = Hash::make($validated['senha_ong']);

        // Status inicial
        $validated['status_ong'] = 'aguardando aprovação';

        unset($validated['aceitou_termos']);

        // Cria ONG
        $ong = Ong::create($validated);

        // Assinatura eletrônica
        //Assinatura::create([
           // 'dispositivo' => $request->header('Sec-CH-UA-Platform') ?? 'Desconhecido',
            //'ip_assinatura' => $request->ip(),
            //'user_agent_assinatura' => $request->userAgent(),
            //'documento_url' => '/docs/termos-de-uso.pdf',
            //'documento_hash' => hash('sha256', '/docs/termos-de-uso.pdf'),
            //'geoloc_assinatura' => null,
        //]);

        // Token Sanctum
        $token = $ong
            ->createToken('ong-token', ['ong'])
            ->plainTextToken;

        return response()->json([
            'message' => 'ONG cadastrada com sucesso.',
            'ong' => $ong,
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function login(Request $request)
{
    $validated = $request->validate([
        'login_ong' => ['required'],
        'senha_ong' => ['required'],
    ]);

    $ong = Ong::where('login_ong', $validated['login_ong'])
        ->first();

    if (!$ong) {

        return response()->json([
            'message' => 'ONG não encontrada.'
        ], 404);

    }

    if (!Hash::check($validated['senha_ong'], $ong->senha_ong)) {

        return response()->json([
            'message' => 'Senha inválida.'
        ], 401);

    }

    $token = $ong
        ->createToken('ong-token', ['ong'])
        ->plainTextToken;

    return response()->json([
        'message' => 'Login realizado com sucesso.',
        'ong' => $ong,
        'token' => $token,
        'token_type' => 'Bearer',
    ]);
}

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout realizado com sucesso.'
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
}