@extends('layouts.account')
@section('title','Cadastro institucional')
@section('institution-navigation', '1')
@section('content')
@if(isset($ownProfile))
<x-breadcrumb-ong :raiz-href="$webNavigation->institutionalPanel ? route('ong.painel') : route('inicio')" :raiz-label="$webNavigation->institutionalPanel ? 'Dashboard' : 'Início'" :itens="[['label' => 'Minha organização']]" />
<section class="account-profile-hero">
    <div class="account-avatar" aria-hidden="true">{{ $webNavigation->initials }}</div>
    <div>
        <span class="account-eyebrow">Minha organização</span>
        <h1 class="break-words">{{ $ownProfile['name'] }}</h1>
        @if($ownProfile['place'])<p>{{ $ownProfile['place'] }}</p>@endif
    </div>
    <div class="account-profile-actions">
        <x-botao-secundario :href="route('ong.perfil.editar')">Revisar dados</x-botao-secundario>
        @if($ownProfile['publicUrl'])<x-botao-secundario :href="$ownProfile['publicUrl']">Ver perfil público</x-botao-secundario>@endif
        <x-botao-secundario :href="route('conta')">Segurança da conta</x-botao-secundario></div>
</section>
<x-registration-status :status="$ownProfile['status']" :tone="$ownProfile['statusTone']" description="Acompanhe a situação cadastral da sua organização." />
<div class="account-two-columns">
    <section class="account-card">
        <h2>Sobre a organização</h2>
        @if($ownProfile['description'])<p>{{ $ownProfile['description'] }}</p>@endif
        <dl class="account-review">
            @if($ownProfile['cnpj'])<div><dt>CNPJ</dt><dd>{{ $ownProfile['cnpj'] }}</dd></div>@endif
            <div><dt>Contato institucional</dt><dd class="break-all">{{ $ownProfile['email'] ?: 'Não informado' }}</dd></div>
            <div><dt>Localidade</dt><dd>{{ $ownProfile['place'] ?: 'Não informada' }}</dd></div>
        </dl>
    </section>
    <section class="account-card">
        <h2>Responsável institucional</h2>
        <p>A consulta e a edição dos dados do responsável ainda estão indisponíveis nesta página.</p>
        <div class="account-note">Os contatos do responsável são informações privadas.</div>
    </section>
</div>
<div class="account-inline-links">
    @if($webNavigation->institutionalPanel)
        <x-botao-secundario :href="route('ong.painel')" size="sm">Voltar ao painel</x-botao-secundario>
    @endif
    <x-botao-secundario :href="route('ong.privacidade')" size="sm">Privacidade e dados</x-botao-secundario>
</div>
@else
<x-demo-scenarios :demo="$demo" />
<section class="account-profile-hero"><div class="account-avatar">{{ \App\Support\PublicProfileFixtures::ong()['usuario']['iniciais'] }}</div><div><span class="account-eyebrow">Minha organização</span><h1>{{ $demo['account']['nome_fantasia'] }}</h1><p>Guarulhos, SP · {{ $demo['account']['email_ong'] }}</p></div><x-botao-secundario :href="route('ong.perfil.editar', ['cenario'=>$demo['scenario']])">Revisar dados</x-botao-secundario></section>
<x-registration-status :demo="$demo" />
<div class="account-two-columns"><section class="account-card"><h2>Sobre a organização</h2><p>{{ $demo['account']['descricao_ong'] }}</p><dl class="account-review"><div><dt>CNPJ</dt><dd>00.000.000/0000-00</dd></div><div><dt>Contato</dt><dd>{{ $demo['account']['email_ong'] }}</dd></div><div><dt>Localidade</dt><dd>{{ $demo['account']['cidade_ong'] }} · {{ $demo['account']['uf_ong'] }}</dd></div></dl></section><section class="account-card"><h2>Responsável institucional</h2><p>{{ $demo['account']['nome_responsavel_ong'] }}</p><p>{{ $demo['account']['email_responsavel_ong'] }}</p><div class="account-note">Os contatos do responsável ficam na área privada da organização.</div></section></div>
<div class="account-inline-links"><x-botao-secundario :href="route('ong.painel')" size="sm">Voltar ao painel</x-botao-secundario><x-botao-secundario :href="route('ong.aprovacao-pendente', ['cenario'=>$demo['scenario']])" size="sm">Acompanhar situação cadastral</x-botao-secundario></div>
<div class="account-inline-links"><x-botao-secundario :href="route('ong.privacidade', ['contexto'=>'ong'])">Privacidade e dados</x-botao-secundario></div>
@endif
@endsection
