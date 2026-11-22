@extends('layouts.account')
@section('title', 'Meus certificados')
@section('content')
<header class="mb-6">
    <h1 class="text-2xl font-semibold">Meus certificados</h1>
    <p class="mt-2">Sua participação reconhecida, com as horas de cada Agenda concluída.</p>
    <x-botao-secundario :href="\App\Support\VisualFiveFixtures::personalUrl()" class="mt-4">Minhas participações</x-botao-secundario>
</header>
<section class="account-card" data-certificates="personal" data-verify-url="{{ route('certificados.verificar') }}">
    <h2 class="text-xl font-semibold">{{ $participation['title'] }}</h2>
    <p class="mt-3" data-certificate-reason aria-live="polite">Consultando a situação do certificado…</p>
    <p class="mt-3" data-certificate-monitoring hidden>A modalidade estudantil exige monitoria confirmada nas Agendas pertinentes. O certificado geral continua disponível.</p>
    <form data-certificate-form class="mt-4">
        <label for="certificate-modality">Modalidade do certificado</label>
        <select id="certificate-modality" name="modalidade" class="mt-2 mb-4 block w-full rounded-lg border border-[#dedbd1] bg-white p-3">
            <option value="geral">Geral</option>
            <option value="estudantil">Estudantil</option>
        </select>
        <p class="mb-4">Será gerado um único certificado por Evento. A modalidade escolhida e as horas serão preservadas.</p>
        <x-botao-primario as="button" type="submit" data-certificate-issue :disabled="true">Gerar meu certificado</x-botao-primario>
    </form>
    <p data-certificate-error role="alert" hidden>Não foi possível consultar ou guardar o certificado. Recarregue a página e tente novamente.</p>
    <article data-certificate-result tabindex="-1" hidden class="mt-6">
        @include('certificados.resumo')
        <div class="mt-6 flex flex-wrap gap-3">
            <x-botao-primario as="button" data-certificate-preview>Abrir visualização</x-botao-primario>
            <x-botao-secundario data-certificate-download>Baixar PDF</x-botao-secundario>
            <x-botao-secundario data-certificate-verify>Verificar certificado</x-botao-secundario>
        </div>
        <dialog class="account-card ajudae-dialog" data-certificate-dialog aria-labelledby="certificate-dialog-title" style="width:min(860px,calc(100% - 2rem));max-height:90vh;overflow:auto;margin:auto">
            <h2 id="certificate-dialog-title" class="text-xl font-semibold">Certificado de participação</h2>
            <div class="mt-4">@include('certificados.resumo')</div>
            <p class="mt-4">O PDF contém os mesmos dados deste certificado.</p>
            <div class="mt-4 flex flex-wrap gap-3">
                <x-botao-secundario data-certificate-pdf target="_blank" rel="noopener">Abrir PDF</x-botao-secundario>
                <x-botao-secundario as="button" data-certificate-close>Fechar</x-botao-secundario>
            </div>
        </dialog>
    </article>
    <noscript>Ative o JavaScript para consultar os certificados nesta página.</noscript>
    <script type="application/json" data-certificate-fixture>@json($participation)</script>
</section>
@endsection
