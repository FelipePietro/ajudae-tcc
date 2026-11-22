@extends('layouts.account')
@section('title','Situação da ONG')
@section('content')
<x-demo-scenarios :demo="$demo" />
<div class="account-page-heading"><span class="account-eyebrow">Cadastro institucional</span><h1>Cada etapa, com clareza.</h1><p>Acompanhe a situação de {{ $demo['account']['nome_fantasia'] }}.</p></div>
<div class="account-narrow"><x-registration-status :demo="$demo" /><div class="account-card"><h2>O que acontece agora?</h2><p>Cadastro e aprovação são etapas diferentes. Acompanhe sua situação nesta área.</p><x-botao-secundario :href="route('ong.perfil.editar', ['cenario'=>$demo['scenario']])">Revisar informações</x-botao-secundario><div class="account-inline-links"><x-botao-secundario :href="route('ong.perfil', ['area'=>'privada', 'contexto'=>'ong', 'cenario'=>$demo['scenario']])" size="sm">Ver cadastro institucional</x-botao-secundario><x-botao-secundario :href="route('login')" size="sm">Ir para o login</x-botao-secundario></div></div></div>
@endsection
