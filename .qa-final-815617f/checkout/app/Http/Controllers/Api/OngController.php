<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OngRegistrationRequest;
use App\Http\Resources\OngPrivateResource;
use App\Http\Resources\OngPublicResource;
use App\Models\Ong;
use App\Services\AccountCredentials;
use App\Services\AccountDeletion;
use App\Services\OngRegistrationService;
use App\Services\OngValidationService;
use App\Support\AccountAccess;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OngController extends Controller
{
    public function register(OngRegistrationRequest $request, OngRegistrationService $registration)
    {
        [$ong, $token] = $registration->register($request->validated(), $request->ip(), $request->userAgent(), issueToken: true);

        return ApiResponse::success([
            'ong' => new OngPrivateResource($ong),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'ONG cadastrada com sucesso.', 201);
    }

    public function login(Request $request, AccountCredentials $credentials)
    {
        $validated = $request->validate([
            'login_ong' => ['required', 'string'],
            'senha_ong' => ['required', 'string'],
        ]);

        $result = $credentials->authenticate('ong', $validated['login_ong'], $validated['senha_ong'],
            fn (Ong $account) => [$account, $account->createToken('ong-token', ['ong'])->plainTextToken]);

        if ($result === null) {
            return ApiResponse::error('Credenciais inválidas.', null, 401);
        }

        [$account, $token] = $result;
        $profile = $account->exclusao_pendente ? AccountAccess::deletionStatus($account) : new OngPrivateResource($account);

        return ApiResponse::success([
            'ong' => $profile,
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
            : new OngPrivateResource($account));
    }

    public function index()
    {
        $perPage = min(max(request()->integer('per_page', 20), 1), 100);
        $ongs = Ong::orderBy('nome_fantasia')->paginate($perPage);

        return ApiResponse::success(OngPublicResource::collection($ongs));
    }

    public function show($id)
    {
        return ApiResponse::success(new OngPublicResource(Ong::findOrFail($id)));
    }

    public function update(Request $request, $id, AccountCredentials $credentials)
    {
        $ong = Ong::findOrFail($id);

        // Garante que a ONG só altere a própria conta
        if ($request->user()->ong_id !== $ong->ong_id) {
            return ApiResponse::error('Você não tem permissão para alterar esta ONG.', null, 403);
        }

        $validated = $request->validate([
            'descricao_ong' => [
                'sometimes',
                'string',
                'max:256',
            ],

            'pfp_ong_link' => [
                'sometimes',
                'string',
                'max:255',
            ],

            'email_ong' => [
                'sometimes',
                'email',
                'max:100',
                Rule::unique('ong', 'email_ong')->ignore($ong->ong_id, 'ong_id'),
            ],

            'tel_ong' => [
                'sometimes',
                'string',
                'max:15',
            ],

            'logradouro_ong' => [
                'sometimes',
                'string',
                'max:100',
            ],

            'cidade_ong' => [
                'sometimes',
                'string',
                'max:100',
            ],

            'uf_ong' => [
                'sometimes',
                'string',
                'size:2',
            ],

            'cep_ong' => [
                'sometimes',
                'string',
                'size:8',
            ],

            'bairro_ong' => [
                'sometimes',
                'string',
                'max:100',
            ],

            'site_url_ong' => [
                'sometimes',
                'string',
                'max:255',
            ],

            'login_ong' => [
                'sometimes',
                'string',
                'max:64',
                Rule::unique('ong', 'login_ong')
                    ->ignore($ong->ong_id, 'ong_id'),
            ],

            'senha_atual' => ['required_with:senha_ong', 'nullable', 'string'],
            'senha_ong' => [
                'sometimes',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        unset($validated['senha_atual']);
        $ong = $credentials->update($request->user(), $validated, $request->input('senha_atual'));

        return ApiResponse::success(new OngPrivateResource($ong->fresh()), 'ONG atualizada com sucesso.');
    }

    public function updateStatus(Request $request, $id, OngValidationService $validation)
    {
        $ong = Ong::findOrFail($id);
        $validated = $request->validate(OngValidationService::STATUS_RULES);
        $ong = $validation->update($ong, $request->user(), $validated['status_ong']);

        return ApiResponse::success(new OngPublicResource($ong), 'Status da ONG atualizado com sucesso.');
    }

    public function destroy(Request $request, $id, AccountDeletion $deletions)
    {
        $account = Ong::findOrFail($id);
        if (! $request->user()->is($account)) {
            return ApiResponse::error('Acesso negado.', null, 403);
        }

        $account = $deletions->schedule($request->user());

        return ApiResponse::success(['deletar_em' => $account->deletar_em], 'A exclusão da ONG foi agendada para daqui a 7 dias.');
    }

    public function cancelarExclusao(Request $request, $id, AccountDeletion $deletions)
    {
        $account = Ong::findOrFail($id);
        if (! $request->user()->is($account)) {
            return ApiResponse::error('Acesso negado.', null, 403);
        }

        $deletions->cancel($request->user());

        return ApiResponse::success(null, 'Exclusão da ONG cancelada com sucesso.');
    }
}
