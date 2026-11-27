@props(['pages' => null, 'params' => [], 'queues' => [], 'webNavigation' => null])

@php
    $pages = $webNavigation
        ? ['admin' => ['Painel admin', null, '▦']] + $webNavigation->adminAreas()
        : ($pages ?? \App\Support\VisualFourFixtures::PAGES);
@endphp
@unless($webNavigation)
    <x-notification-context />
@endunless
<input type="checkbox" id="admin-menu" class="ong-menu-check" aria-label="Menu da administração" aria-controls="admin-sidebar">
<label for="admin-menu" class="ong-menu-btn" aria-hidden="true">☰</label>
<label for="admin-menu" class="ong-menu-bg" aria-hidden="true"></label>

<aside class="ong-sidebar admin-sidebar" id="admin-sidebar" aria-label="Administração">
    <button type="button" class="nav-drawer-close" data-admin-menu-close aria-label="Fechar menu">Fechar menu ×</button>
    <a aria-label="Ajudae — início" data-brand-home href="{{ $webNavigation ? route('inicio') : ($params ? route('admin.admin', $params) : \App\Support\NavigationContext::homeUrl()) }}" class="ong-sidebar-brand">
        <span class="ong-sidebar-nome"><span class="brand-aju-white">Ajud</span><span class="brand-dae">ae</span></span>
    </a>
    <a @class(['ong-sidebar-link','is-active'=>request()->routeIs('notificacoes')]) @if(request()->routeIs('notificacoes')) aria-current="page" @endif @unless($webNavigation) data-notification-entry @endunless href="{{ route('notificacoes', $webNavigation ? [] : ['contexto'=>'administrador', ...request()->only('visao')]) }}">Notificações</a>
    <hr class="admin-sidebar-rule">
    <span class="ong-sidebar-org">Administrador</span>

    @foreach(['Moderação' => ['admin','fila-eventos','cadastros-ong','upgrades-org','candidatos','conclusoes-padrinho','denuncias','solicitacoes-lgpd','responder-lgpd'], 'Plataforma' => ['usuarios','relatorios','log-acoes','configuracoes','campanhas-xp']] as $group => $items)
        @continue(!array_intersect($items, array_keys($pages)))
        <p class="ong-sidebar-label">{{ $group }}</p>
        <nav class="ong-sidebar-nav" aria-label="{{ $group }}">
            @foreach($items as $item)
                @continue(!isset($pages[$item]))
                @php($active = request()->routeIs('admin.'.$item))
                <a href="{{ $webNavigation ? $webNavigation->adminAreaUrl($item) : route('admin.'.$item, $params) }}" @class(['ong-sidebar-link', 'is-active' => $active]) @if($active) aria-current="page" @endif>
                    <span class="ong-sidebar-ico" aria-hidden="true">{{ $pages[$item][2] }}</span>
                    {{ $item === 'admin' ? 'Painel admin' : $pages[$item][0] }}
                    @if(! $webNavigation && isset($queues[$item]) && !in_array($item, ['denuncias']))
                        <span class="ong-badge" data-admin-count="{{ $item }}" @if(!\App\Support\VisualFourFixtures::pendingCount($queues[$item])) hidden @endif>{{ \App\Support\VisualFourFixtures::pendingCount($queues[$item]) }}</span>
                    @endif
                </a>
            @endforeach
        </nav>
    @endforeach

    @if($webNavigation)
        <p class="ong-sidebar-label">Conta</p>
        <nav class="ong-sidebar-nav" aria-label="Conta">
            @foreach($webNavigation->links() as $route => $label)
                @continue(str_starts_with($route, 'admin.'))
                <a href="{{ route($route) }}" class="ong-sidebar-link">{{ $label }}</a>
            @endforeach
            <form action="{{ route('logout') }}" method="POST" class="px-4 py-2">
                @csrf
                <x-botao-dourado as="button" type="submit" size="sm">Sair</x-botao-dourado>
            </form>
        </nav>
    @endif
    <div class="ong-sidebar-user">
        <span class="ong-avatar" aria-hidden="true">AD</span>
        <div><strong>Administração</strong><small>{{ $webNavigation ? $webNavigation->name : match($params['visao'] ?? '') {'revisao' => 'Revisão', 'privacidade' => 'Consulta LGPD', default => 'Acesso integral'} }}</small></div>
    </div>
</aside>
