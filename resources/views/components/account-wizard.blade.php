@props(['demo', 'sections'])
<section class="account-wizard" data-account-wizard data-actor="{{ $demo['actor'] }}">
    <aside class="account-wizard-rail">
        <span class="account-eyebrow">Um passo de cada vez</span><h2>Comece a fazer parte.</h2>
        <p>Conexões que transformam a comunidade começam com cuidado e confiança.</p>
        <ol aria-label="Etapas do cadastro">@foreach($sections as $index => $section)<li><button type="button" data-step-nav="{{ $index }}" @if($index) disabled @endif aria-current="{{ $index === 0 ? 'step' : 'false' }}"><span>{{ $index + 1 }}</span>{{ $section['title'] }}</button></li>@endforeach</ol>
        <div class="account-rail-note">🔒 Seus documentos e dados cadastrais não pertencem ao perfil público.</div>
    </aside>
    <div class="account-wizard-content">
        <div class="account-wizard-progress"><span data-step-count>Passo 1 de {{ count($sections) }}</span><span>Cadastro {{ $demo['actor'] === 'ong' ? 'da ONG' : 'de Pessoa' }}</span></div>
        <progress data-step-progress max="{{ count($sections) }}" value="1" aria-label="Progresso do cadastro"></progress>
        <div data-wizard-error class="account-form-error" role="alert" tabindex="-1" hidden></div>
        <form data-demo-registration method="POST" action="{{ $demo['actor'] === 'ong' ? route('ong.cadastrar') : route('cadastro.pessoa.finalizar') }}" onsubmit="return false;" autocomplete="off" novalidate>
            @csrf
            @foreach($sections as $index => $section)
                <section data-step="{{ $index }}" @if($index) hidden @endif aria-labelledby="step-heading-{{ $index }}">
                    <h1 id="step-heading-{{ $index }}" tabindex="-1">{{ $section['title'] }}</h1><p class="account-intro">{{ $section['intro'] }}</p>
                    @if(isset($section['fields']))
                        <div class="account-fields">@foreach($section['fields'] as $field)<x-account-field :field="$field" :value="$demo['seed'] && ($demo['scenario'] !== 'parcial' || in_array($field['key'], ['nm_pessoa','nome_fantasia'])) ? ($demo['account'][$field['key']] ?? '') : ''" />@endforeach</div>
                        @if($index === 0)
                            @if(request()->boolean('inspecao'))<x-botao-secundario as="button" :href="null" type="button" data-fill-example size="sm">Preencher campos</x-botao-secundario>@endif
                            <div class="account-note" data-document data-required-selection><strong>{{ $demo['actor'] === 'ong' ? 'Logo institucional' : 'Foto de perfil' }} · obrigatória</strong><p>Selecione uma imagem para conferir o cadastro.</p><x-botao-secundario as="label" size="sm" for="profile-image">Escolher imagem local</x-botao-secundario><input id="profile-image" type="file" accept="image/jpeg,image/png,image/webp" data-local-file>@if(request()->boolean('inspecao'))<x-botao-secundario as="button" :href="null" type="button" data-file-example size="sm">Continuar sem arquivo</x-botao-secundario>@endif<p data-file-feedback role="status">Nenhuma imagem enviada.</p><x-botao-secundario as="button" :href="null" type="button" data-file-clear hidden size="sm">Descartar seleção</x-botao-secundario></div>
                        @endif
                        @if($demo['actor'] === 'ong' && $index === 0)@if(request()->boolean('inspecao'))<x-botao-secundario as="button" :href="null" type="button" data-cnpj-demo>Conferir CNPJ</x-botao-secundario>@endif<p data-cnpj-feedback role="status" class="account-hint"></p>@endif
                    @elseif($section['special'] === 'preferences')
                        <x-volunteer-preferences :demo="$demo" />
                    @elseif($section['special'] === 'documents')
                        @if($demo['actor'] === 'pessoa')<x-sponsorship-referral />@endif
                        <x-private-documents :demo="$demo" :registration="true" />
                    @elseif($section['special'] === 'ong-documents')
                        <div class="account-document" data-document data-required-selection><div class="account-document-body"><h3>Identidade do responsável <small>Obrigatória</small></h3><p>A identificação do responsável fica na área privada. Nenhum documento será enviado nesta revisão.</p><x-botao-secundario as="label" size="sm" for="ong-document">Selecionar arquivo local</x-botao-secundario><input id="ong-document" type="file" data-local-file accept="application/pdf,image/jpeg,image/png">@if(request()->boolean('inspecao'))<x-botao-secundario as="button" :href="null" type="button" data-file-example size="sm">Continuar sem arquivo</x-botao-secundario>@endif<p data-file-feedback role="status">Nenhum documento enviado ou armazenado.</p><x-botao-secundario as="button" :href="null" type="button" data-file-clear hidden size="sm">Descartar seleção</x-botao-secundario></div></div>
                    @elseif($section['special'] === 'credentials')
                        <x-account-field :field="['key' => 'login_'.$demo['actor'], 'label' => 'Login da conta', 'type' => 'text', 'required' => true, 'hint' => 'Informe seu nome de login.']" :value="$demo['seed'] ? $demo['account']['login_'.$demo['actor']] : ''" />
                        <div class="account-fields">
                            <div class="account-field"><label for="demo-password">Senha *</label><input id="demo-password" type="password" data-secret required minlength="8" autocomplete="off" aria-describedby="demo-password-hint demo-password-error"><small id="demo-password-hint">Mínimo de 8 caracteres.</small><small id="demo-password-error" class="account-error" data-field-error></small></div>
                            <div class="account-field"><label for="demo-password-confirm">Confirmar senha *</label><input id="demo-password-confirm" type="password" data-secret required minlength="8" autocomplete="off" aria-describedby="demo-password-confirm-error"><small id="demo-password-confirm-error" class="account-error" data-field-error></small></div>
                        </div>
                        <p class="account-note">Ao avançar ou voltar, a senha será apagada. A revisão não recupera nem mostra esses valores.</p>
                    @elseif($section['special'] === 'review')
                        <dl class="account-review" data-review></dl>
                        <div class="account-note">Identificadores, senhas e documentos não aparecem neste resumo. Confira os dados antes de continuar.</div>
                        <label class="account-consent"><input type="checkbox" data-demo-terms> Li a apresentação dos <a href="{{ route('termos') }}" target="_blank" rel="noopener">termos de uso</a> e da <a href="{{ route('privacidade') }}" target="_blank" rel="noopener">privacidade</a> nesta revisão. *</label>
                        <p class="account-hint">A leitura nesta página não registra assinatura ou aceite.</p>
                    @endif
                </section>
            @endforeach
            <div class="account-wizard-actions"><x-botao-secundario as="button" :href="null" type="button" data-wizard-back disabled>← Voltar</x-botao-secundario><x-botao-primario as="button" :href="null" type="button" data-wizard-next>Continuar →</x-botao-primario></div>
        </form>
        <section class="account-completion" data-completion hidden tabindex="-1"><span class="account-completion-icon">✓</span><span class="account-eyebrow">Revisão concluída</span><h1>O começo de uma boa conexão.</h1><p>Revise sua situação cadastral a seguir. Esta revisão não cria uma conta nem envia arquivos.</p>@if($demo['actor'] === 'pessoa')
            <p data-referral-complete></p>
            <p>Acompanhe sua situação cadastral.</p>
            <x-botao-primario :href="route('perfil', ['area'=>'privada','contexto'=>'pessoa','apadrinhamento'=>'solicitante'])" data-registration-sponsorship hidden>Acompanhar meu apadrinhamento →</x-botao-primario>
        @else
            <x-botao-primario :href="route('ong.aprovacao-pendente', ['cenario' => 'pendente'])">Ver situação cadastral →</x-botao-primario>
        @endif
        <x-botao-secundario :href="route('login')" size="sm">Ir para o login</x-botao-secundario></section>
        <noscript><x-feedback>Ative JavaScript para navegar nas etapas locais. Nenhuma credencial ou arquivo será enviado por este formulário.</x-feedback></noscript>
        <script type="application/json" data-demo-seed>@json($demo['account'])</script>
    </div>
</section>
