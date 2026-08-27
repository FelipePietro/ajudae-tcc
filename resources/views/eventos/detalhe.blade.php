<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>{{ $evento->nm_evento }} | Ajudae</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>


<body class="bg-[#f7f5ef] text-[#25251f]">

    @include('/components/navbar-feed')


<main
    class="mx-auto max-w-[1100px] px-6 py-6"
>

    {{-- CAPA --}}
    <div
        class="relative h-[310px] overflow-hidden
               rounded-3xl bg-gradient-to-br
               from-[#174b36] to-[#4b9c73]"
    >

        @if($evento->imagem_evento_link)

            <img
                src="{{ $evento->imagem_evento_link }}"
                alt="{{ $evento->nm_evento }}"
                class="h-full w-full object-cover"
            >

        @endif


        <div
            class="absolute bottom-6 left-6 flex gap-2"
        >

            <span
                class="rounded-full border border-white/30
                       bg-white/15 px-4 py-2
                       text-xs text-white backdrop-blur"
            >
                🌿 ONG
            </span>

            <span
                class="rounded-full border border-white/30
                       bg-white/15 px-4 py-2
                       text-xs text-white backdrop-blur"
            >
                {{ $evento->categoria->nome_categoria ?? 'Evento social' }}
            </span>

            <span
                class="rounded-full border border-white/30
                       bg-white/15 px-4 py-2
                       text-xs text-white backdrop-blur"
            >
                ✅ Publicado
            </span>

        </div>

    </div>


    <h1 class="mt-7 text-3xl font-semibold">
        {{ $evento->nm_evento }}
    </h1>


    <div
        class="mt-5 grid grid-cols-1
               gap-7 lg:grid-cols-[1fr_320px]"
    >

        {{-- COLUNA ESQUERDA --}}
        <div class="space-y-6">


            {{-- ONG --}}
            <div
                class="flex items-center justify-between
                       rounded-xl border border-[#dedbd1]
                       bg-white p-5"
            >

                <div class="flex items-center gap-4">

                    <div
                        class="flex h-12 w-12 items-center
                               justify-center rounded-xl
                               bg-[#dcefe5] text-2xl"
                    >
                        🌱
                    </div>

                    <div>

                        <h2 class="font-semibold">
                            {{ $evento->ong->nm_ong ?? 'ONG organizadora' }}
                        </h2>

                        <p class="text-xs text-neutral-500">
                            Responsável pelo evento
                        </p>

                    </div>

                </div>


                <button
                    class="rounded-full border border-[#c9c4b8]
                           px-5 py-2 text-xs"
                >
                    Ver perfil da ONG
                </button>

            </div>


            {{-- INFORMAÇÕES --}}
            <div
                class="grid gap-6 rounded-xl border
                       border-[#dedbd1] bg-white p-6
                       md:grid-cols-2"
            >

                <div>

                    <p class="text-xs text-neutral-500">
                        📅 Início
                    </p>

                    <strong class="mt-2 block text-sm">
                        {{ $agenda->data_inicio->format('d/m/Y · H:i') }}
                    </strong>

                </div>


                <div>

                    <p class="text-xs text-neutral-500">
                        🏁 Fim
                    </p>

                    <strong class="mt-2 block text-sm">
                        {{ $agenda->data_fim->format('d/m/Y · H:i') }}
                    </strong>

                </div>


                <div>

                    <p class="text-xs text-neutral-500">
                        📍 Local
                    </p>

                    <strong class="mt-2 block text-sm">
                        {{ $evento->logradouro_evento }}
                    </strong>

                    <p class="mt-1 text-sm">
                        {{ $evento->cidade_evento }},
                        {{ $evento->uf_evento }}
                    </p>

                </div>


                <div>

                    <p class="text-xs text-neutral-500">
                        👥 Vagas disponíveis
                    </p>

                    <strong class="mt-2 block text-sm">
                        {{ $vagasDisponiveis ?? 0 }}
                    </strong>

                </div>

            </div>


            {{-- HABILIDADES --}}
            @if(isset($evento->habilidades))

                <div>

                    <p class="text-xs text-neutral-500">
                        Habilidades desejadas
                    </p>

                    <div class="mt-3 flex flex-wrap gap-2">

                        @foreach($evento->habilidades as $habilidade)

                            <span
                                class="rounded-full bg-[#dcefe5]
                                       px-4 py-2 text-xs
                                       text-[#174b36]"
                            >
                                {{ $habilidade->nome_habilidade }}
                            </span>

                        @endforeach

                    </div>

                </div>

            @endif


            {{-- SOBRE --}}
            <section>

                <h2 class="text-sm text-neutral-500">
                    Sobre o evento
                </h2>

                <div
                    class="mt-4 whitespace-pre-line
                           text-sm leading-7 text-neutral-700"
                >{{ $evento->descricao_evento }}</div>

            </section>


            {{-- MAPA --}}
            <div
                class="flex h-44 items-center justify-center
                       rounded-xl border border-[#c7dfd1]
                       bg-[#dff2e5]"
            >
                <p class="text-sm text-[#466856]">
                    📍 {{ $evento->logradouro_evento }} —
                    {{ $evento->cidade_evento }},
                    {{ $evento->uf_evento }}
                </p>
            </div>


            {{-- AVISO --}}
            <div
                class="border-l-4 border-[#e7a11b]
                       bg-[#fffaf0] p-5 text-xs
                       leading-5 text-neutral-600"
            >
                Após a candidatura, o organizador revisará seu perfil
                antes de aprovar. Você receberá uma notificação com o
                resultado.
            </div>

        </div>


        {{-- SIDEBAR --}}
        <aside class="space-y-5">

            <div
                class="rounded-2xl border border-[#dedbd1]
                       bg-white p-6 shadow-sm"
            >

                <p class="text-xs text-neutral-500">
                    Vagas disponíveis
                </p>

                <p class="mt-1 text-4xl font-medium">
                    {{ $vagasDisponiveis ?? 0 }}
                </p>


                <p class="mt-2 text-xs text-neutral-500">
                    de {{ $totalVagas ?? 0 }} vagas
                    • {{ $confirmados ?? 0 }} confirmados
                </p>


                <div
                    class="mt-4 h-2 overflow-hidden
                           rounded-full bg-[#dcefe5]"
                >
                    <div
                        class="h-full rounded-full bg-[#174b36]"
                        style="width:
                        {{ $percentualOcupado ?? 0 }}%"
                    ></div>
                </div>


                <form
                    action="{{ route(
                        'eventos.candidatar',
                        $agenda->agenda_id
                    ) }}"
                    method="POST"
                    class="mt-6"
                >

                    @csrf

                    <button
                        type="submit"
                        class="w-full rounded-full bg-[#174b36]
                               px-5 py-3 font-semibold
                               text-white hover:bg-[#123b2b]"
                    >
                        🙋 Quero me voluntariar
                    </button>

                </form>


                <button
                    type="button"
                    class="mt-3 w-full rounded-full border
                           border-[#c9c4b8] px-5 py-2
                           text-sm"
                >
                    🔗 Compartilhar evento
                </button>


                <div
                    class="mt-6 border-t border-[#dedbd1]
                           pt-5 text-center text-xs
                           leading-5 text-neutral-500"
                >
                    Candidatura sujeita à aprovação do organizador.
                </div>

            </div>


            {{-- RECOMPENSAS --}}
            <div
                class="rounded-xl border border-[#bddccd]
                       bg-[#edf7f2] p-5"
            >

                <h3 class="text-sm font-medium">
                    Recompensas deste evento
                </h3>

                <div
                    class="mt-4 space-y-3 text-sm"
                >

                    <p>⭐ +150 XP ao concluir</p>

                    <p>🌱 Badge "Plantador de Sementes"</p>

                    <p>📈 +1 posição no ranking</p>

                </div>

            </div>

        </aside>

    </div>

</main>

</body>
</html>