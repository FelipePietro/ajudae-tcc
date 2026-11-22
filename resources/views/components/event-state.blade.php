@props(['flow', 'management' => false])
{{-- Source is VisualTwoFixtures; this is a viewmodel, never an API envelope. --}}
<div class="event-flow" data-event-surface>
@if($management)
<p class="text-sm font-semibold" data-event-status>{{ $flow['event_label'] }}</p>
<p class="text-sm text-neutral-600 mt-2 mb-5" data-event-guidance>{{ $flow['reanalysis'] ? 'Alterações sensíveis aguardam nova aprovação. As candidaturas existentes são mantidas.' : ($flow['event_status'] === 'ativo' ? 'Alterações em descrição, local ou capacidade das Agendas exigem nova aprovação.' : 'Eventos cancelados e finalizados não aceitam alterações.') }}</p>
@endif
@if($flow['loading'])<x-feedback kind="loading" aria-busy="true">Carregando eventos… Aguarde para consultar as oportunidades.</x-feedback>@endif
@if($flow['error'])<x-feedback kind="error"><p>Não foi possível carregar os eventos. Tente novamente para consultar as oportunidades.</p><x-botao-secundario :href="request()->fullUrlWithQuery(['cenario'=>'publicado'])" size="sm">Tentar novamente</x-botao-secundario></x-feedback>@endif
<script type="application/json" data-event-flow>@json(collect($flow)->except(['candidates', 'candidate_events'])->all())</script>
</div>
