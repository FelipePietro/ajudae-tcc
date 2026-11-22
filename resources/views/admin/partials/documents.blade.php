<section class="admin-documents" aria-label="Documentos privados">
    <h3>Documentos privados</h3>
    @foreach(\App\Support\VisualFourFixtures::DOCUMENTS as $index => $label)
        @php
            $documentState = $record['documents'][$index];
            $documentLabel = match($documentState) {'aprovado' => 'Documento validado', 'rejeitado' => 'Documento rejeitado', default => 'Validação pendente'};
        @endphp
        <div class="admin-document">
            <div class="admin-document-title">
                <strong>{{ $label }}</strong>
                <span class="admin-tag admin-tag-{{ $tones[$documentState] ?? 'ouro' }}" data-admin-document="{{ $index }}">{{ $documentLabel }}</span>
            </div>
            <div class="admin-review-actions">
                <x-botao-secundario as="button" size="sm" texto="Visualizar documento" data-admin-preview="{{ $index }}" aria-label="Visualizar documento · {{ $label }}" aria-haspopup="dialog" />
                <div class="admin-review-actions" role="group" aria-label="Decisão · {{ $label }}">
                    <x-botao-primario as="button" size="sm" data-admin-action="doc-{{ $index }}-aprovado" texto="Validar documento" hidden />
                    <x-botao-declined as="button" size="sm" data-admin-action="doc-{{ $index }}-rejeitado" texto="Rejeitar documento" hidden />
                </div>
            </div>
        </div>
    @endforeach
</section>
