@props(['organizer' => false, 'candidatesOnly' => false, 'dashboard' => false])
<section data-managed-events data-csrf="{{ csrf_token() }}" data-organizer="{{ $organizer ? '1' : '0' }}" data-mode="{{ $dashboard ? 'dashboard' : ($candidatesOnly ? 'candidates' : 'manage') }}" data-list-url="{{ route($organizer ? 'organizador.eventos' : 'ong.eventos.index') }}" class="mt-6 space-y-4">
    @if(!$dashboard && !$candidatesOnly)
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Resumo dos Eventos carregados">
            @foreach(['ativo' => 'Publicados', 'aguardando a confirmação' => 'Aguardando aprovação', 'finalizado' => 'Realizados'] as $state => $label)
                <div class="rounded-2xl border border-[#dedbd1] bg-white p-5 shadow-sm"><p class="text-sm text-[#817c70]">{{ $label }}</p><p class="mt-2 text-3xl font-semibold text-[#17392a]" data-count="{{ $state }}">—</p></div>
            @endforeach
            <div class="rounded-2xl border border-[#dedbd1] bg-white p-5 shadow-sm"><p class="text-sm text-[#817c70]">Candidaturas pendentes</p><p class="mt-2 text-3xl font-semibold text-[#17392a]">—</p><p class="text-xs">Consulte por Agenda.</p></div>
        </section>
        <p class="text-xs text-[#817c70]">Resumo dos Eventos carregados. Use Carregar mais para consultar o restante.</p>
    @endif
    @unless($dashboard)
        <div class="flex flex-wrap gap-3 items-end">
            <label class="text-sm flex-1">Buscar por evento ou local<input data-event-search type="search" class="block w-full rounded-xl border border-[#d8d4ca] bg-white p-3" placeholder="Buscar eventos"></label>
            <label class="text-sm">Status<select data-event-filter class="block rounded-xl border border-[#d8d4ca] bg-white p-3"><option value="">Todos</option><option value="ativo">Publicados</option><option value="aguardando a confirmação">Aguardando aprovação</option><option value="reprovado">Recusados</option><option value="cancelado">Cancelados</option><option value="finalizado">Finalizados</option></select></label>
            @if($candidatesOnly)<x-botao-secundario :href="route('ong.eventos.index')">Meus eventos</x-botao-secundario>@endif
        </div>
    @endunless
    <p data-list-status role="status" aria-live="polite" class="text-sm text-[#56594f]">Carregando Eventos…</p>
    <div data-managed-list class="space-y-4"></div>
    <x-botao-secundario as="button" data-load-more hidden>Carregar mais</x-botao-secundario>
    <template data-event-card>
        <article class="rounded-2xl border border-[#dedbd1] bg-white p-5 space-y-3">
            <header class="flex flex-wrap items-center justify-between gap-2"><h2 data-title class="font-semibold text-lg text-[#17392a] break-words"></h2><span data-state class="rounded-full bg-[#eef7f2] px-3 py-1 text-sm"></span></header>
            <p data-scope class="text-sm"></p><p data-description class="text-sm break-words"></p><p data-location class="text-sm"></p><p data-agenda-summary class="text-sm"></p>
            <div data-actions class="flex flex-wrap gap-2">
                <x-botao-secundario data-public-link size="sm">Ver Evento público</x-botao-secundario>
                <x-botao-primario as="button" data-open-edit size="sm">Evento e Agendas</x-botao-primario>
                <x-botao-secundario as="button" data-open-candidates size="sm">Candidaturas por Agenda</x-botao-secundario>
            </div>
            <div data-detail hidden class="border-t border-[#dedbd1] pt-4 space-y-4"></div>
        </article>
    </template>
    <template data-action-button><x-botao-secundario as="button" type="button" size="sm"></x-botao-secundario></template>
    <template data-edit-form>
        <form class="space-y-4">
            <h3 class="font-semibold">Dados do Evento</h3>
            <div class="grid gap-3 sm:grid-cols-2">
                <label>Nome<input name="nm_evento" required maxlength="64" class="block w-full rounded-xl border border-[#d8d4ca] p-3"></label>
                <label>Categoria<select name="cat_evento_id" required class="block w-full rounded-xl border border-[#d8d4ca] p-3"></select></label>
                <label>Modalidade<select name="modalidade_evento" required class="block w-full rounded-xl border border-[#d8d4ca] p-3"><option value="presencial">Presencial</option><option value="online">Online</option><option value="hibrido">Híbrido</option></select></label>
                <label>Vagas informativas do Evento<input name="vagas_evento" type="number" min="1" max="4294967295" required class="block w-full rounded-xl border border-[#d8d4ca] p-3"></label>
                <label class="sm:col-span-2">Descrição<textarea name="descricao_evento" required maxlength="256" class="block w-full rounded-xl border border-[#d8d4ca] p-3"></textarea></label>
                <label>CEP<input name="cep_evento" maxlength="9" inputmode="numeric" pattern="[0-9]{5}-?[0-9]{3}" class="block w-full rounded-xl border border-[#d8d4ca] p-3"></label>
                <label>Logradouro<input name="logradouro_evento" maxlength="64" class="block w-full rounded-xl border border-[#d8d4ca] p-3"></label>
                <label>Bairro<input name="bairro_evento" maxlength="64" class="block w-full rounded-xl border border-[#d8d4ca] p-3"></label>
                <label>Cidade<input name="cidade_evento" maxlength="64" class="block w-full rounded-xl border border-[#d8d4ca] p-3"></label>
                <label>UF<input name="uf_evento" maxlength="2" minlength="2" class="block w-full rounded-xl border border-[#d8d4ca] p-3"></label>
                <label>Complemento<input name="compl_evento" maxlength="64" class="block w-full rounded-xl border border-[#d8d4ca] p-3"></label>
            </div>
            <div data-event-catalogs class="grid gap-3 sm:grid-cols-2"></div>
            <x-botao-primario as="button" type="submit">Salvar alterações do Evento</x-botao-primario>
        </form>
    </template>
    <template data-agenda-form>
        <form class="rounded-xl border border-[#dedbd1] p-4 space-y-3">
            <h4 data-agenda-title class="font-semibold"></h4>
            <div class="grid gap-3 sm:grid-cols-2">
                <label>Início<input data-agenda-start type="datetime-local" required class="block w-full rounded-xl border border-[#d8d4ca] p-3"></label>
                <label>Término<input data-agenda-end type="datetime-local" required class="block w-full rounded-xl border border-[#d8d4ca] p-3"></label>
                <label>Vagas da Agenda<input data-agenda-capacity type="number" min="1" max="4294967295" required class="block w-full rounded-xl border border-[#d8d4ca] p-3"></label>
            </div>
            <div data-agenda-actions class="flex flex-wrap gap-2"><x-botao-primario as="button" type="submit" size="sm">Salvar Agenda</x-botao-primario></div>
        </form>
    </template>
    <template data-image-form>
        <form class="space-y-3"><h3 class="font-semibold">Imagem do Evento</h3><img data-private-image alt="Imagem do Evento" class="max-h-48 rounded-xl" hidden><p data-image-status class="text-sm"></p><label class="block">JPEG, PNG ou WebP (até 5 MiB)<input type="file" name="imagem" accept="image/jpeg,image/png,image/webp" required class="block w-full"></label><div class="flex flex-wrap gap-2"><x-botao-primario as="button" type="submit" size="sm">Enviar imagem</x-botao-primario><x-botao-secundario as="button" data-remove-image size="sm">Remover imagem</x-botao-secundario></div></form>
    </template>
</section>
