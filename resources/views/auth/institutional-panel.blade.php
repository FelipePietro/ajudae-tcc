<section class="panel" id="organizadores-ong" tabindex="-1" data-active-organizer-summary="{{ route('identity.organizer-links.index', absolute: false) }}">
    <div class="panel-header"><h2 class="panel-title">Organizadores vinculados</h2></div>
    <p class="mt-4 text-sm text-[#56594f]" role="status" data-active-organizer-feedback></p>
    <ul class="ff-list" data-active-organizer-names></ul>
    <x-botao-secundario :href="route('ong.organizadores')" size="sm">Ver organizadores</x-botao-secundario>
</section>
<section class="panel" id="candidaturas-ong" tabindex="-1">
    <div class="panel-header"><h2 class="panel-title">Candidaturas</h2></div>
    <p class="mt-4 text-sm text-[#56594f]">A consulta e a seleção de candidaturas ainda estão indisponíveis.</p>
    <x-botao-secundario :href="route('ong.candidatos')" size="sm">Ver candidatos</x-botao-secundario>
</section>
