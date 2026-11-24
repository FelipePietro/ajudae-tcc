@extends('layouts.account')
@section('title', 'Minhas participações')

@section('content')
@php
    $integrated = config('identity.enabled');
@endphp
<header class="mb-6">
    <h1 class="text-2xl font-semibold">Minhas participações</h1>
    <div class="mt-4 flex flex-wrap gap-3">
        <x-botao-secundario :href="$integrated ? route('certificados.index') : route('certificados.index', [...\App\Support\NavigationContext::personParameters(), ...request()->only('exemplo')])">Certificados</x-botao-secundario>
        <x-botao-secundario :href="$integrated ? route('perfil') : route('perfil', ['area' => 'privada', ...\App\Support\NavigationContext::personParameters(), ...request()->only('exemplo')])">Meu perfil</x-botao-secundario>
        <x-botao-secundario :disabled="$integrated && request()->attributes->get('web_account')->status_validacao !== 'aprovado'" :href="$integrated ? route('perfil').'#meu-xp' : \App\Support\VisualFiveFixtures::experienceUrl()">Meu XP</x-botao-secundario>
        <x-botao-secundario :disabled="$integrated && request()->attributes->get('web_account')->status_validacao !== 'aprovado'" :href="$integrated ? route('perfil').'#confiabilidade' : route('perfil', ['area' => 'privada', ...\App\Support\NavigationContext::personParameters(), ...request()->only('exemplo')]).'#confiabilidade'">Confiabilidade</x-botao-secundario>
        @if($integrated && \App\Support\WebNavigation::for(request())->volunteer)
            <x-botao-secundario :href="route('candidaturas')">Minhas candidaturas</x-botao-secundario>
        @elseif(!$integrated && request()->query('contexto') === 'pessoa')
            <x-botao-secundario :href="route('candidaturas', ['contexto' => 'pessoa', 'cenario' => 'participacao', 'evento' => $participation['id'], ...request()->only('exemplo')])">Minhas candidaturas</x-botao-secundario>
        @endif
    </div>
</header>

@if($integrated)
    <section data-web-participation data-url="{{ route('identity.participation.index') }}" data-csrf="{{ csrf_token() }}" aria-label="Histórico de participações">
        <p data-participation-status role="status"></p>
        <div data-participation-list class="grid gap-4"></div>
        <x-botao-secundario as="button" type="button" :href="null" data-participation-next hidden>Carregar mais</x-botao-secundario>
        <template data-participation-template>
            <article class="account-card">
                <h2 class="text-lg font-semibold" data-event-title></h2>
                <ul class="mt-3 grid gap-2" data-event-agendas></ul>
                <p class="mt-4" data-event-rating role="status"></p>
                <form data-event-form class="mt-4 grid gap-3" hidden>
                    <label>Nota para a ONG
                        <select name="nota" required>
                            <option value="">Selecione</option>
                            @foreach(range(1, 5) as $nota)<option value="{{ $nota }}">{{ $nota }}</option>@endforeach
                        </select>
                    </label>
                    <p data-event-deadline></p>
                    <div><x-botao-primario as="button" type="submit" :href="null" size="sm">Enviar avaliação</x-botao-primario></div>
                    <p data-event-error role="alert"></p>
                </form>
            </article>
        </template>
    </section>
@elseif($unavailable)
    <div class="account-card" role="alert">
        <p>Não foi possível carregar suas participações.</p>
        <x-botao-secundario :href="\App\Support\VisualFiveFixtures::personalUrl()" class="mt-4">Tentar novamente</x-botao-secundario>
    </div>
@elseif($participation['records'] === [])
    <div class="account-card"><p>Você ainda não tem participações para acompanhar.</p></div>
@else
    <div class="account-section-heading">
        <h2>{{ $participation['title'] }}</h2>
        <x-botao-secundario :href="route('eventos.show', ['id' => $participation['id'], 'cenario' => 'participacao', ...\App\Support\NavigationContext::personParameters(), ...request()->only('exemplo')])" size="sm">Ver evento</x-botao-secundario>
    </div>
    <x-participation-agendas :participation="$participation" />
@endif
@endsection
