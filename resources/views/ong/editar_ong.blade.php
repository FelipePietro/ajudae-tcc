@extends('layouts.account')
@section('title','Editar cadastro da ONG')
@section('institution-navigation', '1')
@section('content')
@if(isset($accountFields))
<div class="account-page-heading">
    <a href="{{ route('ong.perfil') }}">← Cadastro institucional</a>
    <h1>Editar cadastro da ONG</h1>
    <p>Confira os dados cadastrados.</p>
</div>
<section class="account-card account-narrow">
    <h2>Dados cadastrais e contato</h2>
    <div class="account-fields mt-4">
        @foreach($accountFields as $label => $value)
            <x-account-field :field="['key' => 'registered-'.$loop->index, 'label' => $label, 'required' => false, 'readonly' => true]" :value="$value" />
        @endforeach
    </div>
    <p class="account-note">A edição dos dados cadastrais ainda está indisponível.</p>
</section>
<section class="account-card account-narrow">
    <h2>Imagem de perfil</h2><p>A troca de imagem ainda está indisponível.</p>
    <x-botao-secundario as="button" class="mt-4" disabled>Selecionar imagem</x-botao-secundario>
</section>
<section class="account-card account-narrow">
    <h2>Gerenciamento da conta</h2>
    <div class="account-actions mt-4">
        <x-botao-secundario :href="route('conta')">Segurança da conta</x-botao-secundario>
        <x-botao-secundario :href="route('ong.privacidade')">Privacidade e dados</x-botao-secundario>
    </div>
</section>
@else
<x-demo-scenarios :demo="$demo" />
<div class="account-page-heading"><a href="{{ route('ong.perfil', ['area'=>'privada', 'contexto'=>'ong', 'cenario'=>$demo['scenario']]) }}">← Cadastro institucional</a><h1>Informações que aproximam.</h1><p>Revise os dados da organização e de seu responsável.</p></div>
<div class="account-narrow">@if($demo['ongCorrectionGap'])<x-registration-status :demo="$demo" />@endif
<form data-demo-edit method="POST" action="{{ route('ong.perfil.atualizar', ['id'=>1]) }}" novalidate autocomplete="off">@csrf @method('PUT')
@foreach($sections as $section)@if(isset($section['fields']))<section class="account-card"><h2>{{ $section['title'] }}</h2><p class="account-intro">{{ $section['intro'] }}</p><div class="account-fields">@foreach($section['fields'] as $field)<x-account-field :field="$field" :value="$demo['account'][$field['key']] ?? ''" />@endforeach</div></section>@endif @endforeach
<div class="account-wizard-actions"><x-botao-secundario :href="route('ong.perfil')">Cancelar</x-botao-secundario><x-botao-primario as="button" :href="null" type="submit" data-demo-edit-save>Revisar informações</x-botao-primario></div><p data-edit-feedback role="status" class="account-hint"></p></form>
<div class="account-note">A documentação do responsável é privada. Alterações nesse documento não estão disponíveis nesta página.</div><x-botao-declined :href="route('ong.perfil.excluir', ['contexto'=>'ong'])" size="sm">Consultar exclusão da conta</x-botao-declined></div>
<div class="account-inline-links"><x-botao-secundario :href="route('ong.privacidade', ['contexto'=>'ong'])">Privacidade e dados</x-botao-secundario></div>
@endif
@endsection
