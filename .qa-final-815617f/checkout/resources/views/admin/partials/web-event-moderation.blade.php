<section data-event-moderation data-url="{{ route('identity.admin.events.index') }}" data-csrf="{{ csrf_token() }}">
    <header class="fila-cabecalho">
        <div><h1>{{ $title }}</h1><p>Decida sobre os Eventos que aguardam aprovação ou reanálise.</p></div>
    </header>
    <div class="admin-widget admin-review-filters">
        <label>Buscar Evento <input type="search" class="cfg-input" maxlength="100" data-event-filter></label>
    </div>
    <p role="status" tabindex="-1" data-event-feedback></p>
    <div class="admin-fila admin-review-queue" data-moderation-list></div>
    <x-botao-secundario as="button" type="button" data-moderation-more hidden>Carregar mais</x-botao-secundario>

    <template>
        <article class="admin-card">
            <header class="admin-card-topo"><div><h2></h2><span class="admin-tag admin-tag-ouro">Aguardando análise</span></div></header>
            <details>
                <summary>Ver detalhes</summary>
                <p class="admin-desc whitespace-pre-wrap break-words" data-event-description></p>
                <dl class="admin-review-facts">
                    <div><dt>Modalidade</dt><dd data-event-mode></dd></div>
                    <div data-event-location hidden><dt>Localidade</dt><dd></dd></div>
                </dl>
                <h3>Agendas</h3>
                <ul class="admin-review-list" data-moderation-agendas></ul>
                <p data-agendas-empty hidden>Nenhuma Agenda cadastrada.</p>
                <div class="admin-card-acoes admin-review-actions">
                    <x-botao-primario as="button" type="button" data-event-approve>Aprovar publicação</x-botao-primario>
                    <x-botao-declined as="button" type="button" data-event-reject>Reprovar</x-botao-declined>
                </div>
            </details>
        </article>
    </template>

    <dialog class="ajudae-dialog" aria-labelledby="event-moderation-title">
        <h2 id="event-moderation-title"></h2>
        <p data-event-subject></p>
        <form data-event-decision>
            <label data-motive-label hidden><span>Motivo da reprovação</span>
                <textarea name="motivo" class="cfg-input" rows="3" maxlength="2000" aria-describedby="event-motive-help"></textarea>
                <small id="event-motive-help">Descreva o motivo relacionado ao Evento.</small>
            </label>
            <p role="alert" data-event-decision-error></p>
            <div class="flex flex-wrap gap-2">
                <x-botao-secundario as="button" type="button" data-event-decision-back>Voltar</x-botao-secundario>
                <x-botao-primario as="button" type="submit">Confirmar decisão</x-botao-primario>
            </div>
        </form>
    </dialog>
</section>
