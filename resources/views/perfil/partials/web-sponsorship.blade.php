<section id="apadrinhamento" tabindex="-1" class="account-sponsorship" data-web-sponsorship data-registration-url="{{ route('cadastro.pessoa', absolute: false) }}">
    <div class="account-card">
        <h2>Apadrinhamento</h2>
        <p>Acompanhe indicações, solicitações, vínculos e pedidos de conclusão.</p>
        <p data-sponsor-feedback role="status" tabindex="-1" class="account-hint"></p>
        <x-botao-secundario as="button" type="button" size="sm" data-sponsor-refresh>Atualizar apadrinhamento</x-botao-secundario>
    </div>

    <section class="account-card">
        <h3>Posso ser padrinho?</h3>
        <p data-sponsor-eligibility class="account-hint"></p>
        <div data-sponsor-share hidden class="account-note">
            <label class="account-field">
                Código de indicação
                <input type="text" readonly data-sponsor-code>
            </label>
            <p>Quem receber o código pode informá-lo ao criar uma conta ou durante uma correção cadastral autorizada.</p>
            <div class="account-sponsorship-actions">
                <x-botao-secundario as="button" type="button" size="sm" data-sponsor-copy="code">Copiar código</x-botao-secundario>
                <x-botao-secundario as="button" type="button" size="sm" data-sponsor-copy="link">Copiar link de cadastro</x-botao-secundario>
            </div>
            <input class="sr-only" tabindex="-1" readonly data-sponsor-link aria-label="Link de cadastro com código de indicação">
        </div>
        <div class="account-sponsorship-actions">
            <x-botao-primario as="button" type="button" size="sm" data-sponsor-issue hidden>Gerar código</x-botao-primario>
            <x-botao-declined as="button" type="button" size="sm" data-sponsor-revoke hidden>Revogar código</x-botao-declined>
        </div>
        <details class="mt-3">
            <summary>Critérios de elegibilidade</summary>
            <p>É preciso ter 60 dias de aprovação, 10 participações, média de pelo menos 4,5, nenhum bloqueio nem ausência injustificada em 90 dias, no máximo uma desistência tardia no período e vaga entre os dois vínculos ativos permitidos.</p>
        </details>
    </section>

    <section class="account-card" data-sponsor-application hidden>
        <h3>Meu apadrinhamento</h3>
        <form method="post" data-sponsor-referral hidden>
            <label class="account-field">
                Código de indicação
                <input name="codigo_padrinho" required maxlength="64" pattern="[0-9a-fA-F]{64}" autocomplete="off" spellcheck="false" aria-describedby="sponsor-referral-error">
            </label>
            <p id="sponsor-referral-error" class="account-error" role="alert"></p>
            <x-botao-primario as="button" type="submit" size="sm">Registrar indicação</x-botao-primario>
        </form>
        <div data-sponsor-directory hidden>
            <h4>Padrinhos elegíveis</h4>
            <ul class="account-sponsorship-list" data-sponsor-list="elegiveis"></ul>
            <x-botao-secundario as="button" type="button" size="sm" data-sponsor-more="elegiveis" hidden>Carregar mais</x-botao-secundario>
        </div>
    </section>

    @foreach(['recebidas' => 'Solicitações recebidas', 'solicitacoes' => 'Histórico de solicitações', 'vinculos' => 'Meus vínculos'] as $key => $title)
        <section class="account-card">
            <h3>{{ $title }}</h3>
            <ul class="account-sponsorship-list" data-sponsor-list="{{ $key }}"></ul>
            <x-botao-secundario as="button" type="button" size="sm" data-sponsor-more="{{ $key }}" hidden>Carregar mais</x-botao-secundario>
        </section>
    @endforeach

    <template data-sponsor-row>
        <li class="account-note">
            <strong data-sponsor-label></strong>
            <p data-sponsor-summary class="account-hint"></p>
            <p data-sponsor-row-feedback class="account-hint" role="status"></p>
            <div class="account-sponsorship-actions" data-sponsor-actions></div>
        </li>
    </template>
    <template data-sponsor-action>
        <x-botao-secundario as="button" type="button" size="sm"></x-botao-secundario>
    </template>
    <template data-sponsor-vinculo>
        <p data-sponsor-progress></p>
        <div data-sponsor-completion hidden>
            <x-botao-primario as="button" type="button" size="sm">Solicitar conclusão</x-botao-primario>
        </div>
        <form method="post" data-sponsor-end hidden class="mt-3">
            <label class="account-field">Motivo do encerramento
                <textarea name="motivo" required rows="2" maxlength="2000"></textarea>
            </label>
            <x-botao-declined as="button" type="submit" size="sm">Encerrar vínculo</x-botao-declined>
        </form>
    </template>
</section>
