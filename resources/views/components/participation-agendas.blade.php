@props(['participation'])
@php
    $manager = $participation['actor']['type'] !== 'pessoa';
    $presenceLabels = ['pendente' => 'Presença pendente', 'presente' => 'Presença confirmada', 'ausente' => 'Ausência registrada'];
@endphp
<div data-participation>
    <script type="application/json" data-participation-fixture>@json($participation)</script>
    <noscript><p class="mb-4">Ative o JavaScript para registrar decisões e consultar alterações desta aba.</p></noscript>
    <p role="alert" data-participation-error hidden class="mb-4 text-red-800"></p>
    <p role="status" tabindex="-1" data-participation-feedback class="text-sm mb-3"></p>
    <section class="account-card mb-4" aria-labelledby="closure-event-title">
        <h2 id="closure-event-title" class="text-lg font-semibold">{{ $manager ? 'Encerramento do Evento' : 'Andamento do Evento' }}</h2>
        <p class="mt-2 text-sm" data-closure-event-status aria-live="polite"></p>
        @if($manager)
            <p class="mt-2 text-sm">Cada Agenda é finalizada separadamente. O Evento só termina depois de encerrar todas as Agendas e concluir as avaliações obrigatórias.</p>
            <ul class="mt-3 list-disc ps-5 text-sm space-y-1" aria-label="Condições de encerramento">
                <li>Agendas abertas <strong data-closure-total="abertas"></strong></li>
                <li>Agendas encerradas, incluindo as canceladas <strong data-closure-total="resolvidas"></strong></li>
                <li>Agendas canceladas <strong data-closure-total="canceladas"></strong></li>
                <li>Registros de presença ou ausência pendentes em Agendas abertas <strong data-closure-total="presencas"></strong></li>
                <li>Avaliações obrigatórias pendentes <strong data-closure-total="avaliacoes"></strong></li>
                <li>Avaliações concluídas <strong data-closure-total="avaliadas"></strong></li>
            </ul>
            <div class="mt-3 flex flex-wrap gap-2">
                <x-botao-secundario href="#avaliacoes-obrigatorias" size="sm">Ver avaliações</x-botao-secundario>
                @foreach($participation['agendas'] as $agenda)
                    <x-botao-secundario :href="'#agenda-'.$agenda['id']" data-closure-open-agenda="{{ $agenda['id'] }}" size="sm" hidden>Conferir Agenda {{ $agenda['number'] }}</x-botao-secundario>
                @endforeach
            </div>
            <p id="closure-event-reason" class="mt-3 text-sm" data-closure-event-reason hidden></p>
            <p class="mt-3 text-sm" data-closure-event-history hidden></p>
            <x-botao-primario as="button" class="mt-3" size="sm" data-closure-action="finalizarEvento" aria-describedby="closure-event-reason" disabled>Finalizar Evento</x-botao-primario>
        @endif
    </section>
    @if($manager)
        <section id="avaliacoes-obrigatorias" class="account-card mb-4 scroll-mt-24" aria-labelledby="volunteer-evaluations-title">
            <h2 id="volunteer-evaluations-title" class="text-lg font-semibold">Avaliações de voluntários</h2>
            <p class="mt-2 text-sm">Uma avaliação por pessoa presente, mesmo quando ela participou de várias Agendas.</p>
            <p class="mt-2 text-sm" data-closure-review-help hidden></p>
            @foreach(collect($participation['records'])->unique('personId') as $record)
                <article data-volunteer-evaluation="{{ $record['personId'] }}" class="mt-3 rounded-xl border border-[#dedbd1] px-4 py-3" hidden>
                    <h3 class="font-semibold">{{ $record['name'] }}</h3>
                    <p class="mt-1 text-sm" data-evaluation-state></p>
                    <x-botao-primario as="button" size="sm" class="mt-3" data-evaluation-action="avaliarVoluntario" data-person="{{ $record['personId'] }}" aria-label="Avaliar {{ $record['name'] }}" hidden>Avaliar voluntário</x-botao-primario>
                </article>
            @endforeach
        </section>
    @elseif($participation['reviewOng'] ?? null)
        <section data-ong-evaluation class="account-card mb-4" aria-labelledby="ong-evaluation-title" hidden>
            <h2 id="ong-evaluation-title" class="text-lg font-semibold">Avaliação da ONG <span class="text-sm font-normal">(opcional)</span></h2>
            <p class="mt-2 text-sm" data-evaluation-state></p>
            <x-botao-primario as="button" size="sm" class="mt-3" data-evaluation-action="avaliarOng" data-person="{{ $participation['actor']['id'] }}" hidden>Avaliar ONG</x-botao-primario>
        </section>
    @endif
    @foreach($participation['agendas'] as $agenda)
        @php
            $start = \Carbon\Carbon::parse($agenda['start']);
            $end = \Carbon\Carbon::parse($agenda['end']);
            $records = collect($participation['records'])->where('agendaId', $agenda['id']);
            $pendingHistory = ($participation['historicalOnly'] ?? false)
                && !$records->contains(fn ($record) => $record['withdrawal'] || in_array($record['presence'], ['presente', 'ausente'], true));
            $agendaLabel = match ($agenda['state']) {
                'cancelada' => 'Agenda cancelada',
                'finalizada' => 'Agenda finalizada',
                default => $start->isFuture() ? 'Ainda não começou' : ($manager ? 'Agenda iniciada · registro de presença' : 'Agenda iniciada'),
            };
        @endphp
        <details id="agenda-{{ $agenda['id'] }}" data-participation-agenda="{{ $agenda['id'] }}" class="account-card mb-4 scroll-mt-24" @if($pendingHistory) hidden @endif @if($loop->first || !$manager) open @endif>
            <summary class="cursor-pointer font-semibold text-[#17392A]">
                Agenda {{ $agenda['number'] }} · {{ $start->format('d/m/Y') }}, {{ $start->format('H:i') }}–{{ $end->format('H:i') }}
                <span class="block text-sm font-normal mt-2" data-participation-agenda-state data-active-label="{{ $agendaLabel }}">{{ $agendaLabel }}</span>
            </summary>
            @if($manager)
                <div class="mt-3">
                    <p data-participation-totals class="text-sm"></p>
                    <p data-agenda-xp class="mt-2 text-sm font-semibold"></p>
                    <p id="closure-agenda-{{ $agenda['id'] }}-reason" data-closure-agenda-reason class="mt-2 text-sm" hidden></p>
                    <p data-closure-agenda-history class="mt-2 text-sm" hidden></p>
                    <x-botao-primario as="button" size="sm" class="mt-3" data-closure-action="finalizarAgenda" aria-describedby="closure-agenda-{{ $agenda['id'] }}-reason" disabled>Finalizar Agenda {{ $agenda['number'] }}</x-botao-primario>
                </div>
            @endif
            <div class="grid gap-3 mt-4">
                @forelse($records as $record)
                    <article @if($pendingHistory) data-promoted-record="{{ $record['id'] }}" @else data-participation-record="{{ $record['id'] }}" @endif class="rounded-xl border border-[#dedbd1] px-4 py-3 min-w-0">
                        @if($manager)
                            <h3 class="font-semibold">{{ $record['name'] }}</h3>
                        @endif
                        @if($manager)<p class="text-sm mt-2" data-record-reliability hidden></p>@endif
                        <p class="text-sm" data-record-selection>Candidatura aprovada</p>
                        <p class="text-sm font-semibold mt-2" data-record-presence>{{ $agenda['state'] === 'cancelada' ? 'Participação cancelada pela Agenda' : $presenceLabels[$record['presence']] }}</p>
                        <p class="text-sm font-semibold mt-2 text-[#17392A]" data-record-xp="{{ $record['id'] }}" hidden></p>
                        <p class="text-sm mt-2" data-record-monitoring hidden></p>
                        <section class="mt-3 border-t border-[#dedbd1] pt-3" aria-label="Justificativa da Agenda {{ $agenda['number'] }}" data-record-justification-panel hidden>
                            <h4 class="text-sm font-semibold">Justificativa desta Agenda</h4>
                            <p class="text-sm mt-2" data-record-justification></p>
                            <p class="text-sm mt-2" data-record-justification-reason hidden></p>
                            <p class="text-sm mt-2" data-record-justification-decision hidden></p>
                        </section>
                        <details data-record-history hidden class="mt-3">
                            <summary class="cursor-pointer text-sm font-semibold">Histórico da participação</summary>
                            <ol class="list-disc pl-5 mt-2 text-sm space-y-1"></ol>
                        </details>
                        <div class="flex flex-wrap gap-2 mt-3" data-record-actions hidden>
                            @if($manager)
                                <x-botao-primario as="button" data-participation-action="presente" size="sm" hidden>Marcar presença</x-botao-primario>
                                <x-botao-declined as="button" data-participation-action="ausente" size="sm" hidden>Marcar ausência</x-botao-declined>
                                <x-botao-primario as="button" data-participation-action="validada" size="sm" hidden>Validar monitoria</x-botao-primario>
                                <x-botao-declined as="button" data-participation-action="rejeitada" size="sm" hidden>Rejeitar monitoria</x-botao-declined>
                                <x-botao-primario as="button" data-participation-action="deferida" size="sm" hidden>Aceitar justificativa</x-botao-primario>
                                <x-botao-declined as="button" data-participation-action="indeferida" size="sm" hidden>Recusar justificativa</x-botao-declined>
                            @else
                                <x-botao-declined as="button" data-participation-action="desistir" size="sm" hidden>Desistir desta Agenda</x-botao-declined>
                                <x-botao-secundario as="button" data-participation-action="justificar" size="sm" hidden>Enviar justificativa</x-botao-secundario>
                            @endif
                        </div>
                    </article>
                @empty
                    <p class="rounded-xl border border-dashed border-[#dedbd1] p-4 text-sm">Nenhuma pessoa selecionada nesta Agenda.</p>
                @endforelse
            </div>
        </details>
    @endforeach

    <dialog data-participation-dialog aria-labelledby="participation-dialog-title" aria-describedby="participation-dialog-review" class="ajudae-dialog ajudae-dialog--compact rounded-2xl p-6 w-full max-w-md border border-[#dedbd1]">
        <x-dialog-close />
        <form data-participation-form>
            <h2 id="participation-dialog-title" class="text-xl font-semibold">Revisar decisão</h2>
            <p id="participation-dialog-review" class="mt-3" data-participation-review></p>
            @unless($manager)
                <label class="mt-4 block" data-justification-choice hidden>
                    Motivo da justificativa
                    <select class="block border border-[#dedbd1] rounded-lg p-2 w-full mt-1" name="reason" disabled>
                        <option value="">Selecione um motivo</option>
                        <option value="imprevisto">Imprevisto pessoal</option>
                        <option value="transporte">Imprevisto no deslocamento</option>
                        <option value="horario">Conflito de horário</option>
                    </select>
                </label>
            @endunless
            <label class="mt-4 block" hidden>
                Motivo da decisão
                <select name="decisionReason" class="block border border-[#dedbd1] rounded-lg p-2 w-full mt-1" disabled>
                    <option value="">Selecione o fundamento</option>
                    <option value="analise">Análise da justificativa apresentada</option>
                    <option value="esclarecimento">Esclarecimento confirmado com o titular</option>
                </select>
            </label>
            <label class="mt-4 block" hidden>
                Nota de 1 a 5
                <select name="nota" class="block border border-[#dedbd1] rounded-lg p-2 w-full mt-1" aria-required="true" aria-describedby="participation-note-error" disabled>
                    <option value="">Selecione a nota</option>
                    @foreach(range(1, 5) as $note)
                        <option value="{{ $note }}">{{ $note }}</option>
                    @endforeach
                </select>
            </label>
            <p id="participation-note-error" data-evaluation-error class="mt-2 text-sm text-red-800" role="alert" hidden>Selecione uma nota inteira de 1 a 5.</p>
            <x-botao-primario as="button" type="submit" class="mt-5">Confirmar</x-botao-primario>
        </form>
    </dialog>
</div>
