<?php

namespace App\Http\Controllers;

use App\Models\CatEvento;
use Illuminate\Http\Request;

class CatEventoController extends Controller
{
    public function index()
    {
        return CatEvento::all();
    }

    public function store(Request $request)
    {
        $CatEvento = CatEvento::create($request->all());

        return response()->json($CatEvento, 201);
    }

    public function show(string $id)
    {
        return CatEvento::findOrFail($id);
    }

    public function update(Request $request, string $id)
    {
        $CatEvento = CatEvento::findOrFail($id);

        $CatEvento->update($request->all());

        return $CatEvento;
    }

    public function destroy(string $id)
    {
        $CatEvento = CatEvento::findOrFail($id);

        $CatEvento->delete();

        return response()->json([
            'mensagem' => 'Removido com sucesso'
        ]);
    }
}