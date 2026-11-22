@extends('layouts.account')
@section('title','Editar cadastro da ONG')
@section('institution-navigation', '1')
@section('content')
<x-demo-scenarios :demo="$demo" />
<div class="account-page-heading"><a href="{{ route('ong.perfil', ['area'=>'privada', 'contexto'=>'ong', 'cenario'=>$demo['scenario']]) }}">← Cadastro institucional</a><h1>Informações que aproximam.</h1><p>Revise os dados da organização e de seu responsável.</p></div>
<div class="account-narrow">@if($demo['ongCorrectionGap'])<x-registration-status :demo="$demo" />@endif
<form data-demo-edit method="POST" action="{{ route('ong.perfil.atualizar', ['id'=>1]) }}" novalidate autocomplete="off">@csrf @method('PUT')
@foreach($sections as $section)@if(isset($section['fields']))<section class="account-card"><h2>{{ $section['title'] }}</h2><p class="account-intro">{{ $section['intro'] }}</p><div class="account-fields">@foreach($section['fields'] as $field)<x-account-field :field="$field" :value="$demo['account'][$field['key']] ?? ''" />@endforeach</div></section>@endif @endforeach
<div class="account-wizard-actions"><x-botao-secundario :href="route('ong.perfil')">Cancelar</x-botao-secundario><x-botao-primario as="button" :href="null" type="submit" data-demo-edit-save>Revisar informações</x-botao-primario></div><p data-edit-feedback role="status" class="account-hint"></p></form>
<div class="account-note">A documentação do responsável é privada. Alterações nesse documento não estão disponíveis nesta página.</div><x-botao-declined :href="route('ong.perfil.excluir', ['contexto'=>'ong'])" size="sm">Consultar exclusão da conta</x-botao-declined></div>
<div class="account-inline-links"><x-botao-secundario :href="route('ong.privacidade', ['contexto'=>'ong'])">Privacidade e dados</x-botao-secundario></div>
@endsection
