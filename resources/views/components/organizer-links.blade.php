@props(['work' => null, 'institution' => false, 'integrated' => false, 'currentOrganization' => null])
@if($integrated)
    @include('organizadores.partials.web-links')
@else
<div id="vinculos" class="space-y-5">
@if($institution)
<section class="panel"><h2 class="panel-title">Convidar organizador</h2><p class="panel-sub">Informe o identificador do organizador que deseja convidar.</p>
<form data-organizer-invite onsubmit="return false;" novalidate class="mt-4">
<label for="organizer-identity" class="block text-sm font-semibold">Nome de usuário ou e-mail</label>
<div class="flex flex-wrap items-start gap-3 mt-2"><input id="organizer-identity" name="identificador" required maxlength="128" autocomplete="off" placeholder="Digite o usuário ou e-mail do organizador" aria-describedby="organizer-identity-error" class="border border-[#dedbd1] rounded-xl p-3 w-full sm:max-w-md" data-invite-identity><x-botao-primario as="button" type="submit">Enviar convite</x-botao-primario></div>
<p id="organizer-identity-error" data-invite-error class="text-sm mt-2"></p></form></section>
<section class="panel" data-link-section="sent"><h2 class="panel-title">Convites enviados</h2><p class="panel-sub">A aceitação cabe à Pessoa convidada.</p><div class="space-y-4 mt-4">@foreach(($work['people'] ?? []) as $row)<x-organizer-link-card :work="$work" :row="$row" institution group="sent" />@endforeach</div><p data-group-empty class="text-sm mt-4">Nenhum convite enviado para acompanhar.</p></section>
<section class="panel" data-link-section="active"><h2 class="panel-title">Organizadores vinculados</h2><p class="panel-sub">O vínculo não concede responsabilidade automática pelos Eventos.</p><div class="space-y-4 mt-4">@foreach(($work['people'] ?? []) as $row)<x-organizer-link-card :work="$work" :row="$row" institution group="active" />@endforeach</div><p data-group-empty class="text-sm mt-4">Nenhum organizador vinculado.</p></section>
@else
<section class="panel"><h2 class="panel-title">Convites e vínculo com ONG</h2><p class="panel-sub">Um organizador pode manter somente um vínculo ativo por vez.</p><div class="space-y-4 mt-4">@foreach($work['organizations'] as $row)<x-organizer-link-card :work="$work" :row="$row" />@endforeach</div>
<p data-links-empty class="text-sm mt-4" @if(count($work['links'])) hidden @endif>Nenhum convite ou vínculo para acompanhar.</p>
<x-botao-secundario data-linked-workspace :href="route('organizador.workspace',['contexto'=>'organizador-vinculado','jornada'=>count($work['visibleEvents']) ? 'vinculado' : 'vinculado-sem-eventos'])" class="mt-5" :hidden="!$work['linked']">Atuar com a organização</x-botao-secundario>
<x-botao-secundario data-independent-workspace :href="route('organizador.workspace',['contexto'=>'organizador','jornada'=>$work['scenario']])" class="mt-5" :hidden="$work['linked']">Minha atuação independente</x-botao-secundario></section>
@endif
<section class="panel"><details><summary class="cursor-pointer font-semibold">Histórico de vínculos</summary><ul data-links-history class="text-sm space-y-2 mt-3"></ul></details></section>
</div>
@endif
