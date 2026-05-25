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
}