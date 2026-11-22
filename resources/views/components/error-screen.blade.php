@props(['status'])
@php
    $messages = [
        401 => ['Entre para continuar', 'Sua sessão não está disponível. Entre novamente e retome sua navegação.'],
        403 => ['Acesso negado', 'Este conteúdo não está disponível para sua conta. Volte ao início do seu contexto para continuar.'],
        404 => ['Esse caminho não foi encontrado.', 'A página pode ter mudado de endereço ou não estar disponível. Volte ao início ou explore os eventos.'],
        409 => ['A situação mudou', 'A ação não pôde ser concluída porque o registro mudou ou já foi processado. Consulte a situação atual antes de tentar novamente.'],
        419 => ['Sessão expirada', 'A sessão deste formulário expirou. Abra a página novamente antes de revisar e enviar os dados.'],
        422 => ['Revise os dados informados', 'Não foi possível concluir a operação. Retorne ao formulário e confira os campos indicados antes de enviar novamente.'],
        500 => ['Não foi possível concluir a operação', 'Ocorreu uma falha ao processar o pedido. Consulte a situação atual antes de repetir uma ação. Se o problema continuar, tente mais tarde.'],
        503 => ['Serviço indisponível', 'Não foi possível carregar esta página agora. Tente novamente mais tarde.'],
    ];
    [$heading, $message] = $messages[$status];
    $demoNavigation = \Illuminate\Support\Facades\Route::has('feed') && \Illuminate\Support\Facades\Route::has('admin.admin');
    $context = $demoNavigation ? \App\Support\NavigationContext::resolve(request()->query('contexto'))['key'] : 'visitante';
    if (! $demoNavigation && $status === 404) {
        $message = 'A página pode ter mudado de endereço ou não estar disponível.';
    }
@endphp
<!doctype html>
<html lang="pt-BR">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $heading }} · Ajudae</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body class="min-h-screen bg-[#f7f5ef] font-outfit text-[#17392a]">
<a href="#error-main" class="account-skip">Ir para o conteúdo</a>
@if($demoNavigation)
    <x-context-navbar default-context="visitante" />
@endif
<div data-error-content @class(['lg:ml-[230px]' => $context === 'ong'])>
<main id="error-main" class="mx-auto max-w-3xl px-6 py-24 text-center" tabindex="-1" data-http-state="{{ $status }}">
    <p class="font-serif text-6xl text-[#906012]" aria-hidden="true">{{ $status }}</p>
    <h1 class="mt-6 text-3xl font-semibold">{{ $heading }}</h1>
    <p class="mt-4 text-[#56594f]">{{ $message }}</p>
    <div class="mt-8 flex flex-wrap justify-center gap-4">
        @if($status === 401 && \Illuminate\Support\Facades\Route::has('login'))
            <x-botao-primario :href="route('login', $demoNavigation ? ['contexto'=>'visitante'] : [])">Entrar</x-botao-primario>
        @endif
        @if(\Illuminate\Support\Facades\Route::has('inicio'))
            <x-botao-secundario :href="$demoNavigation ? \App\Support\NavigationContext::homeUrl() : route('inicio')">Ir para o início</x-botao-secundario>
        @endif
        @if($demoNavigation && $status === 404)
            <x-botao-secundario :href="route('feed', ['contexto'=>$context])">Explorar eventos</x-botao-secundario>
        @endif
        @if(in_array($status, [500, 503]) && request()->isMethod('GET'))
            <x-botao-secundario :href="request()->fullUrl()">Tentar novamente</x-botao-secundario>
        @endif
    </div>
</main>
@if($demoNavigation)
    <x-footer />
@endif
</div>
</body></html>
