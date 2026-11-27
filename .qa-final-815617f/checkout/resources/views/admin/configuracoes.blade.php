@extends('layouts.admin')

@section('content')
    <header class="fila-cabecalho"><div><h1>{{ $title }}</h1><p>Perfil e permissões da atuação atual.</p></div></header>
    <div class="cfg-grid">
        <section class="admin-widget">
            <h3>Perfil do administrador</h3>
            <div class="cfg-field"><label for="admin-role">Cargo</label><input class="cfg-input" id="admin-role" value="Administrador" readonly></div>
            <div class="cfg-field"><label for="admin-access">Atuação</label><input class="cfg-input" id="admin-access" value="{{ match($variant) {'revisao' => 'Revisão', 'privacidade' => 'Consulta LGPD', default => 'Acesso integral'} }}" readonly></div>
        </section>
        <section class="admin-widget">
            <h3>Notificações</h3>
            <div class="cfg-row"><div class="cfg-row-info"><strong>Filas de revisão</strong><small>Consulte as pendências no painel.</small></div></div>
            <x-botao-secundario :href="route('admin.admin', $params)" texto="Ver painel" size="sm" />
        </section>
        <section class="admin-widget">
            <h3>Moderação</h3>
            <div class="cfg-row"><div class="cfg-row-info"><strong>Publicação de Eventos</strong><small>Requer aprovação administrativa.</small></div></div>
            <ul class="admin-review-list">
                @foreach($pages as $key => [$label, $permission])
                    @continue(!$permission || $key === 'usuarios')
                    <li>{{ $label }}</li>
                @endforeach
            </ul>
        </section>
        <section class="admin-widget">
            <h3>Segurança</h3>
            @isset($integratedAdmin)<x-botao-secundario :href="route('conta')" size="sm">Segurança da conta</x-botao-secundario>@endisset
            <div class="cfg-row"><div class="cfg-row-info"><strong>Acessos restritos</strong><small>As permissões são definidas para a conta administrativa.</small></div></div>
            @unless(in_array($variant, ['revisao','privacidade']))
                <ul class="admin-review-list"><li>Reprovação definitiva de Pessoa</li><li>Conclusão de apadrinhamento</li><li>Resposta a solicitações LGPD</li></ul>
            @endunless
        </section>
    </div>
@endsection
