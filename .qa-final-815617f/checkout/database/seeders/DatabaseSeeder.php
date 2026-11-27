<?php

namespace Database\Seeders;

use App\Models\CatEvento;
use App\Models\Permissao;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        CatEvento::firstOrCreate(['nome_categoria' => 'Assistência social']);
        CatEvento::firstOrCreate(['nome_categoria' => 'Educação']);
        CatEvento::firstOrCreate(['nome_categoria' => 'Meio ambiente']);

        foreach ([
            'usuarios.validar',
            'ongs.validar',
            'eventos.aprovar',
            'usuarios.moderar',
            'organizadores.validar',
            'xp.campanhas.gerenciar',
            'lgpd.visualizar',
            'lgpd.responder',
            'admin.logs.visualizar',
            'padrinhos.validar_conclusao',
        ] as $permissao) {
            Permissao::firstOrCreate(['nome' => $permissao]);
        }
    }
}
