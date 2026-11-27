@extends('layouts.account')
@section('title','Editar cadastro Pessoa')
@section('content')
@if(isset($accountFields))
<div class="account-page-heading">
    <a href="{{ route('perfil') }}">← Meu perfil</a>
    <h1>Editar cadastro Pessoa</h1>
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
        <x-botao-secundario :href="route('perfil.privacidade')">Privacidade e dados</x-botao-secundario>
    </div>
</section>
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
