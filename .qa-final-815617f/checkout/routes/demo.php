<?php

use App\Support\NavigationContext;
use App\Support\NotificationFixtures;
use App\Support\PrivacyFixtures;
use App\Support\PublicProfileFixtures;
use App\Support\SponsorshipFixtures;
use App\Support\VisualFiveFixtures;
use App\Support\VisualFourFixtures;
use App\Support\VisualOneFixtures;
// MOCKS RESTAURADOS: dados abaixo são fixtures de navegação, nunca respostas reais.
// Substituição futura e contratos: docs/frontend-demo-contratos-2026-10-22.md.
// Este arquivo é carregado apenas por routes/web.php; não aciona controllers da API.
use App\Support\VisualThreeFixtures;
use App\Support\VisualTwoFixtures;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Explicit local inspection only; preserves the HTTP status without throwing a real failure.
Route::get('/inspecao/estados/{status}', function (string $status) {
    abort_unless(app()->environment(['local', 'testing']) && request()->boolean('inspecao'), 404);
    abort_unless(in_array($status, ['401', '403', '404', '409', '419', '422', '500', '503'], true), 404);

    return response()->view('errors.'.$status, [], (int) $status);
})->name('inspecao.estados');

/*
|--------------------------------------------------------------------------
| LANDING PAGE
|--------------------------------------------------------------------------
*/

require __DIR__.'/public-pages.php';

/*
|--------------------------------------------------------------------------
| LOGIN
|--------------------------------------------------------------------------
*/

Route::get('/login', function () {
    $data = VisualOneFixtures::make('pessoa', request()->query('cenario', 'aprovado'));

    return view('login', $data);
})->name('login');

/*
|--------------------------------------------------------------------------
| CANDIDATURAS
|--------------------------------------------------------------------------
*/

Route::get('/candidatura', function () {
    return view('candidatura', VisualTwoFixtures::application(request()->query('cenario'), max(1, (int) request()->query('evento', 1)), request()->query('agenda')));
})->name('candidatura.form');

Route::get('/minhas-candidaturas', function () {
    $data = VisualTwoFixtures::page('applications', request()->query('cenario'), max(1, (int) request()->query('evento', 1)), request()->query('agenda'));
    if (request()->query('cenario') === 'participacao') {
        abort_unless(request()->query('contexto') === 'pessoa', 403);
        abort_unless(! request()->query->has('pessoa') && request()->query('evento', '7') === '7', 404);
        $data['participation'] = VisualFiveFixtures::personal('pessoa', false);
    }

    return view('minhas-candidaturas', $data);
})->name('candidaturas');

Route::get('/minhas-participacoes', function () {
    $context = request()->query('contexto');
    abort_unless(in_array($context, VisualFiveFixtures::PERSON_CONTEXTS, true), 403);
    abort_unless(! request()->query->has('pessoa'), 404);

    $scenario = request()->query('situacao');
    abort_unless(in_array($scenario, [null, 'vazio', 'indisponivel'], true), 404);

    return view('participacoes.pessoal', [
        'participation' => VisualFiveFixtures::personal($context, $scenario !== null, reviews: true),
        'unavailable' => $scenario === 'indisponivel',
    ]);
})->name('participacoes');

Route::get('/meus-certificados', function () {
    $context = request()->query('contexto');
    abort_unless(in_array($context, VisualFiveFixtures::PERSON_CONTEXTS, true), 403);
    abort_unless(! request()->query->has('pessoa'), 404);

    return view('certificados.lista', ['participation' => VisualFiveFixtures::personal($context, false)]);
})->name('certificados.index');

Route::get('/certificados/verificar', fn () => view('certificados.verificacao'))->name('certificados.verificar');

Route::post('/candidatura/confirmar', function () {
    return redirect()
        ->route('candidaturas')
        ->with('success', 'Revisão concluída. Nenhuma candidatura foi enviada.');
})->name('candidatura.confirmar');

Route::post('/agendas/{id}/candidatar', function ($id) {
    return redirect()->route('candidatura.form', ['agenda' => $id, 'evento' => max(1, intdiv((int) $id, 100))]);
})->name('eventos.candidatar');

/*
|--------------------------------------------------------------------------
| PERFIL DO USUÁRIO
|--------------------------------------------------------------------------
*/

Route::get('/perfil', function () {
    $data = VisualOneFixtures::make('pessoa', request()->query('cenario', 'aprovado'));

    $selected = request()->query('pessoa');
    if ($selected !== null || request()->query('area') === 'publica') {
        abort_unless(is_string($selected) && preg_match('/^[1-9][0-9]*$/D', $selected), 404);
        abort_if($selected === '1' && in_array(request()->query('privacidade'), ['pendente', 'prazo-encerrado', 'anonimizada'], true), 404);
        $public = PublicProfileFixtures::selectedPerson((int) $selected);
        abort_unless($public, 404);
        $context = NavigationContext::resolve(request()->query('contexto'))['key'];

        return view('perfil.index-perfil', array_merge($public, ['profileReturn' => PublicProfileFixtures::personReturn((int) $selected, $context)]));
    }
    $sponsorship = request()->query('apadrinhamento');
    if ($sponsorship !== null) {
        abort_unless(request()->query('area') === 'privada' && request()->query('contexto') === 'pessoa', 403);
        abort_unless(in_array($sponsorship, ['padrinho', 'solicitante'], true), 404);
        $applicantExample = request()->query('cenario_solicitante');
        abort_unless($applicantExample === null || ($sponsorship === 'solicitante'
            && is_string($applicantExample) && isset(SponsorshipFixtures::APPLICANT_EXAMPLES[$applicantExample])), 404);
        [$scenario, $name] = SponsorshipFixtures::profile(request()->query('padrinho_cenario'));

        return view('perfil.apadrinhamento', $data + [
            'sponsorshipRole' => $sponsorship,
            'sponsorshipScenario' => $scenario,
            'sponsorshipName' => $sponsorship === 'padrinho' ? $name : ($applicantExample ? 'Solicitante de exemplo' : 'Minha indicação'),
            'applicantExample' => $applicantExample,
            'sponsorshipApplicant' => $data['demo']['account']['nm_pessoa'],
        ]);
    }

    if (request()->query('area') === 'privada' || (request()->has('cenario') && request()->query('cenario') !== 'publico')) {
        $context = NavigationContext::resolve(request()->query('contexto'))['key'];
        $data['organizerWork'] = VisualThreeFixtures::make($context);
        $data['experience'] = in_array($context, VisualFiveFixtures::PERSON_CONTEXTS, true)
            ? VisualFiveFixtures::personal($context, false)
            : null;

        return view('perfil.area-privada', $data);
    }

    return view('perfil.index-perfil', PublicProfileFixtures::pessoa());
})->name('perfil');

/*
|--------------------------------------------------------------------------
| PREFERÊNCIAS DO USUÁRIO
|--------------------------------------------------------------------------
*/

Route::get('/perfil/preferencias', function () {
    $data = VisualOneFixtures::make('pessoa', request()->query('cenario', 'aprovado'));

    return view('perfil.preferencias', $data);
})->name('perfil.preferencias');

/*
|--------------------------------------------------------------------------
| EDITAR PERFIL
|--------------------------------------------------------------------------
*/

Route::get('/perfil/editar', function () {
    $data = VisualOneFixtures::make('pessoa', request()->query('cenario', 'aprovado'));
    $data['editFields'] = VisualOneFixtures::sections('pessoa')[1]['fields'];

    return view('perfil.editar-perfil', $data);
})->name('perfil.editar');

Route::get('/perfil/privacidade', fn () => PrivacyFixtures::page())->name('perfil.privacidade');
Route::get('/perfil/excluir', fn () => PrivacyFixtures::page(false, 'delete'))->name('perfil.excluir');
Route::get('/perfil/exclusao-pendente', fn () => PrivacyFixtures::page(false, 'pending'))->name('perfil.exclusao-pendente');

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
    $people = array_values(array_filter(VisualFiveFixtures::rankingPeople(), fn ($person) => $person['approved'] && ! ($person['deleted'] ?? false) && ! ($person['anonymous'] ?? false)));
    if (in_array(request()->query('privacidade'), ['pendente', 'prazo-encerrado', 'anonimizada'], true)) {
        $people = array_values(array_filter($people, fn ($person) => $person['id'] !== 1));
    }
    $fixtures = array_map(function ($id) {
        $fixture = VisualFiveFixtures::event($id);
        // Public projection retains only fields needed by the XP ledger, never justification.
        $fixture['records'] = array_map(fn ($record) => array_intersect_key($record, array_flip(['id', 'personId', 'agendaId', 'selection', 'presence', 'withdrawal'])), $fixture['records']);

        return $fixture;
    }, [1, 7, 8]);

    return view('ranking.index', compact('fixtures', 'people'));
})->name('ranking');

/*
|--------------------------------------------------------------------------
| NOTIFICAÇÕES
|--------------------------------------------------------------------------
*/

Route::get('/notificacoes', fn () => NotificationFixtures::page())->name('notificacoes');

/*
|--------------------------------------------------------------------------
| FEED DE EVENTOS
|--------------------------------------------------------------------------
*/

Route::get('/feed', function () {
    $data = VisualTwoFixtures::page('feed', request()->query('cenario'), 1, request()->query('agenda'));

    return view('feed.index', $data);
})->name('feed');

/*
|--------------------------------------------------------------------------
| DETALHES DO EVENTO
|--------------------------------------------------------------------------
*/

Route::get('/eventos/{id}', function ($id) {
    $data = VisualTwoFixtures::page('detail', request()->query('cenario'), (int) $id, request()->query('agenda'));
    abort_if($data['flow']['event_status'] !== 'ativo', 404);

    return view('eventos.detalhe', $data);
})->name('eventos.show');

/*
|--------------------------------------------------------------------------
| ONG - CADASTRO
|--------------------------------------------------------------------------
*/

Route::get('/cadastro/ong', function () {
    $data = VisualOneFixtures::make('ong', request()->query('cenario', 'inicio'));
    $data['sections'] = VisualOneFixtures::sections('ong');

    return view('ong.cadastro_ong', $data);
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

        'razao_social' => 'Instituto Exemplo Cadastrado',

        'nome_fantasia' => 'Exemplo Cadastrado',

        'cidade' => 'São Paulo',

        'estado' => 'SP',

        'situacao' => 'ATIVA',

    ];

    if (
        $dadosSimulados['situacao']
        !== 'ATIVA'
    ) {

        return response()->json([
            'message' => 'CNPJ inativo ou com natureza jurídica incompatível',
        ], 422);

    }

    return response()->json(
        $dadosSimulados
    );

})->name('ong.consultar-cnpj');

Route::post('/ong/cadastrar', function (Request $request) {

    $request->validate([

        'cnpj' => ['required', 'string', 'size:18'],

        'razao_social' => ['required', 'string', 'max:255'],

        'nome_fantasia' => ['required', 'string', 'max:255'],

        'cidade' => ['required', 'string', 'max:120'],

        'estado' => ['required', 'string', 'size:2'],

        'email' => ['required', 'email', 'max:255'],

        'telefone' => ['required', 'string', 'max:15'],

        'senha' => ['required', 'string', 'min:8', 'confirmed'],

        'descricao' => ['required', 'string', 'max:2000'],

        'logo' => ['nullable', 'image', 'max:5120'],

        'aceite_termo_responsabilidade' => ['accepted'],

        'aceite_privacidade' => ['accepted'],

    ], [

        'aceite_termo_responsabilidade.accepted' => 'É necessário aceitar o Termo de Responsabilidade da ONG.',

        'aceite_privacidade.accepted' => 'É necessário concordar com a Política de Privacidade.',

    ]);

    return redirect()
        ->route('ong.aprovacao-pendente')
        ->with(
            'sucesso',
            'Revisão concluída. Nenhuma conta foi criada ou mensagem enviada.'
        );

})->name('ong.cadastrar');

Route::get('/ong/aprovacao-pendente', function () {
    $data = VisualOneFixtures::make('ong', request()->query('cenario', 'pendente'));

    return view('ong.aprovacao-pendente', $data);
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

        'nome' => 'Instituto Esperança Social',

        'nome_fantasia' => 'Esperança Social',

        'cidade' => 'São Paulo',

        'estado' => 'SP',

        'cnpj' => '11.222.333/0001-81',

        'area_atuacao' => 'Assistência social · Educação',

        'ativa' => true,

        'verificada' => true,

        'site' => 'esperancasocial.org.br',

        'aprovado_em' => '12 jan. 2024',

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
            'meta' => '2 eventos designados · Organizador principal',
            'status' => 'accepted',
        ],

        [
            'iniciais' => 'ML',
            'cor' => 'yellow',
            'nome' => 'Maria Lima',
            'meta' => '1 evento designado',
            'status' => 'accepted',
        ],

        [
            'iniciais' => 'PS',
            'cor' => 'blue',
            'nome' => 'Pedro Santos',
            'meta' => 'Nenhum evento · convite aguardando resposta',
            'status' => 'pending',
        ],

    ];

    $atividades = [

        [
            'cor' => 'green',
            'texto' => 'Carlos aprovou candidatura de Ana Costa para <strong>Limpeza de Parques</strong>',
            'tempo' => 'hoje, 14:32',
        ],

        [
            'cor' => 'orange',
            'texto' => 'Evento <strong>Campanha de Vacinação</strong> enviado para aprovação',
            'tempo' => 'ontem, 09:15',
        ],

        [
            'cor' => 'blue',
            'texto' => 'Pedro Santos convidado para a equipe via e-mail',
            'tempo' => 'há 2 dias',
        ],

        [
            'cor' => 'green',
            'texto' => '<strong>Doação de Roupas</strong> publicado — 15 candidaturas recebidas',
            'tempo' => 'há 3 dias',
        ],

    ];

    $stats = [

        'eventos_ativos' => collect($eventos)
            ->where(
                'status_class',
                'status-published'
            )
            ->count(),

        'funcionarios' => count($funcionarios),

        'total_voluntarios' => 128,

        'eventos_realizados' => 32,

        'horas_doadas' => '2.580',

    ];

    $organizerWork = VisualThreeFixtures::make('ong');
    $eventos = array_map(function ($event) use ($organizerWork) {
        $person = collect($organizerWork['people'])->firstWhere('pessoa_id', $event['responsavel_pessoa_id']);

        return ['evento_id' => $event['evento_id'], 'nome' => $event['nm_evento'], 'local' => 'São Paulo, SP', 'data' => 'Data da próxima Agenda',
            'responsavel' => $person['nm_pessoa'] ?? 'Conta da organização', 'cargo' => 'Responsável operacional',
            'status' => $event['status_evento'] === 'ativo' ? 'Publicado' : 'Aprovação pendente', 'status_class' => 'status-published', 'candidatos' => 'Ver candidaturas'];
    }, $organizerWork['visibleEvents']);

    return view(
        '/ong/painel_ong',
        compact(
            'ong',
            'eventos',
            'funcionarios',
            'atividades',
            'stats',
            'organizerWork'
        )
    );

})->name('ong.painel');

/*
|--------------------------------------------------------------------------
| ONG - MEUS EVENTOS
|--------------------------------------------------------------------------
*/

// Frontend-only organizer journeys. No API/controllers/database calls.
Route::get('/organizador/solicitacao', function () {
    $context = NavigationContext::resolve(request()->query('contexto'), 'pessoa')['key'];
    abort_unless($context === 'pessoa', 403);

    return view('organizadores.solicitacao', ['work' => VisualThreeFixtures::make($context)]);
})->name('organizador.solicitacao');

Route::get('/organizador', function () {
    $context = NavigationContext::resolve(request()->query('contexto'), 'organizador')['key'];
    abort_unless(in_array($context, ['organizador', 'organizador-vinculado'], true), 403);

    $work = VisualThreeFixtures::make($context);
    if ($work['actingContext'] !== $context) {
        return redirect()->route('organizador.workspace', ['contexto' => $work['actingContext'], 'jornada' => $work['scenario'], 'inspecao' => request()->boolean('inspecao') ? 1 : null]);
    }

    return view('organizadores.workspace', ['work' => $work]);
})->name('organizador.workspace');

Route::get('/ong/organizadores', function () {
    $context = NavigationContext::resolve(request()->query('contexto'), 'ong')['key'];
    abort_unless($context === 'ong', 403);

    return view('organizadores.ong', ['work' => VisualThreeFixtures::make('ong')]);
})->name('ong.organizadores');

Route::get('/ong/eventos', function () {
    $data = VisualTwoFixtures::page('management', request()->query('cenario'), 1, request()->query('agenda'));

    return view('ong.meus-eventos', $data);
})->name('ong.eventos.index');

/*
|--------------------------------------------------------------------------
| ONG - CANDIDATOS
|--------------------------------------------------------------------------
*/

Route::get('/ong/candidatos', function () {
    if (request()->query('cenario') === 'participacao') {
        abort_unless(VisualFiveFixtures::manager((int) request()->query('evento')), 403);
    }
    $data = VisualTwoFixtures::page('candidates', request()->query('cenario'), max(1, (int) request()->query('evento', 1)), request()->query('agenda'));

    return view('ong.candidatos', $data);
})->name('ong.candidatos');

Route::get('/ong/eventos/{id}/presenca', function (int $id) {
    abort_unless(VisualFiveFixtures::event($id), 404);
    $participation = VisualFiveFixtures::manager($id);
    abort_unless($participation, 403);

    return view('participacoes.gestao', compact('participation'));
})->whereNumber('id')->name('ong.eventos.presenca');

/*
|--------------------------------------------------------------------------
| ONG - CRIAR EVENTO
|--------------------------------------------------------------------------
*/

Route::get('/ong/eventos/criar', function () {
    $data = VisualTwoFixtures::page('create', request()->query('cenario'), 1, request()->query('agenda'));

    return view('ong.criar', $data);
})->name('ong.eventos.criar');

Route::post('/ong/eventos', function () {

    return redirect()
        ->route('ong.eventos.criar')
        ->with(
            'success',
            'Revisão concluída. Nenhum evento foi enviado.'
        );

})->name('ong.eventos.store');

/*
|--------------------------------------------------------------------------
| ONG - EDITAR EVENTO
|--------------------------------------------------------------------------
*/

Route::get('/ong/eventos/{id}/editar', function ($id) {
    $data = VisualTwoFixtures::page('edit', request()->query('cenario'), (int) $id, request()->query('agenda'));

    return view('ong.editar', $data);
})->name('ong.eventos.editar');

Route::put('/ong/eventos/{id}', function ($id) {

    return redirect()
        ->route(
            'ong.eventos.editar',
            $id
        )
        ->with(
            'success',
            'Revisão concluída. Nenhuma alteração foi enviada.'
        );

})->name('ong.eventos.atualizar');

/*
|--------------------------------------------------------------------------
| ONG - PERFIL
|--------------------------------------------------------------------------
*/

Route::get('/ong/perfil', function () {
    $data = VisualOneFixtures::make('ong', request()->query('cenario', 'pendente'));

    if (request()->query('area') === 'privada') {
        return view('ong.area-cadastral', $data);
    }

    // Explicit public target, independent of the browsing account.
    $organization = request()->query('ong', '1');
    abort_unless(is_string($organization) && in_array($organization, ['1', '2'], true), 404);

    abort_if($organization === '1' && in_array(request()->query('privacidade'), ['pendente', 'prazo-encerrado', 'anonimizada'], true), 404);
    $data = PublicProfileFixtures::ong((int) $organization);
    $context = NavigationContext::resolve(request()->query('contexto'), 'ong')['key'];
    $data['originEvent'] = in_array($context, ['pessoa', 'visitante'], true)
        ? VisualTwoFixtures::profileOrigin(request()->query('evento_origem'), (int) $organization)
        : null;
    $data['originContext'] = $context;

    return view('ong.perfil_ong', $data);
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
    $data = VisualOneFixtures::make('ong', request()->query('cenario', 'pendente'));
    $data['sections'] = VisualOneFixtures::sections('ong');

    // Presentation follows the actual PATCH whitelist; unsupported identity changes remain read-only.
    foreach ($data['sections'] as &$section) {
        if (isset($section['fields'])) {
            foreach ($section['fields'] as &$field) {
                if (! in_array($field['key'], ['descricao_ong', 'email_ong', 'tel_ong', 'logradouro_ong', 'cidade_ong', 'uf_ong', 'cep_ong', 'bairro_ong', 'site_url_ong'], true)) {
                    $field['readonly'] = true;
                    $field['hint'] = 'Este dado não pode ser alterado nesta página.';
                }
                if ($field['key'] === 'descricao_ong') {
                    $field['max'] = 256;
                }
            }
            unset($field);
        }
    }
    unset($section);

    return view('ong.editar_ong', $data);
})->name('ong.perfil.editar');

Route::put('/ong/perfil/{id}', function (
    Request $request,
    $id
) {

    $request->validate([

        'razao_social' => ['required', 'string', 'max:255'],

        'nome_fantasia' => ['required', 'string', 'max:255'],

        'cidade' => ['required', 'string', 'max:120'],

        'estado' => ['required', 'string', 'size:2'],

        'email' => ['required', 'email', 'max:255'],

        'telefone' => ['nullable', 'string', 'max:20'],

        'descricao' => ['required', 'string', 'max:2000'],

        'senha' => ['nullable', 'string', 'min:8', 'confirmed'],

        'logo' => ['nullable', 'image', 'max:5120'],

        'remover_logo' => ['nullable', 'boolean'],

    ]);

    return redirect()
        ->route('ong.perfil.editar')
        ->with(
            'sucesso',
            'Revisão concluída. Nenhum dado foi enviado.'
        );

})->name('ong.perfil.atualizar');

/*
|--------------------------------------------------------------------------
| EXCLUSÃO DE CONTA — ONG (MOCK FRONT-END)
|--------------------------------------------------------------------------
*/

Route::get('/ong/privacidade', fn () => PrivacyFixtures::page(true))->name('ong.privacidade');
Route::get('/ong/perfil/excluir', fn () => PrivacyFixtures::page(true, 'delete'))->name('ong.perfil.excluir');
Route::get('/ong/perfil/exclusao-pendente', fn () => PrivacyFixtures::page(true, 'pending'))->name('ong.perfil.exclusao-pendente');

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

Route::get('/ong/notificacoes', fn () => NotificationFixtures::page(true))->name('ong.notificacoes');

Route::get('/ong/configuracoes', function () {

    return redirect()
        ->route('ong.perfil.editar');

})->name('ong.configuracoes');

// ==============================
// ROTAS DO PAINEL ADMINISTRATIVO
// ==============================

Route::prefix('admin')->name('admin.')->group(function () {
    foreach (array_keys(VisualFourFixtures::PAGES) as $page) {
        Route::get($page === 'admin' ? '/' : '/'.$page, fn () => VisualFourFixtures::page($page))
            ->name($page);
    }
});

Route::get('/cadastro', function () {
    $data = VisualOneFixtures::make('pessoa', request()->query('cenario', 'inicio'));
    $data['sections'] = VisualOneFixtures::sections('pessoa');

    return view('cadastro', $data);
})->name('cadastro.pessoa');

Route::post('/cadastro', function (Request $request) {

    $request->validate([
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

    // P0: validação transitória apenas. Nunca guardar payload, senha ou assinatura.
    // Integração futura: POST /api/v1/pessoas/register, pelo grupo.
    return redirect()
        ->route('login')
        ->with(
            'cadastro_sucesso',
            'Revisão concluída. Nenhuma conta foi criada.'
        );

})->name('cadastro.pessoa.finalizar');

