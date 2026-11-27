<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Presença, monitoria e encerramento · Ajudae</title>
    @vite(['resources/css/app.css', 'resources/css/painel_ong.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#F7F6F2]">
    <x-context-navbar default-context="ong" />
    <main class="ong-page-content">
        @php
            $navigation = request()->only('contexto', 'jornada', 'cenario', 'exemplo');
            $organizer = $participation['actor']['type'] === 'organizador';
            $crumbs = $organizer
                ? [['label' => 'Presença e monitoria']]
                : [['label' => 'Meus eventos', 'route' => 'ong.eventos.index', 'params' => request()->only('exemplo')], ['label' => 'Presença e monitoria']];
        @endphp
        <x-breadcrumb-ong :itens="$crumbs" :raiz-href="$organizer ? route('organizador.workspace', request()->only('contexto', 'jornada', 'exemplo')) : null" :raiz-label="$organizer ? 'Minha atuação' : 'Dashboard'" />
        <div class="event-flow px-4 py-6 sm:px-6 lg:px-7">
            <header class="mb-6">
                <h1 class="text-2xl font-semibold">Presença e monitoria</h1>
                <p class="mt-2 text-base">{{ $participation['title'] }}</p>
                <div class="mt-4 flex flex-wrap gap-3">
                    <x-botao-secundario :href="route('ong.candidatos', ['evento' => $participation['id'], ...$navigation, 'cenario' => $participation['writable'] ? 'participacao' : ($navigation['cenario'] ?? null)])" size="sm">Voltar às candidaturas</x-botao-secundario>
                    @if($participation['writable'])
                        <x-botao-secundario :href="route('eventos.show', ['id' => $participation['id'], ...$navigation, 'cenario' => 'participacao'])" size="sm">Ver evento</x-botao-secundario>
                    @endif
                </div>
            </header>
            @unless($participation['writable'])
                <p class="panel mb-4">O evento está em consulta. O registro de presença e as decisões estão indisponíveis nesta situação.</p>
            @endunless
            <x-participation-agendas :participation="$participation" />
            <x-participation-certificates :participation="$participation" />
        </div>
    </main>
</body>
</html>
