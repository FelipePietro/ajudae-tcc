@php
    $integrated = isset($publicEvent);
    $evento = $publicEvent ?? $evento;
    $place = implode(', ', array_filter([$evento->cidade_evento, $evento->uf_evento]));
    $mode = match ($evento->modalidade_evento) {
        'presencial' => 'Presencial', 'online' => 'Online', 'hibrido' => 'Híbrido', default => null,
    };
@endphp
<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>{{ $evento->nm_evento }} | Ajudae</title>

    @vite(['resources/css/app.css', $integrated ? 'resources/js/identity.js' : 'resources/js/app.js'])

</head>


<body @unless($integrated) data-events-page @endunless class="bg-[#f7f5ef] text-[#25251f]">

    <x-context-navbar />


<main
    class="mx-auto max-w-[1100px] px-6 py-6"
>
@if($integrated)
    <x-botao-secundario :href="route('feed')" class="mb-5" size="sm">Voltar aos eventos</x-botao-secundario>
@else
    <x-event-state :flow="$flow" />
@endif

    {{-- CAPA --}}
    <div
        class="relative h-44 overflow-hidden sm:h-[310px]
               rounded-3xl bg-gradient-to-br
               from-[#174b36] to-[#4b9c73]"
    >

        @if($integrated)
            @if($publicEventCover)
                <img data-public-event-image src="{{ $publicEventCover }}" alt="" referrerpolicy="no-referrer" class="h-full w-full object-cover" hidden>
            @endif
        @elseif($evento->imagem_evento_link)

            <img
                src="{{ $evento->imagem_evento_link }}"
                alt="{{ $evento->nm_evento }}"
                class="h-full w-full object-cover"
            >

        @endif


        <div
            class="absolute bottom-6 left-6 flex gap-2"
        >
            @if($integrated)
                @if($mode)
                    <span class="rounded-full border border-white/30 bg-white/15 px-4 py-2 text-xs text-white">{{ $mode }}</span>
                @endif
            @else

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
                <span @if($flow['scenario'] === 'participacao') data-closure-event-badge="{{ \App\Support\VisualFiveFixtures::storageId($flow['event_id']) }}" @endif>{{ $flow['event_label'] }}</span>
            </span>
            @endif

        </div>

    </div>


    <h1 class="mt-7 break-words text-3xl font-semibold">
        {{ $evento->nm_evento }}
    </h1>


    <div
        class="mt-5 grid grid-cols-1
               gap-7 lg:grid-cols-[1fr_320px]"
    >

        {{-- COLUNA ESQUERDA --}}
        <div class="min-w-0 space-y-6">
            @if($integrated)
                <section id="agendas-evento" class="rounded-xl border border-[#dedbd1] bg-white p-5">
                    <h2 class="font-semibold">Agendas e candidaturas</h2>
                    @php($candidateViewer = request()->attributes->get('web_account'))
                    @if($candidateViewer instanceof \App\Models\Pessoa && $candidateViewer->isApprovedVolunteer() && \App\Support\AccountAccess::canOperate($candidateViewer))
                        <div data-public-agendas="{{ route('identity.application.agendas', $evento) }}" class="mt-3 space-y-2" role="status">Carregando Agendas…</div>
                    @else
                        <p class="mt-2 text-sm">Entre com uma conta de voluntário aprovada para consultar disponibilidade e se candidatar.</p>
                    @endif
                </section>
                @if($place)
                    <section class="grid gap-6 rounded-xl border border-[#dedbd1] bg-white p-6">
                        <h2 class="text-xs text-neutral-500">📍 Local</h2>
                        <p class="break-words text-sm font-semibold">{{ $place }}</p>
                    </section>
                @endif
            @else
            <div id="agendas-evento"><x-agenda-options :flow="$flow" /></div>


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
                            {{ $evento->ong?->nm_ong ?? $evento->pessoa->nm_pessoa }}
                        </h2>

                        <p class="text-xs text-neutral-500">
                            Responsável pelo evento
                        </p>

                    </div>

                </div>


                @if($evento->ong)
                <x-botao-secundario :href="route('ong.perfil', ['contexto'=>request()->query('contexto','pessoa'), 'ong'=>$evento->ong->ong_id, 'evento_origem'=>$evento->evento_id])" size="sm">
                    Ver perfil da ONG
                </x-botao-secundario>
                @else
                <x-botao-secundario :href="route('perfil',['area'=>'publica','pessoa'=>$evento->pessoa->pessoa_id,'contexto'=>request()->query('contexto','pessoa'),'origem'=>'evento','evento_origem'=>$evento->evento_id])" size="sm">Ver perfil do organizador</x-botao-secundario>
                @endif

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


            @endif

            {{-- SOBRE --}}
            <section>

                <h2 class="text-sm text-neutral-500">
                    Sobre o evento
                </h2>

                <div
                    class="mt-4 whitespace-pre-line break-words
                           text-sm leading-7 text-neutral-700"
                >{{ $evento->descricao_evento }}</div>

            </section>


            @unless($integrated)
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
                antes de aprovar. Acompanhe a situação em suas candidaturas.
            </div>
            @endunless

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

                @if($integrated)
                    <p id="agenda-availability" class="mt-2 text-sm">A disponibilidade é consultada por Agenda.</p>
                    @php($viewer = request()->attributes->get('web_account'))
                    @if($viewer instanceof \App\Models\Pessoa && $viewer->isApprovedVolunteer() && \App\Support\AccountAccess::canOperate($viewer))
                        <x-botao-primario :href="route('candidatura.form', ['evento' => $evento->getKey()])" class="mt-6 w-full">Escolher uma Agenda</x-botao-primario>
                    @else
                        <x-botao-primario as="button" disabled aria-describedby="agenda-availability" class="mt-6 w-full">Escolher uma Agenda</x-botao-primario>
                    @endif
                @else
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


@if($flow['scenario'] === 'participacao')
    <x-botao-secundario href="#agendas-evento" class="mt-6">Consultar Agendas</x-botao-secundario>
@elseif($flow['eligible'])
    <x-botao-primario href="#agendas-evento" class="mt-6">Escolher uma Agenda</x-botao-primario>
@else
    <x-botao-primario as="button" :href="null" type="button" disabled aria-describedby="agenda-eligibility" class="mt-6">Escolher uma Agenda</x-botao-primario>
    <p id="agenda-eligibility" class="text-sm mt-2">Candidaturas disponíveis para Pessoas voluntárias aprovadas.</p>
@endif


                <x-botao-secundario as="button" :href="null" type="button" class="mt-3 w-full">
                    🔗 Compartilhar evento
                </x-botao-secundario>


                @unless($flow['scenario'] === 'participacao')
                <div
                    class="mt-6 border-t border-[#dedbd1]
                           pt-5 text-center text-xs
                           leading-5 text-neutral-500"
                >
                    Candidatura sujeita à aprovação do organizador.
                </div>
                @endunless
                @endif

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

                    <p>XP conforme a duração declarada da Agenda e os critérios de participação.</p>

                    <p>Certificado conforme finalização e requisitos aplicáveis.</p>

                    <p>A posição no ranking depende do histórico de participação.</p>

                </div>

            </div>

        </aside>

    </div>

</main>

</body>
</html>
