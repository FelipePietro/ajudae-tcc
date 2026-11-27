@props(['flow', 'application' => false])
<section class="event-flow rounded-xl border border-[#dedbd1] bg-white p-5 my-5" data-agenda-options>
    @if($flow['scenario'] === 'participacao')
        <h2 class="font-semibold">Agendas do evento</h2>
        @if(request()->query('contexto') === 'pessoa' && $flow['event_id'] === \App\Support\VisualFiveFixtures::PERSONAL_EVENT)
            <x-botao-secundario :href="\App\Support\VisualFiveFixtures::personalUrl()" size="sm" class="mt-3">Acompanhar minhas participações</x-botao-secundario>
        @endif
    @else
        <h2 class="font-semibold">Escolha uma Agenda</h2><p class="mt-2 text-sm text-neutral-600">Cada Agenda tem seu horário e suas próprias vagas. Você pode participar de mais de uma.</p>
    @endif
    <div class="space-y-3 mt-4">
    @foreach($flow['agendas'] as $item)
        @php
            $start = \Carbon\Carbon::parse($item['data_inicio']); $end = \Carbon\Carbon::parse($item['data_fim']);
            $available = max(0, $item['vagas_agenda']-$item['ocupadas']);
            $closed = now()->greaterThanOrEqualTo($start->copy()->subHour());
            $reason = $item['inscricao'] ? 'Você já possui uma candidatura nesta Agenda.' : ($item['status_ativo'] !== 'ativo' ? 'Agenda encerrada ou cancelada.' : ($flow['event_status'] !== 'ativo' ? 'Evento indisponível para candidatura.' : (!$flow['eligible'] ? 'A candidatura é exclusiva para voluntários aprovados.' : ($closed ? 'As candidaturas encerram uma hora antes do início.' : (!$available ? 'Todas as vagas foram preenchidas. Não há lista de espera.' : null)))));
        @endphp
        <article class="rounded-xl border border-[#dedbd1] p-4" data-agenda-option data-agenda-id="{{ $item['agenda_id'] }}">
            <h3 class="font-semibold">Agenda {{ $loop->iteration }} · {{ $start->format('d/m/Y') }}</h3>
            <p class="text-sm mt-1">{{ $start->format('H:i') }}–{{ $end->format('H:i') }} · {{ (int) $start->diffInMinutes($end) }} minutos</p>
            @if($flow['scenario'] === 'participacao')
                <p class="text-sm mt-1" @if($item['status_ativo'] === 'ativo') data-closure-agenda-badge="{{ \App\Support\VisualFiveFixtures::storageId($flow['event_id']) }}" data-agenda="{{ $item['agenda_id'] }}" @endif>{{ ['cancelado' => 'Agenda cancelada', 'finalizado' => 'Agenda finalizada'][$item['status_ativo']] ?? ($start->isFuture() ? 'Ainda não começou' : 'Agenda iniciada') }}</p>
            @else
            <p class="text-sm mt-1">{{ $available }} de {{ $item['vagas_agenda'] }} vagas disponíveis</p>
            @if($item['inscricao'])<p class="text-sm mt-2">Candidatura: {{ ['pendente'=>'Aguardando seleção','aprovado'=>'Aprovada','reprovado'=>'Recusada','desistente'=>'Desistência'][$item['inscricao']] ?? $item['inscricao'] }}</p>@endif
            @if($reason)<p class="text-sm mt-2 text-neutral-600">{{ $reason }}</p>
            @elseif($application)<label class="flex gap-2 mt-3"><input type="radio" name="agenda_escolhida" value="{{ $item['agenda_id'] }}" @checked((string) request()->query('agenda', $flow['agendas'][0]['agenda_id']) === (string) $item['agenda_id'])> Participar desta Agenda</label>
            @else<x-botao-primario :href="route('candidatura.form', ['evento'=>$flow['event_id'], 'agenda'=>$item['agenda_id'], 'cenario'=>$flow['scenario'], 'contexto'=>request()->query('contexto','pessoa')])" class="mt-3">Escolher Agenda</x-botao-primario>@endif
            @endif
        </article>
    @endforeach
    </div>
</section>
