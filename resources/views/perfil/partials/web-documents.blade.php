<section id="documentos" tabindex="-1" class="account-card">
    <span class="account-eyebrow">Área privada</span>
    <h2>Seus documentos</h2>
    <p class="account-intro">Identidade e certidão de antecedentes são obrigatórias. Envie a CNH quando a atividade exigir.</p>
    <div class="account-document-list">
        @foreach(['documento_identidade' => 'Identidade', 'antecedentes_criminais' => 'Certidão de antecedentes', 'cnh' => 'CNH'] as $type => $label)
            <article class="account-document" data-private-document="{{ $type }}">
                <div class="account-document-icon" aria-hidden="true">▤</div>
                <div class="account-document-body">
                    <h3>{{ $label }}</h3>
                    <p class="account-badge info" data-document-state>Consultando situação…</p>
                    <p class="account-error" data-document-reason hidden></p>
                    <p class="account-hint" data-document-renewal hidden></p>
                    <x-botao-secundario href="#" size="sm" data-private-download hidden>Baixar documento</x-botao-secundario>
                    <form method="post" enctype="multipart/form-data" data-document-upload hidden class="account-field mt-3">
                        <label for="private-{{ $type }}">Arquivo de {{ $label }}</label>
                        <input id="private-{{ $type }}" type="file" name="arquivo" required accept="application/pdf,image/jpeg,image/png" aria-describedby="private-{{ $type }}-hint private-{{ $type }}-error">
                        <small id="private-{{ $type }}-hint">PDF, JPEG ou PNG, até 10 MiB. Um novo envio substitui o documento atual.</small>
                        <small id="private-{{ $type }}-error" data-document-error class="account-error" role="alert"></small>
                        <x-botao-primario as="button" type="submit" size="sm">Enviar documento</x-botao-primario>
                    </form>
                </div>
            </article>
        @endforeach
    </div>
    <p data-document-feedback role="status" class="account-hint"></p>
    <x-botao-secundario as="button" type="button" size="sm" data-document-refresh>Atualizar documentos</x-botao-secundario>
</section>
<section id="validacao" tabindex="-1">
    <x-registration-status :status="$ownProfile['status']" :tone="$ownProfile['statusTone']" description="Acompanhe a situação do seu cadastro." live />
    <div class="account-card">
        <h2>Acompanhe as próximas etapas</h2>
        <ol class="account-timeline">
            <li><strong>Cadastro</strong><p>Informações pessoais para validação.</p></li>
            <li><strong>Documentação</strong><p>Identidade e antecedentes. CNH quando aplicável.</p></li>
            <li><strong>Validação</strong><p>A administração analisa o cadastro e os documentos enviados.</p></li>
        </ol>
        <p data-validation-block class="account-note" hidden></p>
        <p data-validation-sponsor class="account-note" hidden>Para concluir sua validação, é necessário obter o aceite de um padrinho elegível.</p>
        <x-botao-primario as="button" type="button" data-validation-resubmit hidden>Reenviar para análise</x-botao-primario>
        <x-botao-secundario as="button" type="button" size="sm" data-document-refresh>Atualizar situação</x-botao-secundario>
        <p data-document-feedback role="status" class="account-hint"></p>
    </div>
</section>
