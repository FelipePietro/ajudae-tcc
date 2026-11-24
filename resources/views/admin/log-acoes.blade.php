@extends('layouts.admin')

@section('content')
    @php
        $integrated = isset($integratedAdmin);
    @endphp
    <header class="fila-cabecalho">
        <div><h1>{{ $title }}</h1><p>{{ $integrated ? 'Histórico de decisões administrativas.' : 'Decisões registradas nesta navegação.' }}</p></div>
        @if($variant !== 'falha')
            <div class="fila-resumo"><div><strong data-admin-log-count>{{ $integrated ? '—' : 0 }}</strong><span>Decisões registradas</span></div></div>
        @endif
    </header>
    @if($variant === 'falha')
        <p role="alert" class="admin-widget">Não foi possível carregar o histórico. <a href="{{ route('admin.log-acoes', ['contexto' => 'administrador']) }}">Tentar novamente</a></p>
    @else
        <div class="cand-toolbar">
            <label class="cand-busca"><span class="sr-only">Buscar por ação, administrador ou alvo</span><input type="search" @disabled($integrated) data-admin-table-search placeholder="Buscar por ação, admin ou alvo..."></label>
        </div>
        <div class="admin-tabela-wrap"><div class="admin-tabela-scroll" tabindex="0" role="region" aria-label="Log de ações">
            <table class="admin-tabela">
                <thead><tr><th scope="col">Data / hora</th><th scope="col">Administrador</th><th scope="col">Ação</th><th scope="col">Alvo</th></tr></thead>
                <tbody data-admin-log data-admin-table-rows></tbody>
            </table>
            <p class="admin-table-empty" data-admin-no-logs>{{ $integrated ? 'A consulta de decisões ainda está indisponível.' : 'Nenhuma decisão nesta navegação.' }}</p>
            <p class="admin-table-empty" data-admin-table-empty hidden>Nenhuma decisão corresponde à busca.</p>
        </div></div>
        <template data-admin-log-entry>
            <tr>
                <td><time></time></td><td data-log-actor></td>
                <td><span class="admin-tag" data-log-action></span></td>
                <td><strong data-log-title></strong><p data-log-reason hidden></p></td>
            </tr>
        </template>
    @endif
    @unless($integrated)@include('admin.partials.data')@endunless
@endsection
