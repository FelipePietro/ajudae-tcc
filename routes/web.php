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