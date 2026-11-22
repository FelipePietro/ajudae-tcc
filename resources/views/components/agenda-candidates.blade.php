@props(['flow', 'expanded' => true])
@if($flow['can_manage_candidates'] ?? false)
{{-- Same native disclosure and card geometry used in Meus Eventos. Collapsing keeps DOM state. --}}
<details class="event-flow my-4 rounded-xl border border-[#dedbd1] p-4" data-event-disclosure @unless($flow['scenario'] === 'participacao') data-candidate-management @endunless @if($expanded) open @endif>
    <summary class="cursor-pointer font-semibold" aria-expanded="{{ $expanded ? 'true' : 'false' }}" aria-controls="candidate-event-{{ $flow['event_id'] }}">
        <span class="block">{{ $flow['event_title'] }}</span>
        <span class="block text-sm font-normal mt-2"><span @if($flow['scenario'] === 'participacao') data-closure-event-badge="{{ \App\Support\VisualFiveFixtures::storageId($flow['event_id']) }}" @endif>{{ $flow['event_label'] }}</span> · {{ count($flow['agendas']) }} Agendas @unless($flow['scenario'] === 'participacao') · <span data-pending-count>{{ collect($flow['candidates'])->where('status_inscricao','pendente')->count() }}</span> pendentes · <span data-approved-count>{{ collect($flow['candidates'])->where('status_inscricao','aprovado')->count() }}</span> aprovados @endunless</span>
    </summary>
    <div id="candidate-event-{{ $flow['event_id'] }}" class="mt-5">
    <script type="application/json" data-candidate-flow>@json(['agendas'=>$flow['agendas'], 'event_status'=>$flow['event_status']])</script>
    <h2 class="text-lg font-semibold mb-4">Candidaturas por Agenda</h2>
    @if($flow['scenario'] !== 'participacao' && ($presenceUrl = \App\Support\VisualFiveFixtures::managerUrl($flow['event_id'])))
        <x-botao-secundario :href="$presenceUrl" size="sm">Presença e monitoria</x-botao-secundario>
    @endif
    @foreach($flow['agendas'] as $item)
    <section class="mt-5" data-candidate-agenda="{{ $item['agenda_id'] }}">
        <h3 class="font-semibold">Agenda {{ $loop->iteration }} · {{ \Carbon\Carbon::parse($item['data_inicio'])->format('d/m/Y H:i') }}</h3>
        @if($flow['scenario'] === 'participacao')
            <x-botao-secundario :href="\App\Support\VisualFiveFixtures::managerUrl($flow['event_id'], $item['agenda_id'])" size="sm" class="mt-3">Presença e monitoria da Agenda {{ $loop->iteration }}</x-botao-secundario>
        @else
            <p class="text-sm mt-1" data-capacity-summary>{{ $item['ocupadas'] }} de {{ $item['vagas_agenda'] }} vagas ocupadas · {{ max(0,$item['vagas_agenda']-$item['ocupadas']) }} disponíveis</p>
        @endif
        <div class="cand-lista mt-4">@forelse(collect($flow['candidates'])->where('agenda_id',$item['agenda_id']) as $candidate)
            @if($flow['scenario'] === 'participacao')
                <article class="cand-card" data-participation-selection="{{ $candidate['inscricao_id'] }}" data-participation-event="{{ \App\Support\VisualFiveFixtures::storageId($flow['event_id']) }}">
                    <h4 class="font-semibold">{{ $candidate['nome'] }}</h4>
                    <p class="text-sm mt-2" data-selection-label>{{ $candidate['withdrawal'] ? 'Desistência registrada' : 'Candidatura aprovada' }}</p>
                </article>
                @continue
            @endif
            <article class="cand-card" data-person-id="{{ $candidate['pessoa_id'] }}" data-candidate="{{ $candidate['inscricao_id'] }}" data-agenda-id="{{ $item['agenda_id'] }}" data-state="{{ $candidate['status_inscricao'] }}">
                <header class="cand-card-topo">
                    <div class="cand-card-pessoa"><span class="cand-avatar" aria-hidden="true">{{ mb_substr($candidate['nome'],0,1) }}</span><div><div class="cand-card-nome"><strong>{{ $candidate['nome'] }}</strong><x-botao-secundario :href="route('perfil',['area'=>'publica','pessoa'=>$candidate['pessoa_id'],'contexto'=>request()->query('contexto','ong'),'origem'=>'candidatos','evento_origem'=>$flow['event_id'],'jornada'=>request()->query('jornada')])" size="sm">Ver perfil público</x-botao-secundario></div><p data-candidate-state class="text-sm mt-1">{{ ['pendente'=>'Aguardando seleção','aprovado'=>'Aprovada','reprovado'=>'Recusada'][$candidate['status_inscricao']] }}</p><p class="text-sm text-neutral-500 mt-1">Agenda {{ $loop->parent->iteration }} · {{ \Carbon\Carbon::parse($item['data_inicio'])->format('d/m H:i') }}</p></div></div>
                    <span class="cand-match">{{ $candidate['match']['total'] }} correspondências</span>
                </header>
                <div class="cand-card-meta flex-wrap"><span>⚡ {{ $candidate['xp_pessoa'] }} XP</span><span>{{ $candidate['eventos_concluidos_mock'] }} {{ $candidate['eventos_concluidos_mock'] === 1 ? 'evento concluído' : 'eventos concluídos' }}</span></div>
                <p class="cand-hab-label">Habilidades compatíveis com o evento</p>
                <div class="cand-habs" aria-label="Habilidades compatíveis">@forelse($candidate['habilidades_compativeis'] ?? [] as $skill)<span class="cand-hab is-match">{{ $skill }}</span>@empty<span class="text-sm text-neutral-500">Nenhuma habilidade em comum. Isso não impede a candidatura.</span>@endforelse</div>
                <p class="text-sm mt-3">Confiabilidade: {{ $candidate['confiabilidade'] }}</p>
                @if(!empty($candidate['carta_apresentacao_mock']))<details class="mt-3"><summary class="cursor-pointer text-sm font-semibold">Carta de apresentação</summary><blockquote class="whitespace-pre-wrap break-words">{{ $candidate['carta_apresentacao_mock'] }}</blockquote></details>@endif
                <footer class="cand-card-acoes"><small>Afinidade e XP são informativos. A decisão cabe ao responsável.</small><div>@if($candidate['status_inscricao']==='pendente' && !in_array($flow['event_status'],['cancelado','finalizado']) && $item['status_ativo']==='ativo')<x-botao-primario as="button" :href="null" size="sm" data-candidate-action="aprovado">Aprovar candidatura</x-botao-primario><x-botao-declined as="button" :href="null" size="sm" data-candidate-action="reprovado">Recusar candidatura</x-botao-declined>@endif</div></footer>
            </article>
        @empty<p class="rounded-xl border border-dashed border-[#dedbd1] p-6 text-sm">Nenhuma candidatura nesta Agenda.</p>@endforelse</div>
    </section>@endforeach
    <p role="status" data-candidate-feedback class="text-sm mt-4"></p>
    <dialog data-candidate-confirm role="dialog" aria-modal="true" class="ajudae-dialog ajudae-dialog--compact rounded-2xl p-6 w-full max-w-md border border-[#dedbd1]" aria-labelledby="candidate-confirm-{{ $flow['event_id'] }}"><x-dialog-close /><h2 id="candidate-confirm-{{ $flow['event_id'] }}" class="font-semibold text-xl">Revisar seleção</h2><p class="mt-3" data-candidate-confirm-text></p><p class="text-sm mt-2 text-neutral-500">A revisão não envia a alteração.</p><div class="flex flex-wrap gap-3 mt-5"><x-botao-primario as="button" :href="null" data-confirm-candidate>Concluir revisão</x-botao-primario><x-botao-secundario as="button" :href="null" data-cancel-candidate>Voltar</x-botao-secundario></div></dialog>
    </div>
</details>
@else<p class="event-flow rounded-xl border p-5">A gestão de candidaturas está disponível para o responsável pelo evento.</p>@endif
