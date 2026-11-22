@empty($webNavigation)
    <x-notification-context />
@endempty
<nav class="navbar" aria-label="Navegação principal" @isset($webNavigation) data-integrated-person-navbar @endisset>

    <a aria-label="Ajudae — início" data-brand-home href="{{ isset($webNavigation) ? route('inicio') : \App\Support\NavigationContext::homeUrl() }}" class="navbar-brand">
        <span class="brand-aju">Ajud</span>
        <span class="brand-dae">ae</span>
    </a>


    <button
        type="button"
        id="navbar-toggle"
        class="navbar-toggle"
        aria-label="Abrir menu"
        aria-controls="navbar-links"
        aria-expanded="false"
    >
        ☰
    </button>


    <ul
        id="navbar-links"
        class="navbar-links"
    >

        @isset($webNavigation)
            @php
                $entries = $webNavigation->links();
                if ($webNavigation->organizer) {
                    $entries = [
                        'organizador.workspace' => 'Minha atuação',
                        'feed' => 'Feed',
                        'organizador.eventos' => 'Meus eventos',
                    ] + $entries;
                }
            @endphp
            @foreach($entries as $name => $label)
                <li><a href="{{ route($name) }}" @if(request()->routeIs($name)) class="nav-active" aria-current="page" @endif>{{ $name === 'feed' && !$webNavigation->organization ? 'Feed' : $label }}</a></li>
                @if($name === 'feed' && $webNavigation->volunteer)
                        <li><a href="{{ route('candidaturas') }}">Candidaturas</a></li>
                @endif
                @if($name === 'perfil' && !$webNavigation->organizer)
                    <li><a href="{{ route('ranking') }}">Ranking</a></li>
                @endif
            @endforeach
            @unless($webNavigation->restricted)
                @php
                    $notificationRoute = $webNavigation->organization ? 'ong.notificacoes' : 'notificacoes';
                @endphp
                <li><a href="{{ route($notificationRoute) }}" class="btn-icone notification-entry" aria-label="Notificações" @if(request()->routeIs($notificationRoute)) aria-current="page" @endif>
                    <span class="notification-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" focusable="false"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg></span>
                </a></li>
            @endunless
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-botao-secundario as="button" type="submit" size="sm">Sair</x-botao-secundario>
                </form>
            </li>
            @if($webNavigation->profile)
                <li><a href="{{ route($webNavigation->profile) }}" class="avatar" aria-label="Avatar · {{ $webNavigation->links()[$webNavigation->profile] }}">{{ $webNavigation->initials }}</a></li>
            @endif
        @else
        @php $navigation = $navigation ?? \App\Support\NavigationContext::resolve(request()->query('contexto')); @endphp
        @foreach($navigation['links'] as $link)
        <li><a href="{{ $link['href'] }}" @if($link['label']==='Notificações') data-notification-entry class="btn-icone notification-entry" aria-label="Notificações" @endif>@if($link['label']==='Notificações')<span class="notification-icon" data-notification-icon><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" focusable="false"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg></span>@else{{ $link['label'] }}@endif</a></li>
        @endforeach
        @if(!collect($navigation['links'])->contains('label', 'Notificações'))
        <li><a data-notification-entry class="notification-entry" href="{{ route('notificacoes', ['contexto'=>$navigation['key'], ...request()->only('visao')]) }}">Notificações</a></li>
        @endif
        <li><a href="{{ $navigation['key'] === 'ong' ? route('ong.perfil', ['area'=>'privada', 'contexto'=>'ong']) : collect($navigation['links'])->firstWhere('label','Meu perfil')['href'] ?? \App\Support\NavigationContext::privateProfileUrl() }}" class="avatar" aria-label="{{ $navigation['key'] === 'ong' ? 'Minha organização' : 'Meu perfil' }}">{{ $navigation['avatar'] ?? ($navigation['key']==='ong' ? 'IE' : (in_array($navigation['key'], ['pessoa','organizador','organizador-vinculado']) ? 'LP' : 'CF')) }}</a></li>
        @endisset
    </ul>

</nav>


{{-- Menu keyboard behavior is shared in navigation-accessibility.js. --}}
