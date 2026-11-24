<section class="event-flow" data-web-application data-agendas="{{ route('identity.application.agendas', $publicEvent) }}" data-submit="{{ route('identity.application.store', $publicEvent) }}" data-csrf="{{ csrf_token() }}">
    <div data-web-step>
        <h2 tabindex="-1" class="text-lg font-semibold">Escolha sua Agenda</h2>
        <fieldset data-agendas-list class="my-4 grid gap-3"><legend class="sr-only">Horários</legend></fieldset>
        <label class="flex items-center gap-2 mb-4"><input type="checkbox" name="declarou_estudante"> Sou estudante e preciso de confirmação de monitoria</label>
        <x-botao-primario as="button" type="button" data-next disabled>Continuar para apresentação</x-botao-primario>
        <x-botao-secundario as="button" type="button" data-retry hidden>Atualizar Agendas</x-botao-secundario>
    </div>
    <div data-web-step hidden>
        <h2 tabindex="-1" class="text-lg font-semibold">Sua apresentação</h2>
        <label for="application-letter" class="block font-semibold mt-4">Carta de apresentação (opcional)</label>
        <textarea id="application-letter" maxlength="2000" rows="5" class="w-full rounded-xl border border-[#d8d4ca] p-3 mt-2" aria-describedby="application-letter-hint"></textarea>
        <p id="application-letter-hint" class="text-sm text-neutral-600">Somente você e o responsável autorizado podem ler a carta enviada.</p>
        <div class="flex flex-wrap gap-3 mt-5">
            <x-botao-secundario as="button" type="button" data-go="0">Voltar</x-botao-secundario>
            <x-botao-primario as="button" type="button" data-review>Revisar candidatura</x-botao-primario>
        </div>
    </div>
    <div data-web-step hidden>
        <h2 tabindex="-1" class="text-lg font-semibold">Revise sua candidatura</h2>
        <dl class="space-y-3 mt-4">
            <div><dt class="font-semibold">Evento</dt><dd>{{ $publicEvent->nm_evento }}</dd></div>
            <div><dt class="font-semibold">Agenda</dt><dd data-review-agenda></dd></div>
            <div><dt class="font-semibold">Pessoa candidata</dt><dd>{{ $candidate }}</dd></div>
            <div><dt class="font-semibold">Monitoria estudantil</dt><dd data-review-student></dd></div>
            <div><dt class="font-semibold">Carta de apresentação</dt><dd data-review-letter class="whitespace-pre-wrap break-words"></dd></div>
        </dl>
        <div class="flex flex-wrap gap-3 mt-5">
            <x-botao-secundario as="button" type="button" data-go="1">Editar apresentação</x-botao-secundario>
            <x-botao-secundario as="button" type="button" data-go="0">Trocar Agenda</x-botao-secundario>
            <x-botao-primario as="button" type="button" data-confirm>Confirmar candidatura</x-botao-primario>
        </div>
    </div>
    <div data-web-step hidden>
        <h2 tabindex="-1" class="text-lg font-semibold">Candidatura enviada</h2>
        <p class="mt-3">Acompanhe sua candidatura enquanto o responsável faz a seleção.</p>
        <div class="flex flex-wrap gap-3 mt-5">
            <x-botao-primario :href="route('candidaturas')">Ver minhas candidaturas</x-botao-primario>
            <x-botao-secundario as="button" type="button" data-another>Escolher outra Agenda</x-botao-secundario>
        </div>
    </div>
    <p class="mt-3 text-sm" role="status" data-feedback></p>
</section>
