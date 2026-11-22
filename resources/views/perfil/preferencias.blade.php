@extends('layouts.account')
@section('title','Preferências de voluntariado')
@section('content')
<x-demo-scenarios :demo="$demo" />
<div class="account-page-heading"><a href="{{ \App\Support\NavigationContext::privateProfileUrl() }}">← Meu perfil</a><h1>Como você quer ajudar?</h1><p>Suas habilidades, suas causas e o que você pode compartilhar.</p></div>
<section class="account-card account-narrow"><h2 class="sr-only">Preferências de voluntariado</h2><x-volunteer-preferences :demo="$demo" /><x-botao-secundario href="{{ \App\Support\NavigationContext::privateProfileUrl() }}#preferencias">Voltar ao perfil</x-botao-secundario></section>
@endsection
