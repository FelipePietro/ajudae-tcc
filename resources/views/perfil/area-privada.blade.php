@extends('layouts.account')
@section('title', 'Minha jornada')
@section('content')
@if(isset($ownProfile))
<div data-own-profile>
    <section class="account-profile-hero">
        <div class="account-avatar" aria-hidden="true">{{ $webNavigation->initials }}</div>
        <div>
            <span class="account-eyebrow">Meus dados pessoais</span>
            <h1 class="break-words">{{ $ownProfile['name'] }}</h1>
            @if($ownProfile['place'])<p>{{ $ownProfile['place'] }}</p>@endif
        </div>
        <div class="account-profile-actions" role="group" aria-label="Ações do meu perfil">
            <x-botao-secundario :href="route('perfil.editar')">Meu cadastro</x-botao-secundario>
            @if($ownProfile['publicUrl'])<x-botao-secundario :href="$ownProfile['publicUrl']">Ver perfil público</x-botao-secundario>@endif
            <x-botao-secundario :href="route('conta')">Segurança da conta</x-botao-secundario>
        </div>
    </section>
    <div class="account-profile-grid">
        <aside class="account-profile-nav" aria-label="Seções do perfil">
            <a href="#resumo" data-profile-tab>Visão geral</a>
            @if($ownProfile['approved'])
                <a href="#confiabilidade" data-profile-tab>Confiabilidade</a>
                <a href="#meu-xp" data-profile-tab>Meu XP</a>
            @endif
            <a href="{{ route('perfil.privacidade') }}">Privacidade e dados ↗</a>
            <a href="#preferencias" data-profile-tab>Preferências</a>
            <a href="#documentos" data-profile-tab>Documentos privados</a>
            <a href="#validacao" data-profile-tab>Validação cadastral</a>
            @if($ownProfile['volunteer'])
                <a href="#apadrinhamento" data-profile-tab>Apadrinhamento</a>
                <a href="{{ route('candidaturas') }}">Minhas candidaturas ↗</a>
            @endif
            <a href="{{ route('participacoes') }}">Minhas participações ↗</a>
            <a href="{{ route('certificados.index') }}">Meus certificados ↗</a>
        </aside>
        <div>
            <section id="resumo" tabindex="-1">
                <x-registration-status :status="$ownProfile['status']" :tone="$ownProfile['statusTone']" description="Acompanhe a situação do seu cadastro." />
                <div class="account-card">
                    <div class="account-section-heading"><h2>Seu cadastro</h2><span class="account-badge info">Dados pessoais</span></div>
                    <dl class="account-review">
                        <div><dt>Nome</dt><dd>{{ $ownProfile['name'] }}</dd></div>
                        <div><dt>Contato privado</dt><dd class="break-all">{{ $ownProfile['email'] ?: 'Não informado' }}</dd></div>
                        <div><dt>Localidade</dt><dd>{{ $ownProfile['place'] ?: 'Não informada' }}</dd></div>
                    </dl>
                    <x-botao-secundario href="#documentos" size="sm">Conferir documentação →</x-botao-secundario>
                </div>
                @if($ownProfile['volunteer'] || $webNavigation->organizer)
                    <x-organizer-promotion-summary :navigation="$webNavigation" />
                @endif
            </section>
            <section id="preferencias" tabindex="-1" class="account-card">
                <h2>Como você quer ajudar?</h2>
                <p class="account-intro">Veja as preferências registradas no seu cadastro.</p>
                <dl class="account-review">
                    @foreach($ownProfile['preferences'] as $category => $values)
                        <div><dt>{{ $category }}</dt><dd>{{ $values ? implode(', ', $values) : 'Nenhuma registrada' }}</dd></div>
                    @endforeach
                </dl>
                <x-botao-secundario :href="route('perfil.preferencias')">Conferir preferências</x-botao-secundario>
            </section>
            <section id="documentos" tabindex="-1" class="account-card">
                <span class="account-eyebrow">Área privada</span>
                <h2>Seus documentos</h2>
                <p class="account-intro">A consulta e o envio de documentos pelo perfil ainda estão indisponíveis.</p>
                <ul class="account-timeline">
                    <li><strong>Identidade</strong></li>
                    <li><strong>Certidão de antecedentes</strong></li>
                    <li><strong>CNH</strong><p>Exigida somente quando aplicável.</p></li>
                </ul>
            </section>
            <section id="validacao" tabindex="-1">
                <x-registration-status :status="$ownProfile['status']" :tone="$ownProfile['statusTone']" description="Acompanhe a situação do seu cadastro." />
                <div class="account-card">
                    <h2>Acompanhe as próximas etapas</h2>
                    <ol class="account-timeline">
                        <li><strong>Cadastro</strong><p>Informações pessoais para validação.</p></li>
                        <li><strong>Documentação</strong><p>Identidade e antecedentes. CNH somente quando aplicável.</p></li>
                        <li><strong>{{ $ownProfile['status'] }}</strong><p>Situação atual informada na sua conta.</p></li>
                    </ol>
                </div>
            </section>
            @if($ownProfile['volunteer'])
                <section id="apadrinhamento" tabindex="-1" class="account-card">
                    <h2>Apadrinhamento</h2><p>A consulta de elegibilidade e de pedidos ainda está indisponível.</p>
                </section>
            @endif
            @if($ownProfile['approved'])
                <section id="confiabilidade" tabindex="-1" class="account-card">
                    <h2>Confiabilidade</h2><p>A consulta da confiabilidade ainda está indisponível nesta página.</p>
                </section>
                <section id="meu-xp" tabindex="-1" class="account-card">
                    <h2>Meu XP</h2><p>A consulta de XP e conquistas ainda está indisponível nesta página.</p>
                </section>
            @endif
        </div>
    </div>
</div>
@else
@php $personNavigation = \App\Support\NavigationContext::personParameters(); @endphp
<x-demo-scenarios :demo="$demo" />
<section class="account-profile-hero"><div class="account-avatar" aria-label="Avatar de {{ $demo['account']['nm_pessoa'] }}">LP</div><div><span class="account-eyebrow">Meus dados pessoais</span><h1>{{ $demo['account']['nm_pessoa'] }}</h1><p>{{ $demo['account']['cidade_pessoa'] }}, {{ $demo['account']['uf_pessoa'] }} · Voluntariado com propósito</p></div><div class="account-profile-actions" role="group" aria-label="Ações do meu perfil">@if($demo['private'])<x-botao-secundario :href="route('perfil.editar', ['cenario'=>$demo['scenario'], ...$personNavigation])">Meu cadastro</x-botao-secundario>@endif<x-botao-secundario :href="route('perfil', ['area'=>'publica', 'pessoa'=>$organizerWork['person']['pessoa_id'], ...$personNavigation])">Ver perfil público</x-botao-secundario></div></section>

<div class="account-profile-grid"><aside class="account-profile-nav" aria-label="Seções do perfil"><a href="#resumo" data-tab="resumo" aria-current="page">Visão geral</a>
@if($experience)
    <a href="#confiabilidade" data-tab="confiabilidade">Confiabilidade</a>
    <a href="#meu-xp" data-tab="meu-xp">Meu XP</a>
@endif
<a href="{{ route('perfil.privacidade', $personNavigation) }}">Privacidade e dados ↗</a>
<a href="#preferencias" data-tab="preferencias">Preferências</a><a href="#documentos" data-tab="documentos">Documentos privados</a><a href="#validacao" data-tab="validacao">Validação cadastral</a>@if($personNavigation['contexto'] === 'pessoa')<a href="#apadrinhamento" data-tab="apadrinhamento">Apadrinhamento</a>
@endif
@if($personNavigation['contexto'] === 'pessoa')<a href="{{ route('candidaturas', ['contexto'=>'pessoa']) }}">Minhas candidaturas ↗</a>@endif
@if(in_array(request()->query('contexto'), \App\Support\VisualFiveFixtures::PERSON_CONTEXTS, true))
    <a href="{{ \App\Support\VisualFiveFixtures::personalUrl() }}">Minhas participações ↗</a>
@endif
</aside><div>
<section data-tab-panel="resumo"><x-registration-status :demo="$demo" /><div class="account-card"><div class="account-section-heading"><h2>Seu cadastro</h2><span class="account-badge info">Dados pessoais</span></div><dl class="account-review"><div><dt>Nome</dt><dd>{{ $demo['account']['nm_pessoa'] }}</dd></div><div><dt>Contato privado</dt><dd>{{ $demo['account']['email_pessoa'] }}</dd></div><div><dt>Localidade</dt><dd>{{ $demo['account']['cidade_pessoa'] }} · {{ $demo['account']['uf_pessoa'] }}</dd></div><div><dt>Conclusão do perfil</dt><dd>{{ $demo['scenario'] === 'parcial' ? 'Contato e documentos por revisar' : 'Dados preenchidos' }}</dd></div></dl><x-botao-secundario href="#documentos" data-open-tab="documentos" size="sm">Conferir documentação →</x-botao-secundario></div><x-organizer-promotion-summary :work="$organizerWork" /></section>
<section data-tab-panel="preferencias" hidden><div class="account-card"><h2>Como você quer ajudar?</h2><p class="account-intro">Adicione ou remova preferências nesta visualização.</p><x-volunteer-preferences :demo="$demo" /><x-botao-secundario :href="route('perfil.preferencias', $personNavigation)">Alterar preferências</x-botao-secundario></div></section>
<section data-tab-panel="documentos" hidden><div class="account-card"><span class="account-eyebrow">Área privada</span><h2>Seus documentos</h2><p class="account-intro">Acompanhe cada item sem expor o conteúdo dos arquivos.</p><x-private-documents :demo="$demo" /></div></section>
<section data-tab-panel="validacao" hidden><x-registration-status :demo="$demo" /><div class="account-card"><h2>Acompanhe as próximas etapas</h2><ol class="account-timeline"><li><strong>Cadastro</strong><p>Dados pessoais apresentados para revisão.</p></li><li><strong>Documentação</strong><p>Identidade e antecedentes exigidos. CNH somente quando aplicável.</p></li><li><strong>{{ $demo['statusMeta'][0] }}</strong><p>Acompanhe a situação do seu cadastro.</p></li></ol><div class="account-note">Etapas do processo cadastral. As datas de análise serão apresentadas quando estiverem disponíveis.</div></div></section>
@if($personNavigation['contexto'] === 'pessoa')
<section class="account-card" data-tab-panel="apadrinhamento" hidden>
    <h2>Apadrinhamento</h2>
    <p>Consulte sua elegibilidade, os pedidos recebidos e as indicações que você enviou.</p>
    <div class="account-sponsorship-actions">
        <x-botao-primario :href="route('perfil', ['area'=>'privada','contexto'=>'pessoa','cenario'=>$demo['scenario'],'apadrinhamento'=>'padrinho'])">Posso ser padrinho?</x-botao-primario>
        <x-botao-secundario :href="route('perfil', ['area'=>'privada','contexto'=>'pessoa','apadrinhamento'=>'solicitante'])">Meu apadrinhamento</x-botao-secundario>
    </div>
</section>
@endif
@if($experience)
    <section data-tab-panel="confiabilidade" hidden aria-labelledby="confiabilidade-title">
        <x-participation-reliability :participation="$experience" />
    </section>
    <section data-tab-panel="meu-xp" hidden aria-labelledby="meu-xp-title">
        <x-participation-xp :experience="$experience" />
    </section>
@endif
</div></div>

@endif
@endsection
