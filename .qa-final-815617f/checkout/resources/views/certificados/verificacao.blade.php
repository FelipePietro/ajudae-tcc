@extends('layouts.account')
@section('title', 'Verificar certificado')
@section('content')
@php
    $integrated = config('identity.enabled');
@endphp
<h1 class="text-2xl font-semibold mb-6">Verificar certificado</h1>
<section class="account-card" @if($integrated) data-web-certificate-verification data-url="{{ route('identity.certificates.verify') }}" @else data-certificate-verification @endif>
    <form>
        <label for="certificate-code">Código de verificação</label>
        <input id="certificate-code" name="codigo" class="mt-2 mb-4 block w-full rounded-lg border border-[#dedbd1] bg-white p-3" required maxlength="{{ $integrated ? 64 : 36 }}" autocomplete="off" spellcheck="false">
        <x-botao-primario as="button" type="submit">Verificar</x-botao-primario>
    </form>
    <p class="mt-4" data-verification-message role="status">Informe o código que consta no certificado.</p>
    <article class="mt-6" data-certificate-result hidden>
        @if($integrated)
            <dl class="grid gap-4 sm:grid-cols-2" style="overflow-wrap:anywhere">
                <div><dt class="font-semibold">Evento</dt><dd data-certificate-title></dd></div>
                <div><dt class="font-semibold">Carga horária</dt><dd data-certificate-duration></dd></div>
                <div><dt class="font-semibold">Data de emissão</dt><dd data-certificate-date></dd></div>
                <div><dt class="font-semibold">Código de verificação</dt><dd data-certificate-code></dd></div>
            </dl>
        @else
            @include('certificados.resumo')
        @endif
    </article>
    <noscript>Ative o JavaScript para verificar o certificado nesta página.</noscript>
</section>
@endsection
