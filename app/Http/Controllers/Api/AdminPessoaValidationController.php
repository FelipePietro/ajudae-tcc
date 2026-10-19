<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DocumentoPessoaResource;
use App\Http\Resources\PessoaAdminResource;
use App\Models\DocumentoPessoa;
use App\Models\Pessoa;
use App\Services\PessoaValidationService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminPessoaValidationController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'status' => ['sometimes', Rule::in([
                'aguardando_validacao',
                'em_analise',
                'correcao_solicitada',
                'aprovado',
                'reprovado',
                'reprovado_definitivamente',
            ])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Pessoa::query()
            ->where('role_pessoa', 'voluntario')
            ->with('documentos')
            ->orderBy('created_at');
        if (isset($validated['status'])) {
            $query->where('status_validacao', $validated['status']);
        } else {
            $query->whereIn('status_validacao', [
                'aguardando_validacao', 'em_analise', 'correcao_solicitada', 'reprovado',
            ]);
        }

        return ApiResponse::success(PessoaAdminResource::collection(
            $query->paginate($validated['per_page'] ?? 20)
        ));
    }

    public function show(Pessoa $pessoa)
    {
        $pessoa->load(['documentos' => fn ($query) => $query->latest(), 'historicoValidacao' => fn ($query) => $query->latest()]);

        return ApiResponse::success(new PessoaAdminResource($pessoa));
    }

    public function assumir(Request $request, Pessoa $pessoa, PessoaValidationService $service)
    {
        return ApiResponse::success(
            new PessoaAdminResource($service->assumir($pessoa, $request->user())),
            'Análise assumida com sucesso.'
        );
    }

    public function solicitarCorrecao(Request $request, Pessoa $pessoa, PessoaValidationService $service)
    {
        $validated = $request->validate(['motivo' => ['required', 'string', 'max:2000']]);

        return ApiResponse::success(
            new PessoaAdminResource($service->solicitarCorrecao($pessoa, $request->user(), $validated['motivo'])),
            'Correção solicitada com sucesso.'
        );
    }

    public function reprovar(Request $request, Pessoa $pessoa, PessoaValidationService $service)
    {
        $validated = $request->validate(['motivo' => ['required', 'string', 'max:2000']]);

        return ApiResponse::success(
            new PessoaAdminResource($service->reprovar($pessoa, $request->user(), $validated['motivo'])),
            'Validação reprovada.'
        );
    }

    public function aprovar(Request $request, Pessoa $pessoa, PessoaValidationService $service)
    {
        return ApiResponse::success(
            new PessoaAdminResource($service->aprovar($pessoa, $request->user())),
            'Pessoa aprovada com sucesso.'
        );
    }

    public function reprovarDefinitivamente(Request $request, Pessoa $pessoa, PessoaValidationService $service)
    {
        $validated = $request->validate(['motivo' => ['required', 'string', 'max:2000']]);

        return ApiResponse::success(
            new PessoaAdminResource($service->reprovarDefinitivamente($pessoa, $request->user(), $validated['motivo'])),
            'Pessoa reprovada definitivamente.'
        );
    }

    public function validarDocumento(Request $request, DocumentoPessoa $documento, PessoaValidationService $service)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['aprovado', 'rejeitado'])],
            'motivo' => [Rule::requiredIf($request->input('status') === 'rejeitado'), 'nullable', 'string', 'max:2000'],
        ]);

        return ApiResponse::success(
            new DocumentoPessoaResource($service->validarDocumento(
                $documento,
                $request->user(),
                $validated['status'],
                $validated['motivo'] ?? null
            )),
            'Documento analisado com sucesso.'
        );
    }
}
