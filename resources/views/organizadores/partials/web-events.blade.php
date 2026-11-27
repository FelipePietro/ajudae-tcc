<div data-organizer-event-list data-url="{{ route('identity.events.index', absolute: false) }}"
    @if($assign) data-links-url="{{ route('identity.organizer-links.index', absolute: false) }}" data-csrf="{{ csrf_token() }}" @endif>
    <p class="text-sm mt-3" role="status" data-event-list-feedback></p>
    <ul class="organizer-event-grid mt-5" data-organizer-event-rows></ul>
    <x-botao-secundario as="button" type="button" size="sm" data-event-list-more hidden>Carregar mais</x-botao-secundario>
    <template>
        <li class="account-card">
            <h3 class="font-semibold" data-event-name></h3>
            <p class="account-badge info" data-event-status></p>
            @if($assign)
                <p data-event-responsible class="text-sm mt-2"></p>
                <form method="post" class="mt-3 flex flex-wrap gap-3 items-end" hidden>
                    <label class="text-sm">Responsável operacional
                        <select name="pessoa_id" class="block w-full border border-[#dedbd1] rounded-xl p-3 mt-2"></select>
                    </label>
                    <x-botao-secundario as="button" type="submit" size="sm">Salvar responsável</x-botao-secundario>
                </form>
                <p data-event-result role="status" class="text-sm mt-2"></p>
            @endif
        </li>
    </template>
</div>
