@extends('layouts.identity')
@section('title', $title)
@section('content')
    <header class="account-profile-hero">
        <div><h1>{{ $title }}</h1></div>
        <x-botao-secundario :href="route($backRoute)">{{ $backLabel }}</x-botao-secundario>
    </header>
    <section class="account-card"><p>{{ $message }}</p></section>
@endsection
