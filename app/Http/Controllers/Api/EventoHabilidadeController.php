<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Models\Habilidade;
use Illuminate\Http\Request;

class EventoHabilidadeController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTAR HABILIDADES DO EVENTO
    |--------------------------------------------------------------------------
    */

    public function index($id)
    {
        $evento = Evento::find($id);

        if (!$evento) {
            return response()->json([
                'message' => 'Evento não encontrado.'
            ], 404);
        }

        return response()->json([
            'data' => $evento->habilidades()->get()
        ], 200);
    }


    /*
    |--------------------------------------------------------------------------
    | ADICIONAR HABILIDADE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request, $id)
    {
        $evento = Evento::find($id);

        if (!$evento) {
            return response()->json([
                'message' => 'Evento não encontrado.'
            ], 404);
        }


        $validated = $request->validate([

            'habilidade_id' => [
                'required',
                'integer',
                'exists:habilidade,habilidade_id'
            ]

        ]);


        $jaExiste = $evento
            ->habilidades()
            ->where(
                'habilidade.habilidade_id',
                $validated['habilidade_id']
            )
            ->exists();


        if ($jaExiste) {

            return response()->json([
                'message' =>
                    'Esta habilidade já está associada ao evento.'
            ], 409);

        }


        $evento
            ->habilidades()
            ->attach(
                $validated['habilidade_id']
            );


        return response()->json([

            'message' =>
                'Habilidade adicionada ao evento com sucesso.',

            'data' =>
                $evento->habilidades()->get()

        ], 201);
    }


    /*
    |--------------------------------------------------------------------------
    | REMOVER HABILIDADE
    |--------------------------------------------------------------------------
    */

    public function destroy(Request $request, $id, $hid)
    {
        $evento = Evento::find($id);

        if (!$evento) {

            return response()->json([
                'message' => 'Evento não encontrado.'
            ], 404);

        }


        $habilidade = Habilidade::find($hid);

        if (!$habilidade) {

            return response()->json([
                'message' => 'Habilidade não encontrada.'
            ], 404);

        }


        $existe = $evento
            ->habilidades()
            ->where(
                'habilidade.habilidade_id',
                $hid
            )
            ->exists();


        if (!$existe) {

            return response()->json([
                'message' =>
                    'Esta habilidade não está associada ao evento.'
            ], 404);

        }


        $evento
            ->habilidades()
            ->detach($hid);


        return response()->noContent();
    }
}