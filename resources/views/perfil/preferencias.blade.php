@extends('layouts.account')
@section('title','Preferências de voluntariado')
@section('content')
@if(isset($savedPreferences))
@php $personId = request()->attributes->get('web_account')?->pessoa_id; @endphp
<div class="account-page-heading">
    <a href="{{ route('perfil') }}">← Meu perfil</a>
    <h1>Preferências de voluntariado</h1>
    <p>Adicione ou remova habilidades, causas e recursos.</p>
</div>
<div data-preferences-manager data-person-id="{{ $personId }}" data-csrf-token="{{ csrf_token() }}">
    {{-- Habilidades --}}
    <section class="account-card account-narrow" data-skills-manager>
        <h2>Habilidades</h2>
        <p class="account-intro">Escolha habilidades e indique seu nível de 1 a 5.</p>
        <div data-skills-list class="preference-list mb-4">
            <p class="account-empty">Carregando...</p>
        </div>
        <div class="preference-add-form">
            <select data-skills-catalog class="account-select">
                <option value="">Selecione uma habilidade</option>
            </select>
            <select data-skills-level class="account-select account-select-small">
                <option value="1">Nível 1</option>
                <option value="2">Nível 2</option>
                <option value="3" selected>Nível 3</option>
                <option value="4">Nível 4</option>
                <option value="5">Nível 5</option>
            </select>
            <x-botao-primario as="button" :href="null" type="button" data-skills-add size="sm">Adicionar</x-botao-primario>
        </div>
        <p data-skills-feedback role="status" class="account-hint mt-2"></p>
    </section>

    {{-- Causas --}}
    <section class="account-card account-narrow" data-causes-manager>
        <h2>Causas</h2>
        <p class="account-intro">Selecione causas com as quais você se identifica.</p>
        <div data-causes-list class="preference-list mb-4">
            <p class="account-empty">Carregando...</p>
        </div>
        <div class="preference-add-form">
            <select data-causes-catalog class="account-select">
                <option value="">Selecione uma causa</option>
            </select>
            <x-botao-primario as="button" :href="null" type="button" data-causes-add size="sm">Adicionar</x-botao-primario>
        </div>
        <p data-causes-feedback role="status" class="account-hint mt-2"></p>
    </section>

    {{-- Recursos --}}
    <section class="account-card account-narrow" data-resources-manager>
        <h2>Recursos</h2>
        <p class="account-intro">Indique recursos que você pode disponibilizar (opcional).</p>
        <div data-resources-list class="preference-list mb-4">
            <p class="account-empty">Carregando...</p>
        </div>
        <div class="preference-add-form">
            <select data-resources-catalog class="account-select">
                <option value="">Selecione um recurso</option>
            </select>
            <input type="text" data-resources-details placeholder="Detalhes (opcional)" maxlength="255" class="account-input">
            <x-botao-primario as="button" :href="null" type="button" data-resources-add size="sm">Adicionar</x-botao-primario>
        </div>
        <p data-resources-feedback role="status" class="account-hint mt-2"></p>
    </section>
</div>
<div class="account-inline-links mt-4">
    <x-botao-secundario :href="route('perfil').'#preferencias'">Voltar ao perfil</x-botao-secundario>
</div>
@else
<x-demo-scenarios :demo="$demo" />
<div class="account-page-heading"><a href="{{ \App\Support\NavigationContext::privateProfileUrl() }}">← Meu perfil</a><h1>Como você quer ajudar?</h1><p>Suas habilidades, suas causas e o que você pode compartilhar.</p></div>
<section class="account-card account-narrow"><h2 class="sr-only">Preferências de voluntariado</h2><x-volunteer-preferences :demo="$demo" /><x-botao-secundario href="{{ \App\Support\NavigationContext::privateProfileUrl() }}#preferencias">Voltar ao perfil</x-botao-secundario></section>
@endif
@endsection
