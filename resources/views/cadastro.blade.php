@extends('layouts.account')
@section('title', 'Cadastro de Pessoa')
@section('content')
<x-demo-scenarios :demo="$demo" />
<x-account-wizard :demo="$demo" :sections="$sections" />
@endsection
