@extends('layouts.account')
@section('title', 'Cadastro de Pessoa')
@section('content')
@unless($integratedRegistration ?? false)
    <x-demo-scenarios :demo="$demo" />
@endunless
<x-account-wizard :demo="$demo ?? null" :sections="$sections" :integrated="$integratedRegistration ?? false" />
@endsection
