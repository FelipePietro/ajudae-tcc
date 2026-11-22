@extends('layouts.account')
@section('title', 'Minhas participações')

@section('content')
<header class="mb-6">
    <h1 class="text-2xl font-semibold">Minhas participações</h1>
    <div class="mt-4 flex flex-wrap gap-3">
        <x-botao-secundario :href="route('certificados.index', [...\App\Support\NavigationContext::personParameters(), ...request()->only('exemplo')])">Certificados</x-botao-secundario>
        <x-botao-secundario :href="route('perfil', ['area' => 'privada', ...\App\Support\NavigationContext::personParameters(), ...request()->only('exemplo')])">Meu perfil</x-botao-secundario>
        <x-botao-secundario :href="\App\Support\VisualFiveFixtures::experienceUrl()">Meu XP</x-botao-secundario>
        <x-botao-secundario :href="route('perfil', ['area' => 'privada', ...\App\Support\NavigationContext::personParameters(), ...request()->only('exemplo')]).'#confiabilidade'">Confiabilidade</x-botao-secundario>
        @if(request()->query('contexto') === 'pessoa')
            <x-botao-secundario :href="route('candidaturas', ['contexto' => 'pessoa', 'cenario' => 'participacao', 'evento' => $participation['id'], ...request()->only('exemplo')])">Minhas candidaturas</x-botao-secundario>
        @endif
    </div>
</header>

@if($unavailable)
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
