<?php

namespace App\Http\Controllers;

use App\Models\Recurso;
use Illuminate\Http\Request;

class RecursoController extends Controller
{
    public function index()
    {
        return Recurso::all();
    }

    public function store(Request $request)
    {
        $Recurso = Recurso::create($request->all());

        return response()->json($Recurso, 201);
    }

    public function show(string $id)
    {
        return Recurso::findOrFail($id);
    }

    public function update(Request $request, string $id)
    {
        $Recurso = Recurso::findOrFail($id);

        $Recurso->update($request->all());

        return $Recurso;
    }

    public function destroy(string $id)
    {
        $Recurso = Recurso::findOrFail($id);

        $Recurso->delete();

        return response()->json([
            'mensagem' => 'Removido com sucesso'
        ]);
    }
}