@props(['participation'])
<div class="account-card" data-reliability data-participation-url="{{ \App\Support\VisualFiveFixtures::personalUrl() }}">
    <script type="application/json" data-reliability-fixture>@json($participation)</script>
    <h2 id="confiabilidade-title">Confiabilidade</h2>
    <p class="account-intro">Seu compromisso com cada Agenda, separado de XP e avaliações.</p>
    <noscript>Ative o JavaScript para consultar seu histórico.</noscript>
    <p role="alert" data-reliability-error hidden>Não foi possível consultar o histórico. Recarregue para tentar novamente.</p>
    <div data-reliability-content hidden>
        <p class="text-2xl font-semibold text-[#17392a]" data-reliability-value role="status"></p>
        <p class="text-sm mt-2" data-reliability-progress></p>
        <p class="account-note mt-4">Ausência injustificada: −20. Desistência tardia injustificada: −10. Desistência normal sem justificativa: −5. Participação concluída: +3. O resultado fica entre 0 e 100. Uma justificativa aceita remove a penalidade e preserva a ocorrência.</p>
        <h3 class="mt-5">Histórico de confiabilidade</h3>
        <p data-reliability-empty hidden>Nenhuma ocorrência registrada.</p>
        <ol class="list-none p-0 grid gap-3 mt-3" data-reliability-rows></ol>
        <template><li class="border border-[#dedbd1] rounded-xl p-4"><h4 class="font-semibold" data-kind></h4><p class="text-sm mt-2" data-effect></p><p class="text-sm mt-2" data-justification></p><ol class="text-sm list-disc ps-5 mt-2" data-decisions></ol><a class="underline text-sm inline-block mt-3" data-link>Ver participação e justificativa</a></li></template>
        <div class="flex flex-wrap gap-2 mt-5"><x-botao-secundario :href="\App\Support\VisualFiveFixtures::personalUrl()">Minhas participações</x-botao-secundario><x-botao-secundario :href="route('ranking', [...\App\Support\NavigationContext::personParameters(), 'exemplo'=>request()->query('exemplo')])">Ver ranking por XP</x-botao-secundario></div>
    </div>
</div>
