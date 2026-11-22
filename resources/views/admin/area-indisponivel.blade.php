@extends('layouts.admin')
@section('title', $title)
@section('content')
    <header class="fila-cabecalho"><div><h1>{{ $title }}</h1></div></header>
    <div class="admin-fila admin-review-queue">
        <section class="admin-card">
            <p class="admin-desc">{{ $message ?? 'A consulta e as operações de '.mb_strtolower($title).' ainda estão indisponíveis nesta área.' }}</p>
            <x-botao-secundario :href="route('admin.admin')" size="sm">Voltar ao painel</x-botao-secundario>
        </section>
    </div>
@endsection
