<?php

use Illuminate\Support\Facades\Route;

Route::get('/teste', function () {
    return view('teste');
});

Route::get('/candidatura', function () {
    return view('candidatura', [
        'id_evento'          => 1,
        'nome_evento'        => 'Limpeza de Parques',
        'nome_ong'           => 'ONG Verde SP',
        'data_evento'        => '15 de maio de 2025',
        'hora_inicio_evento' => '08h',
        'hora_fim_evento'    => '16h',
        'endereco_evento'    => 'Horto Florestal, SP',
        'vagas_evento'       => 8,

        'nome_user'          => 'Lucas Pereira',
        'cidade_user'        => 'São Paulo',
        'uf_user'            => 'SP',
        'nivel_user'         => 3,
        'titulo_nivel'       => 'Aprendiz',
        'xp_user'            => 450,
        'badges_user'        => 3,
        'eventos_user'       => 8,
        'habilidades_user'   => ['Trabalho em equipe', 'Comunicação', 'Jardinagem'],
    ]);
});
