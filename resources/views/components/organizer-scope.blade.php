@props(['flow'])
@if(isset($flow['management_scope']))
<p class="text-sm text-neutral-600 mt-3 mb-4">{{ $flow['management_scope']['label'] }}@if($flow['management_scope']['context'] !== 'ong') · {{ $flow['management_scope']['person'] }} · Somente Eventos sob sua responsabilidade
@endif</p>
@if(!($flow['management_allowed'] ?? true))<p class="text-sm mt-3" role="alert">Este Evento não está sob sua responsabilidade nesta área.</p><x-botao-secundario :href="route('ong.eventos.index',['contexto'=>$flow['management_scope']['context'],'jornada'=>$flow['management_scope']['scenario']])" class="mt-4">Meus Eventos</x-botao-secundario>@endif
@endif
