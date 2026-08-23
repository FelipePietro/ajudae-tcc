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

Route::get('/perfil', function () {

    return view('perfil/index-perfil', [

        /*
        |--------------------------------------------------------------------------
        | Informações principais
        |--------------------------------------------------------------------------
        */

        'id_user' => 1,

        'nome_user' => 'Lucas Pereira',

        'foto_user' => 'https://placehold.co/200x200',

        'cidade_user' => 'São Paulo',

        'uf_user' => 'SP',

        'tipo_user' => 'Voluntário',

        'bio_user' => 'Apaixonado por causas ambientais e educação. Busco contribuir com a comunidade e aprender com cada experiência. Acredito que pequenas ações coletivas geram transformações reais.',


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
        | Causas de interesse
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

});

Route::get('/perfil/preferencias', function () {

    /*
    |--------------------------------------------------------------------------
    | GET /api/v1/pessoas/{id}/habilidades
    |--------------------------------------------------------------------------
    */

    $habilidadesPessoaResponse = [
        'data' => [

            [
                'habilidade_id' => 1,
                'nome_habilidade' => 'Trabalho em equipe',
                'descricao_habilidade' => 'Capacidade de colaborar e atuar em conjunto com outras pessoas.',

                'pivot' => [
                    'pessoa_id' => 1,
                    'habilidade_id' => 1,
                    'nivel_habilidade' => 4,
                ],
            ],

            [
                'habilidade_id' => 2,
                'nome_habilidade' => 'Comunicação',
                'descricao_habilidade' => 'Facilidade para transmitir ideias e dialogar com diferentes públicos.',

                'pivot' => [
                    'pessoa_id' => 1,
                    'habilidade_id' => 2,
                    'nivel_habilidade' => 3,
                ],
            ],

            [
                'habilidade_id' => 3,
                'nome_habilidade' => 'Jardinagem',
                'descricao_habilidade' => 'Conhecimento em cuidados básicos com plantas e espaços verdes.',

                'pivot' => [
                    'pessoa_id' => 1,
                    'habilidade_id' => 3,
                    'nivel_habilidade' => 2,
                ],
            ],

        ],
    ];


    /*
    |--------------------------------------------------------------------------
    | GET /api/v1/habilidades
    |--------------------------------------------------------------------------
    |
    | Esse endpoint retorna diretamente um array de Models.
    |
    */

    $habilidadesDisponiveisResponse = [

        [
            'habilidade_id' => 1,
            'nome_habilidade' => 'Trabalho em equipe',
            'descricao_habilidade' => 'Capacidade de colaborar e atuar em conjunto com outras pessoas.',
        ],

        [
            'habilidade_id' => 2,
            'nome_habilidade' => 'Comunicação',
            'descricao_habilidade' => 'Facilidade para transmitir ideias e dialogar com diferentes públicos.',
        ],

        [
            'habilidade_id' => 3,
            'nome_habilidade' => 'Jardinagem',
            'descricao_habilidade' => 'Conhecimento em cuidados básicos com plantas e espaços verdes.',
        ],

        [
            'habilidade_id' => 4,
            'nome_habilidade' => 'Primeiros socorros',
            'descricao_habilidade' => 'Conhecimentos básicos de atendimento em situações emergenciais.',
        ],

        [
            'habilidade_id' => 5,
            'nome_habilidade' => 'Liderança',
            'descricao_habilidade' => 'Capacidade de coordenar pessoas e atividades.',
        ],

    ];


    /*
    |--------------------------------------------------------------------------
    | GET /api/v1/pessoas/{id}/recursos
    |--------------------------------------------------------------------------
    */

    $recursosPessoaResponse = [
        'data' => [

            [
                'recurso_id' => 1,
                'nome_recurso' => 'Veículo',
                'descricao_recurso' => 'Veículo próprio disponível para auxiliar atividades voluntárias.',

                'pivot' => [
                    'pessoa_id' => 1,
                    'recurso_id' => 1,
                    'detalhes_recurso' => 'Carro disponível aos finais de semana.',
                ],
            ],

            [
                'recurso_id' => 2,
                'nome_recurso' => 'Notebook',
                'descricao_recurso' => 'Computador portátil disponível para atividades.',

                'pivot' => [
                    'pessoa_id' => 1,
                    'recurso_id' => 2,
                    'detalhes_recurso' => 'Notebook pessoal com acesso à internet.',
                ],
            ],

        ],
    ];


    /*
    |--------------------------------------------------------------------------
    | GET /api/v1/recursos
    |--------------------------------------------------------------------------
    */

    $recursosDisponiveisResponse = [

        [
            'recurso_id' => 1,
            'nome_recurso' => 'Veículo',
            'descricao_recurso' => 'Veículo próprio disponível para auxiliar atividades voluntárias.',
        ],

        [
            'recurso_id' => 2,
            'nome_recurso' => 'Notebook',
            'descricao_recurso' => 'Computador portátil disponível para atividades.',
        ],

        [
            'recurso_id' => 3,
            'nome_recurso' => 'Ferramentas',
            'descricao_recurso' => 'Ferramentas manuais para manutenção e atividades externas.',
        ],

        [
            'recurso_id' => 4,
            'nome_recurso' => 'Câmera',
            'descricao_recurso' => 'Equipamento fotográfico para registro de ações.',
        ],

    ];


    /*
    |--------------------------------------------------------------------------
    | GET /api/v1/pessoas/{id}/causas
    |--------------------------------------------------------------------------
    */

    $causasPessoaResponse = [
        'data' => [

            [
                'causa_id' => 1,
                'nome_causa' => 'Meio Ambiente',
                'descricao_causa' => 'Ações relacionadas à preservação ambiental e sustentabilidade.',

                'pivot' => [
                    'pessoa_id' => 1,
                    'causa_id' => 1,
                ],
            ],

            [
                'causa_id' => 2,
                'nome_causa' => 'Educação',
                'descricao_causa' => 'Projetos voltados ao ensino, reforço escolar e inclusão educacional.',

                'pivot' => [
                    'pessoa_id' => 1,
                    'causa_id' => 2,
                ],
            ],

        ],
    ];


    /*
    |--------------------------------------------------------------------------
    | GET /api/v1/causas
    |--------------------------------------------------------------------------
    */

    $causasDisponiveisResponse = [

        [
            'causa_id' => 1,
            'nome_causa' => 'Meio Ambiente',
            'descricao_causa' => 'Ações relacionadas à preservação ambiental e sustentabilidade.',
        ],

        [
            'causa_id' => 2,
            'nome_causa' => 'Educação',
            'descricao_causa' => 'Projetos voltados ao ensino, reforço escolar e inclusão educacional.',
        ],

        [
            'causa_id' => 3,
            'nome_causa' => 'Saúde',
            'descricao_causa' => 'Ações de apoio à saúde, bem-estar e qualidade de vida.',
        ],

        [
            'causa_id' => 4,
            'nome_causa' => 'Proteção Animal',
            'descricao_causa' => 'Ações de cuidado, acolhimento e proteção de animais.',
        ],

        [
            'causa_id' => 5,
            'nome_causa' => 'Assistência Social',
            'descricao_causa' => 'Apoio a pessoas e comunidades em situação de vulnerabilidade.',
        ],

    ];


    /*
    |--------------------------------------------------------------------------
    | VIEW
    |--------------------------------------------------------------------------
    */

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

});

    Route::get('/perfil/editar', function () {

    /*
    |--------------------------------------------------------------------------
    | MOCK
    |--------------------------------------------------------------------------
    | Representa:
    |
    | GET /api/v1/pessoas/{id}
    |
    */

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

        'created_at' => '2026-03-12T15:30:00.000000Z',

        'updated_at' => '2026-08-23T14:00:00.000000Z',

    ];


    return view('/perfil/editar-perfil', [
        'pessoa' => $pessoa
    ]);

});

    Route::get('/perfil/excluir', function () {
    return view('/perfil.excluir');
    });

    Route::get('/perfil/exclusao-pendente', function () {

    /*
    |--------------------------------------------------------------------------
    | MOCK
    |--------------------------------------------------------------------------
    | Representa uma Pessoa com exclusão pendente.
    */

    $pessoa = [

        'pessoa_id' => 1,

        'nm_pessoa' => 'Felipe Pietro',

        'exclusao_pendente' => true,

        'deletar_em' => now()
            ->addDays(6)
            ->toISOString(),

    ];


    return view('perfil.exclusao-pendente', [
        'pessoa' => $pessoa
    ]);

});