@extends('layouts.organizer')
@section('title','Condição de organizador')
@section('content')
<section class="org-card"><div class="org-identity"><div><h1 class="org-name">Organize novas possibilidades</h1><p>{{ $work['person']['nm_pessoa'] }} · Sua identidade e seu histórico continuam os mesmos.</p></div></div><x-botao-secundario :href="route('perfil', ['area'=>'privada','contexto'=>'pessoa'])">Minha jornada</x-botao-secundario></section>
<div class="content-grid"><section class="panel"><h2 class="panel-title">Critérios para solicitar</h2><p class="panel-sub">Todos os critérios precisam ser atendidos.</p><dl class="info-list">@foreach($work['criteria'] as $criterion)<div><dt class="info-label">{{ $criterion['label'] }}</dt><dd class="info-value">{{ $criterion['met'] ? '✓' : '○' }} {{ $criterion['value'] }}</dd></div>@endforeach</dl></section>
<section class="panel"><h2 class="panel-title">Sua solicitação</h2>
<p class="mt-3 font-semibold" data-promotion-status>{{ ['pendente'=>'Aguardando análise','em_analise'=>'Em análise','aprovada'=>'Condição de organizador aprovada','reprovada'=>'Solicitação recusada'][$work['promotion']['status']] ?? ($work['eligible'] ? 'Você pode solicitar' : 'Ainda não disponível') }}</p>
@if($work['promotion']['motivo_decisao'])<p class="mt-3">{{ $work['promotion']['motivo_decisao'] }}</p>@endif
<p class="text-sm mt-3">A mudança para organizador é definitiva. Seu histórico é preservado; após a aprovação, você deixa de se candidatar a novas Agendas como voluntário.</p>
@if($work['eligible'])
<form data-promotion-form class="mt-5" onsubmit="return false;"><label for="organizer-justification" class="block text-sm font-semibold">Justificativa *</label><textarea id="organizer-justification" name="justificativa" maxlength="2000" required rows="5" class="w-full border border-[#dedbd1] rounded-xl p-3 mt-2" aria-describedby="organizer-justification-error"></textarea><p id="organizer-justification-error" data-promotion-error role="alert" class="text-sm text-red-700 mt-2"></p><x-botao-primario as="button" type="submit" class="mt-4">Revisar solicitação</x-botao-primario></form>
@elseif($work['promotion']['status']==='aprovada')
<x-botao-primario :href="route('organizador.workspace',['contexto'=>'organizador','jornada'=>'independente-vazio'])" class="mt-5">Acessar minha atuação</x-botao-primario>
@elseif(in_array($work['promotion']['status'],['pendente','em_analise']))<p class="mt-4 text-sm">Já existe uma solicitação em aberto. Acompanhe a análise antes de solicitar novamente.</p>
@else<p class="mt-4 text-sm">Confira os critérios e o intervalo para uma nova solicitação.</p>@endif
<x-botao-secundario data-promotion-follow :href="route('organizador.solicitacao',['contexto'=>'pessoa','jornada'=>'pendente','inspecao'=>request()->boolean('inspecao') ? 1 : null])" class="mt-4" hidden>Acompanhar solicitação</x-botao-secundario>
</section></div>
@endsection
