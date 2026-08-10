<?php

namespace App\Http\Controllers\Api;

use App\Models\Agenda;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AgendaController extends Controller
{
    /**
     * Listar todas as agendas
     */
    public function index()
    {
        $agendas = Agenda::with('evento')->get();
        return response()->json($agendas);
    }

    /**
     * Exibir uma agenda específica
     */
    public function show($id)
    {
        $agenda = Agenda::with('evento')->find($id);
        
        if (!$agenda) {
            return response()->json(['message' => 'Agenda não encontrada'], 404);
        }

        return response()->json($agenda);
    }

    /**
     * Criar nova agenda
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'status_ativo' => 'required|in:ativo,finalizado',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after:data_inicio',
            'evento_id' => 'required|exists:evento,evento_id'
        ]);

        $agenda = Agenda::create($validated);

        return response()->json($agenda, 201);
    }

    /**
     * Atualizar agenda
     */
    public function update(Request $request, $id)
    {
        $agenda = Agenda::find($id);

        if (!$agenda) {
            return response()->json(['message' => 'Agenda não encontrada'], 404);
        }

        $validated = $request->validate([
            'status_ativo' => 'sometimes|in:ativo,finalizado',
            'data_inicio' => 'sometimes|date',
            'data_fim' => 'sometimes|date',
            'evento_id' => 'sometimes|exists:evento,evento_id'
        ]);

        $agenda->update($validated);

        return response()->json($agenda);
    }

    /**
     * Deletar agenda
     */
    public function destroy($id)
    {
        $agenda = Agenda::find($id);

        if (!$agenda) {
            return response()->json(['message' => 'Agenda não encontrada'], 404);
        }

        $agenda->delete();

        return response()->json(['message' => 'Agenda deletada com sucesso']);
    }

    /**
     * Ativar agenda (alterar status de desativado/finalizado para ativo)
     */
    public function ativar($id)
    {
        $agenda = Agenda::find($id);

        if (!$agenda) {
            return response()->json(['message' => 'Agenda não encontrada'], 404);
        }

        $agenda->ativar();

        return response()->json([
            'message' => 'Agenda ativada com sucesso',
            'agenda' => $agenda
        ]);
    }

    /**
     * Finalizar agenda (alterar status para finalizado)
     */
    public function finalizar($id)
    {
        $agenda = Agenda::find($id);

        if (!$agenda) {
            return response()->json(['message' => 'Agenda não encontrada'], 404);
        }

        $agenda->finalizar();

        return response()->json([
            'message' => 'Agenda finalizada com sucesso',
            'agenda' => $agenda
        ]);
    }
}
