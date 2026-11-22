@extends('layouts.account')
@section('title', $title)
@section('institution-navigation', '1')
@section('content')
    <x-breadcrumb-ong :raiz-href="route($backRoute)" :raiz-label="$backRoute === 'ong.painel' ? 'Dashboard' : 'Minha organização'" :itens="[['label' => $title]]" />
    <header class="account-profile-hero"><div><h1>{{ $title }}</h1></div></header>
    <section class="account-card">
        <p>{{ $message }}</p>
        @if($configuration)
            <x-botao-primario :href="route('conta')">Segurança da conta</x-botao-primario>
            <x-botao-secundario :href="route('ong.privacidade')">Privacidade e dados</x-botao-secundario>
        @else
            <x-botao-secundario :href="route($backRoute)">Voltar para {{ $backRoute === 'ong.painel' ? 'o painel' : 'minha organização' }}</x-botao-secundario>
        @endif
    </section>
@endsection
