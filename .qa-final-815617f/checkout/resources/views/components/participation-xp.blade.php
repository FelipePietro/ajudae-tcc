@props(['experience'])

<div data-xp-profile data-participation-url="{{ \App\Support\VisualFiveFixtures::personalUrl() }}">
    <script type="application/json" data-xp-fixture>@json($experience)</script>
    <div class="account-card">
        <h2 id="meu-xp-title">Meu XP</h2>
        <noscript><p>Ative o JavaScript para consultar seu saldo e histórico.</p></noscript>
        <p data-xp-error role="alert" hidden>Não foi possível consultar seu XP. Recarregue a página para tentar novamente.</p>
        <div data-xp-content hidden>
            <div class="account-stats flex-wrap !mt-0 mb-5">
                <div><strong data-xp-total></strong><span>XP acumulado</span></div>
            </div>
            <p class="text-sm">Nível e progresso indisponíveis.</p>
            <h3>Histórico de XP</h3>
            <div class="account-field max-w-xs" data-xp-filter-field>
                <label for="xp-filter">Filtrar lançamentos</label>
                <select id="xp-filter" data-xp-filter>
                    <option value="todos">Todos</option>
                    <option value="campanha">Com campanha</option>
                    <option value="sem-campanha">Sem campanha</option>
                </select>
            </div>
            <p data-xp-empty role="status" hidden></p>
            <ul data-xp-rows class="grid gap-3 p-0 list-none"></ul>
            <template>
                <li class="rounded-xl border border-[#dedbd1] px-4 py-3">
                    <div class="flex flex-wrap items-baseline justify-between gap-2">
                        <h4 class="font-semibold break-words" data-xp-field="event"></h4>
                        <strong data-xp-field="amount"></strong>
                    </div>
                    <p class="text-sm mt-2">
                        <span data-xp-field="agenda"></span> · <time data-xp-field="date"></time>
                    </p>
                    <dl class="account-review">
                        <div><dt class="text-[#5d654e]!">XP base</dt><dd data-xp-field="base"></dd></div>
                        <div><dt class="text-[#5d654e]!">Duração declarada</dt><dd data-xp-field="duration"></dd></div>
                        <div><dt class="text-[#5d654e]!">Fator temático</dt><dd data-xp-field="thematic"></dd></div>
                    </dl>
                    <x-botao-secundario href="{{ \App\Support\VisualFiveFixtures::personalUrl() }}" size="sm">Ver participação</x-botao-secundario>
                </li>
            </template>
        </div>
    </div>
</div>
