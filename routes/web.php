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
})->name('candidaturas');

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

})->name('perfil');

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




/*
|--------------------------------------------------------------------------
| ROTAS NOMEADAS DE APOIO
|--------------------------------------------------------------------------
| Necessárias para os componentes de navegação enquanto as respectivas
| páginas continuam mockadas.
*/

Route::get('/feed', function () {
    return redirect('/');
})->name('feed');

Route::get('/ranking', function () {
    return redirect('/perfil');
})->name('ranking');

Route::get('/notificacoes', function () {
    return redirect('/perfil');
})->name('notificacoes');

Route::get('/configuracoes', function () {
    return redirect('/perfil/editar');
})->name('configuracoes');


/*
|--------------------------------------------------------------------------
| ROTAS DE NAVEGAÇÃO DA ONG
|--------------------------------------------------------------------------
*/

Route::get('/ong/dashboard', function () {
    return redirect('/ong/painel');
})->name('ong.dashboard');

Route::get('/ong/painel', function () {
    // Troque o caminho abaixo se a view do painel estiver em outra pasta.
    return view('ong.painel');
})->name('ong.painel');

Route::get('/ong/eventos', function () {

    /*
    |--------------------------------------------------------------------------
    | EVENTOS
    |--------------------------------------------------------------------------
    |
    | Estrutura propositalmente próxima do que a API deverá retornar.
    |
    */

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

            'candidaturas_pendentes' =>
                4,

            'candidaturas_aprovadas' =>
                6,
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

            'candidaturas_pendentes' =>
                9,

            'candidaturas_aprovadas' =>
                8,
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

            'candidaturas_pendentes' =>
                6,

            'candidaturas_aprovadas' =>
                3,
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

            'candidaturas_pendentes' =>
                7,

            'candidaturas_aprovadas' =>
                3,
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

            'candidaturas_pendentes' =>
                0,

            'candidaturas_aprovadas' =>
                0,
        ],

    ];


    /*
    |--------------------------------------------------------------------------
    | RESUMO
    |--------------------------------------------------------------------------
    |
    | Já calculado a partir dos próprios eventos.
    |
    | Depois, isso pode ser feito pelo backend ou por um endpoint de resumo.
    |
    */

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

        'eventos' =>
            $eventos,

        'resumo' =>
            $resumo,

    ]);

})->name('ong.eventos.index');

Route::get('/ong/candidatos', function () {
    // Troque o caminho abaixo se a view dos candidatos tiver outro nome.
    return view('ong.candidatos');
})->name('ong.candidatos');


/*
|--------------------------------------------------------------------------
| PÁGINAS DE EVENTOS - MOCK FRONT-END
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| CRIAR EVENTO - ONG
|--------------------------------------------------------------------------
|
| GET /ong/eventos/criar
|
| Mocka as categorias e habilidades que futuramente virão da API.
|
*/

Route::get('/ong/eventos/criar', function () {

    $categorias = collect([
        (object) [
            'cat_evento_id' => 1,
            'nome_categoria' => 'Meio Ambiente',
        ],
        (object) [
            'cat_evento_id' => 2,
            'nome_categoria' => 'Educação',
        ],
        (object) [
            'cat_evento_id' => 3,
            'nome_categoria' => 'Saúde',
        ],
        (object) [
            'cat_evento_id' => 4,
            'nome_categoria' => 'Assistência Social',
        ],
    ]);


    $habilidades = collect([
        (object) [
            'habilidade_id' => 1,
            'nome_habilidade' => 'Trabalho em equipe',
        ],
        (object) [
            'habilidade_id' => 2,
            'nome_habilidade' => 'Disposição física',
        ],
        (object) [
            'habilidade_id' => 3,
            'nome_habilidade' => 'Consciência ambiental',
        ],
        (object) [
            'habilidade_id' => 4,
            'nome_habilidade' => 'Liderança',
        ],
        (object) [
            'habilidade_id' => 5,
            'nome_habilidade' => 'Primeiros socorros',
        ],
        (object) [
            'habilidade_id' => 6,
            'nome_habilidade' => 'Jardinagem',
        ],
        (object) [
            'habilidade_id' => 7,
            'nome_habilidade' => 'Comunicação',
        ],
    ]);


    return view('ong/criar', [
        'categorias' => $categorias,
        'habilidades' => $habilidades,
    ]);

})->name('ong.eventos.criar');


/*
|--------------------------------------------------------------------------
| ENVIO DO EVENTO - MOCK
|--------------------------------------------------------------------------
|
| Por enquanto não salva no banco.
|
| Quando fizermos a integração, essa rota será substituída pelo envio
| para o controller/API.
|
*/

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
| DETALHES DO EVENTO
|--------------------------------------------------------------------------
|
| GET /eventos/{id}
|
| Simula o retorno da API para montar a página.
|
*/

Route::get('/eventos/{id}', function ($id) {

    /*
    |--------------------------------------------------------------------------
    | EVENTO
    |--------------------------------------------------------------------------
    */

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


        /*
        |--------------------------------------------------------------------------
        | ENDEREÇO
        |--------------------------------------------------------------------------
        */

        'cep_evento' => '02377-000',

        'logradouro_evento' =>
            'Rua do Horto, 1488',

        'compl_evento' =>
            null,

        'bairro_evento' =>
            'Horto Florestal',

        'cidade_evento' =>
            'São Paulo',

        'uf_evento' =>
            'SP',


        /*
        |--------------------------------------------------------------------------
        | INFORMAÇÕES DO EVENTO
        |--------------------------------------------------------------------------
        */

        'vagas_evento' =>
            30,

        'modalidade_evento' =>
            'presencial',

        'status_evento' =>
            'publicado',

        'imagem_evento_link' =>
            null,


        /*
        |--------------------------------------------------------------------------
        | CATEGORIA
        |--------------------------------------------------------------------------
        */

        'categoria' => (object) [

            'cat_evento_id' =>
                1,

            'nome_categoria' =>
                'Meio Ambiente',

        ],


        /*
        |--------------------------------------------------------------------------
        | ONG
        |--------------------------------------------------------------------------
        */

        'ong' => (object) [

            'ong_id' =>
                1,

            'nm_ong' =>
                'ONG Verde SP',

        ],


        /*
        |--------------------------------------------------------------------------
        | HABILIDADES
        |--------------------------------------------------------------------------
        */

        'habilidades' => collect([

            (object) [
                'habilidade_id' => 1,
                'nome_habilidade' => 'Trabalho em equipe',
            ],

            (object) [
                'habilidade_id' => 2,
                'nome_habilidade' => 'Disposição física',
            ],

            (object) [
                'habilidade_id' => 3,
                'nome_habilidade' => 'Consciência ambiental',
            ],

        ]),

    ];


    /*
    |--------------------------------------------------------------------------
    | AGENDA
    |--------------------------------------------------------------------------
    |
    | As datas continuam vindo da agenda.
    |
    */

    $agenda = (object) [

        'agenda_id' =>
            1,

        'data_inicio' =>
            \Carbon\Carbon::parse('2026-09-15 08:00:00'),

        'data_fim' =>
            \Carbon\Carbon::parse('2026-09-15 16:00:00'),

        'status_ativo' =>
            'ativo',

    ];


    /*
    |--------------------------------------------------------------------------
    | CÁLCULO DAS VAGAS
    |--------------------------------------------------------------------------
    */

    $totalVagas =
        $evento->vagas_evento;

    $confirmados =
        8;

    $vagasDisponiveis =
        max(
            0,
            $totalVagas - $confirmados
        );


    $percentualOcupado =
        $totalVagas > 0

            ? (
                $confirmados /
                $totalVagas
            ) * 100

            : 0;


    /*
    |--------------------------------------------------------------------------
    | ENVIA PARA A VIEW
    |--------------------------------------------------------------------------
    */

    return view('eventos/detalhe', [

        'evento' =>
            $evento,

        'agenda' =>
            $agenda,

        'totalVagas' =>
            $totalVagas,

        'confirmados' =>
            $confirmados,

        'vagasDisponiveis' =>
            $vagasDisponiveis,

        'percentualOcupado' =>
            $percentualOcupado,

    ]);

})->name('eventos.show');


/*
|--------------------------------------------------------------------------
| CANDIDATURA - MOCK
|--------------------------------------------------------------------------
|
| Simula o clique em "Quero me voluntariar".
|
| Depois será substituído pelo endpoint real da candidatura.
|
*/

Route::post('/agendas/{id}/candidatar', function ($id) {

    return redirect('/candidatura');

})->name('eventos.candidatar');

Route::get('/ong/eventos/{id}/editar', function ($id) {

    /*
    |--------------------------------------------------------------------------
    | MOCK DO EVENTO
    |--------------------------------------------------------------------------
    */

    $evento = (object) [

        'evento_id' => (int) $id,

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
                'nome_habilidade' => 'Trabalho em equipe',
            ],

            (object) [
                'habilidade_id' => 3,
                'nome_habilidade' => 'Consciência ambiental',
            ],

        ]),

    ];


    /*
    |--------------------------------------------------------------------------
    | MOCK DA AGENDA
    |--------------------------------------------------------------------------
    */

    $agenda = (object) [

        'agenda_id' =>
            1,

        'data_inicio' =>
            \Carbon\Carbon::parse(
                '2026-09-15 08:00:00'
            ),

        'data_fim' =>
            \Carbon\Carbon::parse(
                '2026-09-15 16:00:00'
            ),

    ];


    /*
    |--------------------------------------------------------------------------
    | CATEGORIAS
    |--------------------------------------------------------------------------
    */

    $categorias = collect([

        (object) [
            'cat_evento_id' => 1,
            'nome_categoria' => 'Meio Ambiente',
        ],

        (object) [
            'cat_evento_id' => 2,
            'nome_categoria' => 'Educação',
        ],

        (object) [
            'cat_evento_id' => 3,
            'nome_categoria' => 'Saúde',
        ],

        (object) [
            'cat_evento_id' => 4,
            'nome_categoria' => 'Assistência Social',
        ],

    ]);


    /*
    |--------------------------------------------------------------------------
    | HABILIDADES
    |--------------------------------------------------------------------------
    */

    $habilidades = collect([

        (object) [
            'habilidade_id' => 1,
            'nome_habilidade' => 'Trabalho em equipe',
        ],

        (object) [
            'habilidade_id' => 2,
            'nome_habilidade' => 'Disposição física',
        ],

        (object) [
            'habilidade_id' => 3,
            'nome_habilidade' => 'Consciência ambiental',
        ],

        (object) [
            'habilidade_id' => 4,
            'nome_habilidade' => 'Liderança',
        ],

        (object) [
            'habilidade_id' => 5,
            'nome_habilidade' => 'Primeiros socorros',
        ],

    ]);


    return view('/ong/editar', [

        'evento' =>
            $evento,

        'agenda' =>
            $agenda,

        'categorias' =>
            $categorias,

        'habilidades' =>
            $habilidades,

    ]);

})->name('ong.eventos.editar');


Route::put('/ong/eventos/{id}', function ($id) {

    /*
    |--------------------------------------------------------------------------
    | MOCK DO UPDATE
    |--------------------------------------------------------------------------
    |
    | Depois será substituído pela chamada real para a API.
    |
    */

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

    Route::get('/', function () {

        return view('/landing-page');

    })->name('inicio');

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

                'titulo' =>
                    'Candidatura aprovada!',

                'mensagem' =>
                    'Você foi aprovado para o evento <strong>Limpeza de Parques — Cantareira</strong> organizado pela ONG Verde SP.',

                'tempo' =>
                    'há 2 horas',

                'tag' =>
                    'Candidatura',

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

                'tempo' =>
                    'há 5 horas',

                'tag' =>
                    'Badge & XP',

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

                'tempo' =>
                    'há 1 dia',

                'tag' =>
                    'Convite',

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

                'tempo' =>
                    'há 2 dias',

                'tag' =>
                    'Candidatura recusada',

                'cor_tag' =>
                    'bg-red-50 text-red-600',

                'acoes' => [

                    [
                        'texto' => 'Buscar outros eventos →',
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

                'tempo' =>
                    'há 3 dias',

                'tag' =>
                    'XP & Certificado',

                'cor_tag' =>
                    'bg-[#dff4e7] text-[#315e44]',

                'acoes' => [

                    [
                        'texto' => '📥 Baixar certificado',
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

            'usuario' =>
                $usuario,

            'notificacoes' =>
                $notificacoes,

            'naoLidas' =>
                $naoLidas,

        ]);

    })->name('notificacoes');

        /*
|--------------------------------------------------------------------------
| FEED DE EVENTOS
|--------------------------------------------------------------------------
*/

Route::get('/feed', function () {

    /*
    |--------------------------------------------------------------------------
    | USUÁRIO LOGADO
    |--------------------------------------------------------------------------
    */

    $usuario = [

        'pessoa_id' =>
            1,

        'nome' =>
            'Lucas Pereira',

        'xp' =>
            450,

        'posicao_ranking' =>
            42,

    ];


    /*
    |--------------------------------------------------------------------------
    | CATEGORIAS
    |--------------------------------------------------------------------------
    |
    | Depois virá do endpoint real de categorias.
    |
    */

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


    /*
    |--------------------------------------------------------------------------
    | EVENTOS
    |--------------------------------------------------------------------------
    |
    | Esse formato já está propositalmente próximo do que a API deve devolver.
    |
    */

    $eventos = [

        [
            'evento_id' => 1,

            'nm_evento' =>
                'Limpeza de Parques — Parque da Cantareira',

            'imagem_evento_link' =>
                null,

            'icone' =>
                '🌿',

            'tipo_organizador' =>
                'ONG',

            'categoria' => [
                'categoria_id' => 1,
                'nome_categoria' => 'Meio Ambiente',
            ],

            'modalidade_evento' =>
            'presencial',

            'data_formatada' =>
                '15 maio 2026',
                
            'data_inicio' => '2026-08-27 08:00:00',
            'data_fim' => '2026-08-27 16:00:00',

            'horario_formatado' =>
                '08h–16h',

            'cidade_evento' =>
                'São Paulo',

            'uf_evento' =>
                'SP',

            'organizador_nome' =>
                'ONG Verde SP · Carlos F.',

            'vagas_evento' =>
                30,

            'confirmados' =>
                8,

            'vagas_disponiveis' =>
                22,

            'percentual_ocupacao' =>
                26,

        ],


        [
            'evento_id' => 2,

            'nm_evento' =>
                'Aulas de Reforço Escolar — Ensino Fundamental',

            'imagem_evento_link' =>
                null,

            'icone' =>
                '📚',

            'tipo_organizador' =>
                'Organizador ind.',

            'categoria' => [
                'categoria_id' => 2,
                'nome_categoria' => 'Educação',
            ],

            'data_formatada' =>
                '18 maio 2026',

            'data_inicio' => '2026-08-27 08:00:00',
            'data_fim' => '2026-08-27 16:00:00',

            'horario_formatado' =>
                '14h–17h',

            'modalidade_evento' =>
            'presencial',

            'cidade_evento' =>
                'Santo André',

            'uf_evento' =>
                'SP',

            'organizador_nome' =>
                'Org. Educar · Ana M.',

            'vagas_evento' =>
                30,

            'confirmados' =>
                15,

            'vagas_disponiveis' =>
                15,

            'percentual_ocupacao' =>
                50,

        ],


        [
            'evento_id' => 3,

            'nm_evento' =>
                'Campanha de Vacinação Comunitária',

            'imagem_evento_link' =>
                null,

            'icone' =>
                '💉',

            'tipo_organizador' =>
                'ONG',

            'categoria' => [
                'categoria_id' => 3,
                'nome_categoria' => 'Saúde',
            ],

            'data_formatada' =>
                '20 maio 2026',

            'modalidade_evento' =>
            'presencial',

            'data_inicio' => '2026-08-27 08:00:00',
            'data_fim' => '2026-08-27 16:00:00',

            'horario_formatado' =>
                '09h–13h',

            'cidade_evento' =>
                'Guarulhos',

            'uf_evento' =>
                'SP',

            'organizador_nome' =>
                'Cruz Vermelha BR',

            'vagas_evento' =>
                30,

            'confirmados' =>
                27,

            'vagas_disponiveis' =>
                3,

            'percentual_ocupacao' =>
                90,

        ],


        [
            'evento_id' => 4,

            'nm_evento' =>
                'Aula de Culinária Social — Receitas Econômicas',

            'imagem_evento_link' =>
                null,

            'icone' =>
                '🍳',

            'tipo_organizador' =>
                'Organizador ind.',

            'categoria' => [
                'categoria_id' => 4,
                'nome_categoria' => 'Culinária',
            ],

            'data_formatada' =>
                '22 maio 2026',

            'data_inicio' => '2026-08-27 08:00:00',
            'data_fim' => '2026-08-27 16:00:00',

            'horario_formatado' =>
                '10h–13h',

            'modalidade_evento' =>
            'presencial',

            'cidade_evento' =>
                'São Paulo',

            'uf_evento' =>
                'SP',

            'organizador_nome' =>
                'Org. Sabor Social · Beatriz K.',

            'vagas_evento' =>
                30,

            'confirmados' =>
                10,

            'vagas_disponiveis' =>
                20,

            'percentual_ocupacao' =>
                33,

        ],


        [
            'evento_id' => 5,

            'nm_evento' =>
                'Mutirão de Plantio de Árvores Nativas',

            'imagem_evento_link' =>
                null,

            'icone' =>
                '🌳',

            'tipo_organizador' =>
                'ONG',

            'categoria' => [
                'categoria_id' => 1,
                'nome_categoria' => 'Meio Ambiente',
            ],

            'data_formatada' =>
                '25 maio 2026',

            'data_inicio' => '2026-08-27 08:00:00',
            'data_fim' => '2026-08-27 16:00:00',

            'horario_formatado' =>
                '07h–12h',

            'modalidade_evento' =>
            'presencial',

            'cidade_evento' =>
                'Osasco',

            'uf_evento' =>
                'SP',

            'organizador_nome' =>
                'Instituto Raízes Vivas',

            'vagas_evento' =>
                30,

            'confirmados' =>
                18,

            'vagas_disponiveis' =>
                12,

            'percentual_ocupacao' =>
                60,

        ],


        [
            'evento_id' => 6,

            'nm_evento' =>
                'Doação de Roupas — Triagem e Separação',

            'imagem_evento_link' =>
                null,

            'icone' =>
                '👕',

            'tipo_organizador' =>
                'ONG',

            'categoria' => [
                'categoria_id' => 5,
                'nome_categoria' => 'Assistência Social',
            ],

            'data_formatada' =>
                '28 maio 2026',

            'horario_formatado' =>
                '09h–17h',

            'data_inicio' => '2026-08-27 08:00:00',
            'data_fim' => '2026-08-27 16:00:00',

            'cidade_evento' =>
                'Diadema',

            
            'modalidade_evento' =>
            'presencial',

            'uf_evento' =>
                'SP',

            'organizador_nome' =>
                'Instituto Esperança Social',

            'vagas_evento' =>
                30,

            'confirmados' =>
                2,

            'vagas_disponiveis' =>
                28,

            'percentual_ocupacao' =>
                7,

        ],

    ];


    /*
    |--------------------------------------------------------------------------
    | CIDADES
    |--------------------------------------------------------------------------
    */

    $cidades =
        collect($eventos)
            ->pluck('cidade_evento')
            ->unique()
            ->values()
            ->all();


    /*
    |--------------------------------------------------------------------------
    | TOP RANKING
    |--------------------------------------------------------------------------
    */

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


    /*
    |--------------------------------------------------------------------------
    | BADGES
    |--------------------------------------------------------------------------
    */

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

        'usuario' =>
            $usuario,

        'categorias' =>
            $categorias,

        'cidades' =>
            $cidades,

        'eventos' =>
            $eventos,

        'topRanking' =>
            $topRanking,

        'badges' =>
            $badges,

        'badgesConquistados' =>
            $badgesConquistados,

    ]);

})->name('feed');