<?php

use Illuminate\Support\Facades\Route;

Route::get('/teste', function () {
    return view('teste');
});

// Rota de Admin

Route::get('/admin', function () {
    return view('admin');
});

// Rota de Candidatos

Route::get('/candidatos', function () {
    return view('candidatos');
});

// Rota de Dashboard

Route::get('/dashboard', function () {
    return view('dashboard');
});

// Rota de Fila de eventos

Route::get('/fila-eventos', function () {
    return view('fila-eventos');
});

// Rota de Cadastros ONG

Route::get('/cadastros-ong', function () {
    return view('cadastros-ong');
});

// Rota de Upgrades ORG

Route::get('/upgrades-org', function () {
    return view('upgrades-org');
});


// Rota de Denuncias

Route::get('/denuncias', function () {
    return view('denuncias');
});

// Rota de Solicitações LGPD

Route::get('/solicitacoes-lgpd', function () {
    return view('solicitacoes-lgpd');
});