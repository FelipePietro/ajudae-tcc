@extends('layouts.identity')
@section('title', $publicEvent->nm_evento)
@section('content')
<x-botao-secundario :href="route('feed')" size="sm">Voltar aos eventos</x-botao-secundario>
<div class="mt-5 h-32 rounded-3xl bg-gradient-to-br from-[#174b36] to-[#4b9c73] sm:h-48" aria-hidden="true"></div>
<article class="account-card mt-5">
    <h1 class="break-words">{{ $publicEvent->nm_evento }}</h1>
    <p>{{ $publicEvent->descricao_evento }}</p>
    @php($place = implode(', ', array_filter([$publicEvent->cidade_evento, $publicEvent->uf_evento])))
    @if($place)<p>{{ $place }}</p>@endif
    <p>{{ match ($publicEvent->modalidade_evento) {'presencial' => 'Presencial', 'online' => 'Online', 'hibrido' => 'Híbrido', default => 'Modalidade não informada'} }}</p>
</article>
<section class="account-card mt-5">
    <h2>Agendas e candidaturas</h2>
    <p>A consulta de agendas e o envio de candidaturas ainda estão indisponíveis nesta página.</p>
</section>
@endsection

