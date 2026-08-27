<?php

use Illuminate\Support\Facades\Route;

Route::get('/teste', function () {
    return view('teste');
});

Route::get('/cadastro-ong', function () {
    return view('cadastro_ong');
});

Route::get('/painel-ong', function () {
    return view('painel_ong');
});

Route::get('/editar-ong', function () {
    return view('editar_ong');
});

Route::get('/perfil-ong', function () {
    return view('perfil_ong');
});