<div class="account-sponsorship" data-sponsorship-profile
    data-sponsorship-role="{{ $sponsorshipRole }}" data-sponsorship-scenario="{{ $sponsorshipScenario }}"
    @if($sponsorshipRole === 'solicitante') data-applicant-example="{{ $applicantExample }}"
    @else data-sponsorship-applicant="{{ $sponsorshipApplicant }}" @endif
    data-sponsorship-signup="{{ route('cadastro.pessoa', ['contexto' => 'visitante']) }}"
    data-profile-approved="{{ $demo['status'] === 'aprovado' ? 'true' : 'false' }}"
    data-profile-blocked="{{ $demo['blocked'] ? 'true' : 'false' }}"
    data-profile-state="{{ request()->query('estado') === 'falha' ? 'falha' : 'disponivel' }}">
    <p data-sponsorship-feedback role="status" tabindex="-1" class="account-hint"></p>
    <section class="account-card" data-sponsorship-error hidden>
        <h2>Apadrinhamento indisponível</h2><p>Não foi possível carregar os dados.</p>
        <x-botao-secundario :href="request()->fullUrlWithoutQuery('estado')">Tentar novamente</x-botao-secundario>
    </section>
    <p role="status" data-sponsorship-loading>Carregando apadrinhamento…</p>
    <div data-sponsorship-content hidden>
        @if($sponsorshipRole === 'solicitante')
            <section class="account-card" data-sponsorship-unregistered hidden>
                <h2>Meu apadrinhamento</h2>
                <p>Nenhuma indicação disponível. Consulte a situação cadastral para acompanhar o pedido.</p>
                <x-botao-primario :href="route('cadastro.pessoa', ['contexto' => 'visitante'])">Abrir cadastro</x-botao-primario>
            </section>
            <section class="account-card" data-sponsorship-own hidden>
                <div class="account-section-heading">
                    <h2>Meu apadrinhamento</h2>
                    <span class="account-badge info" data-sponsorship-account></span>
                </div>
                @unless($applicantExample)
                    <p class="account-hint">{{ request()->has('cadastro') ? 'Meu cadastro' : $sponsorshipApplicant }} · Situação cadastral</p>
                @endunless
                <dl class="account-review">
                    <div><dt>Situação do pedido</dt><dd data-sponsorship-status></dd></div>
                    <div data-sponsorship-mentor-row hidden><dt>Padrinho indicado</dt><dd data-sponsorship-mentor></dd></div>
                    <div data-sponsorship-requested-row hidden><dt>Pedido em</dt><dd><time data-sponsorship-requested></time></dd></div>
                    <div data-sponsorship-decided-row hidden><dt>Respondido em</dt><dd><time data-sponsorship-decided></time></dd></div>
                    <div data-sponsorship-applicant-code-row hidden><dt>Código da indicação</dt><dd><code data-sponsorship-applicant-code></code></dd></div>
                </dl>
                <p data-sponsorship-next></p>
                <p class="account-hint">O aceite inicia o vínculo. A validação do cadastro cabe à administração.</p>
                <p class="account-note" data-sponsorship-required hidden>É necessário indicar um padrinho elegível e obter seu aceite para a aprovação.</p>
                <p class="account-note" data-sponsorship-progress hidden>0 de 6 meses completos e 0 de 5 participações confirmadas durante o vínculo. A conclusão depende de revisão administrativa.</p>
                <form class="account-sponsorship-correction" data-sponsorship-correction hidden>
                    <label class="account-field" data-sponsorship-correction-field>
                        Código de indicação de padrinho
                        <input maxlength="25" autocomplete="off" spellcheck="false" aria-describedby="sponsorship-correction-error">
                    </label>
                    <x-botao-primario as="button" type="submit">Reapresentar cadastro</x-botao-primario>
                    <p id="sponsorship-correction-error" class="account-error" role="alert" hidden></p>
                </form>
                <section data-sponsorship-history-section hidden>
                    <h3>Histórico de indicações</h3>
                    <ul class="account-sponsorship-list" data-sponsorship-history></ul>
                </section>
            </section>
            <details class="account-card" data-applicant-example-links hidden>
                <summary>Cenários de acompanhamento</summary>
                <p>Consulte as situações de acompanhamento disponíveis para inspeção.</p>
                <div class="account-sponsorship-actions">
                    @php($applicantParameters = ['area' => 'privada', 'contexto' => 'pessoa', 'apadrinhamento' => 'solicitante'])
                    @foreach(\App\Support\SponsorshipFixtures::APPLICANT_EXAMPLES as $example => $label)
                        <x-botao-secundario size="sm" :href="route('perfil', $applicantParameters + ['cenario_solicitante' => $example])" :aria-current="$example === $applicantExample ? 'page' : null">{{ $label }}</x-botao-secundario>
                    @endforeach
                    @if($applicantExample)
                        <x-botao-secundario size="sm" :href="route('perfil', $applicantParameters)">Voltar ao acompanhamento</x-botao-secundario>
                    @endif
                </div>
            </details>
        @else
            <section class="account-card">
                <div class="account-section-heading"><h2>Posso ser padrinho?</h2><span class="account-badge info" data-sponsorship-eligibility></span></div>
                <div class="account-note" data-sponsorship-share hidden>
                    <h3>Compartilhe sua indicação</h3>
                    <p>Compartilhe com alguém que deseja solicitar seu apadrinhamento. O pedido só será criado após o cadastro e depende do seu aceite.</p>
                    <p><strong>Código</strong> <code data-sponsorship-code></code></p>
                    <p><a data-sponsorship-link></a></p>
                    <div class="account-sponsorship-actions">
                        <x-botao-secundario as="button" size="sm" data-sponsorship-copy="code">Copiar código</x-botao-secundario>
                        <x-botao-secundario as="button" size="sm" data-sponsorship-copy="link">Copiar link</x-botao-secundario>
                    </div>
                </div>
                <details open><summary>Critérios de elegibilidade</summary><ul class="account-sponsorship-list" data-sponsorship-criteria></ul></details>
            </section>
            <section class="account-card">
                <h2>Solicitações recebidas</h2><p data-sponsorship-no-requests>Nenhum pedido recebido.</p>
                <article data-sponsorship-received hidden>
                    <h3>{{ $sponsorshipApplicant }} deseja ser apadrinhado por você.</h3>
                    <p><time data-sponsorship-request-date></time> · <strong data-sponsorship-request-state></strong></p>
                    <p>Preferências autorizadas para esta indicação</p>
                    <ul class="account-sponsorship-list">
                        @foreach($demo['catalogs'] as $group => $options)
                            @foreach($options as $option => $label)
                                <li hidden data-sponsorship-preference data-group="{{ $group }}" data-option="{{ $option }}">{{ ucfirst($group) }} · {{ $label }}</li>
                            @endforeach
                        @endforeach
                    </ul>
                    <p data-sponsorship-no-preferences>Nenhuma preferência compartilhada.</p>
                    <div class="account-sponsorship-actions" data-sponsorship-actions hidden>
                        <x-botao-primario as="button" data-sponsorship-decide="accept">Aceitar pedido</x-botao-primario>
                        <x-botao-declined as="button" data-sponsorship-decide="decline">Recusar pedido</x-botao-declined>
                    </div>
                </article>
                <ul class="account-sponsorship-list" data-sponsorship-received-history hidden aria-label="Histórico de pedidos recebidos"></ul>
            </section>
            <section class="account-card">
                <h2>Meus apadrinhados</h2>
                <p data-sponsorship-empty-links>Nenhum vínculo registrado nesta jornada.</p>
                <ul class="account-sponsorship-list" data-sponsorship-history-links hidden>
                    <li><strong>Pessoa de Exemplo 38</strong> · <span data-sponsorship-historical-conclusion></span><br>6 de 6 meses completos · 5 de 5 participações confirmadas</li>
                    <li><strong>Pessoa de Exemplo 46</strong> · Vínculo concluído<br>7 meses · 6 participações confirmadas</li>
                    <li><strong>Pessoa de Exemplo 47</strong> · Vínculo encerrado<br>2 meses · 1 participação confirmada</li>
                </ul>
                <article data-sponsorship-active hidden>
                    <h3>{{ $sponsorshipApplicant }}</h3>
                    <p data-sponsorship-link-state></p>
                    <p data-sponsorship-link-progress></p>
                    <p class="account-hint">A conclusão requer seis meses completos, cinco participações confirmadas durante o vínculo e revisão administrativa.</p>
                </article>
            </section>
            <dialog data-sponsorship-dialog class="ajudae-dialog" aria-labelledby="sponsorship-decision">
                <x-dialog-close />
                <h2 id="sponsorship-decision"></h2><p data-sponsorship-confirm-text></p>
                <div class="account-sponsorship-actions"><x-botao-primario as="button" data-sponsorship-confirm>Confirmar decisão</x-botao-primario></div>
            </dialog>
        @endif
    </div>
</div>
