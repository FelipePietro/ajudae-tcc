@props(['demo' => null, 'sections', 'integrated' => false])
@php($actor = $integrated ? 'pessoa' : $demo['actor'])
<section class="account-wizard" data-account-wizard data-actor="{{ $actor }}" @if($integrated) data-web-registration @endif>
    <aside class="account-wizard-rail">
        <span class="account-eyebrow">Um passo de cada vez</span><h2>Comece a fazer parte.</h2>
        <p>Conexões que transformam a comunidade começam com cuidado e confiança.</p>
        <ol aria-label="Etapas do cadastro">@foreach($sections as $index => $section)<li><button type="button" data-step-nav="{{ $index }}" @if($index) disabled @endif aria-current="{{ $index === 0 ? 'step' : 'false' }}"><span>{{ $index + 1 }}</span>{{ $section['title'] }}</button></li>@endforeach</ol>
        <div class="account-rail-note">🔒 Seus documentos e dados cadastrais não pertencem ao perfil público.</div>
    </aside>
    <div class="account-wizard-content">
        <div class="account-wizard-progress"><span data-step-count>Passo 1 de {{ count($sections) }}</span><span>Cadastro {{ $actor === 'ong' ? 'da ONG' : 'de Pessoa' }}</span></div>
        <progress data-step-progress max="{{ count($sections) }}" value="1" aria-label="Progresso do cadastro"></progress>
        <div data-wizard-error class="account-form-error" role="alert" tabindex="-1" hidden></div>
        <form method="POST" action="{{ $integrated ? route('identity.registration.store') : ($actor === 'ong' ? route('ong.cadastrar') : route('cadastro.pessoa.finalizar')) }}" @unless($integrated) data-demo-registration onsubmit="return false;" autocomplete="off" @endunless novalidate>
            @csrf
            @foreach($sections as $index => $section)
                <section data-step="{{ $index }}" @if($index) hidden @endif aria-labelledby="step-heading-{{ $index }}">
                    <h1 id="step-heading-{{ $index }}" tabindex="-1">{{ $section['title'] }}</h1><p class="account-intro">{{ $section['intro'] }}</p>
                    @if(isset($section['fields']))
                        <div class="account-fields">@foreach($section['fields'] as $field)<x-account-field :field="$field" :value="!$integrated && $demo['seed'] && ($demo['scenario'] !== 'parcial' || in_array($field['key'], ['nm_pessoa','nome_fantasia'])) ? ($demo['account'][$field['key']] ?? '') : ''" />@endforeach</div>
                        @if($index === 0 && !$integrated)
                            @if(request()->boolean('inspecao'))<x-botao-secundario as="button" :href="null" type="button" data-fill-example size="sm">Preencher campos</x-botao-secundario>@endif
                            <div class="account-note" data-document data-required-selection><strong>{{ $demo['actor'] === 'ong' ? 'Logo institucional' : 'Foto de perfil' }} · obrigatória</strong><p>Selecione uma imagem para conferir o cadastro.</p><x-botao-secundario as="label" size="sm" for="profile-image">Escolher imagem local</x-botao-secundario><input id="profile-image" type="file" accept="image/jpeg,image/png,image/webp" data-local-file>@if(request()->boolean('inspecao'))<x-botao-secundario as="button" :href="null" type="button" data-file-example size="sm">Continuar sem arquivo</x-botao-secundario>@endif<p data-file-feedback role="status">Nenhuma imagem enviada.</p><x-botao-secundario as="button" :href="null" type="button" data-file-clear hidden size="sm">Descartar seleção</x-botao-secundario></div>
                        @endif
                        @if($actor === 'ong' && $index === 0)@if(request()->boolean('inspecao'))<x-botao-secundario as="button" :href="null" type="button" data-cnpj-demo>Conferir CNPJ</x-botao-secundario>@endif<p data-cnpj-feedback role="status" class="account-hint"></p>@endif
                    @elseif($section['special'] === 'preferences')
                        <x-volunteer-preferences :demo="$demo" />
                    @elseif($section['special'] === 'documents')
                        @if($integrated)
                            <div class="account-note"><p>O envio de arquivos não está disponível neste formulário. Sua conta permanece pendente da validação dos documentos.</p></div>
                            <div class="account-field">
                                <label for="codigo_padrinho">Código de indicação de padrinho (opcional)</label>
                                <input id="codigo_padrinho" name="codigo_padrinho" maxlength="64" pattern="[0-9a-fA-F]{64}" autocomplete="off" spellcheck="false" aria-describedby="codigo_padrinho-hint codigo_padrinho-error">
                                <small id="codigo_padrinho-hint">Se recebeu um código, informe-o para enviar o pedido de apadrinhamento com o cadastro.</small>
                                <small id="codigo_padrinho-error" class="account-error" data-field-error></small>
                            </div>
                        @else
                            @if($actor === 'pessoa')<x-sponsorship-referral />@endif
                            <x-private-documents :demo="$demo" :registration="true" />
                        @endif
                    @elseif($section['special'] === 'ong-documents')
                        <div class="account-document" data-document data-required-selection><div class="account-document-body"><h3>Identidade do responsável <small>Obrigatória</small></h3><p>A identificação do responsável fica na área privada. Nenhum documento será enviado nesta revisão.</p><x-botao-secundario as="label" size="sm" for="ong-document">Selecionar arquivo local</x-botao-secundario><input id="ong-document" type="file" data-local-file accept="application/pdf,image/jpeg,image/png">@if(request()->boolean('inspecao'))<x-botao-secundario as="button" :href="null" type="button" data-file-example size="sm">Continuar sem arquivo</x-botao-secundario>@endif<p data-file-feedback role="status">Nenhum documento enviado ou armazenado.</p><x-botao-secundario as="button" :href="null" type="button" data-file-clear hidden size="sm">Descartar seleção</x-botao-secundario></div></div>
                    @elseif($section['special'] === 'credentials')
                        <x-account-field :field="['key' => 'login_'.$actor, 'label' => 'Login da conta', 'type' => 'text', 'required' => true, 'hint' => 'Informe seu nome de login.']" :value="!$integrated && $demo['seed'] ? $demo['account']['login_'.$actor] : ''" />
                        @if($integrated)
                            <div class="account-fields">
                                <div class="account-field"><label for="senha_pessoa">Senha *</label><input id="senha_pessoa" name="senha_pessoa" type="password" required minlength="8" autocomplete="new-password" aria-describedby="senha_pessoa-hint senha_pessoa-error"><small id="senha_pessoa-hint">Mínimo de 8 caracteres.</small><small id="senha_pessoa-error" class="account-error" data-field-error></small></div>
                                <div class="account-field"><label for="senha_pessoa_confirmation">Confirmar senha *</label><input id="senha_pessoa_confirmation" name="senha_pessoa_confirmation" type="password" required minlength="8" autocomplete="new-password" aria-describedby="senha_pessoa_confirmation-error"><small id="senha_pessoa_confirmation-error" class="account-error" data-field-error></small></div>
                            </div>
                        @else
                        <div class="account-fields">
                            <div class="account-field"><label for="demo-password">Senha *</label><input id="demo-password" type="password" data-secret required minlength="8" autocomplete="off" aria-describedby="demo-password-hint demo-password-error"><small id="demo-password-hint">Mínimo de 8 caracteres.</small><small id="demo-password-error" class="account-error" data-field-error></small></div>
                            <div class="account-field"><label for="demo-password-confirm">Confirmar senha *</label><input id="demo-password-confirm" type="password" data-secret required minlength="8" autocomplete="off" aria-describedby="demo-password-confirm-error"><small id="demo-password-confirm-error" class="account-error" data-field-error></small></div>
                        </div>
                        <p class="account-note">Ao avançar ou voltar, a senha será apagada. A revisão não recupera nem mostra esses valores.</p>
                        @endif
                    @elseif($section['special'] === 'review')
                        <dl class="account-review" data-review></dl>
                        <div class="account-note">Identificadores, senhas e documentos não aparecem neste resumo. Confira os dados antes de continuar.</div>
                        @if($integrated)
                            <div class="account-field">
                                <label class="account-consent"><input type="checkbox" name="aceitou_termos" value="1" required aria-describedby="aceitou_termos-error"> Li e aceito os <a href="{{ route('termos') }}" target="_blank" rel="noopener">termos de uso</a> e li a <a href="{{ route('privacidade') }}" target="_blank" rel="noopener">política de privacidade</a>. *</label>
                                <small id="aceitou_termos-error" class="account-error" data-field-error></small>
                            </div>
                        @else
                            <label class="account-consent"><input type="checkbox" data-demo-terms> Li a apresentação dos <a href="{{ route('termos') }}" target="_blank" rel="noopener">termos de uso</a> e da <a href="{{ route('privacidade') }}" target="_blank" rel="noopener">privacidade</a> nesta revisão. *</label>
                            <p class="account-hint">A leitura nesta página não registra assinatura ou aceite.</p>
                        @endif
                    @endif
                </section>
            @endforeach
            <div class="account-wizard-actions"><x-botao-secundario as="button" :href="null" type="button" data-wizard-back disabled>← Voltar</x-botao-secundario><x-botao-primario as="button" :href="null" type="button" data-wizard-next>Continuar →</x-botao-primario></div>
        </form>
        @if($integrated)
            <section class="account-completion" data-completion hidden tabindex="-1">
                <span class="account-completion-icon" aria-hidden="true">✓</span>
                <h1>Conta criada</h1>
                <p>Entre com seu login para acompanhar a validação do cadastro.</p>
                <x-botao-primario :href="route('login')">Entrar na minha conta</x-botao-primario>
            </section>
            <noscript><x-feedback>Ative o JavaScript para preencher e enviar o cadastro.</x-feedback></noscript>
        @else
        <section class="account-completion" data-completion hidden tabindex="-1"><span class="account-completion-icon">✓</span><span class="account-eyebrow">Revisão concluída</span><h1>O começo de uma boa conexão.</h1><p>Revise sua situação cadastral a seguir. Esta revisão não cria uma conta nem envia arquivos.</p>@if($actor === 'pessoa')
            <p data-referral-complete></p>
            <p>Acompanhe sua situação cadastral.</p>
            <x-botao-primario :href="route('perfil', ['area'=>'privada','contexto'=>'pessoa','apadrinhamento'=>'solicitante'])" data-registration-sponsorship hidden>Acompanhar meu apadrinhamento →</x-botao-primario>
        @else
            <x-botao-primario :href="route('ong.aprovacao-pendente', ['cenario' => 'pendente'])">Ver situação cadastral →</x-botao-primario>
        @endif
        <x-botao-secundario :href="route('login')" size="sm">Ir para o login</x-botao-secundario></section>
        <noscript><x-feedback>Ative JavaScript para navegar nas etapas locais. Nenhuma credencial ou arquivo será enviado por este formulário.</x-feedback></noscript>
        <script type="application/json" data-demo-seed>@json($demo['account'])</script>
        @endif
    </div>
</section>
