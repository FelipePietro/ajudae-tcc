<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| LANDING PAGE
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('/landing-page');
})->name('inicio');


/*
|--------------------------------------------------------------------------
| LOGIN
|--------------------------------------------------------------------------
*/

Route::get('/login', function () {
    return view('/login');
})->name('login');

/*
|--------------------------------------------------------------------------
| CANDIDATURAS
|--------------------------------------------------------------------------
*/

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

        'habilidades_user' => [
            'Trabalho em equipe',
            'Comunicação',
            'Jardinagem',
        ],
    ]);
})->name('candidatura.form');


Route::get('/minhas-candidaturas', function () {
    return view('minhas-candidaturas');
})->name('candidaturas');


Route::post('/candidatura/confirmar', function () {
    return redirect()
        ->route('candidaturas')
        ->with('success', 'Candidatura enviada com sucesso.');
})->name('candidatura.confirmar');


Route::post('/agendas/{id}/candidatar', function ($id) {
    return redirect()->route('candidatura.form');
})->name('eventos.candidatar');


/*
|--------------------------------------------------------------------------
| PERFIL DO USUÁRIO
|--------------------------------------------------------------------------
*/

Route::get('/perfil', function () {

    return view('perfil/index-perfil', [

        'id_user' => 1,

        'nome_user' => 'Lucas Pereira',

        'foto_user' => 'https://placehold.co/200x200',

        'cidade_user' => 'São Paulo',

        'uf_user' => 'SP',

        'tipo_user' => 'Voluntário',

        'bio_user' =>
            'Apaixonado por causas ambientais e educação. Busco contribuir com a comunidade e aprender com cada experiência. Acredito que pequenas ações coletivas geram transformações reais.',

        /*
        |--------------------------------------------------------------------------
        | Gamificação
        |--------------------------------------------------------------------------
        */

        'nivel_user' => 3,

        'titulo_nivel' => 'Aprendiz',

        'xp_user' => 1000,

        'xp_proximo_nivel' => 600,

        'titulo_proximo_nivel' => 'Colaborador',

        'badges_user' => 3,

        'eventos_user' => 8,

        'posicao_user' => 67,

        /*
        |--------------------------------------------------------------------------
        | Habilidades
        |--------------------------------------------------------------------------
        */

        'habilidades_user' => [

            [
                'id' => 1,
                'nome' => 'Trabalho em equipe',
                'nivel' => 4,
            ],

            [
                'id' => 2,
                'nome' => 'Comunicação',
                'nivel' => 3,
            ],

            [
                'id' => 3,
                'nome' => 'Jardinagem',
                'nivel' => 2,
            ],

            [
                'id' => 4,
                'nome' => 'Liderança',
                'nivel' => 3,
            ],

        ],

        /*
        |--------------------------------------------------------------------------
        | Recursos
        |--------------------------------------------------------------------------
        */

        'recursos_user' => [

            [
                'id' => 1,
                'nome' => 'Veículo',
                'detalhes' => 'Disponível aos finais de semana',
            ],

            [
                'id' => 2,
                'nome' => 'Notebook',
                'detalhes' => 'Notebook próprio',
            ],

        ],

        /*
        |--------------------------------------------------------------------------
        | Causas
        |--------------------------------------------------------------------------
        */

        'causas_user' => [

            [
                'id' => 1,
                'nome' => 'Meio Ambiente',
                'icone' => '🌱',
            ],

            [
                'id' => 2,
                'nome' => 'Educação',
                'icone' => '📚',
            ],

            [
                'id' => 3,
                'nome' => 'Saúde',
                'icone' => '❤️',
            ],

        ],

        /*
        |--------------------------------------------------------------------------
        | Ranking
        |--------------------------------------------------------------------------
        */

        'ranking' => [

            [
                'posicao' => 1,
                'nome' => 'Ana Costa',
                'xp' => 1200,
            ],

            [
                'posicao' => 2,
                'nome' => 'Bruno Melo',
                'xp' => 980,
            ],

            [
                'posicao' => 3,
                'nome' => 'Carlos F.',
                'xp' => 850,
            ],

        ],

        /*
        |--------------------------------------------------------------------------
        | Badges
        |--------------------------------------------------------------------------
        */

        'badges' => [

            [
                'id' => 1,
                'nome' => 'Primeiro Passo',
                'descricao' => 'Participou do primeiro evento.',
                'icone' => '🌱',
            ],

            [
                'id' => 2,
                'nome' => 'Voluntário Ativo',
                'descricao' => 'Participou de 5 eventos.',
                'icone' => '🏅',
            ],

            [
                'id' => 3,
                'nome' => 'Amigo da Natureza',
                'descricao' => 'Participou de eventos ambientais.',
                'icone' => '🌳',
            ],

        ],

        /*
        |--------------------------------------------------------------------------
        | Histórico
        |--------------------------------------------------------------------------
        */

        'historico' => [

            [
                'id' => 1,
                'nome_evento' => 'Limpeza no Parque',
                'data' => '05/04/2026',
                'status' => 'Concluído',
                'xp_recebido' => 100,
            ],

            [
                'id' => 2,
                'nome_evento' => 'Aulas de Reforço',
                'data' => '19/04/2026',
                'status' => 'Concluído',
                'xp_recebido' => 80,
            ],

            [
                'id' => 3,
                'nome_evento' => 'Campanha de Arrecadação',
                'data' => '10/05/2026',
                'status' => 'Concluído',
                'xp_recebido' => 120,
            ],

        ],

        /*
        |--------------------------------------------------------------------------
        | Certificados
        |--------------------------------------------------------------------------
        */

        'certificados' => [

            [
                'id' => 1,
                'nome_evento' => 'Limpeza no Parque',
                'data' => '05 abr. 2026',
                'url' => '#',
            ],

            [
                'id' => 2,
                'nome_evento' => 'Aulas de Reforço',
                'data' => '19 abr. 2026',
                'url' => '#',
            ],

        ],

    ]);

})->name('perfil');


/*
|--------------------------------------------------------------------------
| PREFERÊNCIAS DO USUÁRIO
|--------------------------------------------------------------------------
*/

Route::get('/perfil/preferencias', function () {

    $habilidadesPessoaResponse = [

        'data' => [

            [
                'habilidade_id' => 1,
                'nome_habilidade' => 'Trabalho em equipe',
                'descricao_habilidade' =>
                    'Capacidade de colaborar e atuar em conjunto com outras pessoas.',

                'pivot' => [
                    'pessoa_id' => 1,
                    'habilidade_id' => 1,
                    'nivel_habilidade' => 4,
                ],
            ],

            [
                'habilidade_id' => 2,
                'nome_habilidade' => 'Comunicação',
                'descricao_habilidade' =>
                    'Facilidade para transmitir ideias e dialogar com diferentes públicos.',

                'pivot' => [
                    'pessoa_id' => 1,
                    'habilidade_id' => 2,
                    'nivel_habilidade' => 3,
                ],
            ],

            [
                'habilidade_id' => 3,
                'nome_habilidade' => 'Jardinagem',
                'descricao_habilidade' =>
                    'Conhecimento em cuidados básicos com plantas e espaços verdes.',

                'pivot' => [
                    'pessoa_id' => 1,
                    'habilidade_id' => 3,
                    'nivel_habilidade' => 2,
                ],
            ],

        ],

    ];


    $habilidadesDisponiveisResponse = [

        [
            'habilidade_id' => 1,
            'nome_habilidade' => 'Trabalho em equipe',
            'descricao_habilidade' =>
                'Capacidade de colaborar e atuar em conjunto com outras pessoas.',
        ],

        [
            'habilidade_id' => 2,
            'nome_habilidade' => 'Comunicação',
            'descricao_habilidade' =>
                'Facilidade para transmitir ideias e dialogar com diferentes públicos.',
        ],

        [
            'habilidade_id' => 3,
            'nome_habilidade' => 'Jardinagem',
            'descricao_habilidade' =>
                'Conhecimento em cuidados básicos com plantas e espaços verdes.',
        ],

        [
            'habilidade_id' => 4,
            'nome_habilidade' => 'Primeiros socorros',
            'descricao_habilidade' =>
                'Conhecimentos básicos de atendimento em situações emergenciais.',
        ],

        [
            'habilidade_id' => 5,
            'nome_habilidade' => 'Liderança',
            'descricao_habilidade' =>
                'Capacidade de coordenar pessoas e atividades.',
        ],

    ];


    $recursosPessoaResponse = [

        'data' => [

            [
                'recurso_id' => 1,
                'nome_recurso' => 'Veículo',
                'descricao_recurso' =>
                    'Veículo próprio disponível para auxiliar atividades voluntárias.',

                'pivot' => [
                    'pessoa_id' => 1,
                    'recurso_id' => 1,
                    'detalhes_recurso' =>
                        'Carro disponível aos finais de semana.',
                ],
            ],

            [
                'recurso_id' => 2,
                'nome_recurso' => 'Notebook',
                'descricao_recurso' =>
                    'Computador portátil disponível para atividades.',

                'pivot' => [
                    'pessoa_id' => 1,
                    'recurso_id' => 2,
                    'detalhes_recurso' =>
                        'Notebook pessoal com acesso à internet.',
                ],
            ],

        ],

    ];


    $recursosDisponiveisResponse = [

        [
            'recurso_id' => 1,
            'nome_recurso' => 'Veículo',
            'descricao_recurso' =>
                'Veículo próprio disponível para auxiliar atividades voluntárias.',
        ],

        [
            'recurso_id' => 2,
            'nome_recurso' => 'Notebook',
            'descricao_recurso' =>
                'Computador portátil disponível para atividades.',
        ],

        [
            'recurso_id' => 3,
            'nome_recurso' => 'Ferramentas',
            'descricao_recurso' =>
                'Ferramentas manuais para manutenção e atividades externas.',
        ],

        [
            'recurso_id' => 4,
            'nome_recurso' => 'Câmera',
            'descricao_recurso' =>
                'Equipamento fotográfico para registro de ações.',
        ],

    ];


    $causasPessoaResponse = [

        'data' => [

            [
                'causa_id' => 1,
                'nome_causa' => 'Meio Ambiente',
                'descricao_causa' =>
                    'Ações relacionadas à preservação ambiental e sustentabilidade.',

                'pivot' => [
                    'pessoa_id' => 1,
                    'causa_id' => 1,
                ],
            ],

            [
                'causa_id' => 2,
                'nome_causa' => 'Educação',
                'descricao_causa' =>
                    'Projetos voltados ao ensino, reforço escolar e inclusão educacional.',

                'pivot' => [
                    'pessoa_id' => 1,
                    'causa_id' => 2,
                ],
            ],

        ],

    ];


    $causasDisponiveisResponse = [

        [
            'causa_id' => 1,
            'nome_causa' => 'Meio Ambiente',
            'descricao_causa' =>
                'Ações relacionadas à preservação ambiental e sustentabilidade.',
        ],

        [
            'causa_id' => 2,
            'nome_causa' => 'Educação',
            'descricao_causa' =>
                'Projetos voltados ao ensino, reforço escolar e inclusão educacional.',
        ],

        [
            'causa_id' => 3,
            'nome_causa' => 'Saúde',
            'descricao_causa' =>
                'Ações de apoio à saúde, bem-estar e qualidade de vida.',
        ],

        [
            'causa_id' => 4,
            'nome_causa' => 'Proteção Animal',
            'descricao_causa' =>
                'Ações de cuidado, acolhimento e proteção de animais.',
        ],

        [
            'causa_id' => 5,
            'nome_causa' => 'Assistência Social',
            'descricao_causa' =>
                'Apoio a pessoas e comunidades em situação de vulnerabilidade.',
        ],

    ];


    return view('perfil/preferencias', [

        'habilidades_pessoa' =>
            $habilidadesPessoaResponse,

        'habilidades_disponiveis' =>
            $habilidadesDisponiveisResponse,

        'recursos_pessoa' =>
            $recursosPessoaResponse,

        'recursos_disponiveis' =>
            $recursosDisponiveisResponse,

        'causas_pessoa' =>
            $causasPessoaResponse,

        'causas_disponiveis' =>
            $causasDisponiveisResponse,

    ]);

})->name('perfil.preferencias');


/*
|--------------------------------------------------------------------------
| EDITAR PERFIL
|--------------------------------------------------------------------------
*/

Route::get('/perfil/editar', function () {

    $pessoa = [

        'pessoa_id' => 1,

        'nm_pessoa' => 'Felipe Pietro',

        'genero_pessoa' => 'masculino',

        'cpf_pessoa' => '12345678900',

        'dt_nasc' => '2008-02-15',

        'email_pessoa' => 'felipe@email.com',

        'tele_pessoa' => '11987654321',

        'cep_pessoa' => '07123456',

        'logradouro_pessoa' => 'Rua das Flores',

        'compl_pessoa' => 'Casa 2',

        'cidade_pessoa' => 'Guarulhos',

        'bairro_pessoa' => 'Pimentas',

        'uf_pessoa' => 'SP',

        'role_pessoa' => 'voluntario',

        'xp_pessoa' => 1250,

        'avaliacao_pessoa' => '4.80',

        'rg_pessoa' => '123456789',

        'antecedentes_pessoa_link' => null,

        'cnh_pessoa_link' => null,

        'login_pessoa' => 'felipepietro',

        'pfp_pessoa_link' =>
            'https://placehold.co/400x400',

        'rg_pessoa_link' => null,

        'exclusao_pendente' => false,

        'deletar_em' => null,

        'created_at' =>
            '2026-03-12T15:30:00.000000Z',

        'updated_at' =>
            '2026-08-23T14:00:00.000000Z',

    ];


    return view('/perfil/editar-perfil', [
        'pessoa' => $pessoa,
    ]);

})->name('perfil.editar');


Route::get('/perfil/excluir', function () {

    return view('/perfil/excluir');

})->name('perfil.excluir');


Route::get('/perfil/exclusao-pendente', function () {

    $pessoa = [

        'pessoa_id' => 1,

        'nm_pessoa' => 'Felipe Pietro',

        'exclusao_pendente' => true,

        'deletar_em' =>
            now()
                ->addDays(6)
                ->toISOString(),

    ];


    return view('/perfil/exclusao-pendente', [
        'pessoa' => $pessoa,
    ]);

})->name('perfil.exclusao-pendente');


Route::get('/configuracoes', function () {

    return redirect()
        ->route('perfil.editar');

})->name('configuracoes');


/*
|--------------------------------------------------------------------------
| RANKING
|--------------------------------------------------------------------------
*/

Route::get('/ranking', function () {

    $usuario = [

        'nome' => 'Lucas Pereira',

        'nivel' => 3,

        'titulo_nivel' => 'Aprendiz',

        'xp' => 450,

        'xp_proximo_nivel' => 600,

        'percentual_xp' => 75,

        'posicao' => 42,

    ];


    $top3 = [

        [
            'nome' => 'Ana Costa',
            'iniciais' => 'AC',
            'cidade' => 'São Paulo, SP',
            'xp' => 1200,
        ],

        [
            'nome' => 'Bruno Melo',
            'iniciais' => 'BM',
            'cidade' => 'São Paulo, SP',
            'xp' => 980,
        ],

        [
            'nome' => 'Carlos F.',
            'iniciais' => 'CF',
            'cidade' => 'Campinas, SP',
            'xp' => 850,
        ],

    ];


    $ranking = [

        [
            'posicao' => 4,
            'nome' => 'Diana Rocha',
            'iniciais' => 'DR',
            'xp' => 820,
            'nivel' => 4,
            'badges' => 5,
        ],

        [
            'posicao' => 5,
            'nome' => 'Eduardo Lima',
            'iniciais' => 'EL',
            'xp' => 790,
            'nivel' => 4,
            'badges' => 4,
        ],

        [
            'posicao' => 6,
            'nome' => 'Fernanda Castro',
            'iniciais' => 'FC',
            'xp' => 750,
            'nivel' => 3,
            'badges' => 3,
        ],

        [
            'posicao' => 7,
            'nome' => 'Gabriel Neto',
            'iniciais' => 'GN',
            'xp' => 710,
            'nivel' => 3,
            'badges' => 4,
        ],

        [
            'posicao' => 8,
            'nome' => 'Helena Pires',
            'iniciais' => 'HP',
            'xp' => 670,
            'nivel' => 3,
            'badges' => 2,
        ],

        [
            'posicao' => 9,
            'nome' => 'Igor Santos',
            'iniciais' => 'IS',
            'xp' => 640,
            'nivel' => 3,
            'badges' => 4,
        ],

        [
            'posicao' => 10,
            'nome' => 'Juliana Costa',
            'iniciais' => 'JC',
            'xp' => 600,
            'nivel' => 3,
            'badges' => 2,
        ],

    ];


    return view('/ranking/index', [

        'usuario' => $usuario,

        'top3' => $top3,

        'ranking' => $ranking,

    ]);

})->name('ranking');


/*
|--------------------------------------------------------------------------
| NOTIFICAÇÕES
|--------------------------------------------------------------------------
*/

Route::get('/notificacoes', function () {

    $usuario = [

        'nivel' => 3,

        'titulo_nivel' => 'Aprendiz',

        'xp' => 450,

        'xp_proximo_nivel' => 600,

        'percentual_xp' => 75,

    ];


    $notificacoes = [

        [
            'grupo' => 'Hoje',

            'tipo' => 'candidatura',

            'lida' => false,

            'icone' => '✅',

            'cor_icone' => 'bg-[#dff4e7]',

            'titulo' => 'Candidatura aprovada!',

            'mensagem' =>
                'Você foi aprovado para o evento <strong>Limpeza de Parques — Cantareira</strong> organizado pela ONG Verde SP.',

            'tempo' => 'há 2 horas',

            'tag' => 'Candidatura',

            'cor_tag' =>
                'bg-[#dff4e7] text-[#315e44]',

            'acoes' => [

                [
                    'texto' => 'Ver evento →',

                    'href' => '/eventos/1',

                    'classe' =>
                        'bg-[#edf7f2] text-[#174b36]',
                ],

            ],

        ],

        [
            'grupo' => null,

            'tipo' => 'gamificacao',

            'lida' => false,

            'icone' => '🏅',

            'cor_icone' => 'bg-[#fff2d8]',

            'titulo' =>
                'Novo badge conquistado! 🎉',

            'mensagem' =>
                'Você conquistou o badge <strong>Plantador de Sementes</strong> e recebeu <strong>+80 XP</strong>.',

            'tempo' => 'há 5 horas',

            'tag' => 'Badge & XP',

            'cor_tag' =>
                'bg-[#fff2d8] text-[#b16d14]',

            'acoes' => [

                [
                    'texto' => 'Ver meu perfil →',

                    'href' => '/perfil',

                    'classe' =>
                        'bg-[#edf7f2] text-[#174b36]',
                ],

            ],

        ],

        [
            'grupo' => 'Ontem',

            'tipo' => 'sistema',

            'lida' => true,

            'icone' => '🏢',

            'cor_icone' => 'bg-[#e7eef7]',

            'titulo' =>
                'Convite de ONG recebido',

            'mensagem' =>
                'A <strong>ONG Verde SP</strong> convidou você para ser funcionário/organizador associado.',

            'tempo' => 'há 1 dia',

            'tag' => 'Convite',

            'cor_tag' =>
                'bg-[#e7eef7] text-[#456d9d]',

            'acoes' => [

                [
                    'texto' => 'Aceitar convite',
                    'href' => '#',
                    'classe' =>
                        'bg-[#174b36] text-white',
                ],

                [
                    'texto' => 'Recusar',
                    'href' => '#',
                    'classe' =>
                        'border border-[#d1cbc0] text-[#666158]',
                ],

            ],

        ],

        [
            'grupo' => 'Há 2 dias',

            'tipo' => 'candidatura',

            'lida' => true,

            'icone' => '❌',

            'cor_icone' => 'bg-red-50',

            'titulo' =>
                'Candidatura não aprovada',

            'mensagem' =>
                'Sua candidatura para <strong>Mutirão de Pintura</strong> não foi aprovada neste ciclo.',

            'tempo' => 'há 2 dias',

            'tag' => 'Candidatura recusada',

            'cor_tag' =>
                'bg-red-50 text-red-600',

            'acoes' => [

                [
                    'texto' =>
                        'Buscar outros eventos →',

                    'href' => '/feed',

                    'classe' =>
                        'bg-[#edf7f2] text-[#174b36]',
                ],

            ],

        ],

        [
            'grupo' => 'Há 3 dias',

            'tipo' => 'gamificacao',

            'lida' => true,

            'icone' => '✅',

            'cor_icone' => 'bg-[#dff4e7]',

            'titulo' =>
                'Presença confirmada — XP concedido!',

            'mensagem' =>
                'O organizador confirmou sua presença no evento <strong>Aulas de Reforço</strong>. Você recebeu <strong>+60 XP</strong>.',

            'tempo' => 'há 3 dias',

            'tag' => 'XP & Certificado',

            'cor_tag' =>
                'bg-[#dff4e7] text-[#315e44]',

            'acoes' => [

                [
                    'texto' =>
                        '📥 Baixar certificado',

                    'href' => '#',

                    'classe' =>
                        'border border-[#e3a62f] text-[#b16d14]',
                ],

            ],

        ],

    ];


    $naoLidas =
        collect($notificacoes)
            ->where('lida', false)
            ->count();


    return view('/notificacoes/index', [

        'usuario' => $usuario,

        'notificacoes' => $notificacoes,

        'naoLidas' => $naoLidas,

    ]);

})->name('notificacoes');


/*
|--------------------------------------------------------------------------
| FEED DE EVENTOS
|--------------------------------------------------------------------------
*/

Route::get('/feed', function () {

    $usuario = [

        'pessoa_id' => 1,

        'nome' => 'Lucas Pereira',

        'xp' => 450,

        'posicao_ranking' => 42,

    ];


    $categorias = [

        [
            'categoria_id' => 1,
            'nome_categoria' => 'Meio Ambiente',
        ],

        [
            'categoria_id' => 2,
            'nome_categoria' => 'Educação',
        ],

        [
            'categoria_id' => 3,
            'nome_categoria' => 'Saúde',
        ],

        [
            'categoria_id' => 4,
            'nome_categoria' => 'Culinária',
        ],

        [
            'categoria_id' => 5,
            'nome_categoria' => 'Assistência Social',
        ],

    ];


    $eventos = [

        [
            'evento_id' => 1,

            'nm_evento' =>
                'Limpeza de Parques — Parque da Cantareira',

            'imagem_evento_link' => null,

            'icone' => '🌿',

            'tipo_organizador' => 'ONG',

            'categoria' => [
                'categoria_id' => 1,
                'nome_categoria' => 'Meio Ambiente',
            ],

            'modalidade_evento' => 'presencial',

            'data_formatada' => '15 maio 2026',

            'data_inicio' => '2026-08-27 08:00:00',

            'data_fim' => '2026-08-27 16:00:00',

            'horario_formatado' => '08h–16h',

            'cidade_evento' => 'São Paulo',

            'uf_evento' => 'SP',

            'organizador_nome' =>
                'ONG Verde SP · Carlos F.',

            'vagas_evento' => 30,

            'confirmados' => 8,

            'vagas_disponiveis' => 22,

            'percentual_ocupacao' => 26,
        ],

        [
            'evento_id' => 2,

            'nm_evento' =>
                'Aulas de Reforço Escolar — Ensino Fundamental',

            'imagem_evento_link' => null,

            'icone' => '📚',

            'tipo_organizador' =>
                'Organizador ind.',

            'categoria' => [
                'categoria_id' => 2,
                'nome_categoria' => 'Educação',
            ],

            'data_formatada' =>
                '18 maio 2026',

            'data_inicio' =>
                '2026-08-27 08:00:00',

            'data_fim' =>
                '2026-08-27 16:00:00',

            'horario_formatado' =>
                '14h–17h',

            'modalidade_evento' =>
                'presencial',

            'cidade_evento' =>
                'Santo André',

            'uf_evento' => 'SP',

            'organizador_nome' =>
                'Org. Educar · Ana M.',

            'vagas_evento' => 30,

            'confirmados' => 15,

            'vagas_disponiveis' => 15,

            'percentual_ocupacao' => 50,
        ],

        [
            'evento_id' => 3,

            'nm_evento' =>
                'Campanha de Vacinação Comunitária',

            'imagem_evento_link' => null,

            'icone' => '💉',

            'tipo_organizador' => 'ONG',

            'categoria' => [
                'categoria_id' => 3,
                'nome_categoria' => 'Saúde',
            ],

            'data_formatada' =>
                '20 maio 2026',

            'modalidade_evento' =>
                'presencial',

            'data_inicio' =>
                '2026-08-27 08:00:00',

            'data_fim' =>
                '2026-08-27 16:00:00',

            'horario_formatado' =>
                '09h–13h',

            'cidade_evento' =>
                'Guarulhos',

            'uf_evento' => 'SP',

            'organizador_nome' =>
                'Cruz Vermelha BR',

            'vagas_evento' => 30,

            'confirmados' => 27,

            'vagas_disponiveis' => 3,

            'percentual_ocupacao' => 90,
        ],

        [
            'evento_id' => 4,

            'nm_evento' =>
                'Aula de Culinária Social — Receitas Econômicas',

            'imagem_evento_link' => null,

            'icone' => '🍳',

            'tipo_organizador' =>
                'Organizador ind.',

            'categoria' => [
                'categoria_id' => 4,
                'nome_categoria' => 'Culinária',
            ],

            'data_formatada' =>
                '22 maio 2026',

            'data_inicio' =>
                '2026-08-27 08:00:00',

            'data_fim' =>
                '2026-08-27 16:00:00',

            'horario_formatado' =>
                '10h–13h',

            'modalidade_evento' =>
                'presencial',

            'cidade_evento' =>
                'São Paulo',

            'uf_evento' => 'SP',

            'organizador_nome' =>
                'Org. Sabor Social · Beatriz K.',

            'vagas_evento' => 30,

            'confirmados' => 10,

            'vagas_disponiveis' => 20,

            'percentual_ocupacao' => 33,
        ],

        [
            'evento_id' => 5,

            'nm_evento' =>
                'Mutirão de Plantio de Árvores Nativas',

            'imagem_evento_link' => null,

            'icone' => '🌳',

            'tipo_organizador' => 'ONG',

            'categoria' => [
                'categoria_id' => 1,
                'nome_categoria' => 'Meio Ambiente',
            ],

            'data_formatada' =>
                '25 maio 2026',

            'data_inicio' =>
                '2026-08-27 08:00:00',

            'data_fim' =>
                '2026-08-27 16:00:00',

            'horario_formatado' =>
                '07h–12h',

            'modalidade_evento' =>
                'presencial',

            'cidade_evento' =>
                'Osasco',

            'uf_evento' => 'SP',

            'organizador_nome' =>
                'Instituto Raízes Vivas',

            'vagas_evento' => 30,

            'confirmados' => 18,

            'vagas_disponiveis' => 12,

            'percentual_ocupacao' => 60,
        ],

        [
            'evento_id' => 6,

            'nm_evento' =>
                'Doação de Roupas — Triagem e Separação',

            'imagem_evento_link' => null,

            'icone' => '👕',

            'tipo_organizador' => 'ONG',

            'categoria' => [
                'categoria_id' => 5,
                'nome_categoria' => 'Assistência Social',
            ],

            'data_formatada' =>
                '28 maio 2026',

            'horario_formatado' =>
                '09h–17h',

            'data_inicio' =>
                '2026-08-27 08:00:00',

            'data_fim' =>
                '2026-08-27 16:00:00',

            'cidade_evento' =>
                'Diadema',

            'modalidade_evento' =>
                'presencial',

            'uf_evento' => 'SP',

            'organizador_nome' =>
                'Instituto Esperança Social',

            'vagas_evento' => 30,

            'confirmados' => 2,

            'vagas_disponiveis' => 28,

            'percentual_ocupacao' => 7,
        ],

    ];


    $cidades =
        collect($eventos)
            ->pluck('cidade_evento')
            ->unique()
            ->values()
            ->all();


    $topRanking = [

        [
            'posicao' => 1,
            'nome' => 'Ana Costa',
            'iniciais' => 'AC',
            'xp' => 1200,
        ],

        [
            'posicao' => 2,
            'nome' => 'Bruno Melo',
            'iniciais' => 'BM',
            'xp' => 980,
        ],

        [
            'posicao' => 3,
            'nome' => 'Carlos F.',
            'iniciais' => 'CF',
            'xp' => 850,
        ],

    ];


    $badges = [

        [
            'nome' => 'Plantador',
            'icone' => '🌱',
            'conquistado' => true,
        ],

        [
            'nome' => 'Aliado',
            'icone' => '🤝',
            'conquistado' => true,
        ],

        [
            'nome' => 'Solidário',
            'icone' => '❤️',
            'conquistado' => true,
        ],

        [
            'nome' => 'Estrela',
            'icone' => '🌟',
            'conquistado' => false,
        ],

        [
            'nome' => 'Campeão',
            'icone' => '🏆',
            'conquistado' => false,
        ],

        [
            'nome' => 'Transformador',
            'icone' => '🦋',
            'conquistado' => false,
        ],

    ];


    $badgesConquistados =
        collect($badges)
            ->where('conquistado', true)
            ->count();


    return view('/feed/index', [

        'usuario' => $usuario,

        'categorias' => $categorias,

        'cidades' => $cidades,

        'eventos' => $eventos,

        'topRanking' => $topRanking,

        'badges' => $badges,

        'badgesConquistados' =>
            $badgesConquistados,

    ]);

})->name('feed');


/*
|--------------------------------------------------------------------------
| DETALHES DO EVENTO
|--------------------------------------------------------------------------
*/

Route::get('/eventos/{id}', function ($id) {

    $evento = (object) [

        'evento_id' => (int) $id,

        'nm_evento' =>
            'Limpeza de Parques — Parque Estadual da Cantareira',

        'descricao_evento' =>
            'Junte-se a nós para uma manhã inteira de limpeza e preservação
            do Parque Estadual da Cantareira. Vamos recolher lixo nas trilhas
            e margens da represa, remover plantas invasoras e plantar mudas
            nativas.

            Traremos luvas, sacos de lixo e ferramentas para toda a equipe.
            Você precisa apenas levar protetor solar, água e roupas
            confortáveis para atividade ao ar livre.

            Ao final, haverá um momento de confraternização com lanche
            coletivo.',

        'cep_evento' => '02377-000',

        'logradouro_evento' =>
            'Rua do Horto, 1488',

        'compl_evento' => null,

        'bairro_evento' =>
            'Horto Florestal',

        'cidade_evento' =>
            'São Paulo',

        'uf_evento' => 'SP',

        'vagas_evento' => 30,

        'modalidade_evento' =>
            'presencial',

        'status_evento' =>
            'publicado',

        'imagem_evento_link' => null,

        'categoria' => (object) [

            'cat_evento_id' => 1,

            'nome_categoria' =>
                'Meio Ambiente',

        ],

        'ong' => (object) [

            'ong_id' => 1,

            'nm_ong' =>
                'ONG Verde SP',

        ],

        'habilidades' => collect([

            (object) [
                'habilidade_id' => 1,
                'nome_habilidade' =>
                    'Trabalho em equipe',
            ],

            (object) [
                'habilidade_id' => 2,
                'nome_habilidade' =>
                    'Disposição física',
            ],

            (object) [
                'habilidade_id' => 3,
                'nome_habilidade' =>
                    'Consciência ambiental',
            ],

        ]),

    ];


    $agenda = (object) [

        'agenda_id' => 1,

        'data_inicio' =>
            \Carbon\Carbon::parse(
                '2026-09-15 08:00:00'
            ),

        'data_fim' =>
            \Carbon\Carbon::parse(
                '2026-09-15 16:00:00'
            ),

        'status_ativo' =>
            'ativo',

    ];


    $totalVagas =
        $evento->vagas_evento;

    $confirmados = 8;

    $vagasDisponiveis =
        max(
            0,
            $totalVagas - $confirmados
        );

    $percentualOcupado =
        $totalVagas > 0
            ? ($confirmados / $totalVagas) * 100
            : 0;


    return view('/eventos/detalhe', [

        'evento' => $evento,

        'agenda' => $agenda,

        'totalVagas' => $totalVagas,

        'confirmados' => $confirmados,

        'vagasDisponiveis' =>
            $vagasDisponiveis,

        'percentualOcupado' =>
            $percentualOcupado,

    ]);

})->name('eventos.show');


/*
|--------------------------------------------------------------------------
| ONG - CADASTRO
|--------------------------------------------------------------------------
*/

Route::get('/cadastro/ong', function () {

    return view('/ong/cadastro_ong');

})->name('ong.cadastrar.form');


Route::get('/api/ong/consultar-cnpj/{cnpj}', function (string $cnpj) {

    $cnpj =
        preg_replace(
            '/\D/',
            '',
            $cnpj
        );


    if (strlen($cnpj) !== 14) {

        return response()->json([
            'message' => 'CNPJ inválido',
        ], 422);

    }


    $dadosSimulados = [

        'razao_social' =>
            'Instituto Exemplo Cadastrado',

        'nome_fantasia' =>
            'Exemplo Cadastrado',

        'cidade' =>
            'São Paulo',

        'estado' =>
            'SP',

        'situacao' =>
            'ATIVA',

    ];


    if (
        $dadosSimulados['situacao']
        !== 'ATIVA'
    ) {

        return response()->json([
            'message' =>
                'CNPJ inativo ou com natureza jurídica incompatível',
        ], 422);

    }


    return response()->json(
        $dadosSimulados
    );

})->name('ong.consultar-cnpj');


Route::post('/ong/cadastrar', function (Request $request) {

    $request->validate([

        'cnpj' =>
            ['required', 'string', 'size:18'],

        'razao_social' =>
            ['required', 'string', 'max:255'],

        'nome_fantasia' =>
            ['required', 'string', 'max:255'],

        'cidade' =>
            ['required', 'string', 'max:120'],

        'estado' =>
            ['required', 'string', 'size:2'],

        'email' =>
            ['required', 'email', 'max:255'],

        'telefone' =>
            ['required', 'string', 'max:15'],

        'senha' =>
            ['required', 'string', 'min:8', 'confirmed'],

        'descricao' =>
            ['required', 'string', 'max:2000'],

        'logo' =>
            ['nullable', 'image', 'max:5120'],

        'aceite_termo_responsabilidade' =>
            ['accepted'],

        'aceite_privacidade' =>
            ['accepted'],

    ], [

        'aceite_termo_responsabilidade.accepted' =>
            'É necessário aceitar o Termo de Responsabilidade da ONG.',

        'aceite_privacidade.accepted' =>
            'É necessário concordar com a Política de Privacidade.',

    ]);


    return redirect()
        ->route('ong.aprovacao-pendente')
        ->with(
            'sucesso',
            'Cadastro enviado! Você receberá um e-mail assim que a análise for concluída.'
        );

})->name('ong.cadastrar');

Route::get('/ong/aprovacao-pendente', function () {

    $ong = [
        'ong_id' => 1,
        'nome_fantasia' => 'Mãos que Ajudam',
        'email' => 'contato@maosqueajudam.org.br',
        'status' => 'aguardando_aprovacao',
        'enviado_em' => now()->subHours(2),
    ];

    return view('/ong/aprovacao-pendente', [
        'ong' => $ong,
    ]);

})->name('ong.aprovacao-pendente');

/*
|--------------------------------------------------------------------------
| ONG - PAINEL
|--------------------------------------------------------------------------
*/

Route::get('/ong/dashboard', function () {

    return redirect()
        ->route('ong.painel');

})->name('ong.dashboard');


Route::get('/ong/painel', function () {

    $ong = [

        'icone' => '🌱',

        'nome' =>
            'Instituto Esperança Social',

        'nome_fantasia' =>
            'Esperança Social',

        'cidade' =>
            'São Paulo',

        'estado' => 'SP',

        'cnpj' =>
            '11.222.333/0001-81',

        'area_atuacao' =>
            'Assistência social · Educação',

        'ativa' => true,

        'verificada' => true,

        'site' =>
            'esperancasocial.org.br',

        'aprovado_em' =>
            '12 jan. 2024',

    ];


    $eventos = [

        [
            'icone' => '🌿',
            'nome' => 'Limpeza de Parques',
            'local' => 'Horto Florestal',
            'data' => '15 maio 2025',
            'responsavel' => 'Carlos F.',
            'cargo' => 'Organizador',
            'status' => 'Publicado',
            'status_class' => 'status-published',
            'candidatos' => '8 pend.',
        ],

        [
            'icone' => '👕',
            'nome' => 'Doação de Roupas',
            'local' => 'Brás, SP',
            'data' => '20 maio 2025',
            'responsavel' => 'Maria L.',
            'cargo' => 'Organizadora',
            'status' => 'Publicado',
            'status_class' => 'status-published',
            'candidatos' => '15 pend.',
        ],

        [
            'icone' => '💉',
            'nome' => 'Campanha de Vacinação',
            'local' => 'Centro, SP',
            'data' => '28 maio 2025',
            'responsavel' => null,
            'cargo' => null,
            'status' => 'Aguardando aprovação',
            'status_class' => 'status-waiting',
            'candidatos' => '0 pend.',
        ],

        [
            'icone' => '📚',
            'nome' => 'Aula de Reforço Escolar',
            'local' => 'Cidade Tiradentes',
            'data' => '05 jun 2025',
            'responsavel' => 'Carlos F.',
            'cargo' => 'Organizador',
            'status' => 'Publicado',
            'status_class' => 'status-published',
            'candidatos' => '4 pend.',
        ],

    ];


    $funcionarios = [

        [
            'iniciais' => 'CF',
            'cor' => 'green',
            'nome' => 'Carlos Ferreira',
            'meta' =>
                '2 eventos designados · Organizador principal',
            'status' => 'accepted',
        ],

        [
            'iniciais' => 'ML',
            'cor' => 'yellow',
            'nome' => 'Maria Lima',
            'meta' =>
                '1 evento designado',
            'status' => 'accepted',
        ],

        [
            'iniciais' => 'PS',
            'cor' => 'blue',
            'nome' => 'Pedro Santos',
            'meta' =>
                'Nenhum evento · convite aguardando resposta',
            'status' => 'pending',
        ],

    ];


    $atividades = [

        [
            'cor' => 'green',
            'texto' =>
                'Carlos aprovou candidatura de Ana Costa para <strong>Limpeza de Parques</strong>',
            'tempo' =>
                'hoje, 14:32',
        ],

        [
            'cor' => 'orange',
            'texto' =>
                'Evento <strong>Campanha de Vacinação</strong> enviado para aprovação',
            'tempo' =>
                'ontem, 09:15',
        ],

        [
            'cor' => 'blue',
            'texto' =>
                'Pedro Santos convidado para a equipe via e-mail',
            'tempo' =>
                'há 2 dias',
        ],

        [
            'cor' => 'green',
            'texto' =>
                '<strong>Doação de Roupas</strong> publicado — 15 candidaturas recebidas',
            'tempo' =>
                'há 3 dias',
        ],

    ];


    $stats = [

        'eventos_ativos' =>
            collect($eventos)
                ->where(
                    'status_class',
                    'status-published'
                )
                ->count(),

        'funcionarios' =>
            count($funcionarios),

        'total_voluntarios' => 128,

        'eventos_realizados' => 32,

        'horas_doadas' => '2.580',

    ];


    return view(
        '/ong/painel_ong',
        compact(
            'ong',
            'eventos',
            'funcionarios',
            'atividades',
            'stats'
        )
    );

})->name('ong.painel');


/*
|--------------------------------------------------------------------------
| ONG - MEUS EVENTOS
|--------------------------------------------------------------------------
*/

Route::get('/ong/eventos', function () {

    $eventos = [

        [
            'evento_id' => 1,

            'nm_evento' =>
                'Aula de Reforço Escolar',

            'descricao_resumida' =>
                'Aulas de reforço em matemática e português para estudantes do ensino fundamental na região de Cidade Tiradentes.',

            'data_inicio' =>
                '2026-09-12 14:00:00',

            'cidade_evento' =>
                'São Paulo',

            'bairro_evento' =>
                'Cidade Tiradentes',

            'status_evento' =>
                'publicado',

            'responsavel' => [
                'pessoa_id' => 1,
                'nome' => 'Carlos Ferreira',
                'iniciais' => 'CF',
            ],

            'candidaturas_pendentes' => 4,

            'candidaturas_aprovadas' => 6,
        ],

        [
            'evento_id' => 2,

            'nm_evento' =>
                'Mutirão de Adoção de Animais',

            'descricao_resumida' =>
                'Feira de adoção em parceria com o abrigo municipal. Voluntários vão ajudar na recepção dos adotantes e organização do espaço.',

            'data_inicio' =>
                '2026-09-25 09:00:00',

            'cidade_evento' =>
                'Guarulhos',

            'bairro_evento' =>
                'Parque Bonsucesso',

            'status_evento' =>
                'publicado',

            'responsavel' => [
                'pessoa_id' => 1,
                'nome' => 'Carlos Ferreira',
                'iniciais' => 'CF',
            ],

            'candidaturas_pendentes' => 9,

            'candidaturas_aprovadas' => 8,
        ],

        [
            'evento_id' => 3,

            'nm_evento' =>
                'Arrecadação de Agasalhos de Inverno',

            'descricao_resumida' =>
                'Ponto de coleta e triagem de roupas de inverno para famílias em situação de vulnerabilidade atendidas pela ONG.',

            'data_inicio' =>
                '2026-10-03 09:00:00',

            'cidade_evento' =>
                'São Paulo',

            'bairro_evento' =>
                'Zona Leste',

            'status_evento' =>
                'publicado',

            'responsavel' => [
                'pessoa_id' => 1,
                'nome' => 'Carlos Ferreira',
                'iniciais' => 'CF',
            ],

            'candidaturas_pendentes' => 6,

            'candidaturas_aprovadas' => 3,
        ],

        [
            'evento_id' => 4,

            'nm_evento' =>
                'Campanha de Vacinação Pet',

            'descricao_resumida' =>
                'Apoio à campanha de vacinação antirrábica em parceria com a UBS do Centro. Voluntários vão ajudar na organização das filas.',

            'data_inicio' =>
                '2026-10-18 09:00:00',

            'cidade_evento' =>
                'São Paulo',

            'bairro_evento' =>
                'Centro',

            'status_evento' =>
                'aguardando',

            'responsavel' => [
                'pessoa_id' => 1,
                'nome' => 'Carlos Ferreira',
                'iniciais' => 'CF',
            ],

            'candidaturas_pendentes' => 7,

            'candidaturas_aprovadas' => 3,
        ],

        [
            'evento_id' => 5,

            'nm_evento' =>
                'Limpeza da Praia de Santos',

            'descricao_resumida' =>
                'Manhã de limpeza e conservação da praia. Ainda não enviado para aprovação — continue de onde parou na página Criar evento.',

            'data_inicio' =>
                '2026-11-07 08:00:00',

            'cidade_evento' =>
                'Santos',

            'bairro_evento' =>
                'Av. Ana Costa',

            'status_evento' =>
                'rascunho',

            'responsavel' => [
                'pessoa_id' => 1,
                'nome' => 'Carlos Ferreira',
                'iniciais' => 'CF',
            ],

            'candidaturas_pendentes' => 0,

            'candidaturas_aprovadas' => 0,
        ],

    ];


    $resumo = [

        'ativos' =>
            collect($eventos)
                ->where(
                    'status_evento',
                    'publicado'
                )
                ->count(),

        'aguardando' =>
            collect($eventos)
                ->where(
                    'status_evento',
                    'aguardando'
                )
                ->count(),

        'candidaturas_pendentes' =>
            collect($eventos)
                ->sum(
                    'candidaturas_pendentes'
                ),

        'realizados' =>
            collect($eventos)
                ->where(
                    'status_evento',
                    'realizado'
                )
                ->count(),

    ];


    return view('/ong/meus-eventos', [

        'eventos' => $eventos,

        'resumo' => $resumo,

    ]);

})->name('ong.eventos.index');


/*
|--------------------------------------------------------------------------
| ONG - CANDIDATOS
|--------------------------------------------------------------------------
*/

Route::get('/ong/candidatos', function () {

    $candidatos = [
        [
            'id' => 1,
            'nome' => 'Ana Souza',
            'iniciais' => 'AS',
            'evento' => 'Limpeza de Parques',
            'data_candidatura' => 'Hoje, 10:32',
            'cidade' => 'Guarulhos',
            'xp' => 1250,
            'status' => 'pendente',
        ],
        [
            'id' => 2,
            'nome' => 'João Pedro',
            'iniciais' => 'JP',
            'evento' => 'Doação de Alimentos',
            'data_candidatura' => 'Ontem, 18:45',
            'cidade' => 'São Paulo',
            'xp' => 840,
            'status' => 'pendente',
        ],
        [
            'id' => 3,
            'nome' => 'Mariana Lima',
            'iniciais' => 'ML',
            'evento' => 'Campanha de Vacinação',
            'data_candidatura' => '04 set. 2026',
            'cidade' => 'Guarulhos',
            'xp' => 2130,
            'status' => 'aprovado',
        ],
    ];

    return view('/ong/candidatos', [
        'candidatos' => $candidatos,
    ]);

})->name('ong.candidatos');


/*
|--------------------------------------------------------------------------
| ONG - CRIAR EVENTO
|--------------------------------------------------------------------------
*/

Route::get('/ong/eventos/criar', function () {

    $categorias = collect([

        (object) [
            'cat_evento_id' => 1,
            'nome_categoria' =>
                'Meio Ambiente',
        ],

        (object) [
            'cat_evento_id' => 2,
            'nome_categoria' =>
                'Educação',
        ],

        (object) [
            'cat_evento_id' => 3,
            'nome_categoria' =>
                'Saúde',
        ],

        (object) [
            'cat_evento_id' => 4,
            'nome_categoria' =>
                'Assistência Social',
        ],

    ]);


    $habilidades = collect([

        (object) [
            'habilidade_id' => 1,
            'nome_habilidade' =>
                'Trabalho em equipe',
        ],

        (object) [
            'habilidade_id' => 2,
            'nome_habilidade' =>
                'Disposição física',
        ],

        (object) [
            'habilidade_id' => 3,
            'nome_habilidade' =>
                'Consciência ambiental',
        ],

        (object) [
            'habilidade_id' => 4,
            'nome_habilidade' =>
                'Liderança',
        ],

        (object) [
            'habilidade_id' => 5,
            'nome_habilidade' =>
                'Primeiros socorros',
        ],

        (object) [
            'habilidade_id' => 6,
            'nome_habilidade' =>
                'Jardinagem',
        ],

        (object) [
            'habilidade_id' => 7,
            'nome_habilidade' =>
                'Comunicação',
        ],

    ]);


    return view('/ong/criar', [

        'categorias' => $categorias,

        'habilidades' => $habilidades,

    ]);

})->name('ong.eventos.criar');


Route::post('/ong/eventos', function () {

    return redirect()
        ->route('ong.eventos.criar')
        ->with(
            'success',
            'Evento enviado para aprovação com sucesso.'
        );

})->name('ong.eventos.store');


/*
|--------------------------------------------------------------------------
| ONG - EDITAR EVENTO
|--------------------------------------------------------------------------
*/

Route::get('/ong/eventos/{id}/editar', function ($id) {

    $evento = (object) [

        'evento_id' =>
            (int) $id,

        'nm_evento' =>
            'Limpeza de Parques — Parque Estadual da Cantareira',

        'descricao_evento' =>
            'Junte-se a nós para uma manhã inteira de limpeza e preservação do parque.',

        'cep_evento' =>
            '02377000',

        'logradouro_evento' =>
            'Rua do Horto, 1488',

        'compl_evento' =>
            'Portão 2',

        'vagas_evento' =>
            30,

        'modalidade_evento' =>
            'presencial',

        'status_evento' =>
            'publicado',

        'cat_evento_id' =>
            1,

        'imagem_evento_link' =>
            null,

        'habilidades' => collect([

            (object) [
                'habilidade_id' => 1,
                'nome_habilidade' =>
                    'Trabalho em equipe',
            ],

            (object) [
                'habilidade_id' => 3,
                'nome_habilidade' =>
                    'Consciência ambiental',
            ],

        ]),

    ];


    $agenda = (object) [

        'agenda_id' => 1,

        'data_inicio' =>
            \Carbon\Carbon::parse(
                '2026-09-15 08:00:00'
            ),

        'data_fim' =>
            \Carbon\Carbon::parse(
                '2026-09-15 16:00:00'
            ),

    ];


    $categorias = collect([

        (object) [
            'cat_evento_id' => 1,
            'nome_categoria' =>
                'Meio Ambiente',
        ],

        (object) [
            'cat_evento_id' => 2,
            'nome_categoria' =>
                'Educação',
        ],

        (object) [
            'cat_evento_id' => 3,
            'nome_categoria' =>
                'Saúde',
        ],

        (object) [
            'cat_evento_id' => 4,
            'nome_categoria' =>
                'Assistência Social',
        ],

    ]);


    $habilidades = collect([

        (object) [
            'habilidade_id' => 1,
            'nome_habilidade' =>
                'Trabalho em equipe',
        ],

        (object) [
            'habilidade_id' => 2,
            'nome_habilidade' =>
                'Disposição física',
        ],

        (object) [
            'habilidade_id' => 3,
            'nome_habilidade' =>
                'Consciência ambiental',
        ],

        (object) [
            'habilidade_id' => 4,
            'nome_habilidade' =>
                'Liderança',
        ],

        (object) [
            'habilidade_id' => 5,
            'nome_habilidade' =>
                'Primeiros socorros',
        ],

    ]);


    return view('/ong/editar', [

        'evento' => $evento,

        'agenda' => $agenda,

        'categorias' => $categorias,

        'habilidades' => $habilidades,

    ]);

})->name('ong.eventos.editar');


Route::put('/ong/eventos/{id}', function ($id) {

    return redirect()
        ->route(
            'ong.eventos.editar',
            $id
        )
        ->with(
            'success',
            'Evento atualizado com sucesso.'
        );

})->name('ong.eventos.atualizar');


/*
|--------------------------------------------------------------------------
| ONG - PERFIL
|--------------------------------------------------------------------------
*/

Route::get('/ong/perfil', function () {

    $usuario = [

        'iniciais' => 'CF',

        'nome' => 'Carlos Ferreira',

        'cidade' => 'São Paulo',

        'estado' => 'SP',

        'tipo' =>
            'Organizador independente',

        'membro_desde' =>
            'jan. 2024',

        'verificado' => true,

        'papel' =>
            'Organizador',

        'areas' => [
            'Meio Ambiente',
            'Educação',
        ],

        'email' =>
            'carlos@email.com',

        'telefone' =>
            '(11) 99999-0000',

    ];


    $eventosCriados = [

        [
            'icone' => '🌿',

            'nome' =>
                'Mutirão de Plantio — Instituto Verde',

            'local' =>
                'Parque da Cidade, São Paulo - SP',

            'data' =>
                '12 de setembro de 2026',

            'vagas' =>
                '15 vagas restantes',
        ],

    ];


    $stats = [

        'eventos_organizados' =>
            count($eventosCriados) + 13,

        'voluntarios_gerenciados' =>
            87,

        'badges' => 8,

    ];


    return view(
        '/ong/perfil_ong',
        compact(
            'usuario',
            'eventosCriados',
            'stats'
        )
    );

})->name('ong.perfil');


Route::get('/perfil-ong', function () {

    return redirect()
        ->route('ong.perfil');

});


/*
|--------------------------------------------------------------------------
| ONG - EDITAR PERFIL
|--------------------------------------------------------------------------
*/

Route::get('/ong/perfil/editar', function () {

    $ong = [

        'id' => 4821,

        'cnpj' =>
            '12.345.678/0001-90',

        'razao_social' =>
            'Instituto Mãos que Ajudam',

        'nome_fantasia' =>
            'Mãos que Ajudam',

        'cidade' =>
            'Guarulhos',

        'estado' => 'SP',

        'email' =>
            'contato@maosqueajudam.org.br',

        'telefone' =>
            '(11) 3000-4521',

        'descricao' =>
            'Atuamos há 8 anos apoiando famílias em situação de vulnerabilidade na região de Guarulhos, com foco em segurança alimentar, reforço escolar e capacitação profissional para jovens e adultos.',

        'logo_url' =>
            'https://ajudae.org/logo-atual.png',

        'verificada' => true,

        'ativa_desde' =>
            '12/03/2024',

    ];


    return view(
        '/ong/editar_ong',
        compact('ong')
    );

})->name('ong.perfil.editar');


Route::put('/ong/perfil/{id}', function (
    Request $request,
    $id
) {

    $request->validate([

        'razao_social' =>
            ['required', 'string', 'max:255'],

        'nome_fantasia' =>
            ['required', 'string', 'max:255'],

        'cidade' =>
            ['required', 'string', 'max:120'],

        'estado' =>
            ['required', 'string', 'size:2'],

        'email' =>
            ['required', 'email', 'max:255'],

        'telefone' =>
            ['nullable', 'string', 'max:20'],

        'descricao' =>
            ['required', 'string', 'max:2000'],

        'senha' =>
            ['nullable', 'string', 'min:8', 'confirmed'],

        'logo' =>
            ['nullable', 'image', 'max:5120'],

        'remover_logo' =>
            ['nullable', 'boolean'],

    ]);


    return redirect()
        ->route('ong.perfil.editar')
        ->with(
            'sucesso',
            'Perfil da ONG atualizado com sucesso.'
        );

})->name('ong.perfil.atualizar');


/*
|--------------------------------------------------------------------------
| ALIASES DAS ROTAS ANTIGAS DA ONG
|--------------------------------------------------------------------------
|
| Mantém os links existentes das telas antigas funcionando enquanto
| padronizamos tudo em /ong/...
|
*/

Route::get('/painel-ong', function () {

    return redirect()
        ->route('ong.painel');

});


Route::get('/editar-ong', function () {

    return redirect()
        ->route('ong.perfil.editar');

});


Route::get('/eventos', function () {

    return redirect()
        ->route('ong.eventos.index');

});


/*
|--------------------------------------------------------------------------
| ONG - NOTIFICAÇÕES / CONFIGURAÇÕES
|--------------------------------------------------------------------------
*/

Route::get('/ong/notificacoes', function () {

    $notificacoes = [

        // =========================================================
        // HOJE
        // =========================================================

        [
            'grupo' => 'Hoje',
            'tipo' => 'candidatura',
            'lida' => false,

            'icone' => '👤',
            'cor_icone' => 'bg-[#e1eee5] text-[#174b36]',

            'titulo' => 'Nova candidatura recebida',

            'mensagem' =>
                '<strong>Ana Souza</strong> se candidatou ao evento
                <strong>Limpeza de Parques</strong>.',

            'tempo' => 'Há 12 minutos',

            'tag' => 'Candidatura',
            'cor_tag' => 'bg-[#e1eee5] text-[#174b36]',

            'acoes' => [
                [
                    'texto' => 'Ver candidatura',
                    'href' => route('ong.candidatos'),
                    'classe' =>
                        'bg-[#17392a] text-white hover:bg-[#214b38]',
                ],
                [
                    'texto' => 'Ver evento',
                    'href' => route('ong.eventos.index'),
                    'classe' =>
                        'border border-[#d5d1c8] bg-white text-[#292820] hover:bg-[#f7f5ef]',
                ],
            ],
        ],

        [
            'grupo' => null,
            'tipo' => 'candidatura',
            'lida' => false,

            'icone' => '👥',
            'cor_icone' => 'bg-[#e8edf7] text-[#39517c]',

            'titulo' => 'Novos candidatos aguardando análise',

            'mensagem' =>
                'O evento <strong>Aula de Reforço Escolar</strong>
                recebeu <strong>5 novas candidaturas</strong>.',

            'tempo' => 'Há 1 hora',

            'tag' => 'Candidaturas',
            'cor_tag' => 'bg-[#e8edf7] text-[#39517c]',

            'acoes' => [
                [
                    'texto' => 'Analisar candidatos',
                    'href' => route('ong.candidatos'),
                    'classe' =>
                        'bg-[#17392a] text-white hover:bg-[#214b38]',
                ],
            ],
        ],

        [
            'grupo' => null,
            'tipo' => 'alerta',
            'lida' => false,

            'icone' => '⚠️',
            'cor_icone' => 'bg-[#fff1df] text-[#9a6517]',

            'titulo' => 'Voluntário cancelou a participação',

            'mensagem' =>
                '<strong>João Pedro</strong> informou que não poderá
                participar do evento
                <strong>Mutirão de Adoção de Animais</strong>.',

            'tempo' => 'Há 2 horas',

            'tag' => 'Cancelamento',
            'cor_tag' => 'bg-[#fff1df] text-[#9a6517]',

            'acoes' => [
                [
                    'texto' => 'Ver participantes',
                    'href' => route('ong.candidatos'),
                    'classe' =>
                        'border border-[#d5d1c8] bg-white text-[#292820] hover:bg-[#f7f5ef]',
                ],
            ],
        ],

        [
            'grupo' => null,
            'tipo' => 'evento',
            'lida' => false,

            'icone' => '📅',
            'cor_icone' => 'bg-[#e8edf7] text-[#39517c]',

            'titulo' => 'Evento acontece amanhã',

            'mensagem' =>
                'O evento <strong>Limpeza de Parques</strong>
                começa amanhã às <strong>08:00</strong>.
                Você possui <strong>18 voluntários aprovados</strong>.',

            'tempo' => 'Há 4 horas',

            'tag' => 'Evento próximo',
            'cor_tag' => 'bg-[#e8edf7] text-[#39517c]',

            'acoes' => [
                [
                    'texto' => 'Ver evento',
                    'href' => route('ong.eventos.index'),
                    'classe' =>
                        'bg-[#17392a] text-white hover:bg-[#214b38]',
                ],
            ],
        ],


        // =========================================================
        // ONTEM
        // =========================================================

        [
            'grupo' => 'Ontem',
            'tipo' => 'evento',
            'lida' => true,

            'icone' => '✅',
            'cor_icone' => 'bg-[#e1eee5] text-[#174b36]',

            'titulo' => 'Confirme a presença dos voluntários',

            'mensagem' =>
                'O evento <strong>Campanha de Arrecadação</strong>
                terminou. Confirme quais voluntários realmente
                participaram para liberar <strong>XP e certificados</strong>.',

            'tempo' => 'Ontem, 18:32',

            'tag' => 'Presença',
            'cor_tag' => 'bg-[#e1eee5] text-[#174b36]',

            'acoes' => [
                [
                    'texto' => 'Confirmar presença',
                    'href' => route('ong.candidatos'),
                    'classe' =>
                        'bg-[#17392a] text-white hover:bg-[#214b38]',
                ],
            ],
        ],

        [
            'grupo' => null,
            'tipo' => 'alerta',
            'lida' => true,

            'icone' => '📉',
            'cor_icone' => 'bg-[#fff1df] text-[#9a6517]',

            'titulo' => 'Poucas vagas preenchidas',

            'mensagem' =>
                'O evento <strong>Doação de Alimentos</strong>
                acontece em 3 dias e possui apenas
                <strong>4 das 20 vagas</strong> preenchidas.',

            'tempo' => 'Ontem, 15:10',

            'tag' => 'Atenção',
            'cor_tag' => 'bg-[#fff1df] text-[#9a6517]',

            'acoes' => [
                [
                    'texto' => 'Ver evento',
                    'href' => route('ong.eventos.index'),
                    'classe' =>
                        'border border-[#d5d1c8] bg-white text-[#292820] hover:bg-[#f7f5ef]',
                ],
            ],
        ],


        // =========================================================
        // ESTA SEMANA
        // =========================================================

        [
            'grupo' => 'Esta semana',
            'tipo' => 'sistema',
            'lida' => true,

            'icone' => '🛡️',
            'cor_icone' => 'bg-[#eee8f7] text-[#644b7d]',

            'titulo' => 'Perfil da ONG aprovado',

            'mensagem' =>
                'O cadastro do <strong>Instituto Esperança</strong>
                foi analisado e aprovado pela equipe Ajudaê.
                Sua organização já pode publicar eventos.',

            'tempo' => '2 dias atrás',

            'tag' => 'Sistema',
            'cor_tag' => 'bg-[#eee8f7] text-[#644b7d]',

            'acoes' => [
                [
                    'texto' => 'Ver perfil',
                    'href' => route('ong.perfil'),
                    'classe' =>
                        'border border-[#d5d1c8] bg-white text-[#292820] hover:bg-[#f7f5ef]',
                ],
            ],
        ],

        [
            'grupo' => null,
            'tipo' => 'evento',
            'lida' => true,

            'icone' => '🎉',
            'cor_icone' => 'bg-[#e1eee5] text-[#174b36]',

            'titulo' => 'Evento concluído com sucesso',

            'mensagem' =>
                'O evento <strong>Plantio Comunitário</strong>
                foi concluído com <strong>24 voluntários presentes</strong>.
                Obrigado por mobilizar a comunidade!',

            'tempo' => '3 dias atrás',

            'tag' => 'Concluído',
            'cor_tag' => 'bg-[#e1eee5] text-[#174b36]',

            'acoes' => [
                [
                    'texto' => 'Ver meus eventos',
                    'href' => route('ong.eventos.index'),
                    'classe' =>
                        'border border-[#d5d1c8] bg-white text-[#292820] hover:bg-[#f7f5ef]',
                ],
            ],
        ],

        [
            'grupo' => null,
            'tipo' => 'sistema',
            'lida' => true,

            'icone' => '📢',
            'cor_icone' => 'bg-[#eee8f7] text-[#644b7d]',

            'titulo' => 'Novidade no Ajudaê',

            'mensagem' =>
                'Agora as ONGs podem acompanhar candidaturas,
                aprovar voluntários e confirmar presenças diretamente
                pelo painel.',

            'tempo' => '5 dias atrás',

            'tag' => 'Ajudaê',
            'cor_tag' => 'bg-[#eee8f7] text-[#644b7d]',

            'acoes' => [],
        ],
    ];


    // Quantidade exibida no topo e na navbar
    $naoLidas = collect($notificacoes)
        ->where('lida', false)
        ->count();


    return view('/notificacoes/index-ong', [
        'notificacoes' => $notificacoes,
        'naoLidas' => $naoLidas,
    ]);

})->name('ong.notificacoes');

Route::get('/ong/configuracoes', function () {

    return redirect()
        ->route('ong.perfil.editar');

})->name('ong.configuracoes');


// ==============================
// ROTAS DO PAINEL ADMINISTRATIVO
// ==============================

Route::prefix('admin')->name('admin.')->group(function () {

    // Painel principal
    Route::get('/', function () {
        return view('/admin/admin');
    })->name('admin');

    // Candidatos
    Route::get('/candidatos', function () {
        return view('/admin/candidatos');
    })->name('candidatos');

    // Fila de eventos
    Route::get('/fila-eventos', function () {
        return view('/admin/fila-eventos');
    })->name('fila-eventos');

    // Cadastros de ONG
    Route::get('/cadastros-ong', function () {
        return view('/admin/cadastros-ong');
    })->name('cadastros-ong');

    // Solicitações de upgrade para organizador
    Route::get('/upgrades-org', function () {
        return view('/admin/upgrades-org');
    })->name('upgrades-org');

    // Denúncias
    Route::get('/denuncias', function () {
        return view('/admin/denuncias');
    })->name('denuncias');

    // Solicitações LGPD
    Route::get('/solicitacoes-lgpd', function () {
        return view('/admin/solicitacoes-lgpd');
    })->name('solicitacoes-lgpd');

    // Usuários
    Route::get('/usuarios', function () {
        return view('/admin/usuarios');
    })->name('usuarios');

    // Relatórios
    Route::get('/relatorios', function () {
        return view('/admin/relatorios');
    })->name('relatorios');

    // Log de ações
    Route::get('/log-acoes', function () {
        return view('/admin/log-acoes');
    })->name('log-acoes');

    // Configurações
    Route::get('/configuracoes', function () {
        return view('/admin/configuracoes');
    })->name('configuracoes');

});

Route::get('/cadastro', function () {

    $cadastroMock = [
        'nm_pessoa' => 'Lucas Pereira de Souza',
        'email_pessoa' => 'lucas@email.com',
        'login_pessoa' => 'lucaspereira',
    ];

    $habilidades = [
        ['habilidade_id' => 1, 'nome_habilidade' => 'Comunicação', 'descricao_habilidade' => 'Contato com público, orientação e acolhimento.'],
        ['habilidade_id' => 2, 'nome_habilidade' => 'Tecnologia', 'descricao_habilidade' => 'Informática, sistemas, suporte e ferramentas digitais.'],
        ['habilidade_id' => 3, 'nome_habilidade' => 'Organização', 'descricao_habilidade' => 'Planejamento, logística e apoio operacional.'],
        ['habilidade_id' => 4, 'nome_habilidade' => 'Ensino', 'descricao_habilidade' => 'Reforço escolar, oficinas e compartilhamento de conhecimento.'],
        ['habilidade_id' => 5, 'nome_habilidade' => 'Fotografia e mídia', 'descricao_habilidade' => 'Fotos, vídeos e cobertura de atividades.'],
        ['habilidade_id' => 6, 'nome_habilidade' => 'Primeiros socorros', 'descricao_habilidade' => 'Conhecimentos básicos de atendimento e segurança.'],
    ];

    $causas = [
        ['causa_id' => 1, 'nome_causa' => 'Meio Ambiente', 'descricao_causa' => 'Preservação e sustentabilidade.'],
        ['causa_id' => 2, 'nome_causa' => 'Educação', 'descricao_causa' => 'Ensino, reforço e inclusão educacional.'],
        ['causa_id' => 3, 'nome_causa' => 'Saúde', 'descricao_causa' => 'Bem-estar, prevenção e qualidade de vida.'],
        ['causa_id' => 4, 'nome_causa' => 'Proteção Animal', 'descricao_causa' => 'Cuidado, acolhimento e proteção de animais.'],
        ['causa_id' => 5, 'nome_causa' => 'Assistência Social', 'descricao_causa' => 'Apoio a comunidades em vulnerabilidade.'],
        ['causa_id' => 6, 'nome_causa' => 'Cultura', 'descricao_causa' => 'Arte, cultura e valorização comunitária.'],
    ];

    $recursos = [
        ['recurso_id' => 1, 'nome_recurso' => 'Veículo próprio', 'descricao_recurso' => 'Apoio em transporte ou logística.'],
        ['recurso_id' => 2, 'nome_recurso' => 'Notebook', 'descricao_recurso' => 'Equipamento para atividades digitais.'],
        ['recurso_id' => 3, 'nome_recurso' => 'Equipamento fotográfico', 'descricao_recurso' => 'Câmera ou acessórios para registros.'],
        ['recurso_id' => 4, 'nome_recurso' => 'Ferramentas', 'descricao_recurso' => 'Ferramentas próprias para mutirões.'],
    ];

    $cepMock = [
        '01310000' => [
            'cidade' => 'São Paulo',
            'uf' => 'SP',
            'logradouro' => 'Avenida Paulista',
            'bairro' => 'Bela Vista',
        ],

        'default' => [
            'cidade' => 'Guarulhos',
            'uf' => 'SP',
            'logradouro' => '',
            'bairro' => '',
        ],
    ];

    $documentoUrl = '/docs/termos-de-uso.pdf';

    $termo = [
        'titulo' => 'Termos de Uso e Participação Voluntária',
        'versao' => '1.0',
        'atualizado_em' => '06/09/2026',
        'documento_url' => $documentoUrl,
        'documento_hash' => hash('sha256', $documentoUrl),
    ];

    return view('/cadastro', [
        'cadastroMock' => $cadastroMock,
        'habilidades' => $habilidades,
        'causas' => $causas,
        'recursos' => $recursos,
        'cepMock' => $cepMock,
        'termo' => $termo,
    ]);

})->name('cadastro.pessoa');


Route::post('/cadastro', function (Request $request) {

    $validated = $request->validate([
        'nm_pessoa' => ['required', 'string', 'max:64'],
        'email_pessoa' => ['required', 'email', 'max:128'],
        'cpf_pessoa' => ['required', 'string'],
        'tele_pessoa' => ['required', 'string'],
        'dt_nasc' => ['required', 'date'],

        'genero_pessoa' => [
            'required',
            'in:masculino,feminino,outro,prefiro não dizer',
        ],

        'rg_pessoa' => ['required', 'string', 'max:20'],
        'login_pessoa' => ['required', 'string', 'max:64'],
        'senha_pessoa' => ['required', 'string', 'min:8', 'confirmed'],

        'cep_pessoa' => ['required', 'string'],
        'logradouro_pessoa' => ['required', 'string', 'max:64'],
        'compl_pessoa' => ['nullable', 'string', 'max:64'],
        'cidade_pessoa' => ['required', 'string', 'max:64'],
        'bairro_pessoa' => ['required', 'string', 'max:64'],
        'uf_pessoa' => ['required', 'string', 'size:2'],

        'bio_pessoa' => ['nullable', 'string', 'max:1000'],

        'pfp_pessoa_link' => ['required', 'string', 'max:255'],
        'rg_pessoa_link' => ['required', 'string', 'max:255'],
        'antecedentes_pessoa_link' => ['required', 'string', 'max:255'],
        'cnh_pessoa_link' => ['required', 'string', 'max:255'],

        'habilidades' => ['required', 'array', 'min:1'],
        'habilidades.*' => ['integer'],
        'nivel_habilidade' => ['required', 'array'],

        'causas' => ['required', 'array', 'min:1'],
        'causas.*' => ['integer'],

        'recursos' => ['nullable', 'array'],
        'recursos.*' => ['integer'],
        'detalhes_recurso' => ['nullable', 'string', 'max:255'],

        // Mesma ideia do register() atual da Pessoa.
        'aceitou_termos' => ['required', 'accepted'],
    ]);

    // Normaliza os campos mascarados para o futuro payload da API.
    $validated['cpf_pessoa'] =
        preg_replace('/\D/', '', $validated['cpf_pessoa']);

    $validated['tele_pessoa'] =
        preg_replace('/\D/', '', $validated['tele_pessoa']);

    $validated['cep_pessoa'] =
        preg_replace('/\D/', '', $validated['cep_pessoa']);


    /*
    |--------------------------------------------------------------------------
    | ASSINATURA ELETRÔNICA — MOCK
    |--------------------------------------------------------------------------
    | Não grava no banco.
    | Replica os metadados que já estavam previstos no PessoaController.
    */
    $documentoUrl = '/docs/termos-de-uso.pdf';

    $assinaturaMock = [
        'dispositivo' =>
            $request->header('Sec-CH-UA-Platform')
            ?? 'Desconhecido',

        'ip_assinatura' =>
            $request->ip(),

        'user_agent_assinatura' =>
            $request->userAgent(),

        'documento_url' =>
            $documentoUrl,

        'documento_hash' =>
            hash('sha256', $documentoUrl),

        'geoloc_assinatura' =>
            null,

        'aceito_em' =>
            now()->toISOString(),
    ];

    session([
        'cadastro_pessoa_mock' => $validated,
        'assinatura_pessoa_mock' => $assinaturaMock,
    ]);

    return redirect()
        ->route('login')
        ->with(
            'cadastro_sucesso',
            'Cadastro concluído com sucesso! Agora você já pode entrar.'
        );

})->name('cadastro.pessoa.finalizar');

Route::get('/termos', function () {
    $secoes = [
        ['id' => 'sobre', 'numero' => 1, 'titulo' => 'Sobre o Ajudaê'],
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
})->name('termos');


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

})->name('privacidade');
