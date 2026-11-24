@extends('layouts.identity')
@section('title', 'Minha conta')
@section('content')
<header class="account-page-heading">
    <h1>Segurança da conta</h1>
    @if($name)<p class="break-words">{{ $name }}</p>@endif
</header>
@if($errors->any())
    <p class="account-status danger" role="alert">{{ $errors->first() }}</p>
@endif
<div class="space-y-5">
    @if($pendingDeletion)
        <section class="account-status danger" aria-labelledby="deletion-heading">
            <div>
                <h2 id="deletion-heading">Exclusão solicitada</h2>
                <p>Você pode cancelar até {{ $deadline->format('d/m/Y H:i') }} ({{ $deadline->timezoneName }}).</p>
                <form method="POST" action="{{ route('identity.deletion.cancel') }}" class="mt-4">
                    @csrf
                    @method('PATCH')
                    <x-botao-secundario as="button" :href="null" type="submit">Cancelar exclusão</x-botao-secundario>
                </form>
            </div>
        </section>
    @else
        <section class="account-card">
            <h2>Situação cadastral</h2>
            <p>{{ ucfirst(str_replace('_', ' ', $accountStatus ?? '')) }}</p>
        </section>
        <div @class(['account-two-columns' => $canRequestDeletion])>
        <section class="account-card">
            <h2>Alterar senha</h2>
            <form method="POST" action="{{ route('identity.password') }}">
                @csrf
                <div class="account-field">
                    <label for="current-password">Senha atual</label>
                    <input id="current-password" name="senha_atual" type="password" autocomplete="current-password" required>
                </div>
                <div class="account-field">
                    <label for="new-password">Nova senha</label>
                    <input id="new-password" name="senha" type="password" autocomplete="new-password" minlength="8" required>
                </div>
                <div class="account-field">
                    <label for="confirm-password">Confirme a nova senha</label>
                    <input id="confirm-password" name="senha_confirmation" type="password" autocomplete="new-password" minlength="8" required>
                </div>
                <x-botao-primario as="button" :href="null" type="submit">Alterar senha</x-botao-primario>
            </form>
        </section>
        @if($canRequestDeletion)
            <section class="account-card">
                <h2>Excluir conta</h2>
                <form method="POST" action="{{ route('identity.deletion.schedule') }}">
                    @csrf
                    <label class="mb-4 block"><input type="checkbox" name="confirmar_exclusao" value="1" required> Confirmo que desejo solicitar a exclusão da conta.</label>
                    <x-botao-declined as="button" :href="null" type="submit">Solicitar exclusão</x-botao-declined>
                </form>
            </section>
        @endif
        </div>
    @endif
</div>
@endsection

