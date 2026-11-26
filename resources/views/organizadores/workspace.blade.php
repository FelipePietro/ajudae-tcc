@extends('layouts.organizer')
@section('title', 'Minha atuação')
@section('content')
@if(isset($organizerEntry))
    <section class="org-card">
        <div class="org-identity"><div>
            <h1 class="org-name">Minha atuação</h1>
            <p class="break-words">{{ $organizerName }} · {{ $organizerEntry }}</p>
            <p class="text-sm mt-2">{{ $organizerLinked ? 'A responsabilidade depende da atribuição pela organização.' : 'Eventos próprios, sem representar uma conta institucional.' }}</p>
        </div></div>
        <x-botao-secundario :href="route('perfil')">Minha jornada pessoal</x-botao-secundario>
    </section>
    <section class="panel mb-5" aria-labelledby="organizer-events">
        <div class="panel-header">
            <div><h2 class="panel-title" id="organizer-events">Meus eventos</h2><p class="panel-sub">Eventos sob sua responsabilidade</p></div>
            @unless($organizerLinked)<x-botao-primario :href="route('organizador.eventos.criar')">Criar Evento</x-botao-primario>@endunless
        </div>
        @include('organizadores.partials.web-events', ['assign' => false])
        <x-botao-secundario :href="route('organizador.eventos')" class="mt-5">Ver gestão de Eventos</x-botao-secundario>
    </section>
    <x-organizer-links integrated :current-organization="$activeInstitution" />
@else
<section class="org-card"><div class="org-identity"><div><h1 class="org-name">Minha atuação</h1><p>{{ $work['person']['nm_pessoa'] }} · Organizador {{ $work['linked'] ? 'vinculado' : 'independente' }}</p><p class="text-sm mt-2">{{ $work['linked'] ? $work['organizations'][0]['nome_fantasia'].' · Somente Eventos sob sua responsabilidade.' : 'Eventos próprios, sem representar uma conta institucional.' }}</p></div></div><x-botao-secundario :href="route('perfil',['area'=>'privada','contexto'=>$work['actingContext'],'jornada'=>$work['scenario']])">Minha jornada pessoal</x-botao-secundario></section>
<section class="panel mb-5"><div class="panel-header"><div><h2 class="panel-title">Meus eventos</h2><p class="panel-sub">{{ count($work['visibleEvents']) }} Evento(s) sob sua responsabilidade.</p></div><x-botao-primario :href="route('ong.eventos.criar',['contexto'=>$work['actingContext'],'jornada'=>$work['scenario']])">Criar Evento</x-botao-primario></div>
<div class="organizer-event-grid mt-5">@forelse($work['visibleEvents'] as $event)<x-organizer-event-card :event="$event" :work="$work" />@empty<p>{{ $work['linked'] ? 'Nenhum Evento atribuído. A organização define as responsabilidades; um vínculo não concede acesso aos demais Eventos.' : 'Nenhum Evento próprio para gerenciar. Você pode preparar seu primeiro Evento.' }}</p>@endforelse</div>
<x-botao-secundario :href="route('ong.eventos.index',['contexto'=>$work['actingContext'],'jornada'=>$work['scenario']])" class="mt-5">Ver gestão de Eventos</x-botao-secundario></section>
<x-organizer-links :work="$work" />
@endif
@endsection
