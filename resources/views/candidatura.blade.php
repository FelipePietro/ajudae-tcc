<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidatura — Ajudaê</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

    <nav class="navbar">
        <a href="/" class="navbar-brand">
            <span class="brand-aju">Ajud</span><span class="brand-dae">aê</span>
        </a>
        <ul class="navbar-links">
            <li><a href="/evento/{{ $id_evento }}">&lt; Voltar ao evento</a></li>
        </ul>
    </nav>

    <div class="stepper">
        <div class="stepper-item concluido">
            <div class="stepper-circulo">✓</div>
            <span>Ver evento</span>
        </div>
        <div class="stepper-linha concluido"></div>
        <div class="stepper-item ativo">
            <div class="stepper-circulo">2</div>
            <span>Candidatura</span>
        </div>
        <div class="stepper-linha"></div>
        <div class="stepper-item">
            <div class="stepper-circulo">3</div>
            <span>Aguardar aprovação</span>
        </div>
    </div>

    {{-- Wrapper geral: usado só nesta tela, então fica como utilitário direto --}}
    <div class="max-w-[700px] mx-auto my-8 px-4 flex flex-col gap-5">

        {{-- Card do evento: também único nesta tela, sem classe própria --}}
        <div class="flex items-center gap-4 bg-verde-100 border border-[#c8e0d4] rounded-xl px-5 py-4">
            <div class="text-3xl bg-verde-50 p-2.5 rounded-[10px]">🌿</div>
            <div>
                <strong class="text-sm text-verde-900">{{ $nome_evento }} — {{ $nome_ong }}</strong>
                <p class="text-[0.82rem] text-gray-500 mt-1">
                    📅 {{ $data_evento }} &nbsp; 📍 {{ $endereco_evento }} &nbsp; 👥 {{ $vagas_evento }} vagas restantes
                </p>
            </div>
        </div>

        {{-- Card do perfil --}}
        <div class="bg-[--color-bg-base] border border-gray-200 rounded-xl p-5 flex flex-col gap-3">
            <p class="text-[0.8rem] text-gray-400">O organizador verá seu perfil</p>

            <div class="flex items-center gap-3">
                <a href="/perfil" class="avatar">LP</a>
                {{-- Depois trocar por: {{ substr(Auth::user()->nm_pessoa, 0, 2) }} --}}
                <div>
                    <strong class="text-sm text-verde-900">{{ $nome_user }}</strong>
                    <p class="text-[0.82rem] text-gray-500">{{ $cidade_user }}, {{ $uf_user }}</p>
                </div>
            </div>

            <div class="badges-row">
                <span class="badge badge-nivel">⭐ {{ $nivel_user }} — {{ $titulo_nivel }}</span>
                <span class="badge badge-xp">⚡ {{ $xp_user }} XP</span>
                <span class="badge badge-conquistas">🏅 {{ $badges_user }} badges</span>
                <span class="badge badge-eventos">📅 {{ $eventos_user }} eventos</span>
            </div>

            <div class="habilidades-row">
                @foreach($habilidades_user as $habilidade)
                    <span class="tag-habilidade">{{ $habilidade }}</span>
                @endforeach
            </div>

            <a href="/perfil" class="text-[0.82rem] text-verde-900 no-underline hover:underline">Editar perfil antes de enviar →</a>
        </div>

        {{-- Card do formulário de candidatura --}}
        <div class="bg-white border border-gray-200 rounded-xl p-6 flex flex-col gap-6">
            <div>
                <h2 class="text-lg text-verde-900 m-0">Sua candidatura</h2>
                <p class="text-[0.85rem] text-gray-500 m-0 mt-1">Conte um pouco sobre sua motivação para participar deste evento</p>
            </div>

            <form action="/candidatura/confirmar" method="POST">
                @csrf

                <label class="text-[0.85rem] text-gray-700 font-medium">
                    Mensagem de motivação <span class="text-gray-400">— opcional</span>
                </label>
                <textarea
                    name="mensagem"
                    class="textarea-candidatura mt-2"
                    placeholder="Tenho muito interesse em causas ambientais..."
                    maxlength="500"
                ></textarea>
                <p class="text-xs text-gray-300 text-right mt-1">0 / 500</p>
                <p class="text-sm text-gray-400 mt-1">Esta mensagem é opcional, mas aumenta sua chance de aprovação.</p>

                <p class="text-[0.85rem] text-gray-700 font-medium mt-4 mb-2">
                    Confirmação de disponibilidade <span class="text-red-600">*</span>
                </p>

                <div class="flex flex-col gap-3">
                    <label class="label-checkbox">
                        <input type="checkbox" name="disponibilidade" required>
                        <span>Confirmo que estarei disponível em <strong>{{ $data_evento }}, das {{ $hora_inicio_evento }} às {{ $hora_fim_evento }}</strong></span>
                    </label>

                    <label class="label-checkbox">
                        <input type="checkbox" name="termos" required>
                        <span>Estou ciente de que o organizador analisará meu perfil completo antes de aprovar</span>
                    </label>
                </div>

                <div class="aviso-analise mt-4">
                    <span>📋</span>
                    <p><strong>Sobre a análise:</strong> O organizador verá seu perfil completo: foto, bio, habilidades, XP, badges e histórico de participações. Mantenha seu perfil atualizado para aumentar suas chances.</p>
                </div>

                <div class="flex gap-4 justify-between items-center mt-4">
                    <a href="/evento/{{ $id_evento }}" class="btn-cancelar">Cancelar</a>
                    <button type="submit" class="btn-primario flex-[1.4] border-none cursor-pointer">✅ Confirmar candidatura</button>
                </div>
            </form>

            <div class="flex flex-col gap-1">
                <p class="text-xs text-gray-300 text-center m-0">Você pode desistir da candidatura enquanto o status for "pendente".</p>
                <p class="text-xs text-gray-300 text-center m-0">Após aprovação, o cancelamento pode afetar sua reputação na plataforma.</p>
            </div>
        </div>

    </div>

    <x-footer />

</body>

</html>