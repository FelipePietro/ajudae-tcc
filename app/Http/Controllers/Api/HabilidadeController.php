<?php

namespace App\Http\Controllers\Api;

use App\Models\Habilidade;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HabilidadeController extends Controller
{
    public function index()
    {
        return Habilidade::all();
    }

    public function store(Request $request)
    {
        $Habilidade = Habilidade::create($request->all());

        return response()->json($Habilidade, 201);
    }

    public function show(string $id)
    {
        return Habilidade::findOrFail($id);
    }

    public function update(Request $request, string $id)
    {
        $Habilidade = Habilidade::findOrFail($id);

        $Habilidade->update($request->all());

        return $Habilidade;
    }

    public function destroy(string $id)
    {
        $Habilidade = Habilidade::findOrFail($id);

        $Habilidade->delete();

        return response()->json([
            'mensagem' => 'Removido com sucesso'
        ]);
    }
}