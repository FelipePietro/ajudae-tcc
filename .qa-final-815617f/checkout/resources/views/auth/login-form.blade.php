@if(session('status'))<x-feedback>{{ session('status') }}</x-feedback>@endif
@if($errors->any() || isset($loginError))
    <p class="account-error" role="alert">{{ $loginError ?? $errors->first() }}</p>
@endif

@php $preferredType = old('tipo_login', request()->query('tipo') === 'ong' ? 'ong' : 'pessoa'); @endphp

<form method="POST" action="{{ route('login.store') }}">
    @csrf
    <fieldset class="account-account-types">
        <legend>Tipo de conta</legend>
        <label><input type="radio" name="tipo_login" value="pessoa" @checked($preferredType !== 'ong')> Pessoa</label>
        <label><input type="radio" name="tipo_login" value="ong" @checked($preferredType === 'ong')> ONG</label>
    </fieldset>
    <div class="account-field">
        <label for="login-name">Login</label>
        <input id="login-name" name="login" autocomplete="username" value="{{ is_string(old('login')) ? old('login') : '' }}" maxlength="64" required>
    </div>
    <div class="account-field">
        <label for="login-password">Senha</label>
        <input id="login-password" name="senha" type="password" autocomplete="current-password" required>
    </div>
    <x-botao-primario as="button" :href="null" type="submit" class="w-full">Entrar →</x-botao-primario>
</form>
