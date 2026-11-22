@extends('layouts.account')
@section('title', 'Privacidade e dados')
@if($institution)
    @section('institution-navigation', '1')
@endif
@section('content')
@php
    $integratedPrivacy = isset($privateEntry);
    $back = $integratedPrivacy ? route($institution ? 'ong.perfil' : 'perfil') : $privacy['back'];
@endphp
<section @unless($integratedPrivacy) data-privacy-root data-mode="{{ $mode }}" @endunless>
    @unless($integratedPrivacy)
    <script type="application/json" data-privacy-config>{!! json_encode($privacy, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) !!}</script>
    @endunless
    <header class="account-profile-hero"><div><span class="account-eyebrow">{{ $institution ? 'Minha organização' : 'Minha conta' }}</span><h1>Privacidade e dados</h1><p>Controle suas informações e acompanhe seus pedidos.</p></div><x-botao-secundario :href="$back">Voltar ao cadastro</x-botao-secundario></header>
    @if($integratedPrivacy)
        <div class="account-two-columns">
            <section class="account-card">
                <h2>O que fica visível?</h2>
                <p>{{ $institution ? 'Nome, descrição e atividades públicas da organização.' : 'As informações autorizadas para o perfil público.' }}</p>
                <h3>O que fica privado?</h3><p>Contatos cadastrais, documentos e pedidos sobre dados têm acesso restrito.</p>
                <a href="{{ route('privacidade') }}">Ler a política pública de privacidade</a>
            </section>
            <section class="account-card">
                <h2>Corrigir meus dados</h2><p>A edição cadastral ainda está indisponível.</p>
                @unless($institution)<x-botao-secundario :href="$back.'#documentos'">Documentos privados</x-botao-secundario>@endunless
            </section>
        </div>
        <section class="account-card"><h2>Consultar ou obter meus dados</h2><p>O envio de solicitações e a exportação de dados ainda estão indisponíveis nesta área.</p></section>
        <section class="account-card"><h2>Minhas solicitações</h2><p>O histórico de solicitações ainda está indisponível.</p></section>
        <section class="account-card"><h2>Exclusão da conta</h2><p>A solicitação pode ser acompanhada e cancelada em Segurança da conta dentro da janela de sete dias.</p>
            <x-botao-declined :href="route('conta')">Revisar exclusão</x-botao-declined>
        </section>
    @else
    <p class="account-note">Revise seus pedidos e acompanhe o histórico. O registro de uma revisão não efetiva o envio de uma solicitação ou a exclusão da conta.</p>
    <p data-privacy-feedback role="status" tabindex="-1"></p>
    @if($mode === 'home')
        <div class="account-two-columns">
            <section class="account-card"><h2>O que fica visível?</h2><p>{{ $institution ? 'Nome, descrição e atividades públicas da organização.' : 'Identidade pública, biografia, causas e resumos de participação, XP e confiabilidade permitidos.' }}</p><h3>O que fica privado?</h3><p>Contatos cadastrais, documentos, antecedentes, cartas de candidatura e pedidos sobre dados não pertencem ao perfil público. Acesso restrito ao titular e à análise autorizada necessária.</p></section>
            <section class="account-card"><h2>Corrigir meus dados</h2><p>Use a edição cadastral para os campos disponíveis. Dados de identidade sem edição exigem análise; não são alterados por um pedido automaticamente.</p><x-botao-secundario :href="$privacy['edit']">Revisar cadastro</x-botao-secundario>
            @unless($institution)<x-botao-secundario :href="$privacy['back'].'#documentos'">Documentos privados</x-botao-secundario>@endunless
            </section>
        </div>
        <section class="account-card"><h2>Consultar ou obter meus dados</h2><p>Consultar informações, pedir uma cópia e corrigir dados são ações distintas. Nenhum arquivo pessoal será exportado aqui. O pedido não representa aceite de termos ou consentimento.</p>
            <form data-privacy-request><label for="privacy-type">Tipo de solicitação</label><select id="privacy-type" class="account-input" name="tipo" required><option value="">Selecione</option><option value="acesso">Consultar ou obter meus dados</option><option value="informacao">Informações sobre o tratamento</option><option value="correcao">Solicitar análise de correção</option><option value="portabilidade">Consultar portabilidade</option></select><p>Não inclua documentos ou dados sensíveis no pedido.</p><x-botao-primario as="button" type="submit">Preparar pedido</x-botao-primario></form>
        </section>
        <x-privacy-requests />
    @endif
    <section class="account-card" data-privacy-deletion>
        <h2 data-privacy-deletion-title>Exclusão da conta</h2>
        <p data-privacy-deletion-state tabindex="-1"></p>
        <p>A janela reversível é de <strong>7 dias</strong>. Enquanto pendente, novas participações ficam suspensas e o perfil público fica indisponível. Históricos de participação, certificados e auditoria não são apagados; a conclusão futura exige análise de retenção e anonimização.</p>
        <ul data-privacy-blockers>@foreach($privacy['blockers'] as $blocker)<li>{{ $blocker }}</li>@endforeach</ul>
        @if($mode === 'home')
            <x-botao-declined :href="$privacy['delete']">Revisar exclusão</x-botao-declined>
        @else
            <form data-privacy-delete-form><label for="confirmacao-exclusao">Digite EXCLUIR para revisar a solicitação</label><input class="account-input" id="confirmacao-exclusao" autocomplete="off" required pattern="EXCLUIR" aria-describedby="privacy-delete-help"><p id="privacy-delete-help">Esta revisão não efetiva a exclusão da conta.</p><x-botao-declined as="button" type="submit" id="btn-confirmar-exclusao">Solicitar exclusão</x-botao-declined></form>
        @endif
        <x-botao-primario as="button" data-privacy-undo hidden>Cancelar exclusão</x-botao-primario>
        @if($mode !== 'home')<x-botao-secundario :href="$privacy['home']" data-privacy-return>Voltar à privacidade</x-botao-secundario>@endif
        <details><summary>Documentos e preservação do histórico</summary><p>A política prevê eliminar o binário documental 30 dias após a validação, conservando somente metadados e auditoria necessários. Antecedentes têm renovação a cada 12 meses; o vencimento não revoga automaticamente a aprovação. Esta página não elimina arquivos, não executa rotinas e não cria links de documentos.</p></details>
        <ol data-privacy-deletion-history aria-label="Histórico da conta"></ol>
    </section>
    @endif
</section>
@endsection
