<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pessoa;
use App\Models\Assinatura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PessoaController extends Controller
{
    public function index()
    {
        $pessoas = Pessoa::all();
        return response()->json($pessoas);
    }

    public function show($id)
    {
        $pessoa = Pessoa::find($id);
        if (!$pessoa) {
            return response()->json(['message' => 'Pessoa não encontrada.'], 404);
        }
        return response()->json($pessoa);
    }



    public function register(Request $request)
    {
        $validated = $request->validate([
            // Dados pessoais
            'nm_pessoa' => ['required', 'string', 'max:64'],
            'genero_pessoa' => [
                'required',
                Rule::in([
                    'masculino',
                    'feminino',
                    'outro',
                    'prefiro não dizer',
                ]),
            ],
            'cpf_pessoa' => ['required', 'string', 'size:11', 'unique:pessoa,cpf_pessoa'],
            'dt_nasc' => ['required', 'date'],
            'rg_pessoa' => ['required', 'string', 'max:20'],

            // Dados de contato
            'email_pessoa' => ['required', 'email', 'max:128', 'unique:pessoa,email_pessoa'],
            'tele_pessoa' => ['required', 'string', 'size:11'],

            // Endereço
            'cep_pessoa' => ['required', 'string', 'size:8'],
            'logradouro_pessoa' => ['required', 'string', 'max:64'],
            'compl_pessoa' => ['nullable', 'string', 'max:64'],
            'cidade_pessoa' => ['required', 'string', 'max:64'],
            'bairro_pessoa' => ['required', 'string', 'max:64'],
            'uf_pessoa' => ['required', 'string', 'size:2'],

            // Dados da conta
            'login_pessoa' => ['required', 'string', 'max:64', 'unique:pessoa,login_pessoa'],
            'senha_pessoa' => ['required', 'string', 'min:8', 'confirmed'],

            // Links de documentos
            'antecedentes_pessoa_link' => ['required', 'string', 'max:255'],
            'cnh_pessoa_link' => ['required', 'string', 'max:255'],
            'rg_pessoa_link' => ['required', 'string', 'max:255'],
            'pfp_pessoa_link' => ['required', 'string', 'max:255'],

            // Aceite dos termos
            'aceitou_termos' => ['required', 'accepted'],
        ]);

        // Criptografa a senha
        $validated['senha_pessoa'] = Hash::make($validated['senha_pessoa']);

        // Define a role padrão
        $validated['role_pessoa'] = 'voluntario';

        // Remove campo auxiliar
        unset($validated['aceitou_termos']);

        // Cria a pessoa
        $pessoa = Pessoa::create($validated);

        // Registra a assinatura eletrônica
        //Assinatura::create([
            //'dispositivo' => $request->header('Sec-CH-UA-Platform') ?? 'Desconhecido',
           // 'ip_assinatura' => $request->ip(),
            //'user_agent_assinatura' => $request->userAgent(),
            //'documento_url' => '/docs/termos-de-uso.pdf',
            //'documento_hash' => hash('sha256', '/docs/termos-de-uso.pdf'),
            //'geoloc_assinatura' => null,
   //     ]);

        // Gera token Sanctum
        $token = $pessoa
            ->createToken('pessoa-token', ['pessoa'])
            ->plainTextToken;

        // Retorna resposta JSON
        return response()->json([
            'message' => 'Cadastro realizado com sucesso.',
            'user' => $pessoa,
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }



    public function login(Request $request)
    {
        $validated = $request->validate([
            'login_pessoa' => ['required'],
            'senha_pessoa' => ['required'],
        ]);

        $pessoa = Pessoa::where('login_pessoa', $validated['login_pessoa'])
            ->first();

        if (!$pessoa) {

            return response()->json([
                'message' => 'Usuário não encontrado.'
            ], 404);

        }

        if (!Hash::check($validated['senha_pessoa'], $pessoa->senha_pessoa)) {

            return response()->json([
                'message' => 'Senha inválida.'
            ], 401);

        }

        $token = $pessoa
            ->createToken('pessoa-token', ['pessoa'])
            ->plainTextToken;

        return response()->json([
            'message' => 'Login realizado com sucesso.',
            'user' => $pessoa,
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

public function update(Request $request, $id)
{
    $pessoa = Pessoa::find($id);

    if (!$pessoa) {
        return response()->json([
            'message' => 'Pessoa não encontrada.'
        ], 404);
    }

    // Impede que uma pessoa altere os dados de outra conta
    if ($request->user()->pessoa_id !== $pessoa->pessoa_id) {
        return response()->json([
            'message' => 'Você não tem permissão para alterar esta conta.'
        ], 403);
    }

    $validated = $request->validate([
        'email_pessoa' => [
            'sometimes',
            'email',
            'max:128',
            Rule::unique('pessoa', 'email_pessoa')
                ->ignore($pessoa->pessoa_id, 'pessoa_id'),
        ],

        'tele_pessoa' => [
            'sometimes',
            'string',
            'size:11',
        ],

        'cep_pessoa' => [
            'sometimes',
            'string',
            'size:8',
        ],

        'logradouro_pessoa' => [
            'sometimes',
            'string',
            'max:64',
        ],

        'compl_pessoa' => [
            'sometimes',
            'nullable',
            'string',
            'max:64',
        ],

        'cidade_pessoa' => [
            'sometimes',
            'string',
            'max:64',
        ],

        'bairro_pessoa' => [
            'sometimes',
            'string',
            'max:64',
        ],

        'uf_pessoa' => [
            'sometimes',
            'string',
            'size:2',
        ],

        'pfp_pessoa_link' => [
            'sometimes',
            'string',
            'max:255',
        ],

        'senha_pessoa' => [
            'sometimes',
            'string',
            'min:8',
            'confirmed',
        ],
    ]);

    // Se a senha foi enviada, criptografa antes de salvar
    if (isset($validated['senha_pessoa'])) {
        $validated['senha_pessoa'] = Hash::make(
            $validated['senha_pessoa']
        );
    }

    $pessoa->update($validated);

    return response()->json([
        'message' => 'Pessoa atualizada com sucesso.',
        'user' => $pessoa->fresh(),
    ], 200);
}

    public function destroy(Request $request, $id)
{
    $pessoa = Pessoa::find($id);

    if (!$pessoa) {
        return response()->json([
            'message' => 'Pessoa não encontrada.'
        ], 404);
    }

    if ($request->user()->pessoa_id !== $pessoa->pessoa_id) {
        return response()->json([
            'message' => 'Você não tem permissão para excluir esta conta.'
        ], 403);
    }

    $pessoa->update([
        'exclusao_pendente' => true,
        'deletar_em' => now()->addDays(7),
    ]);

    return response()->json([
        'message' => 'A exclusão da conta foi agendada para daqui a 7 dias.',
        'deletar_em' => $pessoa->deletar_em,
    ], 200);
}

    public function cancelarExclusao(Request $request, $id)
{
    $pessoa = Pessoa::find($id);

    if (!$pessoa) {
        return response()->json([
            'message' => 'Pessoa não encontrada.'
        ], 404);
    }

    if ($request->user()->pessoa_id !== $pessoa->pessoa_id) {
        return response()->json([
            'message' => 'Você não tem permissão para alterar esta conta.'
        ], 403);
    }

    $pessoa->update([
        'exclusao_pendente' => false,
        'deletar_em' => null,
    ]);

    return response()->json([
        'message' => 'Exclusão cancelada com sucesso.'
    ]);
}

}