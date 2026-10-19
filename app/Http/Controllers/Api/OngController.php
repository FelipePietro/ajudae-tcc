<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OngPrivateResource;
use App\Http\Resources\OngPublicResource;
use App\Models\AdminLog;
use App\Models\Ong;
use App\Rules\Cnpj;
use App\Rules\Cpf;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class OngController extends Controller
{
    public function register(Request $request)
    {
        $request->merge([
            'cnpj_ong' => preg_replace('/\D/', '', (string) $request->input('cnpj_ong')),
            'cpf_responsavel_ong' => preg_replace('/\D/', '', (string) $request->input('cpf_responsavel_ong')),
        ]);

        $validated = $request->validate([

            // Dados da ONG

            'login_ong' => ['required', 'string', 'max:64', 'unique:ong,login_ong'],
            'nome_fantasia' => ['required', 'string', 'max:255'],
            'cnpj_ong' => ['required', 'digits:14', new Cnpj, 'unique:ong,cnpj_ong'],
            'email_ong' => ['required', 'email', 'unique:ong,email_ong'],
            'senha_ong' => ['required', 'string', 'min:8', 'confirmed'],
            'tel_ong' => ['required', 'digits_between:10,15'],

            // Endereço
            'cep_ong' => ['required', 'string', 'size:8'],
            'logradouro_ong' => ['required', 'string'],
            'bairro_ong' => ['required', 'string'],
            'cidade_ong' => ['required', 'string'],
            'uf_ong' => ['required', 'string', 'size:2'],

            // Informações
            'descricao_ong' => ['required', 'string'],
            'site_url_ong' => ['nullable', 'url:http,https', 'max:255'],
            'pfp_ong_link' => ['required', 'string'],

            // Responsável
            'nome_responsavel_ong' => ['required', 'string'],
            'cpf_responsavel_ong' => ['required', 'digits:11', new Cpf],
            'rg_responsavel_ong_link' => ['required', 'string'],
            'email_responsavel_ong' => ['required', 'email'],
            'tel_responsavel_ong' => ['required', 'digits_between:10,15'],

            // Termos
            'aceitou_termos' => ['required', 'accepted'],
        ]);

        $ong = DB::transaction(function () use ($validated, $request) {
            unset($validated['aceitou_termos']);
            $validated['senha_ong'] = Hash::make($validated['senha_ong']);

            $ong = Ong::create($validated);
            $ong->assinaturas()->create([
                'tipo_termo' => 'termos_de_uso',
                'versao_termo' => '1.0',
                'aceito_em' => now(),
                'ip_assinatura' => $request->ip(),
                'user_agent_assinatura' => mb_substr((string) $request->userAgent(), 0, 255),
                'documento_hash' => hash('sha256', 'termos_de_uso:1.0'),
            ]);

            return $ong->refresh();
        });

        // Token Sanctum
        $token = $ong
            ->createToken('ong-token', ['ong'])
            ->plainTextToken;

        return ApiResponse::success([
            'ong' => new OngPrivateResource($ong),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'ONG cadastrada com sucesso.', 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'login_ong' => ['required'],
            'senha_ong' => ['required'],
        ]);

        $ong = Ong::where('login_ong', $validated['login_ong'])
            ->first();

        if (! $ong || ! Hash::check($validated['senha_ong'], $ong->senha_ong)) {
            return ApiResponse::error('Credenciais inválidas.', null, 401);
        }

        $token = $ong
            ->createToken('ong-token', ['ong'])
            ->plainTextToken;

        return ApiResponse::success([
            'ong' => new OngPrivateResource($ong),
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
        return ApiResponse::success(new OngPrivateResource($request->user()));
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

    public function update(Request $request, $id)
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

            'senha_ong' => [
                'sometimes',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        if (isset($validated['senha_ong'])) {
            $validated['senha_ong'] = Hash::make(
                $validated['senha_ong']
            );
        }

        $ong->update($validated);

        return ApiResponse::success(new OngPrivateResource($ong->fresh()), 'ONG atualizada com sucesso.');
    }

    public function updateStatus(Request $request, $id)
    {
        $ong = Ong::findOrFail($id);

        $validated = $request->validate([
            'status_ong' => [
                'required',
                Rule::in([
                    'ativo',
                    'aguardando aprovação',
                    'reprovado',
                ]),
            ],
        ]);

        $statusAnterior = $ong->status_ong;
        DB::transaction(function () use ($ong, $validated, $request, $statusAnterior) {
            $ong->forceFill(['status_ong' => $validated['status_ong']])->save();
            AdminLog::create([
                'pessoa_id' => $request->user()->pessoa_id,
                'acao' => 'ong.status_atualizado',
                'recurso_type' => Ong::class,
                'recurso_id' => $ong->ong_id,
                'detalhes' => ['de' => $statusAnterior, 'para' => $validated['status_ong']],
            ]);
        });

        return ApiResponse::success(new OngPublicResource($ong->fresh()), 'Status da ONG atualizado com sucesso.');
    }

    public function destroy(Request $request, $id)
    {
        $ong = Ong::findOrFail($id);

        // A ONG só pode solicitar a exclusão da própria conta
        if ($request->user()->ong_id !== $ong->ong_id) {
            return ApiResponse::error('Você não tem permissão para excluir esta ONG.', null, 403);
        }

        // Evita agendar a exclusão novamente
        if ($ong->exclusao_pendente) {
            return ApiResponse::error('A exclusão desta ONG já está agendada.', null, 409);
        }

        if ($ong->eventos()->whereNotIn('status_evento', ['finalizado', 'cancelado', 'reprovado'])->exists()) {
            return ApiResponse::error('A ONG possui eventos ativos e não pode solicitar exclusão.', null, 409);
        }

        $ong->forceFill([
            'exclusao_pendente' => true,
            'deletar_em' => now()->addDays(7),
        ])->save();

        return ApiResponse::success(['deletar_em' => $ong->deletar_em], 'A exclusão da ONG foi agendada para daqui a 7 dias.');
    }

    public function cancelarExclusao(Request $request, $id)
    {
        $ong = Ong::findOrFail($id);

        if ($request->user()->ong_id !== $ong->ong_id) {
            return ApiResponse::error('Você não tem permissão para alterar esta ONG.', null, 403);
        }

        if (! $ong->exclusao_pendente) {
            return ApiResponse::error('Não existe exclusão agendada para esta ONG.', null, 409);
        }

        $ong->forceFill([
            'exclusao_pendente' => false,
            'deletar_em' => null,
        ])->save();

        return ApiResponse::success(null, 'Exclusão da ONG cancelada com sucesso.');
    }
}
