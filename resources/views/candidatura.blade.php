<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidatura — Ajudae</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body data-events-page class="bg-[#f5f5f0] text-[#1a3a2a] font-sans">

    {{-- NAVBAR --}}
    <x-context-navbar />
    <x-event-state :flow="$flow" />

    {{-- Original circles/connectors/labels recovered from HEAD, with four functional stages. --}}
    <nav aria-label="Etapas da candidatura" class="flex items-start justify-center py-6 px-4 bg-[#f5f5f0]" data-application-progress>
        @foreach(['Escolha da Agenda', 'Carta opcional', 'Revisão', 'Confirmação'] as $label)
        <div class="flex flex-col items-center gap-1 w-16 sm:w-24" data-application-step-label="{{ $loop->index }}" @if($loop->first) aria-current="step" @endif data-step-state="{{ $loop->first ? 'current' : 'future' }}">
            <span class="w-8 h-8 rounded-full bg-white border-2 flex items-center justify-center text-sm {{ $loop->first ? 'border-[#1a3a2a] text-[#1a3a2a] font-semibold' : 'border-gray-300 text-gray-400' }}" data-progress-circle>{{ $loop->iteration }}</span>
            <span class="text-[10px] sm:text-xs text-center leading-tight {{ $loop->first ? 'text-[#1a3a2a] font-semibold' : 'text-gray-400' }}" data-progress-label>{{ $label }}</span>
        </div>
        @unless($loop->last)<span class="h-0.5 w-4 sm:w-12 bg-gray-300 mt-4 shrink-0" data-progress-connector="{{ $loop->index }}" aria-hidden="true"></span>@endunless
        @endforeach
    </nav>

    <div class="max-w-[700px] mx-auto px-4 pt-6"><a href="{{ route('eventos.show', ['id'=>$flow['event_id'], 'contexto'=>request()->query('contexto','pessoa')]) }}" class="text-sm underline">← Ver evento</a></div>

    {{-- CONTEÚDO --}}
    <main class="max-w-[700px] mx-auto my-8 px-4 flex flex-col gap-5">

        {{-- Card do evento --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 bg-[#EEF7F2] border border-[#c8e0d4] rounded-xl px-5 py-4">
            <div class="text-3xl bg-[#d4eddf] p-2.5 rounded-[10px] shrink-0">🌿</div>
            <div>
                <strong class="text-sm text-[#1a3a2a]">{{ $nome_evento }} — {{ $nome_ong }}</strong>
                <p class="text-[0.82rem] text-gray-500 mt-1 flex flex-col sm:flex-row sm:gap-2">
                    <span>📅 {{ $data_evento }}</span>
                    <span>📍 {{ $endereco_evento }}</span>
                    <span>👥 Vagas disponíveis por Agenda abaixo</span>
                </p>
            </div>
        </div>

        {{-- Card do perfil --}}
        <div class="bg-[#f5f5f0] border border-gray-200 rounded-xl p-5 flex flex-col gap-3">
            <p class="text-[0.8rem] text-gray-400">O responsável verá as informações públicas do seu perfil</p>

            <div class="flex items-center gap-3">
                <a href="/perfil" class="bg-[#2d6a4f] text-white rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold no-underline uppercase shrink-0">
                    LP
                    {{-- Depois trocar por: {{ substr(Auth::user()->nm_pessoa, 0, 2) }} --}}
                </a>
                <div>
                    <strong class="text-sm text-[#1a3a2a] block">{{ $nome_user }}</strong>
                    <p class="text-[0.82rem] text-gray-500 m-0">{{ $cidade_user }}, {{ $uf_user }}</p>
                </div>
            </div>

            {{-- Badges --}}
            <div class="flex flex-wrap gap-2">
                <span class="text-xs px-3 py-1 rounded-full font-medium bg-[#fff8e1] text-[#7a5c00]">⭐ {{ $nivel_user }} — {{ $titulo_nivel }}</span>
                <span class="text-xs px-3 py-1 rounded-full font-medium bg-[#e8f5e9] text-[#2d6a4f]">⚡ {{ $xp_user }} XP</span>
                <span class="text-xs px-3 py-1 rounded-full font-medium bg-[#fce4ec] text-[#880e4f]">🏅 {{ $badges_user }} badges</span>
                <span class="text-xs px-3 py-1 rounded-full font-medium bg-[#e3f2fd] text-[#0d47a1]">📅 {{ $eventos_user }} eventos</span>
            </div>

            {{-- Habilidades --}}
            <div class="flex flex-wrap gap-2">
                @foreach($habilidades_user as $habilidade)
                    <span class="text-xs px-3 py-1 rounded-md bg-gray-100 text-gray-700">{{ $habilidade }}</span>
                @endforeach
            </div>

            <x-botao-secundario href="/perfil">
                Editar perfil antes de enviar →
            </x-botao-secundario>
        </div>

        {{-- Card do formulário --}}
        <div class="bg-white border border-gray-200 rounded-xl p-6 flex flex-col gap-5">

            <div>
                <h1 class="text-lg text-[#1a3a2a] m-0">Sua candidatura</h1>
                <p class="text-[0.85rem] text-gray-500 m-0 mt-1">Escolha o horário de participação. A seleção será feita pelo responsável.</p>
            </div>

            <x-application-steps :flow="$flow" :candidate="$nome_user" />


        </div>

    </main>

    <x-footer />

</body>

</html>
