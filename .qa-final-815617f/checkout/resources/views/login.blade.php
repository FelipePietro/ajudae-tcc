@extends(($integratedLogin ?? false) ? 'layouts.identity' : 'layouts.account')
@section('title', 'Entrar')
@section('content')
<section class="account-login">
    <div class="account-login-story"><span class="account-eyebrow">Bom ter você por aqui</span><h1>Seu próximo gesto pode fazer a diferença.</h1><p>Encontre pessoas, conecte-se a causas e acompanhe sua jornada no Ajudae.</p><div class="account-story-circle" aria-hidden="true">ae<span>juntos.</span></div><a href="{{ route('inicio') }}">← Voltar ao início</a></div>
    <div class="account-login-form"><span class="account-eyebrow">Acesse sua jornada</span><h2>Boas-vindas de volta</h2><p class="account-muted">Acesse sua conta para acompanhar suas atividades.</p>
    @if($integratedLogin ?? false)
        @include('auth.login-form')
    @else
    @if(session('cadastro_sucesso'))<x-feedback>{{ session('cadastro_sucesso') }}</x-feedback>@endif
    <form id="loginForm" data-demo-login method="POST" action="{{ route('login.store') }}" autocomplete="off" novalidate>
        @csrf
        <fieldset class="account-account-types"><legend>Tipo de conta</legend><label><input type="radio" name="tipo_login" value="pessoa" checked> Pessoa <small>Voluntário ou organizador</small></label><label><input type="radio" name="tipo_login" value="ong"> ONG <small>Conta institucional</small></label></fieldset>
        <x-account-field :field="['key'=>'login', 'label'=>'Login', 'type'=>'text', 'required'=>true, 'hint'=>'Informe seu nome de login.']" value="alex.exemplo" />
        <div class="account-field"><label for="login-secret">Senha *</label><div class="account-password"><input type="password" id="login-secret" data-secret autocomplete="off" required aria-describedby="login-secret-error"><button type="button" data-password-toggle aria-label="Mostrar senha">Mostrar</button></div><small id="login-secret-error" class="account-error" data-field-error></small></div>
        <a href="#recovery-feedback" class="account-recovery-link" data-recovery aria-controls="recovery-feedback">Esqueceu a senha?</a>
        @if(request()->boolean('inspecao'))<label class="account-login-error-choice"><input type="checkbox" data-login-error> Ver mensagem de acesso inválido</label>@endif
        <div class="account-form-error" data-login-feedback role="alert" hidden tabindex="-1"></div>
        <x-botao-primario as="button" :href="null" type="submit" data-login-enter class="w-full">Entrar →</x-botao-primario>
        <p id="recovery-feedback" data-recovery-feedback class="account-hint" role="status"></p>
        <div class="account-login-divider">Ainda não faz parte?</div><x-botao-secundario :href="route('cadastro.pessoa')" size="sm">Criar cadastro Pessoa →</x-botao-secundario><x-botao-secundario :href="route('ong.cadastrar.form')" size="sm">Cadastrar uma ONG →</x-botao-secundario>
        <div data-login-targets data-pessoa="{{ route('feed', ['contexto'=>'pessoa']) }}" data-ong="{{ route('ong.painel', ['contexto'=>'ong']) }}"></div>
    </form>
    @endif
    </div>
</section>
@endsection
