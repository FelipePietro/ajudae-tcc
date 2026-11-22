<!doctype html>
<html lang="pt-BR">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>@yield('title') · Ajudae</title>@vite(['resources/css/app.css','resources/css/painel_ong.css',isset($organizerEntry) ? 'resources/js/identity.js' : 'resources/js/app.js'])</head>
<body class="min-h-screen bg-[#F7F6F2]">
<a class="account-skip" href="#organizer-main">Ir para o conteúdo</a>
<x-context-navbar :default-context="$work['context'] ?? 'pessoa'" />
<main id="organizer-main" tabindex="-1" @class(['ong-page-content' => !isset($organizerEntry)])>
@yield('breadcrumb')
<div class="px-4 py-6 sm:px-6 lg:px-7 event-flow" @unless(isset($organizerEntry)) data-organizer-root @endunless>
@if(isset($organizerEntry))
    @yield('content')
@else
<script type="application/json" data-organizer-state>@json($work)</script>
@if(request()->boolean('inspecao'))
<details class="mb-5"><summary class="cursor-pointer text-sm">Visualizar situação</summary><form method="GET" class="flex flex-wrap gap-3 mt-3"><input type="hidden" name="inspecao" value="1"><input type="hidden" name="contexto" value="{{ $work['context'] }}"><label>Situação<select name="jornada" class="border rounded-xl p-2 ml-2">@foreach(\App\Support\VisualThreeFixtures::availableScenarios($work['context']) as $state)<option value="{{ $state }}" @selected($state===$work['scenario'])>{{ str_replace('-', ' ', ucfirst($state)) }}</option>@endforeach</select></label><x-botao-secundario as="button" type="submit">Visualizar</x-botao-secundario></form></details>
@endif
@if($work['loading'])<p role="status" class="panel">Carregando suas informações…</p>
@elseif($work['error'])<div class="panel" role="alert"><p>Não foi possível carregar suas informações.</p><x-botao-secundario :href="request()->url().'?'.http_build_query(['contexto'=>$work['context']])">Tentar novamente</x-botao-secundario></div>
@else
@yield('content')
@endif
<p role="status" data-organizer-feedback class="text-sm mt-5" tabindex="-1"></p>
<dialog data-organizer-confirm role="dialog" aria-modal="true" class="ajudae-dialog ajudae-dialog--review rounded-2xl p-6 w-full max-w-md border border-[#dedbd1]" aria-labelledby="organizer-confirm-title"><x-dialog-close /><h2 id="organizer-confirm-title" class="text-xl font-semibold">Revise sua decisão</h2><blockquote data-organizer-review hidden class="dialog-review"></blockquote><p data-organizer-confirm-text class="mt-3"></p><p class="text-sm mt-2">A revisão não envia a alteração.</p><div class="flex flex-wrap gap-3 mt-5"><x-botao-primario as="button" data-organizer-confirm-yes>Concluir revisão</x-botao-primario><x-botao-secundario as="button" data-organizer-confirm-no>Voltar</x-botao-secundario></div></dialog>
@endif
</div></main>
</body></html>
