@extends('layouts.account')
@section('title','Editar cadastro Pessoa')
@section('content')
@if(isset($accountFields))
@php
    $personId = request()->attributes->get('web_account')?->pessoa_id;
    $editableFields = [
        ['key' => 'email_pessoa', 'label' => 'E-mail', 'type' => 'email', 'required' => true],
        ['key' => 'tele_pessoa', 'label' => 'Telefone', 'type' => 'tel', 'required' => true],
        ['key' => 'cep_pessoa', 'label' => 'CEP', 'type' => 'text', 'required' => true, 'maxlength' => 9],
        ['key' => 'logradouro_pessoa', 'label' => 'Logradouro', 'type' => 'text', 'required' => true],
        ['key' => 'compl_pessoa', 'label' => 'Complemento', 'type' => 'text', 'required' => false],
        ['key' => 'bairro_pessoa', 'label' => 'Bairro', 'type' => 'text', 'required' => true],
        ['key' => 'cidade_pessoa', 'label' => 'Cidade', 'type' => 'text', 'required' => true],
        ['key' => 'uf_pessoa', 'label' => 'UF', 'type' => 'text', 'required' => true, 'maxlength' => 2],
    ];
@endphp
<div data-person-profile-edit data-person-id="{{ $personId }}" data-csrf-token="{{ csrf_token() }}">
<div class="account-page-heading">
    <a href="{{ route('perfil') }}">← Meu perfil</a>
    <h1>Editar cadastro Pessoa</h1>
    <p>Atualize seus dados de contato e endereço.</p>
</div>
<section class="account-card account-narrow">
    <h2>Dados cadastrais (somente leitura)</h2>
    <div class="account-fields mt-4">
        @foreach($accountFields as $label => $value)
            @if(in_array($label, ['Nome', 'CPF', 'RG', 'Data de nascimento']))
                <x-account-field :field="['key' => 'readonly-'.$loop->index, 'label' => $label, 'required' => false, 'readonly' => true]" :value="$value" />
            @endif
        @endforeach
    </div>
    <p class="account-note">CPF, RG e data de nascimento não podem ser alterados.</p>
</section>
<section class="account-card account-narrow">
    <h2>Contato e endereço</h2>
    <form data-person-edit-form method="POST" novalidate autocomplete="off">
        <div class="account-fields">
            @foreach($editableFields as $field)
                <div class="account-field">
                    <label for="{{ $field['key'] }}">{{ $field['label'] }}@if($field['required'])<span class="required">*</span>@endif</label>
                    <input type="{{ $field['type'] }}" id="{{ $field['key'] }}" name="{{ $field['key'] }}" 
                           value="{{ $editableData[$field['key']] ?? '' }}"
                           @if(isset($field['maxlength'])) maxlength="{{ $field['maxlength'] }}" @endif
                           @if($field['required']) required @endif>
                    <span data-field-error="{{ $field['key'] }}" class="account-field-error" hidden></span>
                </div>
            @endforeach
        </div>
        <div class="account-wizard-actions mt-4">
            <x-botao-secundario :href="route('perfil')">Cancelar</x-botao-secundario>
            <x-botao-primario as="button" :href="null" type="submit">Salvar alterações</x-botao-primario>
        </div>
        <p data-person-edit-feedback role="status" class="account-hint mt-2"></p>
    </form>
</section>
<section class="account-card account-narrow" data-person-photo>
    <h2>Imagem de perfil</h2>
    <div class="account-photo-preview mb-4">
        @if(isset($photoUrl) && $photoUrl)
            <img data-photo-preview src="{{ $photoUrl }}" alt="Sua foto de perfil" class="account-avatar-large">
        @else
            <img data-photo-preview src="" alt="Prévia da foto de perfil" class="account-avatar-large" hidden>
            <div data-photo-placeholder class="account-avatar-placeholder">Sem foto</div>
        @endif
    </div>
    <div class="account-photo-actions">
        <x-botao-secundario as="label" size="sm" for="photo-input">Selecionar imagem</x-botao-secundario>
        <input type="file" id="photo-input" data-photo-input accept="image/jpeg,image/png,image/webp" class="sr-only">
        <x-botao-primario as="button" :href="null" type="button" data-photo-upload hidden size="sm">Enviar</x-botao-primario>
        <x-botao-declined as="button" :href="null" type="button" data-photo-remove :hidden="!isset($photoUrl) || !$photoUrl" size="sm">Remover</x-botao-declined>
    </div>
    <p data-photo-feedback role="status" class="account-hint mt-2">JPEG, PNG ou WebP até 5 MiB.</p>
</section>
<section class="account-card account-narrow">
    <h2>Gerenciamento da conta</h2>
    <div class="account-actions mt-4">
        <x-botao-secundario :href="route('conta')">Segurança da conta</x-botao-secundario>
        <x-botao-secundario :href="route('perfil.privacidade')">Privacidade e dados</x-botao-secundario>
    </div>
</section>
</div>
@else
@php $personNavigation = \App\Support\NavigationContext::personParameters(); @endphp

<x-demo-scenarios :demo="$demo" />
<div class="account-page-heading"><a href="{{ \App\Support\NavigationContext::privateProfileUrl() }}">← Meu perfil</a><h1>Cuidar dos seus dados é cuidar da sua jornada.</h1><p>Revise seus dados antes de enviar alterações.</p></div>
<section class="account-card account-narrow"><h2>Dados cadastrais e contato</h2><form data-demo-edit method="POST" action="{{ route('perfil.demo.atualizar') }}" novalidate autocomplete="off">@csrf<div class="account-fields">@foreach($editFields as $field)<x-account-field :field="$field" :value="$demo['account'][$field['key']] ?? ''" />@endforeach</div><div class="account-note">CPF e identificação foram informados no cadastro. Alterações de identificação e documentação precisam seguir a análise própria; não há campos de documento no perfil público.</div><div class="account-wizard-actions"><x-botao-secundario :href="\App\Support\NavigationContext::privateProfileUrl()">Cancelar</x-botao-secundario><x-botao-primario as="button" :href="null" type="submit" data-demo-edit-save>Revisar dados</x-botao-primario></div><p data-edit-feedback role="status" class="account-hint"></p></form></section>
<section class="account-card account-narrow"><h2>Imagem de perfil</h2><div data-document><x-botao-secundario as="label" size="sm" for="edit-photo">Selecionar imagem local</x-botao-secundario><input id="edit-photo" type="file" accept="image/jpeg,image/png,image/webp" data-local-file><p data-file-feedback role="status">Nenhuma foto enviada.</p><x-botao-secundario as="button" :href="null" type="button" data-file-clear hidden size="sm">Descartar seleção</x-botao-secundario></div></section>
<section class="account-card account-narrow"><h2>Segurança da conta</h2><p>Confira os campos antes de alterar sua senha. Esta revisão não modifica sua conta.</p><x-botao-secundario as="button" :href="null" type="button" data-open-password>Alterar senha</x-botao-secundario><dialog data-password-dialog role="dialog" aria-modal="true" class="ajudae-dialog ajudae-dialog--account account-dialog" aria-labelledby="password-dialog-title"><x-dialog-close /><h2 id="password-dialog-title">Alterar senha</h2><p>Os campos serão apagados ao fechar.</p><label for="change-secret">Nova senha</label><input id="change-secret" type="password" data-secret autocomplete="off"><label for="change-confirm">Confirmação</label><input id="change-confirm" type="password" data-secret autocomplete="off"><p data-change-feedback role="status"></p><div class="account-wizard-actions"><x-botao-primario as="button" :href="null" type="button" data-change-password>Conferir senha</x-botao-primario></div></dialog></section>
<section class="account-card account-narrow"><h2>Gerenciamento da conta</h2><p>Você pode solicitar a exclusão e cancelar o pedido durante os sete dias seguintes.</p><x-botao-declined :href="route('perfil.excluir', $personNavigation)">Excluir minha conta</x-botao-declined></section>
<div class="account-inline-links"><x-botao-secundario :href="route('perfil.privacidade', \App\Support\NavigationContext::personParameters())">Privacidade e dados</x-botao-secundario></div>
@endif
@endsection
