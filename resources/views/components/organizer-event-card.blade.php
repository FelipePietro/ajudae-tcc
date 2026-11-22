@props(['event','work'])
@php
    $labels=['ativo'=>'Publicado','aguardando a confirmação'=>'Aprovação pendente','finalizado'=>'Finalizado','cancelado'=>'Cancelado','reprovado'=>'Recusado'];
    $params=['id'=>$event['evento_id'],'contexto'=>$work['actingContext'],'jornada'=>$work['scenario']];
@endphp
<article class="panel organizer-event-card" data-organizer-event="{{ $event['evento_id'] }}">
    <header class="flex flex-wrap items-start justify-between gap-3"><h3 class="panel-title break-words">{{ $event['nm_evento'] }}</h3><span class="account-badge {{ $event['status_evento']==='ativo' ? 'success' : 'info' }}" @if($event['status_evento']==='ativo') data-closure-event-badge="{{ \App\Support\VisualFiveFixtures::storageId($event['evento_id']) }}" @endif>{{ $labels[$event['status_evento']] }}</span></header>
    <div class="mt-4 border-t border-[#dedbd1] pt-4"><p class="font-semibold">{{ $event['ong_id'] ? 'Atuação vinculada' : 'Atuação independente' }}</p><p class="text-sm mt-2">{{ $event['ong_id'] ? 'Proprietária: '.$work['organizations'][0]['nome_fantasia'].' · Você é responsável operacional' : 'Você é proprietário e responsável' }}</p></div>
    <div class="organizer-event-actions mt-5" aria-label="Ações de {{ $event['nm_evento'] }}">
        @if($event['status_evento']==='ativo')<x-botao-secundario :href="route('eventos.show',$params)" size="sm">Visualizar Evento</x-botao-secundario>@endif
        <x-botao-secundario :href="route('ong.eventos.editar',$params)" size="sm">{{ in_array($event['status_evento'],['finalizado','cancelado']) ? 'Consultar Evento e Agendas' : 'Evento e Agendas' }}</x-botao-secundario>
        <x-botao-secundario :href="route('ong.candidatos',['evento'=>$event['evento_id'],'contexto'=>$work['actingContext'],'jornada'=>$work['scenario']])" size="sm">Candidaturas</x-botao-secundario>
        @if($presenceUrl = \App\Support\VisualFiveFixtures::managerUrl($event['evento_id']))
            <x-botao-secundario :href="$presenceUrl" size="sm">Presença e encerramento</x-botao-secundario>
        @endif
    </div>
</article>
