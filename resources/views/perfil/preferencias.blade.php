@extends('layouts.account')
@section('title','Preferências de voluntariado')
@section('content')
@if(isset($savedPreferences))
<div class="account-page-heading"><h1>Preferências de voluntariado</h1><p>Confira as preferências registradas no seu cadastro.</p></div>
<section class="account-card account-narrow">
    @foreach($savedPreferences as $category => $values)
        <section class="account-preferences-group">
            <h2>{{ $category }}</h2>
            <p>{{ $values ? implode(', ', $values) : 'Nenhuma registrada' }}</p>
        </section>
    @endforeach
    <p class="account-note">A edição de preferências ainda está indisponível.</p>
    <x-botao-secundario :href="route('perfil').'#preferencias'">Voltar ao perfil</x-botao-secundario>
</section>
@else
<x-demo-scenarios :demo="$demo" />
<div class="account-page-heading"><a href="{{ \App\Support\NavigationContext::privateProfileUrl() }}">← Meu perfil</a><h1>Como você quer ajudar?</h1><p>Suas habilidades, suas causas e o que você pode compartilhar.</p></div>
<section class="account-card account-narrow"><h2 class="sr-only">Preferências de voluntariado</h2><x-volunteer-preferences :demo="$demo" /><x-botao-secundario href="{{ \App\Support\NavigationContext::privateProfileUrl() }}#preferencias">Voltar ao perfil</x-botao-secundario></section>
@endif
@endsection
