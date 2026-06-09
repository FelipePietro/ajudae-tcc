<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EventoController
{
      public function index()
    {
        return Evento::all();
    }

    public function store(Request $request)
    {
        $evento = Evento::create($request->all());

        return response()->json($evento, 201);
    }

    public function show(string $id)
    {
        return Evento::findOrFail($id);
    }

    public function update(Request $request, string $id)
    {
        $evento = Evento::findOrFail($id);

        $evento->update($request->all());

        return $evento;
    }

    public function destroy(string $id)
    {
        $evento = Evento::findOrFail($id);

        $evento->delete();

        return response()->json([
            'mensagem' => 'Removido com sucesso'
        ]);
    }
}
