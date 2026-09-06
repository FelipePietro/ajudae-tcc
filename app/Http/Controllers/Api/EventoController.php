<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Models\Ong;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EventoController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTAR EVENTOS / FILTRAR POR CATEGORIA
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = Evento::with([
            'ong',
            'categoria'
        ]);

        if ($request->filled('categoria')) {
            $query->where(
                'cat_evento_id',
                $request->categoria
            );
        }

        $eventos = $query->get();

        return response()->json([
            'data' => $eventos
        ], 200);
    }


    /*
    |--------------------------------------------------------------------------
    | DETALHES DE UM EVENTO
    |--------------------------------------------------------------------------
    */

    public function show($id)
    {
        $evento = Evento::with([
            'ong',
            'categoria',
            'habilidades'
        ])->find($id);

        if (!$evento) {
            return response()->json([
                'message' => 'Evento não encontrado.'
            ], 404);
        }

        return response()->json([
            'data' => $evento
        ], 200);
    }


    /*
    |--------------------------------------------------------------------------
    | CRIAR EVENTO
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'logradouro_evento' => ['required', 'string', 'max:64'],
            'cep_evento' => ['required', 'string', 'size:8'],
            'cidade_evento' => ['required', 'string', 'max:64'],
            'bairro_evento' => ['required', 'string', 'max:64'],
            'uf_evento' => ['required', 'string', 'size:2'],

            'nm_evento' => ['required', 'string', 'max:64'],
            'descricao_evento' => ['required', 'string', 'max:256'],
            'imagem_evento_link' => ['required', 'string', 'max:255'],

            'cat_evento_id' => [
                'required',
                'integer',
                'exists:cat_evento,cat_evento_id'
            ],

            'compl_evento' => [
                'nullable',
                'string',
                'max:64'
            ],

            'vagas_evento' => [
                'required',
                'integer',
                'min:1'
            ],

            'modalidade_evento' => [
                'required',
                'in:presencial,online,hibrido'
            ],
        ]);

        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | ONG CRIANDO EVENTO
        |--------------------------------------------------------------------------
        */

        if ($user instanceof \App\Models\Ong) {
            $validated['ong_id'] = $user->ong_id;
            $validated['pessoa_id'] = null;
        }

        /*
        |--------------------------------------------------------------------------
        | ORGANIZADOR SOLO CRIANDO EVENTO
        |--------------------------------------------------------------------------
        */

        elseif ($user instanceof \App\Models\Pessoa) {

            if ($user->role_pessoa !== 'organizador') {
                return response()->json([
                    'message' => 'Apenas organizadores podem criar eventos.'
                ], 403);
            }

            $validated['pessoa_id'] = $user->pessoa_id;
            $validated['ong_id'] = null;
        }

        else {
            return response()->json([
                'message' => 'Usuário não autorizado.'
            ], 403);
        }

        $validated['status_evento'] = 'aguardando a confirmação';

        $evento = Evento::create($validated);

        return response()->json([
            'message' => 'Evento criado com sucesso.',
            'data' => $evento
        ], 201);
    }


    /*
    |--------------------------------------------------------------------------
    | ATUALIZAR EVENTO
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, $id)
    {
        $evento = Evento::find($id);

        if (!$evento) {
            return response()->json([
                'message' => 'Evento não encontrado.'
            ], 404);
        }

        /*
         * Impede ONG de alterar evento de outra ONG.
         */
        if ($request->user()->ong_id !== $evento->ong_id) {
            return response()->json([
                'message' =>
                    'Você não tem permissão para alterar este evento.'
            ], 403);
        }

        $validated = $request->validate([
            'logradouro_evento' => [
                'sometimes',
                'string',
                'max:64'
            ],

            'cep_evento' => [
                'sometimes',
                'string',
                'size:8'
            ],

            'cidade_evento' => [
                'sometimes',
                'string',
                'max:64'
            ],

            'bairro_evento' => [
                'sometimes',
                'string',
                'max:64'
            ],

            'uf_evento' => [
                'sometimes',
                'string',
                'size:2'
            ],

            'nm_evento' => [
                'sometimes',
                'string',
                'max:64'
            ],

            'descricao_evento' => [
                'sometimes',
                'string',
                'max:256'
            ],

            'imagem_evento_link' => [
                'sometimes',
                'string',
                'max:255'
            ],

            'cat_evento_id' => [
                'sometimes',
                'integer',
                'exists:cat_evento,cat_evento_id'
            ],

            'compl_evento' => [
                'sometimes',
                'nullable',
                'string',
                'max:64'
            ],

            'vagas_evento' => [
                'sometimes',
                'integer',
                'min:1'
            ],

            'modalidade_evento' => [
                'sometimes',
                'in:presencial,online,hibrido'
            ],
        ]);

        $evento->update($validated);

        return response()->json([
            'message' => 'Evento atualizado com sucesso.',
            'data' => $evento->fresh()
        ], 200);
    }


    /*
    |--------------------------------------------------------------------------
    | EXCLUIR EVENTO
    |--------------------------------------------------------------------------
    */

    public function destroy(Request $request, $id)
    {
        $evento = Evento::find($id);

        if (!$evento) {
            return response()->json([
                'message' => 'Evento não encontrado.'
            ], 404);
        }

        if ($request->user()->ong_id !== $evento->ong_id) {
            return response()->json([
                'message' =>
                    'Você não tem permissão para excluir este evento.'
            ], 403);
        }

        $evento->delete();

        return response()->noContent();
    }


    /*
    |--------------------------------------------------------------------------
    | ALTERAR STATUS — ADMIN
    |--------------------------------------------------------------------------
    */

    public function updateStatus(Request $request, $id)
    {
        $evento = Evento::find($id);

        if (!$evento) {
            return response()->json([
                'message' => 'Evento não encontrado.'
            ], 404);
        }

        $validated = $request->validate([
            'status_evento' => [
                'required',
                Rule::in([
                    'aguardando a confirmação',
                    'ativo',
                    'cancelado',
                    'finalizado',
                    'reprovado'
                ])
            ]
        ]);

        $evento->update([
            'status_evento' =>
                $validated['status_evento']
        ]);

        return response()->json([
            'message' =>
                'Status do evento atualizado com sucesso.',
            'data' => $evento
        ], 200);
    }


    /*
    |--------------------------------------------------------------------------
    | EVENTOS DE UMA ONG
    |--------------------------------------------------------------------------
    */

    public function porOng($id)
    {
        $ong = Ong::find($id);

        if (!$ong) {
            return response()->json([
                'message' => 'ONG não encontrada.'
            ], 404);
        }

        $eventos = Evento::with('categoria')
            ->where('ong_id', $id)
            ->get();

        return response()->json([
            'data' => $eventos
        ], 200);
    }
}