<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PessoaRegistrationRequest;
use App\Http\Resources\PessoaPrivateResource;
use App\Http\Resources\PessoaPublicResource;
use App\Models\Pessoa;
use App\Services\AccountCredentials;
use App\Services\AccountDeletion;
use App\Services\AvaliacaoService;
use App\Services\ConfiabilidadeService;
use App\Services\PessoaRegistrationService;
use App\Support\AccountAccess;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PessoaController extends Controller
{
    public function index(Request $request, AvaliacaoService $avaliacoes, ConfiabilidadeService $confiabilidade)
    {
        $perPage = min(max($request->integer('per_page', 20), 1), 100);
        $pessoas = Pessoa::query()
            ->where('role_pessoa', 'voluntario')
            ->where('status_validacao', 'aprovado')
            ->orderBy('nm_pessoa')
            ->paginate($perPage);

        $ids = $pessoas->getCollection()->pluck('pessoa_id');
        $medias = $avaliacoes->mediasPorPessoas($ids);
        $resumos = $confiabilidade->resumosPublicosPorPessoas($ids);
        foreach ($pessoas->getCollection() as $pessoa) {
            $pessoa->setAttribute('media_avaliacao_calculada', $medias[$pessoa->pessoa_id] ?? null);
            $pessoa->setAttribute('confiabilidade_resumo', $resumos[$pessoa->pessoa_id]);
        }

        return ApiResponse::success(PessoaPublicResource::collection($pessoas));
    }

    public function show($id, AvaliacaoService $avaliacoes, ConfiabilidadeService $confiabilidade)
    {
        $pessoa = Pessoa::query()
            ->where('role_pessoa', 'voluntario')
            ->where('status_validacao', 'aprovado')
            ->findOrFail($id);

        $pessoa->setAttribute('media_avaliacao_calculada', $avaliacoes->mediaVoluntario($pessoa));
        $pessoa->setAttribute('confiabilidade_resumo', $confiabilidade->resumoPublico($pessoa));

        return ApiResponse::success(new PessoaPublicResource($pessoa));
    }

    public function register(PessoaRegistrationRequest $request, PessoaRegistrationService $registration)
    {
        $pessoa = $registration->register($request->validated(), $request->ip(), $request->userAgent());
        $token = $pessoa->createToken('pessoa-token', ['pessoa'])->plainTextToken;

        return ApiResponse::success([
            'user' => new PessoaPrivateResource($this->prepararMetricas($pessoa)),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Cadastro realizado com sucesso.', 201);
    }

    public function login(Request $request, AccountCredentials $credentials)
    {
        $validated = $request->validate([
            'login_pessoa' => ['required', 'string'],
            'senha_pessoa' => ['required', 'string'],
        ]);

        $result = $credentials->authenticate('pessoa', $validated['login_pessoa'], $validated['senha_pessoa'],
            fn (Pessoa $account) => [$account, $account->createToken('pessoa-token', ['pessoa'])->plainTextToken]);

        if ($result === null) {
            return ApiResponse::error('Credenciais inválidas.', null, 401);
        }

        [$account, $token] = $result;
        $profile = $account->exclusao_pendente
            ? AccountAccess::deletionStatus($account)
            : new PessoaPrivateResource($this->prepararMetricas($account));

        return ApiResponse::success([
            'user' => $profile,
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Login realizado com sucesso.');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return ApiResponse::success(null, 'Logout realizado com sucesso.');
    }

    public function me(Request $request)
    {
        $account = $request->user();

        return ApiResponse::success($account->exclusao_pendente
            ? AccountAccess::deletionStatus($account)
            : new PessoaPrivateResource($this->prepararMetricas($account)));
    }

    public function update(Request $request, $id, AccountCredentials $credentials)
    {
        $pessoa = Pessoa::findOrFail($id);

        // Impede que uma pessoa altere os dados de outra conta
        if ($request->user()->pessoa_id !== $pessoa->pessoa_id) {
            return ApiResponse::error('Você não tem permissão para alterar esta conta.', null, 403);
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

            'senha_atual' => ['required_with:senha_pessoa', 'nullable', 'string'],
            'senha_pessoa' => [
                'sometimes',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        unset($validated['senha_atual']);

        $pessoa = $credentials->update($request->user(), $validated, $request->input('senha_atual'));

        return ApiResponse::success(new PessoaPrivateResource($this->prepararMetricas($pessoa->fresh())), 'Pessoa atualizada com sucesso.');
    }

    public function destroy(Request $request, $id, AccountDeletion $deletions)
    {
        $account = Pessoa::findOrFail($id);
        if (! $request->user()->is($account)) {
            return ApiResponse::error('Acesso negado.', null, 403);
        }

        $account = $deletions->schedule($request->user());

        return ApiResponse::success(['deletar_em' => $account->deletar_em], 'A exclusão da conta foi agendada para daqui a 7 dias.');
    }

    public function cancelarExclusao(Request $request, $id, AccountDeletion $deletions)
    {
        $account = Pessoa::findOrFail($id);
        if (! $request->user()->is($account)) {
            return ApiResponse::error('Acesso negado.', null, 403);
        }

        $deletions->cancel($request->user());

        return ApiResponse::success(null, 'Exclusão cancelada com sucesso.');
    }

    private function prepararMetricas(Pessoa $pessoa): Pessoa
    {
        $pessoa->setAttribute('media_avaliacao_calculada', app(AvaliacaoService::class)->mediaVoluntario($pessoa));
        $pessoa->setAttribute('confiabilidade_resumo', app(ConfiabilidadeService::class)->resumoPublico($pessoa));

        return $pessoa;
    }
}
