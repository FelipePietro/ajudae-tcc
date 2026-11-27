@extends('layouts.foundation')
@section('content')
    <h1 class="text-2xl font-bold">Minha conta</h1>
    <p>{{ $type === 'pessoa' ? $account['nm_pessoa'] : $account['nome_fantasia'] }}</p>
    <x-feedback>Situação cadastral: {{ $type === 'pessoa' ? $account['status_validacao'] : $account['status_ong'] }}</x-feedback>
    <p>Você está autenticado. Os demais fluxos estão em preparação.</p>
@endsection
