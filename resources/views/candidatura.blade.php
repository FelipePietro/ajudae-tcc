<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidatura — Ajudaê</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#f5f5f0] text-[#1a3a2a] font-sans">

    {{-- NAVBAR --}}
    <nav class="flex items-center justify-between px-6 h-16 bg-[#f5f5f0] border-b border-gray-200">
        <a href="/" class="font-bold text-xl no-underline font-serif">
            <span class="text-[#1A3D2B]">Ajud</span><span class="text-[#e6a817]">aê</span>
        </a>
        <a href="/evento/{{ $id_evento }}" class="text-sm text-[#1a3a2a] no-underline">
            &lt; Voltar ao evento
        </a>
    </nav>

    {{-- STEPPER --}}
    <div class="flex items-center justify-center py-6 px-4 bg-[#f5f5f0]">

        {{-- Passo 1 --}}
        <div class="flex flex-col items-center gap-1">
            <div class="w-8 h-8 rounded-full bg-[#1a3a2a] border-2 border-[#1a3a2a] flex items-center justify-center text-white text-sm">
                ✓
            </div>
            <span class="text-xs text-[#1a3a2a] hidden sm:inline">Ver evento</span>
        </div>

        {{-- Linha 1 --}}
        <div class="h-0.5 w-12 sm:w-20 bg-[#1a3a2a] mb-4"></div>

        {{-- Passo 2 --}}
        <div class="flex flex-col items-center gap-1">
            <div class="w-8 h-8 rounded-full bg-white border-2 border-[#1a3a2a] flex items-center justify-center text-[#1a3a2a] text-sm font-semibold">
                2
            </div>
            <span class="text-xs font-semibold text-[#1a3a2a] hidden sm:inline">Candidatura</span>
        </div>

        {{-- Linha 2 --}}
        <div class="h-0.5 w-12 sm:w-20 bg-gray-300 mb-4"></div>

        {{-- Passo 3 --}}
        <div class="flex flex-col items-center gap-1">
            <div class="w-8 h-8 rounded-full bg-white border-2 border-gray-300 flex items-center justify-center text-gray-400 text-sm">
                3
            </div>
            <span class="text-xs text-gray-400 hidden sm:inline">Aguardar aprovação</span>
        </div>

    </div>

    {{-- CONTEÚDO --}}
    <div class="max-w-[700px] mx-auto my-8 px-4 flex flex-col gap-5">

        {{-- Card do evento --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 bg-[#EEF7F2] border border-[#c8e0d4] rounded-xl px-5 py-4">
            <div class="text-3xl bg-[#d4eddf] p-2.5 rounded-[10px] shrink-0">🌿</div>
            <div>
                <strong class="text-sm text-[#1a3a2a]">{{ $nome_evento }} — {{ $nome_ong }}</strong>
                <p class="text-[0.82rem] text-gray-500 mt-1 flex flex-col sm:flex-row sm:gap-2">
                    <span>📅 {{ $data_evento }}</span>
                    <span>📍 {{ $endereco_evento }}</span>
                    <span>👥 {{ $vagas_evento }} vagas restantes</span>
                </p>
            </div>
        </div>

        {{-- Card do perfil --}}
        <div class="bg-[#f5f5f0] border border-gray-200 rounded-xl p-5 flex flex-col gap-3">
            <p class="text-[0.8rem] text-gray-400">O organizador verá seu perfil</p>

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

            <a href="/perfil" class="text-[0.82rem] text-[#1a3a2a] no-underline hover:underline">
                Editar perfil antes de enviar →
            </a>
        </div>

        {{-- Card do formulário --}}
        <div class="bg-white border border-gray-200 rounded-xl p-6 flex flex-col gap-5">

            <div>
                <h2 class="text-lg text-[#1a3a2a] m-0">Sua candidatura</h2>
                <p class="text-[0.85rem] text-gray-500 m-0 mt-1">Conte um pouco sobre sua motivação para participar deste evento</p>
            </div>

            <form action="/candidatura/confirmar" method="POST" class="flex flex-col gap-4">
                @csrf

                {{-- Textarea --}}
                <div class="flex flex-col gap-1">
                    <label class="text-[0.85rem] text-gray-700 font-medium">
                        Mensagem de motivação <span class="text-gray-400">— opcional</span>
                    </label>
                    <textarea
                        name="mensagem"
                        class="w-full min-h-[110px] border border-gray-300 rounded-lg px-4 py-3 text-sm text-gray-700 resize-none outline-none focus:border-[#1a3a2a] focus:border-2 font-sans"
                        placeholder="Tenho muito interesse em causas ambientais..."
                        maxlength="500"
                    ></textarea>
                    <p class="text-xs text-gray-300 text-right m-0">0 / 500</p>
                    <p class="text-sm text-gray-400 m-0">Esta mensagem é opcional, mas aumenta sua chance de aprovação.</p>
                </div>

                {{-- Checkboxes --}}
                <div class="flex flex-col gap-3">
                    <p class="text-[0.85rem] text-gray-700 font-medium m-0">
                        Confirmação de disponibilidade <span class="text-red-600">*</span>
                    </p>

                    <label class="flex items-start gap-2 text-sm text-gray-700 cursor-pointer">
                        <input type="checkbox" name="disponibilidade" required
                            class="mt-0.5 accent-[#1a3a2a] w-4 h-4 shrink-0">
                        <span>Confirmo que estarei disponível em <strong>{{ $data_evento }}, das {{ $hora_inicio_evento }} às {{ $hora_fim_evento }}</strong></span>
                    </label>

                    <label class="flex items-start gap-2 text-sm text-gray-700 cursor-pointer">
                        <input type="checkbox" name="termos" required
                            class="mt-0.5 accent-[#1a3a2a] w-4 h-4 shrink-0">
                        <span>Estou ciente de que o organizador analisará meu perfil completo antes de aprovar</span>
                    </label>
                </div>

                {{-- Aviso laranja --}}
                <div class="flex gap-3 bg-[#FFFAF0] border-l-4 border-[#E8920A] rounded-lg px-4 py-3 text-[0.82rem] text-[#1a3a2a]">
                    <span class="shrink-0">📋</span>
                    <p class="m-0 leading-relaxed">
                        <strong>Sobre a análise:</strong> O organizador verá seu perfil completo: foto, bio, habilidades, XP, badges e histórico de participações. Mantenha seu perfil atualizado para aumentar suas chances.
                    </p>
                </div>

                {{-- Botões --}}
                <div class="flex flex-col-reverse sm:flex-row gap-3 sm:justify-between sm:items-center">
                    <a href="/evento/{{ $id_evento }}"
                        class="text-center border-2 border-[#1a3a2a] text-[#1a3a2a] rounded-full px-6 py-2.5 text-sm font-medium no-underline hover:bg-[#1a3a2a] hover:text-white transition-colors sm:flex-[0.6]">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="bg-[#1a3a2a] text-white rounded-full px-6 py-2.5 text-sm font-medium border-none cursor-pointer hover:bg-[#2d6a4f] transition-colors sm:flex-[1.4] font-sans">
                        ✅ Confirmar candidatura
                    </button>
                </div>

            </form>

            {{-- Textos finais --}}
            <div class="flex flex-col gap-0.5">
                <p class="text-xs text-gray-300 text-center m-0">Você pode desistir da candidatura enquanto o status for "pendente".</p>
                <p class="text-xs text-gray-300 text-center m-0">Após aprovação, o cancelamento pode afetar sua reputação na plataforma.</p>
            </div>

        </div>

    </div>

    <x-footer />

</body>

</html>