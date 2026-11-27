@php
    // These files are synthetic and outside the web root. Only this authorized Blade embeds them.
    $media = [];
    foreach (['image/png' => 'identidade.png', 'application/pdf' => 'certidao.pdf', 'pdf-image' => 'certidao.png'] as $type => $file) {
        $path = resource_path('fixtures/admin-documents/'.$file);
        $bytes = is_file($path) && is_readable($path) ? file_get_contents($path) : false;
        $media[$type] = $bytes === false ? null : 'data:'.($type === 'pdf-image' ? 'image/png' : $type).';base64,'.base64_encode($bytes);
    }
@endphp
<dialog class="ajudae-dialog admin-document-dialog" data-admin-document-dialog aria-labelledby="admin-document-title" aria-describedby="admin-document-state">
    <x-dialog-close />
    <h2 id="admin-document-title"></h2>
    <p id="admin-document-state"></p>
    <div class="admin-document-viewport" data-admin-document-body></div>
    @if($media['application/pdf'] && $media['pdf-image'])
        <x-botao-secundario as="button" size="sm" texto="Alternar visualização do PDF" data-admin-pdf-toggle hidden />
    @endif
</dialog>
<script type="application/json" data-admin-media>{!! json_encode($media, JSON_HEX_TAG | JSON_THROW_ON_ERROR) !!}</script>
