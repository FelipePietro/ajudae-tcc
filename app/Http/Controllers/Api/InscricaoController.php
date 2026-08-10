<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\Inscricao;
use App\Models\Pessoa;
use App\Models\Ong;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InscricaoController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INSCRIÇÃO
    |--------------------------------------------------------------------------
    */

    public function store(Request $request, $id)
    {
        $agenda = Agenda::find($id);

        if (!$agenda) {
            return response()->json([
                'message' => 'Agenda não encontrada.'
            ], 404);
        }

        if (!$agenda->isAtivo()) {
            return response()->json([
                'message' => 'Esta agenda não está disponível para inscrições.'
            ], 409);
        }

        if (now()->greaterThanOrEqualTo($agenda->data_inicio)) {
            return response()->json([
                'message' => 'Não é possível se inscrever em uma agenda que já começou.'
            ], 409);
        }

        $pessoa = $request->user();

        $jaInscrito = Inscricao::where('agenda_id', $agenda->agenda_id)
            ->where('pessoa_id', $pessoa->pessoa_id)
            ->exists();

        if ($jaInscrito) {
            return response()->json([
                'message' => 'Você já possui uma inscrição nesta agenda.'
            ], 409);
        }

        $inscricao = Inscricao::create([
            'agenda_id' => $agenda->agenda_id,
            'pessoa_id' => $pessoa->pessoa_id,
            'status_inscricao' => 'pendente',
            'dt_inscricao' => now(),
        ]);

        return response()->json([
            'message' => 'Inscrição realizada com sucesso e enviada para aprovação.',
            'data' => $inscricao
        ], 201);
    }


    /*
    |--------------------------------------------------------------------------
    | LISTAR INSCRITOS DE UMA AGENDA
    |--------------------------------------------------------------------------
    */

    public function index(Request $request, $id)
    {
        $agenda = Agenda::with('evento')->find($id);

        if (!$agenda) {
            return response()->json([
                'message' => 'Agenda não encontrada.'
            ], 404);
        }

        if (!$this->podeGerenciarAgenda($request->user(), $agenda)) {
            return response()->json([
                'message' => 'Você não tem permissão para visualizar os inscritos desta agenda.'
            ], 403);
        }

        $validated = $request->validate([
            'status' => [
                'sometimes',
                Rule::in([
                    'pendente',
                    'aprovado',
                    'reprovado',
                    'participando',
                    'participou',
                    'não participou'
                ])
            ]
        ]);

        $query = Inscricao::with('pessoa')
            ->where('agenda_id', $agenda->agenda_id);

        if (isset($validated['status'])) {
            $query->where(
                'status_inscricao',
                $validated['status']
            );
        }

        return response()->json([
            'data' => $query->get()
        ], 200);
    }


    /*
    |--------------------------------------------------------------------------
    | INSCRIÇÕES DE UMA PESSOA
    |--------------------------------------------------------------------------
    */

    public function porPessoa(Request $request, $id)
    {
        $pessoa = Pessoa::find($id);

        if (!$pessoa) {
            return response()->json([
                'message' => 'Pessoa não encontrada.'
            ], 404);
        }

        $user = $request->user();

        if (
            !($user instanceof Pessoa) ||
            (
                $user->pessoa_id !== $pessoa->pessoa_id &&
                $user->role_pessoa !== 'administrador'
            )
        ) {
            return response()->json([
                'message' => 'Você não tem permissão para visualizar estas inscrições.'
            ], 403);
        }

        $inscricoes = Inscricao::with([
            'agenda.evento'
        ])
            ->where('pessoa_id', $pessoa->pessoa_id)
            ->get();

        return response()->json([
            'data' => $inscricoes
        ], 200);
    }


    /*
    |--------------------------------------------------------------------------
    | ALTERAR STATUS
    |--------------------------------------------------------------------------
    */

    public function updateStatus(Request $request, $id)
    {
        $inscricao = Inscricao::with(
            'agenda.evento'
        )->find($id);

        if (!$inscricao) {
            return response()->json([
                'message' => 'Inscrição não encontrada.'
            ], 404);
        }

        if (!$this->podeGerenciarAgenda(
            $request->user(),
            $inscricao->agenda
        )) {
            return response()->json([
                'message' => 'Você não tem permissão para alterar esta inscrição.'
            ], 403);
        }

        $validated = $request->validate([
            'status_inscricao' => [
                'required',
                Rule::in([
                    'pendente',
                    'aprovado',
                    'reprovado',
                    'participando',
                    'participou',
                    'não participou'
                ])
            ]
        ]);

        $inscricao->update([
            'status_inscricao' =>
                $validated['status_inscricao']
        ]);

        return response()->json([
            'message' => 'Status da inscrição atualizado com sucesso.',
            'data' => $inscricao->fresh()
        ], 200);
    }


    /*
    |--------------------------------------------------------------------------
    | CANCELAR INSCRIÇÃO
    |--------------------------------------------------------------------------
    */

    public function destroy(Request $request, $id)
    {
        $inscricao = Inscricao::with('agenda')
            ->find($id);

        if (!$inscricao) {
            return response()->json([
                'message' => 'Inscrição não encontrada.'
            ], 404);
        }

        $user = $request->user();

        if (
            !($user instanceof Pessoa) ||
            $user->pessoa_id !== $inscricao->pessoa_id
        ) {
            return response()->json([
                'message' => 'Você não tem permissão para cancelar esta inscrição.'
            ], 403);
        }

        if (now()->greaterThanOrEqualTo(
            $inscricao->agenda->data_inicio
        )) {
            return response()->json([
                'message' => 'Não é possível cancelar uma inscrição após o início da agenda.'
            ], 409);
        }

        $inscricao->delete();

        return response()->noContent();
    }


    /*
    |--------------------------------------------------------------------------
    | VERIFICAR RESPONSÁVEL PELA AGENDA
    |--------------------------------------------------------------------------
    */

    private function podeGerenciarAgenda($user, Agenda $agenda): bool
    {
        $evento = $agenda->evento;

        if (!$evento) {
            return false;
        }

        /*
         * Evento pertencente a uma ONG
         */
        if ($user instanceof Ong) {
            return $evento->ong_id === $user->ong_id;
        }

        /*
         * Evento de organizador solo ou
         * organizador responsável por evento.
         */
        if ($user instanceof Pessoa) {
            return (
                $user->role_pessoa === 'organizador' &&
                $evento->pessoa_id === $user->pessoa_id
            );
        }

        return false;
    }
}