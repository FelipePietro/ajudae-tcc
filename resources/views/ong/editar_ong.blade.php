@extends('layouts.account')
@section('title','Editar cadastro da ONG')
@section('institution-navigation', '1')
@section('content')
@if(isset($accountFields))
@php
    $ongId = request()->attributes->get('web_account')?->ong_id;
    $editableFields = [
        ['key' => 'descricao_ong', 'label' => 'Descrição', 'type' => 'textarea', 'required' => false, 'maxlength' => 256],
        ['key' => 'email_ong', 'label' => 'E-mail', 'type' => 'email', 'required' => true],
        ['key' => 'tel_ong', 'label' => 'Telefone', 'type' => 'tel', 'required' => true],
        ['key' => 'cep_ong', 'label' => 'CEP', 'type' => 'text', 'required' => true, 'maxlength' => 9],
        ['key' => 'logradouro_ong', 'label' => 'Logradouro', 'type' => 'text', 'required' => true],
        ['key' => 'bairro_ong', 'label' => 'Bairro', 'type' => 'text', 'required' => true],
        ['key' => 'cidade_ong', 'label' => 'Cidade', 'type' => 'text', 'required' => true],
        ['key' => 'uf_ong', 'label' => 'UF', 'type' => 'text', 'required' => true, 'maxlength' => 2],
        ['key' => 'site_url_ong', 'label' => 'Site', 'type' => 'url', 'required' => false],
    ];
@endphp
<div data-ong-profile-edit data-ong-id="{{ $ongId }}" data-csrf-token="{{ csrf_token() }}">
<div class="account-page-heading">
    <a href="{{ route('ong.perfil') }}">← Cadastro institucional</a>
    <h1>Editar cadastro da ONG</h1>
    <p>Atualize os dados institucionais.</p>
</div>
<section class="account-card account-narrow">
    <h2>Dados institucionais (somente leitura)</h2>
    <div class="account-fields mt-4">
        @foreach($accountFields as $label => $value)
            @if(in_array($label, ['Nome fantasia', 'CNPJ', 'Responsável', 'CPF do responsável']))
                <x-account-field :field="['key' => 'readonly-'.$loop->index, 'label' => $label, 'required' => false, 'readonly' => true]" :value="$value" />
            @endif
        @endforeach
    </div>
    <p class="account-note">CNPJ e dados do responsável não podem ser alterados.</p>
</section>
<section class="account-card account-narrow">
    <h2>Contato e endereço</h2>
    <form data-ong-edit-form method="POST" novalidate autocomplete="off">
        <div class="account-fields">
            @foreach($editableFields as $field)
                <div class="account-field">
                    <label for="{{ $field['key'] }}">{{ $field['label'] }}@if($field['required'])<span class="required">*</span>@endif</label>
                    @if($field['type'] === 'textarea')
                        <textarea id="{{ $field['key'] }}" name="{{ $field['key'] }}" 
                                  @if(isset($field['maxlength'])) maxlength="{{ $field['maxlength'] }}" @endif
                                  rows="3">{{ $editableData[$field['key']] ?? '' }}</textarea>
                    @else
                        <input type="{{ $field['type'] }}" id="{{ $field['key'] }}" name="{{ $field['key'] }}" 
                               value="{{ $editableData[$field['key']] ?? '' }}"
                               @if(isset($field['maxlength'])) maxlength="{{ $field['maxlength'] }}" @endif
                               @if($field['required']) required @endif>
                    @endif
                    <span data-field-error="{{ $field['key'] }}" class="account-field-error" hidden></span>
                </div>
            @endforeach
        </div>
        <div class="account-wizard-actions mt-4">
            <x-botao-secundario :href="route('ong.perfil')">Cancelar</x-botao-secundario>
            <x-botao-primario as="button" :href="null" type="submit">Salvar alterações</x-botao-primario>
        </div>
        <p data-ong-edit-feedback role="status" class="account-hint mt-2"></p>
    </form>
</section>
<section class="account-card account-narrow" data-ong-photo>
    <h2>Imagem de perfil</h2>
    <div class="account-photo-preview mb-4">
        @if(isset($photoUrl) && $photoUrl)
            <img data-photo-preview src="{{ $photoUrl }}" alt="Foto da ONG" class="account-avatar-large">
        @else
            <img data-photo-preview src="" alt="Prévia da foto da ONG" class="account-avatar-large" hidden>
            <div data-photo-placeholder class="account-avatar-placeholder">Sem foto</div>
        @endif
    </div>
    <div class="account-photo-actions">
        <x-botao-secundario as="label" size="sm" for="ong-photo-input">Selecionar imagem</x-botao-secundario>
        <input type="file" id="ong-photo-input" data-photo-input accept="image/jpeg,image/png,image/webp" class="sr-only">
        <x-botao-primario as="button" :href="null" type="button" data-photo-upload hidden size="sm">Enviar</x-botao-primario>
        <x-botao-declined as="button" :href="null" type="button" data-photo-remove :hidden="!isset($photoUrl) || !$photoUrl" size="sm">Remover</x-botao-declined>
    </div>
    <p data-photo-feedback role="status" class="account-hint mt-2">JPEG, PNG ou WebP até 5 MiB.</p>
</section>
<section class="account-card account-narrow">
    <h2>Gerenciamento da conta</h2>
    <div class="account-actions mt-4">
        <x-botao-secundario :href="route('conta')">Segurança da conta</x-botao-secundario>
        <x-botao-secundario :href="route('ong.privacidade')">Privacidade e dados</x-botao-secundario>
    </div>
</section>
</div>
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
