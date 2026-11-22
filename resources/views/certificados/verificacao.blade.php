@extends('layouts.account')
@section('title', 'Verificar certificado')
@section('content')
<h1 class="text-2xl font-semibold mb-6">Verificar certificado</h1>
<section class="account-card" data-certificate-verification>
    <form>
        <label for="certificate-code">Código de verificação</label>
        <input id="certificate-code" name="codigo" class="mt-2 mb-4 block w-full rounded-lg border border-[#dedbd1] bg-white p-3" required maxlength="36" autocomplete="off" spellcheck="false">
        <x-botao-primario as="button" type="submit">Verificar</x-botao-primario>
    </form>
    <p class="mt-4" data-verification-message role="status">Informe o código que consta no certificado.</p>
    <article class="mt-6" data-certificate-result hidden>@include('certificados.resumo')</article>
    <noscript>Ative o JavaScript para verificar o certificado nesta página.</noscript>
</section>
@endsection
