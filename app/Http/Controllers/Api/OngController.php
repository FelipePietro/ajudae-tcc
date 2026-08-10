<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ong;
use App\Models\Assinatura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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

    public function index()
{
    $ongs = Ong::orderBy('nome_fantasia')->get();

    return response()->json([
        'data' => $ongs
    ], 200);
}

    public function show($id)
{
    $ong = Ong::find($id);

    if (!$ong) {
        return response()->json([
            'message' => 'ONG não encontrada.'
        ], 404);
    }

    return response()->json([
        'data' => $ong
    ], 200);
}

    public function update(Request $request, $id)
{
    $ong = Ong::find($id);

    if (!$ong) {
        return response()->json([
            'message' => 'ONG não encontrada.'
        ], 404);
    }

    // Garante que a ONG só altere a própria conta
    if ($request->user()->ong_id !== $ong->ong_id) {
        return response()->json([
            'message' => 'Você não tem permissão para alterar esta ONG.'
        ], 403);
    }

    $validated = $request->validate([
        'descricao_ong' => [
            'sometimes',
            'string',
            'max:256'
        ],

        'pfp_ong_link' => [
            'sometimes',
            'string',
            'max:255'
        ],

        'email_ong' => [
            'sometimes',
            'email',
            'max:100',
        ],

        'tel_ong' => [
            'sometimes',
            'string',
            'max:15'
        ],

        'logradouro_ong' => [
            'sometimes',
            'string',
            'max:100'
        ],

        'cidade_ong' => [
            'sometimes',
            'string',
            'max:100'
        ],

        'uf_ong' => [
            'sometimes',
            'string',
            'size:2'
        ],

        'cep_ong' => [
            'sometimes',
            'string',
            'size:8'
        ],

        'bairro_ong' => [
            'sometimes',
            'string',
            'max:100'
        ],

        'site_url_ong' => [
            'sometimes',
            'string',
            'max:255'
        ],

        'login_ong' => [
            'sometimes',
            'string',
            'max:64',
            Rule::unique('ong', 'login_ong')
                ->ignore($ong->ong_id, 'ong_id')
        ],

        'senha_ong' => [
            'sometimes',
            'string',
            'min:8',
            'confirmed'
        ],
    ]);

    if (isset($validated['senha_ong'])) {
        $validated['senha_ong'] = Hash::make(
            $validated['senha_ong']
        );
    }

    $ong->update($validated);

    return response()->json([
        'message' => 'ONG atualizada com sucesso.',
        'data' => $ong->fresh()
    ], 200);
}

    public function updateStatus(Request $request, $id)
{
    $ong = Ong::find($id);

    if (!$ong) {
        return response()->json([
            'message' => 'ONG não encontrada.'
        ], 404);
    }

    $validated = $request->validate([
        'status_ong' => [
            'required',
            Rule::in([
                'ativo',
                'aguardando aprovação',
                'reprovado'
            ])
        ]
    ]);

    $ong->update([
        'status_ong' => $validated['status_ong']
    ]);

    return response()->json([
        'message' => 'Status da ONG atualizado com sucesso.',
        'data' => $ong
    ]);
}

    public function destroy(Request $request, $id)
{
    $ong = Ong::find($id);

    if (!$ong) {
        return response()->json([
            'message' => 'ONG não encontrada.'
        ], 404);
    }

    // A ONG só pode solicitar a exclusão da própria conta
    if ($request->user()->ong_id !== $ong->ong_id) {
        return response()->json([
            'message' => 'Você não tem permissão para excluir esta ONG.'
        ], 403);
    }

    // Evita agendar a exclusão novamente
    if ($ong->exclusao_pendente) {
        return response()->json([
            'message' => 'A exclusão desta ONG já está agendada.',
            'deletar_em' => $ong->deletar_em
        ], 409);
    }

    $ong->update([
        'exclusao_pendente' => true,
        'deletar_em' => now()->addDays(7),
    ]);

    return response()->json([
        'message' => 'A exclusão da ONG foi agendada para daqui a 7 dias.',
        'deletar_em' => $ong->deletar_em
    ], 200);
}

    public function cancelarExclusao(Request $request, $id)
{
    $ong = Ong::find($id);

    if (!$ong) {
        return response()->json([
            'message' => 'ONG não encontrada.'
        ], 404);
    }

    if ($request->user()->ong_id !== $ong->ong_id) {
        return response()->json([
            'message' => 'Você não tem permissão para alterar esta ONG.'
        ], 403);
    }

    if (!$ong->exclusao_pendente) {
        return response()->json([
            'message' => 'Não existe exclusão agendada para esta ONG.'
        ], 409);
    }

    $ong->update([
        'exclusao_pendente' => false,
        'deletar_em' => null,
    ]);

    return response()->json([
        'message' => 'Exclusão da ONG cancelada com sucesso.'
    ], 200);
}

} 