@extends('layouts.admin')

@section('content')
    @php($people = array_filter($queues['candidatos'] ?? [], fn ($row) => $row['kind'] === 'pessoa'))
    <header class="fila-cabecalho">
        <div><h1>{{ $title }}</h1><p>Acompanhe os cadastros de Pessoa.</p></div>
        @if($variant !== 'falha')
            <div class="fila-resumo"><div><strong data-admin-people-count>{{ count($people) - (int) array_any($people, fn ($row) => $row['id'] === \App\Support\SponsorshipFixtures::APPLICANT) }}</strong><span>Cadastros</span></div><div><strong data-admin-metric="candidatos|aprovado">0</strong><span>Aprovados</span></div></div>
        @endif
    </header>
    @if($variant === 'falha')
        <p class="admin-widget" role="alert">Não foi possível carregar a lista. <a href="{{ route('admin.usuarios', ['contexto' => 'administrador']) }}">Tentar novamente</a></p>
    @else
        <div class="cand-toolbar">
            <label class="cand-busca"><span class="sr-only">Buscar Pessoa por nome</span><input type="search" data-admin-table-search placeholder="Buscar por nome..."></label>
            <x-botao-secundario :href="route('admin.candidatos', $params)" texto="Abrir fila de validação" />
        </div>
        <div class="admin-tabela-wrap"><div class="admin-tabela-scroll" tabindex="0" role="region" aria-label="Usuários">
            <table class="admin-tabela">
                <thead><tr><th scope="col">Usuário</th><th scope="col">Papel</th><th scope="col">Nível</th><th scope="col">Eventos</th><th scope="col">Status</th><th scope="col">Ações</th></tr></thead>
                <tbody data-admin-table-rows>
                    @foreach($people as $record)
                        <tr @if($record['id'] === \App\Support\SponsorshipFixtures::APPLICANT) data-admin-referral-user hidden @endif>
                            <td><div class="tab-user"><span class="tab-user-av" aria-hidden="true">PE</span><div><strong>{{ $record['title'] }}</strong></div></div></td>
                            <td><span class="admin-tag admin-tag-cinza">Voluntário</span></td><td>—</td><td>—</td>
                            <td><span class="admin-tag admin-tag-{{ \App\Support\VisualFourFixtures::TONES[$record['status']] ?? 'ouro' }}" data-admin-user-status="{{ $record['id'] }}">{{ \App\Support\VisualFourFixtures::STATUSES[$record['status']] }}</span></td>
                            <td><x-botao-secundario :href="route('admin.candidatos', $params + ['registro' => $record['id']])" texto="Ver cadastro" size="sm" /></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="admin-table-empty" data-admin-table-empty @if($people) hidden @endif>Nenhum cadastro encontrado.</p>
        </div></div>
    @endif
    @include('admin.partials.data')
@endsection
