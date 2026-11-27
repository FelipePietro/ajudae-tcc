<?php

use App\Http\Middleware\DemoSafety;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

if (config('identity.enabled')) {
    require __DIR__.'/identity.php';

    return;
}

if (! app()->environment('local', 'testing') || ! config('identity.demo')) {
    return;
}

// Frontend demonstrativo. BFF preservado, mas não registrado nem exigido aqui.
Route::middleware(DemoSafety::class)->group(function () {
    require __DIR__.'/demo.php';

    // Fallback sem JavaScript: ignora credenciais e navega pelos mocks.
    Route::post('/login', function (Request $request) {
        return redirect()->route($request->input('tipo_login') === 'ong' ? 'ong.painel' : 'feed');
    })->name('login.store');
    Route::get('/conta', fn () => redirect()->route('perfil'))->name('conta');
    Route::post('/perfil/editar', fn () => redirect()->route('perfil.editar')->with('demo_notice', 'Revisão concluída. Nenhum dado foi enviado.'))->name('perfil.demo.atualizar');
    Route::post('/perfil/senha', fn () => redirect()->route('perfil.editar')->with('demo_notice', 'Revisão concluída. Nenhuma senha foi alterada.'))->name('perfil.demo.senha');
    Route::post('/logout', fn () => redirect()->route('login')->with('demo_notice', 'Você saiu desta página.'))->name('logout');
});
