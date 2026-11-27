<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'landing-page')->name('inicio')->block(config('identity.enabled') ? 10 : null);

Route::get('/termos', function () {
    $secoes = [
        ['id' => 'sobre', 'numero' => 1, 'titulo' => 'Sobre o Ajudae'],
        ['id' => 'usuarios', 'numero' => 2, 'titulo' => 'Tipos de usuários'],
        ['id' => 'idade', 'numero' => 3, 'titulo' => 'Requisito de idade'],
        ['id' => 'cadastro', 'numero' => 4, 'titulo' => 'Cadastro e informações'],
        ['id' => 'voluntariado', 'numero' => 5, 'titulo' => 'Serviço voluntário'],
        ['id' => 'eventos', 'numero' => 6, 'titulo' => 'Eventos e oportunidades'],
        ['id' => 'candidaturas', 'numero' => 7, 'titulo' => 'Candidaturas e participação'],
        ['id' => 'gamificacao', 'numero' => 8, 'titulo' => 'XP, níveis e certificados'],
        ['id' => 'conduta', 'numero' => 9, 'titulo' => 'Conduta e uso proibido'],
        ['id' => 'responsabilidades', 'numero' => 10, 'titulo' => 'Responsabilidades'],
        ['id' => 'privacidade', 'numero' => 11, 'titulo' => 'Privacidade e dados'],
        ['id' => 'aceite', 'numero' => 12, 'titulo' => 'Aceite eletrônico'],
        ['id' => 'contas', 'numero' => 13, 'titulo' => 'Suspensão e exclusão'],
        ['id' => 'alteracoes', 'numero' => 14, 'titulo' => 'Alterações destes Termos'],
        ['id' => 'contato', 'numero' => 15, 'titulo' => 'Contato'],
    ];

    return view('/legal/termos', [
        'secoes' => $secoes,
    ]);
})->name('termos')->block(config('identity.enabled') ? 10 : null);

/*
|--------------------------------------------------------------------------
| POLÍTICA DE PRIVACIDADE
|--------------------------------------------------------------------------
*/

Route::get('/privacidade', function () {

    $secoes = [
        ['id' => 'controlador', 'numero' => 1, 'titulo' => 'Responsável pelos dados'],
        ['id' => 'dados-coletados', 'numero' => 2, 'titulo' => 'Dados coletados'],
        ['id' => 'finalidades', 'numero' => 3, 'titulo' => 'Finalidades'],
        ['id' => 'bases-legais', 'numero' => 4, 'titulo' => 'Bases legais'],
        ['id' => 'compartilhamento', 'numero' => 5, 'titulo' => 'Compartilhamento'],
        ['id' => 'ongs-organizadores', 'numero' => 6, 'titulo' => 'ONGs e organizadores'],
        ['id' => 'maiores', 'numero' => 7, 'titulo' => 'Menores de idade'],
        ['id' => 'armazenamento', 'numero' => 8, 'titulo' => 'Armazenamento e segurança'],
        ['id' => 'retencao', 'numero' => 9, 'titulo' => 'Retenção e exclusão'],
        ['id' => 'direitos', 'numero' => 10, 'titulo' => 'Direitos dos titulares'],
        ['id' => 'solicitacoes', 'numero' => 11, 'titulo' => 'Exercício de direitos'],
        ['id' => 'cookies', 'numero' => 12, 'titulo' => 'Cookies'],
        ['id' => 'alteracoes', 'numero' => 13, 'titulo' => 'Alterações da política'],
        ['id' => 'contato', 'numero' => 14, 'titulo' => 'Contato'],
    ];

    return view('/legal/privacidade', [
        'secoes' => $secoes,
    ]);

})->name('privacidade')->block(config('identity.enabled') ? 10 : null);
