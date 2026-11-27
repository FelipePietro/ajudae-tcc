@extends('layouts.identity')
@section('title', $institution ? 'Cadastro de ONG' : 'Cadastro de Pessoa')
@section('content')
<section class="account-card">
    <h1>{{ $institution ? 'Cadastro de ONG' : 'Cadastro de Pessoa' }}</h1>
    <p>O cadastro está indisponível no momento.</p>
    @if(!request()->attributes->has('web_account'))
        <p>Se você já tem uma conta, pode entrar.</p>
        <x-botao-secundario :href="route('login', ['tipo' => $institution ? 'ong' : 'pessoa'])">Entrar</x-botao-secundario>
    @endif
</section>
@endsection
