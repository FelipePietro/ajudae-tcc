@props(['flow' => null])
<section class="event-flow" data-own-applications>
    <label class="block text-sm mb-4">Situação<select class="rounded-xl border border-[#dedbd1] bg-white px-4 py-3 ml-2" data-application-filter @disabled($flow === null)><option value="todos">Todas</option><option value="pendente">Pendentes</option><option value="aprovado">Aprovadas</option><option value="reprovado">Recusadas</option><option value="desistente">Desistências</option></select></label>
    @if($flow === null)
        <p class="rounded-xl border border-dashed border-[#dedbd1] p-8 text-sm" role="status">O acompanhamento de candidaturas ainda está indisponível.</p>
    @else
    @foreach($flow['my_applications'] as $application)
    @php $item=$application['agenda']; $start=\Carbon\Carbon::parse($item['data_inicio']); @endphp
    <article class="card-candidatura-item card-candidatura mb-4" data-own-application="{{ $application['inscricao_id'] }}" data-state="{{ $application['status_inscricao'] }}" data-start="{{ $item['data_inicio'] }}" data-penalty-free="{{ $item['desistencia_sem_penalidade'] ? 'true':'false' }}">
        <div class="card-cand-topo"><div class="card-cand-icone">🌿</div><div class="card-cand-info"><strong>{{ $application['titulo'] }}</strong><p>Agenda {{ $item['agenda_id'] }} · {{ $start->format('d/m/Y H:i') }}–{{ \Carbon\Carbon::parse($item['data_fim'])->format('H:i') }}</p><span data-own-state class="tag-status">{{ ['pendente'=>'Aguardando seleção','aprovado'=>'Aprovada','reprovado'=>'Recusada','desistente'=>'Desistência'][$application['status_inscricao']] }}</span></div></div>
        <div class="card-cand-rodape mt-4"><x-botao-secundario :href="route('eventos.show', ['id'=>$item['evento_id']])" size="sm">Ver evento</x-botao-secundario>@if(in_array($application['status_inscricao'],['pendente','aprovado']) && $start->isFuture())<x-botao-declined as="button" :href="null" type="button" data-withdraw size="sm">Desistir desta Agenda</x-botao-declined>@endif</div><p data-withdraw-result class="text-sm mt-3"></p>
    </article>@endforeach
    <p data-applications-empty class="rounded-xl border border-dashed border-[#dedbd1] p-8 text-sm" @if(count($flow['my_applications'])) hidden @endif>Nenhuma candidatura nesta situação.</p>
    <dialog data-withdraw-dialog aria-labelledby="withdraw-title" aria-describedby="withdraw-description" role="dialog" aria-modal="true" class="ajudae-dialog ajudae-dialog--compact rounded-2xl p-6 w-full max-w-md border border-[#dedbd1]"><x-dialog-close /><h2 id="withdraw-title" class="font-semibold text-xl">Confirmar desistência</h2><p id="withdraw-description" data-withdraw-explanation class="mt-3"></p><p class="text-sm mt-3">O histórico será mantido nesta visualização. Nenhuma alteração será enviada.</p><p class="text-sm mt-2">Justificativas não podem ser enviadas por esta página.</p><div class="flex flex-wrap gap-3 mt-5"><x-botao-declined as="button" :href="null" type="button" data-withdraw-confirm>Confirmar desistência</x-botao-declined><x-botao-secundario as="button" :href="null" type="button" data-withdraw-close>Voltar</x-botao-secundario></div></dialog>
@endif
</section>
