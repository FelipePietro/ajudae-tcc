@extends('layouts.account')
@section('title', 'Apadrinhamento')
@section('content')
    <section class="account-profile-hero">
        <div><span class="account-eyebrow">Área privada</span><h1>{{ $sponsorshipName }}</h1><p>Apadrinhamento</p></div>
        <div class="account-profile-actions">
            <x-botao-secundario :href="route('perfil', ['area' => 'privada', 'contexto' => 'pessoa']).'#apadrinhamento'">Voltar</x-botao-secundario>
        </div>
    </section>
    @include('perfil.partials.apadrinhamento')
@endsection

