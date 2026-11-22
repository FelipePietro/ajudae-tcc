@props(['demo', 'registration' => false])
<div class="account-document-list">
    @foreach($demo['documents'] as $document)
        @php $status = $registration ? null : $document['status']; @endphp
        <article class="account-document" data-document @if(!$registration) data-document-review @endif @if($registration && $document['required']) data-required-selection @endif>
            <div class="account-document-icon" aria-hidden="true">▤</div>
            <div class="account-document-body">
                <h3>{{ $document['label'] }} <small>{{ $document['required'] ? 'Obrigatório' : 'Opcional' }}</small></h3>
                <span data-document-status class="account-badge {{ $status === 'aprovado' ? 'success' : ($status === 'rejeitado' ? 'danger' : 'warning') }}">{{ ['aprovado' => 'Validado', 'rejeitado' => 'Precisa de correção', 'pendente' => $demo['status'] === 'em_analise' ? 'Em análise' : 'Aguardando análise'][ $status ?? '' ] ?? 'Ainda não selecionado' }}</span>
                @if(!$registration && $document['rejection_reason'])<p>{{ $document['rejection_reason'] }}</p>@endif
                @if(!$registration && $document['expires_at'])<p class="account-error">Certidão vencida em 01/10/2026. A renovação pendente não revoga a aprovação global.</p>@endif
                <p class="account-muted">PDF, JPEG ou PNG · até 10 MiB. O arquivo selecionado não é enviado por esta página.</p>
                <x-botao-secundario as="label" size="sm" for="{{ $registration ? 'wizard-' : '' }}file-{{ $document['tipo'] }}">{{ $status ? 'Selecionar substituição' : 'Selecionar arquivo' }}</x-botao-secundario>
                <input id="{{ $registration ? 'wizard-' : '' }}file-{{ $document['tipo'] }}" type="file" accept="application/pdf,image/jpeg,image/png" data-local-file aria-describedby="{{ $registration ? 'wizard-' : '' }}file-{{ $document['tipo'] }}-feedback">
                <p id="{{ $registration ? 'wizard-' : '' }}file-{{ $document['tipo'] }}-feedback" data-file-feedback role="status">Nenhum arquivo enviado.</p>
                @if($registration)
                    @if(request()->boolean('inspecao'))
                        <x-botao-secundario as="button" :href="null" type="button" data-file-example size="sm">Continuar sem arquivo</x-botao-secundario>
                    @endif
                @else
                    <x-botao-primario as="button" :href="null" type="button" data-file-send hidden>Revisar documento</x-botao-primario>
                @endif
                <x-botao-secundario as="button" :href="null" type="button" data-file-clear hidden size="sm">Descartar seleção</x-botao-secundario>
            </div>
        </article>
    @endforeach
</div>
<div class="account-note"><strong>Documentos pertencem à área privada.</strong><p>A política prevê apagar o arquivo 30 dias após a validação e renovar antecedentes a cada 12 meses. A revisão não envia nem remove arquivos. CNH só é necessária para atividade de direção que a exigir.</p></div>
