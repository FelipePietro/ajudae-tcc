@extends('layouts.admin')

@section('content')
    @php
        $eventRows = $queues['fila-eventos'] ?? [];
        $months = [];
        foreach (range(0, 5) as $offset) {
            $date = now('America/Sao_Paulo')->startOfMonth()->addMonths($offset);
            $months[$date->format('Y-m')] = ucfirst($date->locale('pt_BR')->translatedFormat('M'));
        }
        $organizations = array_filter($queues['cadastros-ong'] ?? [], fn ($ong) => in_array($ong['id'], array_column($eventRows, 'ong'), true));
    @endphp
    <header class="fila-cabecalho">
        <div><h1>{{ $title }}</h1><p>Visão geral dos registros acompanhados.</p></div>
    </header>
    @if($variant === 'falha')
        <p role="alert" class="admin-widget">Não foi possível carregar o relatório. <a href="{{ route('admin.relatorios', ['contexto' => 'administrador']) }}">Tentar novamente</a></p>
    @else
        <section class="rel-stats">
            @foreach(['fila-eventos|publicado' => ['laranja','Eventos publicados'], 'fila-eventos|finalizado' => ['azul','Eventos finalizados'], 'candidatos|aprovado' => ['amarelo','Cadastros de Pessoa aprovados'], 'cadastros-ong|ativo' => ['claro','ONGs ativas']] as $metric => [$color, $label])
                <article class="admin-stat admin-stat-{{ $color }}">
                    <strong data-admin-metric="{{ $metric }}">0</strong><p>{{ $label }}</p>
                    <small><a href="{{ route('admin.'.explode('|', $metric)[0], $params) }}">Consultar fila<span class="sr-only"> · {{ $label }}</span></a></small>
                </article>
            @endforeach
        </section>
        <div class="rel-grid" data-admin-report>
            <section class="admin-widget">
                <h3>Eventos publicados por mês de início</h3>
                <div class="rel-bars">
                    @foreach($months as $key => $month)
                        <div class="rel-col" data-report-month="{{ $key }}">
                            <div class="rel-track" aria-hidden="true"><div class="rel-bar"></div></div>
                            <small>{{ $month }} <span data-report-value>0</span></small>
                        </div>
                    @endforeach
                </div>
            </section>
            <section class="admin-widget">
                <h3>Eventos por categoria</h3>
                @forelse(array_unique(array_column($eventRows, 'category')) as $category)
                    <div class="rel-hbar-item" data-report-category="{{ $category }}">
                        <div class="rel-hbar-top"><b>{{ $category }}</b><span data-report-value></span></div>
                        <div class="rel-hbar" aria-hidden="true"><span></span></div>
                    </div>
                @empty
                    <p>Nenhum Evento disponível.</p>
                @endforelse
            </section>
        </div>
        <section class="admin-widget">
            <h3>ONGs por Eventos acompanhados</h3>
            <div class="admin-tabela-scroll" tabindex="0" role="region" aria-label="Eventos de ONG">
                <table class="admin-tabela">
                    <thead><tr><th scope="col">ONG</th><th scope="col">Eventos</th><th scope="col">Publicados</th><th scope="col">Finalizados</th></tr></thead>
                    <tbody>
                        @forelse($organizations as $ong)
                            <tr data-report-ong="{{ $ong['id'] }}"><td><a href="{{ route('admin.cadastros-ong', $params + ['registro' => $ong['id']]) }}">{{ $ong['title'] }}</a></td><td data-report-all>0</td><td data-report-published>0</td><td data-report-finished>0</td></tr>
                        @empty
                            <tr><td colspan="4">Nenhuma organização com Evento acompanhado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif
    @include('admin.partials.data')
@endsection
