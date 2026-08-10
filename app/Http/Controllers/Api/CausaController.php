<?php

namespace App\Http\Controllers\Api;

use App\Models\Causa;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CausaController extends Controller
{
    public function index()
    {
        return Causa::all();
    }

    public function store(Request $request)
    {
        $causa = Causa::create($request->all());

        return response()->json($causa, 201);
    }

    public function show(string $id)
    {
        return Causa::findOrFail($id);
    }

    public function update(Request $request, string $id)
    {
        $causa = Causa::findOrFail($id);

        $causa->update($request->all());

        return $causa;
    }

    public function destroy(string $id)
    {
        $causa = Causa::findOrFail($id);

        $causa->delete();

        return response()->json([
            'mensagem' => 'Removido com sucesso'
        ]);
    }
}