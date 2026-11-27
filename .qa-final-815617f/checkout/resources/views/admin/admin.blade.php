@extends('layouts.admin')
@section('title', $title)

@section('content')
    @php
        $webDashboard = isset($integratedAdmin) ? \App\Support\WebNavigation::for(request()) : null;
        $areas = $webDashboard ? $webDashboard->adminAreas() : $pages;
    @endphp
    <h1 class="sr-only">{{ $title }}</h1>
    @php
        $stats = ['upgrades-org'=>'laranja','cadastros-ong'=>'azul','fila-eventos'=>'amarelo','candidatos'=>'vermelho','solicitacoes-lgpd'=>'claro'];
        $tabs = ['fila-eventos'=>'Eventos','cadastros-ong'=>'ONGs','upgrades-org'=>'Promoções','denuncias'=>'Ocorrências','solicitacoes-lgpd'=>'LGPD','candidatos'=>'Pessoas'];
        $work = array_filter($queues['fila-eventos'] ?? [], fn ($row) => $row['status'] === 'aguardando a confirmação');
        if ($webDashboard) {
            $stats = $stats + array_fill_keys(array_keys($areas), 'claro');
            $tabs = $tabs + array_map(fn ($area) => $area[0], $areas);
        }
    @endphp
    @if(!$webDashboard && $variant === 'falha')
        <p class="admin-alerta" role="alert">Não foi possível carregar as filas. <a href="{{ route('admin.admin', ['contexto' => 'administrador']) }}">Tentar novamente</a></p>
    @else
        @if($webDashboard)
            <p class="admin-alerta">Consulte as áreas disponíveis para a sua conta. Os indicadores e processos em análise ainda estão indisponíveis no painel.</p>
        @else
        <p class="admin-alerta"><strong data-admin-total>{{ array_sum(array_map([\App\Support\VisualFourFixtures::class, 'pendingCount'], $queues)) }}</strong> <b>itens pendentes de revisão</b>
            @if(isset($pages['fila-eventos']))
                · <b data-admin-count="fila-eventos">{{ \App\Support\VisualFourFixtures::pendingCount($queues['fila-eventos'] ?? []) }}</b> Eventos aguardando decisão
            @endif
            @if(isset($pages['candidatos']))
                · <b data-admin-count="candidatos">{{ \App\Support\VisualFourFixtures::pendingCount($queues['candidatos'] ?? []) }}</b> registros na validação de Pessoas
            @endif
        </p>
        @endif
        <section class="admin-stats" aria-label="Filas de trabalho">
            @foreach($stats as $key => $color)
                @continue(!isset($areas[$key]))
                <article class="admin-stat admin-stat-{{ $color }}">
                    @if($webDashboard)
                        <strong aria-hidden="true">—</strong>
                        <p>{{ $areas[$key][0] }}</p>
                        <small><a href="{{ $webDashboard->adminAreaUrl($key) }}">Consultar área<span class="sr-only"> · {{ $areas[$key][0] }}</span></a></small>
                    @else
                    <strong data-admin-count="{{ $key }}">{{ \App\Support\VisualFourFixtures::pendingCount($queues[$key] ?? []) }}</strong>
                    <p>{{ $pages[$key][0] }}</p>
                    <small><a href="{{ route('admin.'.$key, $params) }}">Abrir fila<span class="sr-only"> de {{ $pages[$key][0] }}</span></a></small>
                    @endif
                </article>
            @endforeach
        </section>
    @endif

    <div class="admin-corpo">
        <div class="admin-fila">
            <nav class="admin-tabs" aria-label="Filas">
                @foreach($tabs as $key => $label)
                    @continue(!isset($areas[$key]))
                    <a href="{{ $webDashboard ? $webDashboard->adminAreaUrl($key) : route('admin.'.$key, $params) }}" @class(['is-active' => !$webDashboard && $loop->first])>{{ $label }} @if(!$webDashboard && $key !== 'denuncias')<em data-admin-count="{{ $key }}">{{ \App\Support\VisualFourFixtures::pendingCount($queues[$key] ?? []) }}</em>@endif</a>
                @endforeach
            </nav>
            @if($webDashboard)
                @foreach($areas as $key => $area)
                    <section class="admin-card" id="{{ $key }}" tabindex="-1" @if($area[1]) data-admin-capability="{{ $area[1] }}" @endif>
                        <header class="admin-card-topo"><h2>{{ $permissions[$area[1]] ?? $area[0] }}</h2></header>
                        @if($key === 'solicitacoes-lgpd')
                            <p class="admin-desc">A consulta de registros ainda está indisponível nesta área.</p>
                            <x-botao-secundario :href="$webDashboard->adminAreaUrl($key)" size="sm">Acompanhar solicitações</x-botao-secundario>
                        @else
                            <p class="admin-desc">A disponibilidade de registros e operações é indicada em cada área.</p>
                            <x-botao-secundario :href="$webDashboard->adminAreaUrl($key)" size="sm">Abrir {{ mb_strtolower($area[0]) }}</x-botao-secundario>
                        @endif
                    </section>
                @endforeach
            @elseif(isset($pages['fila-eventos']) && $variant !== 'falha')
                <p class="admin-card" data-admin-dashboard-empty @if($work) hidden @endif>Nenhum Evento aguardando decisão.</p>
                @foreach($work as $row)
                    <article class="admin-card" data-admin-dashboard-event="{{ $row['id'] }}">
                        <header class="admin-card-topo">
                            <div>
                                <h2>{{ $row['title'] }}</h2>
                                <div class="admin-tags">
                                    <span class="admin-tag admin-tag-{{ $row['ong'] ? 'verde' : 'cinza' }}">{{ $row['ong'] ? 'ONG' : 'Independente' }}</span>
                                    <span class="admin-tag-txt">{{ $row['facts']['Entidade autora'] }}</span>
                                    <span class="admin-tag admin-tag-ouro">{{ \App\Support\VisualFourFixtures::STATUSES[\App\Support\VisualFourFixtures::displayState($row)] }}</span>
                                </div>
                            </div>
                        </header>
                        <dl class="admin-meta">
                            <div>
                                <div><dt>Início</dt><dd>{{ \Illuminate\Support\Carbon::parse($row['agendas'][0]['data_inicio'])->format('d/m/Y H:i') }}</dd></div>
                                <div><dt>Vagas informativas</dt><dd>{{ $row['facts']['Vagas informativas do Evento'] }}</dd></div>
                            </div>
                            <div>
                                <div><dt>Responsável</dt><dd>{{ $row['facts']['Responsável'] }}</dd></div>
                                <div><dt>Agendas</dt><dd>{{ count($row['agendas']) }}</dd></div>
                            </div>
                        </dl>
                        <p class="admin-desc">{{ $row['description'] }}</p>
                        <footer class="admin-card-acoes">
                            <x-botao-primario :href="route('admin.fila-eventos', $params + ['registro' => $row['id']])" texto="Analisar Evento" />
                        </footer>
                    </article>
                @endforeach
            @endif
        </div>
        <aside>
            @if($webDashboard && isset($areas['log-acoes']))
                <section class="admin-widget"><h3>Atividade recente</h3><p>A consulta do histórico ainda está indisponível.</p></section>
            @elseif(isset($pages['log-acoes']) && $variant !== 'falha')
                <section class="admin-widget">
                    <h3>Atividade recente</h3>
                    <p data-admin-no-logs>Nenhuma decisão nesta navegação.</p>
                    <ul data-admin-log></ul>
                    <template data-admin-log-entry>
                        <li><span class="admin-dot admin-dot-ouro"></span><p><strong data-log-title></strong><br><span data-log-action></span></p><small><time></time></small></li>
                    </template>
                </section>
            @endif
            @if($webDashboard)
                <section class="admin-widget"><h3>Plataforma hoje</h3><p>Os indicadores ainda estão indisponíveis.</p></section>
            @elseif($variant !== 'falha')
                <section class="admin-widget">
                    <h3>Plataforma hoje</h3>
                    <dl class="admin-hoje">
                        @foreach(['candidatos|aprovado'=>'Cadastros de Pessoa aprovados','fila-eventos|publicado'=>'Eventos publicados','cadastros-ong|ativo'=>'ONGs ativas'] as $metric => $label)
                            @continue(!isset($pages[explode('|', $metric)[0]]))
                            <div><dt>{{ $label }}</dt><dd data-admin-metric="{{ $metric }}">0</dd></div>
                        @endforeach
                    </dl>
                </section>
            @endif
            <section class="admin-widget">
                <h3>Acesso rápido</h3>
                <nav class="admin-atalhos" aria-label="Atalhos administrativos">
                    @foreach($webDashboard ? array_keys($areas) : ['fila-eventos','solicitacoes-lgpd','denuncias','upgrades-org','candidatos','configuracoes'] as $key)
                        @continue(!isset($areas[$key]))
                        <x-botao-secundario :href="$webDashboard ? $webDashboard->adminAreaUrl($key) : route('admin.'.$key, $params)" :texto="$areas[$key][0]" size="sm" />
                    @endforeach
                </nav>
                @if($webDashboard)
                    <x-botao-secundario :href="route('conta')" class="mt-3" size="sm">Gerenciar minha conta</x-botao-secundario>
                @endif
            </section>
        </aside>
    </div>
    @unless($webDashboard)
        @include('admin.partials.data')
    @endunless
@endsection
