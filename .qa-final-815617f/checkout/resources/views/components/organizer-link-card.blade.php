@props(['work','row','institution'=>false,'group'=>'all'])
@php
    $personId=$institution ? $row['pessoa_id'] : $work['person']['pessoa_id']; $ongId=$institution ? 1 : $row['ong_id'];
    $link=collect($work['links'])->reverse()->first(fn($item)=>$item['pessoa_id']===$personId && $item['ong_id']===$ongId);
    $visible=$link && ($group==='all' || ($group==='sent' ? in_array($link['status'],['convite_pendente','recusado']) : $link['status']==='ativo'));
    $params=['area'=>'publica','pessoa'=>$personId,'contexto'=>'ong','origem'=>'organizadores','jornada'=>$work['scenario'],'convites'=>implode(',',\App\Support\VisualThreeFixtures::invitationIds()) ?: null];
@endphp
<article class="event-flow rounded-xl border border-[#dedbd1] p-4" data-organizer-link="{{ $ongId }}-{{ $personId }}" data-link-group="{{ $group }}" @if(!$visible) hidden @endif>
    <div class="flex flex-wrap items-center justify-between gap-3"><div><h3 class="font-semibold">{{ $institution ? $row['nm_pessoa'] : $row['nome_fantasia'] }}</h3>
    @if($institution)<p class="text-sm mt-1">{{ $row['demo_username'] }} · {{ $work['organizations'][0]['nome_fantasia'] }}</p>@endif
    <p data-link-status class="text-sm mt-2">{{ ['convite_pendente'=>'Convite pendente','ativo'=>'Vínculo ativo','recusado'=>'Convite recusado','encerrado'=>'Vínculo encerrado'][$link['status'] ?? 'convite_pendente'] }}</p></div>
    <x-botao-secundario :href="$institution ? route('perfil',$params) : route('ong.perfil',['ong'=>$ongId,'contexto'=>$work['actingContext'],'jornada'=>$work['scenario']])" size="sm">Ver perfil público</x-botao-secundario></div>
    <p data-link-restriction class="text-sm mt-3"></p><div class="flex flex-wrap gap-3 mt-4">
    @unless($institution)
        <x-botao-primario as="button" data-link-action="accept" :hidden="($link['status'] ?? null) !== 'convite_pendente'" :disabled="$work['blockedIndependent'] || $work['linked']">Aceitar convite</x-botao-primario>
        <x-botao-declined as="button" data-link-action="decline" :hidden="($link['status'] ?? null) !== 'convite_pendente'">Recusar convite</x-botao-declined>
    @endunless
    <x-botao-declined as="button" data-link-action="end" :hidden="($link['status'] ?? null) !== 'ativo'" :disabled="collect($work['events'])->contains(fn($event)=>$event['ong_id']===$ongId && $event['responsavel_pessoa_id']===$personId && !in_array($event['status_evento'],['finalizado','cancelado']))">Encerrar vínculo</x-botao-declined>
    </div>
</article>
