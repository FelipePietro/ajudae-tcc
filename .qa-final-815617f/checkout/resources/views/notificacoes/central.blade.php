@php
    $integrated = config('identity.enabled');
    $hasInbox = $integrated && \Illuminate\Support\Facades\Route::has('identity.notifications.index');
    $interactive = !$integrated || $hasInbox;
    $notificationContext = $notificationContext ?? $notificationConfig['context'];
@endphp
@extends($notificationContext === 'administrador' ? 'layouts.admin' : 'layouts.account')
@if($integrated && $notificationContext === 'ong')
    @section('institution-navigation', '1')
@endif
@section('title', 'Notificações')
@section('content')
@if($notificationContext === 'ong')<x-breadcrumb-ong :raiz-href="$integrated && !\App\Support\WebNavigation::for(request())->institutionalPanel ? route('ong.perfil') : null" :raiz-label="$integrated && !\App\Support\WebNavigation::for(request())->institutionalPanel ? 'Minha organização' : 'Dashboard'" :itens="[['label'=>'Notificações']]" />@endif
<section @if($notificationContext === 'administrador') class="account-page" @endif @if($notificationContext === 'ong') class="notification-institution" @endif data-notification-center
    @if($hasInbox)
        data-identity-inbox="{{ route('identity.notifications.index', absolute: false) }}"
        data-read-all="{{ route('identity.notifications.read-all', absolute: false) }}"
        data-csrf-token="{{ csrf_token() }}"
    @endif
    aria-labelledby="notification-title">
    <header class="account-heading"><p class="account-eyebrow">Sua central</p><h1 id="notification-title">Notificações</h1><p>Acompanhe avisos e próximos passos das suas atividades.</p></header>
    <div class="account-card notification-toolbar">
        <div class="account-actions"><p data-notification-summary role="status" aria-live="polite">{{ $interactive ? 'Carregando notificações…' : 'As notificações da sua conta ainda estão indisponíveis aqui.' }}</p><x-botao-secundario as="button" data-notification-all disabled>Marcar todas como lidas</x-botao-secundario></div>
        <form data-notification-filters class="notification-filters" role="search" aria-label="Filtrar notificações">
            <label>Buscar avisos<input @disabled(!$interactive) type="search" name="busca" maxlength="100" autocomplete="off" placeholder="Título ou resumo"></label>
            <label>Leitura<select @disabled(!$interactive) name="leitura"><option value="todas">Todas</option><option value="nao-lidas">Não lidas</option><option value="lidas">Lidas</option></select></label>
            <label>Tipo<select @disabled(!$interactive) name="tipo"><option value="todos">Todos os tipos</option></select></label>
            <x-botao-secundario as="button" :disabled="!$interactive" data-notification-reset>Limpar filtros</x-botao-secundario>
        </form>
    </div>
    @if($interactive)
    <p class="account-card" data-notification-message role="status" tabindex="-1">Carregando notificações…</p>
    @if($hasInbox)<p class="account-card" data-notification-feedback role="status" hidden></p>@endif
    <noscript><p class="account-card">Ative o JavaScript para consultar os avisos desta aba.</p></noscript>
    <div data-notification-list class="notification-list" aria-label="Avisos"></div>
    <div class="account-actions notification-pagination" data-notification-pagination hidden>
        <x-botao-secundario as="button" data-notification-previous>Anterior</x-botao-secundario>
        <span data-notification-page></span>
        <x-botao-secundario as="button" data-notification-next>Próxima</x-botao-secundario>
    </div>
    <template data-notification-template>
        <article class="account-card notification-card">
            <div class="notification-meta"><span class="account-badge info" data-notification-type></span><span data-notification-status></span><span data-notification-origin></span><time></time></div>
            <h2 tabindex="-1"></h2><p data-notification-body></p>
            <div class="account-actions">
                <x-botao-primario as="button" data-notification-open size="sm">Ver detalhes</x-botao-primario>
                <x-botao-secundario as="button" data-notification-read size="sm">Marcar como lida</x-botao-secundario>
            </div>
            <p data-notification-unavailable hidden>{{ $hasInbox ? 'O aviso não tem um destino disponível.' : 'Este conteúdo não está mais disponível.' }}</p>
        </article>
    </template>
    @endif
</section>
@endsection
