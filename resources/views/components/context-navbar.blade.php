@props(['defaultContext' => 'pessoa', 'surface' => 'context', 'profileName' => null, 'profileUrl' => null])
@if(config('identity.enabled'))
    @php
        $webNavigation = \App\Support\WebNavigation::for(request());
    @endphp
    @if($surface === 'institution' && $webNavigation->organization && ! $webNavigation->restricted)
        <div data-nav-layout="institution">
            <x-navbar-ong :web-navigation="$webNavigation" />
        </div>
    @else
        @include('components.navbar')
        @unless($webNavigation->signedIn)
            <div data-integrated-header-spacer class="h-[68px]" aria-hidden="true"></div>
        @endunless
    @endif
@else
@php
    $navigation = \App\Support\NavigationContext::resolve(request()->query('contexto'), $defaultContext);
    if ($profileName && $profileUrl && $navigation['key'] === 'pessoa') {
        $navigation['avatar'] = mb_strtoupper(mb_substr(strtok($profileName, ' '), 0, 1).mb_substr(str($profileName)->afterLast(' '), 0, 1));
        foreach ($navigation['links'] as &$link) {
            if ($link['label'] === 'Meu perfil') $link['href'] = $profileUrl;
        }
        unset($link);
    }
    if ($surface === 'public-profile' && $navigation['layout'] === 'institution') { $navigation['layout'] = 'person'; }
@endphp
<div data-nav-journey="{{ $navigation['journey'] ?? '' }}" data-nav-surface="{{ $surface }}" data-nav-context="{{ $navigation['key'] }}" data-nav-layout="{{ $navigation['layout'] }}">
@if($navigation['layout'] === 'public')
    @include('components.navbar')
    <div class="h-[68px]" aria-hidden="true"></div>
@elseif($navigation['layout'] === 'institution')
    @vite('resources/css/painel_ong.css')
    <x-navbar-ong />
@else
    @include('components.navbar-feed', ['navigation' => $navigation])
@endif
</div>

@endif

