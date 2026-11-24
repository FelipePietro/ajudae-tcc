@php
    $integrated = config('identity.enabled');
    $organizerManagement = $organizerManagement ?? false;
    $listRoute = $organizerManagement ? 'organizador.eventos' : 'ong.eventos.index';
    $managementParams = $integrated ? [] : ['contexto' => request()->query('contexto', 'ong'), 'jornada' => request()->query('jornada')];
    $workspaceUrl = $organizerManagement ? route('organizador.workspace') : null;
@endphp
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Meus eventos | Ajudae</title>

    @vite([
        'resources/css/app.css',
        $integrated ? 'resources/js/identity.js' : 'resources/js/app.js'
    ])
</head>

<body @unless($integrated) data-events-page @endunless
    class="min-h-screen overflow-x-hidden
           bg-[#f7f5ef]
           font-outfit
           text-[#292820]"
>

    <x-context-navbar default-context="ong" :surface="$integrated && !$organizerManagement ? 'institution' : 'context'" />


    <main
        @class(['min-h-screen w-full', 'lg:ml-[230px] lg:w-[calc(100%-230px)]' => !$organizerManagement])
    >


        <x-breadcrumb-ong :raiz-href="$workspaceUrl" :raiz-label="$organizerManagement ? 'Minha atuação' : 'Dashboard'"
            :itens="[
                ['label' => 'Meus eventos']
            ]"
        />
@unless($integrated)<x-event-state :flow="$flow" />@endunless


        {{-- ============================================================= --}}
        {{-- CONTEÚDO --}}
        {{-- ============================================================= --}}

        <div
            class="mx-auto w-full max-w-[1450px]
                   px-4 py-6
                   sm:px-6 sm:py-8
                   lg:px-8"
        >

            {{-- ========================================================= --}}
            {{-- CABEÇALHO --}}
            {{-- ========================================================= --}}

            <div
                class="flex flex-col gap-4
                       md:flex-row
                       md:items-center
                       md:justify-between"
            >

                <div>

                    <h1
                        class="text-2xl font-semibold
                               text-[#17392a]
                               sm:text-3xl"
                    >
                        Meus eventos
                    </h1>

                    <p
                        class="mt-1
                               font-poppins
                               text-sm
                               text-[#817c70]"
                    >
                        Gerencie os eventos sob sua responsabilidade
                    </p>

                </div>


                @if($organizerManagement)
                    <x-botao-secundario :href="$workspaceUrl">Voltar para Minha atuação</x-botao-secundario>
                @else
                <x-botao-primario :href="route('ong.eventos.criar', $managementParams)" class="w-full md:w-auto">
                    + Criar evento
                </x-botao-primario>
                @endif

            </div>


            {{-- ========================================================= --}}
            {{-- CARDS DE RESUMO --}}
@unless($integrated)<x-organizer-scope :flow="$flow" />@endunless
            {{-- ========================================================= --}}

            <section
                class="mt-7 grid
                       grid-cols-1 gap-4
                       sm:grid-cols-2
                       xl:grid-cols-4"
            >

                {{-- ATIVOS --}}
                <div
                    class="rounded-2xl
                           border border-[#dedbd1]
                           bg-white
                           p-5
                           shadow-sm"
                >

                    <div
                        class="flex items-start
                               justify-between gap-3"
                    >

                        <div>

                            <p
                                class="font-poppins
                                       text-xs
                                       text-[#817c70]"
                            >
                                Eventos ativos
                            </p>

                            <p
                                class="mt-2
                                       text-3xl
                                       font-semibold
                                       text-[#17392a]"
                            >
                                {{ $resumo['ativos'] ?? '—' }}
                            </p>

                        </div>


                        <div
                            class="flex h-10 w-10
                                   items-center justify-center
                                   rounded-xl
                                   bg-[#e4f2e9]
                                   text-lg"
                        >
                            🗓️
                        </div>

                    </div>

                </div>


                {{-- AGUARDANDO --}}
                <div
                    class="rounded-2xl
                           border border-[#dedbd1]
                           bg-white
                           p-5
                           shadow-sm"
                >

                    <div
                        class="flex items-start
                               justify-between gap-3"
                    >

                        <div>

                            <p
                                class="font-poppins
                                       text-xs
                                       text-[#817c70]"
                            >
                                Aguardando aprovação
                            </p>

                            <p
                                class="mt-2
                                       text-3xl
                                       font-semibold
                                       text-[#17392a]"
                            >
                                {{ $resumo['aguardando'] ?? '—' }}
                            </p>

                        </div>


                        <div
                            class="flex h-10 w-10
                                   items-center justify-center
                                   rounded-xl
                                   bg-[#fff2d8]
                                   text-lg"
                        >
                            🕒
                        </div>

                    </div>

                </div>


                {{-- CANDIDATURAS --}}
                <div
                    class="rounded-2xl
                           border border-[#dedbd1]
                           bg-white
                           p-5
                           shadow-sm"
                >

                    <div
                        class="flex items-start
                               justify-between gap-3"
                    >

                        <div>

                            <p
                                class="font-poppins
                                       text-xs
                                       text-[#817c70]"
                            >
                                Candidaturas pendentes
                            </p>

                            <p
                                class="mt-2
                                       text-3xl
                                       font-semibold
                                       text-[#17392a]"
                            >
                                {{ $resumo['candidaturas_pendentes'] ?? '—' }}
                            </p>

                        </div>


                        <div
                            class="flex h-10 w-10
                                   items-center justify-center
                                   rounded-xl
                                   bg-[#e8eff8]
                                   text-lg"
                        >
                            👥
                        </div>

                    </div>

                </div>


                {{-- REALIZADOS --}}
                <div
                    class="rounded-2xl
                           border border-[#dedbd1]
                           bg-white
                           p-5
                           shadow-sm"
                >

                    <div
                        class="flex items-start
                               justify-between gap-3"
                    >

                        <div>

                            <p
                                class="font-poppins
                                       text-xs
                                       text-[#817c70]"
                            >
                                Eventos realizados
                            </p>

                            <p
                                class="mt-2
                                       text-3xl
                                       font-semibold
                                       text-[#17392a]"
                            >
                                {{ $resumo['realizados'] ?? '—' }}
                            </p>

                        </div>


                        <div
                            class="flex h-10 w-10
                                   items-center justify-center
                                   rounded-xl
                                   bg-[#f4e8ec]
                                   text-lg"
                        >
                            🏁
                        </div>

                    </div>

                </div>

            </section>


            {{-- ========================================================= --}}
            {{-- BUSCA + FILTROS --}}
            {{-- ========================================================= --}}

            <section
                class="mt-7 flex flex-col gap-3
                       xl:flex-row
                       xl:items-center
                       xl:justify-between"
            >

                {{-- BUSCA --}}
                <div
                    class="relative w-full
                           xl:max-w-[520px]"
                >

                    <span
                        class="pointer-events-none
                               absolute left-4 top-1/2
                               -translate-y-1/2
                               text-[#817c70]"
                    >
                        🔎
                    </span>

                    <input
                        type="search"
                        id="busca-eventos-ong" @disabled($integrated)
                        aria-label="Buscar meus eventos"
                        placeholder="Buscar por evento ou local..."
                        class="w-full
                               rounded-xl
                               border border-[#d8d4ca]
                               bg-white
                               py-3 pl-11 pr-4
                               font-poppins
                               text-sm
                               outline-none
                               transition
                               placeholder:text-[#aaa59b]
                               focus:border-[#17392a]"
                    >

                </div>


                {{-- FILTROS --}}
                <div
                    class="flex max-w-full
                           gap-2
                           overflow-x-auto
                           pb-1"
                >


                                    <x-botao-secundario as="button" :href="null" type="button"
                        data-status="todos" :disabled="$integrated" class="status-btn" size="sm" aria-pressed="true">
                        Todos
                    </x-botao-secundario>


                    <x-botao-secundario as="button" :href="null" type="button"
                        data-status="ativo" :disabled="$integrated" class="status-btn" size="sm" aria-pressed="false">
                        Publicados
                    </x-botao-secundario>


                    <x-botao-secundario as="button" :href="null" type="button"
                        data-status="aguardando a confirmação" :disabled="$integrated" class="status-btn" size="sm" aria-pressed="false">
                        Aguardando
                    </x-botao-secundario>


                    <x-botao-secundario as="button" :href="null" type="button"
                        data-status="reprovado" :disabled="$integrated" class="status-btn" size="sm" aria-pressed="false">
                        Recusados
                    </x-botao-secundario>

                </div>

            </section>


            {{-- ========================================================= --}}
            {{-- LISTA DE EVENTOS --}}
            {{-- ========================================================= --}}

            <section
                id="lista-eventos-ong"
                class="mt-6 space-y-4"
            >

                @foreach(($eventos ?? []) as $evento)

                    @php

                        $data =
                            \Carbon\Carbon::parse(
                                $evento['data_inicio']
                            );

                    @endphp


                    <article
                        data-evento-ong
                        data-status="{{ $evento['status_evento'] }}"
                        data-titulo="{{ strtolower($evento['nm_evento']) }}"
                        data-local="{{ strtolower(
                            $evento['cidade_evento']
                            . ' '
                            . $evento['bairro_evento']
                        ) }}"
                        class="rounded-2xl
                               border border-[#dedbd1]
                               bg-white
                               p-4
                               shadow-sm
                               transition
                               hover:shadow-md
                               sm:p-5"
                    >

                        <div
                            class="flex flex-col gap-4
                                   lg:flex-row"
                        >

                            {{-- ================================================= --}}
                            {{-- DATA --}}
                            {{-- ================================================= --}}

                            <div
                                class="flex
                                       shrink-0
                                       items-center gap-3
                                       lg:block"
                            >

                                <div
                                    class="flex h-[78px] w-[78px]
                                           shrink-0
                                           flex-col
                                           items-center justify-center
                                           rounded-xl
                                           border border-[#dedbd1]
                                           bg-[#fbfaf7]"
                                >

                                    <strong
                                        class="text-2xl
                                               text-[#17392a]"
                                    >
                                        {{ $data->format('d') }}
                                    </strong>

                                    <span
                                        class="font-poppins
                                               text-xs
                                               font-semibold
                                               uppercase
                                               text-[#817c70]"
                                    >
                                        {{ $data
                                            ->locale('pt_BR')
                                            ->translatedFormat('M')
                                        }}
                                    </span>

                                    <span
                                        class="font-poppins
                                               text-[10px]
                                               text-[#aaa59b]"
                                    >
                                        {{ $data->format('Y') }}
                                    </span>

                                </div>

                            </div>


                            {{-- ================================================= --}}
                            {{-- INFORMAÇÕES --}}
                            {{-- ================================================= --}}

                            <div
                                class="min-w-0 flex-1"
                            >

                                {{-- CABEÇALHO --}}
                                <div
                                    class="flex flex-col gap-2
                                           sm:flex-row
                                           sm:items-start
                                           sm:justify-between"
                                >

                                    <div class="min-w-0">

                                        <h2
                                            class="font-poppins
                                                   text-base
                                                   font-semibold
                                                   text-[#17392a]
                                                   sm:text-lg"
                                        >
                                            {{ $evento['nm_evento'] }}
                                        </h2>


                                        <div
                                            class="mt-2
                                                   flex flex-wrap
                                                   items-center gap-x-4
                                                   gap-y-1
                                                   font-poppins
                                                   text-xs
                                                   text-[#817c70]"
                                        >

                                            <span>
                                                📅
                                                {{ $data
                                                    ->locale('pt_BR')
                                                    ->translatedFormat(
                                                        'd \d\e M. \d\e Y'
                                                    )
                                                }}
                                            </span>

                                            <span>
                                                📍
                                                {{ $evento['bairro_evento'] }},
                                                {{ $evento['cidade_evento'] }}
                                            </span>

                                        </div>

                                    </div>


{{-- STATUS --}}
                                    @if($evento['status_evento'] === 'ativo')

                                        <span
                                            data-closure-event-badge="{{ \App\Support\VisualFiveFixtures::storageId($evento['evento_id']) }}"
                                            class="w-fit shrink-0
                                                   rounded-full
                                                   bg-[#dff2e6]
                                                   px-3 py-1.5
                                                   font-poppins
                                                   text-[10px]
                                                   font-semibold
                                                   text-[#347251]"
                                        >
                                            ◉ Publicado
                                        </span>

                                    @elseif($evento['status_evento'] === 'aguardando a confirmação')

                                        <span
                                            class="w-fit shrink-0
                                                   rounded-full
                                                   bg-[#fff2ce]
                                                   px-3 py-1.5
                                                   font-poppins
                                                   text-[10px]
                                                   font-semibold
                                                   text-[#9a6b13]"
                                        >
                                            ⓘ Aguardando aprovação
                                        </span>

                                    @elseif($evento['status_evento'] === 'reprovado')

                                        <span
                                            class="w-fit shrink-0
                                                   rounded-full
                                                   bg-[#efefed]
                                                   px-3 py-1.5
                                                   font-poppins
                                                   text-[10px]
                                                   font-semibold
                                                   text-[#716d65]"
                                        >
                                            Recusado
                                        </span>

                                    @else

                                        <span
                                            class="w-fit shrink-0
                                                   rounded-full
                                                   bg-[#eee]
                                                   px-3 py-1.5
                                                   font-poppins
                                                   text-[10px]"
                                        >
                                            {{ $evento['status_label'] }}
                                        </span>

                                    @endif

                                </div>


<p class="text-sm text-[#817c70] mt-3">{{ count($evento['agendas_mock']) }} Agendas · {{ collect($evento['agendas_mock'])->sum('vagas_agenda') }} vagas somadas nas Agendas</p>
<details class="event-flow my-4 rounded-xl border border-[#dedbd1] p-4" data-event-disclosure><summary class="cursor-pointer font-semibold">Detalhes do evento e Agendas</summary><div class="mt-3 space-y-3"><p>{{ $evento['ong_id'] ? 'Evento institucional · '.$flow['management_scope']['label'] : 'Evento independente · '.$flow['management_scope']['person'] }} · {{ $evento['descricao_resumida'] }}</p>@foreach($evento['agendas_mock'] as $item)<p>Agenda {{ $loop->iteration }} · {{ \Carbon\Carbon::parse($item['data_inicio'])->format('d/m/Y H:i') }}–{{ \Carbon\Carbon::parse($item['data_fim'])->format('H:i') }} · {{ $item['vagas_agenda'] }} vagas · {{ $item['ocupadas'] }} ocupadas</p>@endforeach<p class="text-sm text-[#817c70]">Capacidades independentes por Agenda. A soma não representa pessoas únicas.</p></div></details>
                                {{-- RODAPÉ DO EVENTO --}}
                                <div
                                    class="flex flex-col gap-4
                                           md:flex-row
                                           md:items-center
                                           md:justify-between"
                                >

                                    {{-- ORGANIZADOR --}}
                                    <div
                                        class="flex flex-wrap
                                               items-center gap-4"
                                    >

                                        <div
                                            class="flex items-center gap-3"
                                        >

                                            <div
                                                class="flex h-9 w-9
                                                       shrink-0
                                                       items-center justify-center
                                                       rounded-full
                                                       bg-[#317457]
                                                       font-poppins
                                                       text-xs
                                                       font-semibold
                                                       text-white"
                                            >
                                                {{ $evento['responsavel']['iniciais'] ?? 'ONG' }}
                                            </div>


                                            <div>

                                                <p
                                                    class="font-poppins
                                                           text-xs
                                                           font-semibold"
                                                >
                                                    {{ $evento['responsavel']['nome'] ?? $flow['management_scope']['label'] }}
                                                </p>

                                                <p
                                                    class="font-poppins
                                                           text-[10px]
                                                           text-[#999388]"
                                                >
                                                    {{ $evento['responsavel'] ? 'Organizador' : 'Responsabilidade institucional' }}
                                                </p>

                                            </div>

                                        </div>


                                        {{-- CANDIDATURAS --}}
                                        <div
                                            class="flex items-center gap-2"
                                        >

                                            <div
                                                class="flex h-9 w-9
                                                       items-center justify-center
                                                       rounded-full
                                                       bg-[#eef3f6]"
                                            >
                                                👥
                                            </div>

                                            <div>

                                                <p
                                                    class="font-poppins
                                                           text-xs
                                                           font-semibold"
                                                >
                                                    {{ $evento['candidaturas_pendentes'] }}
                                                    pend.
                                                </p>

                                                <p
                                                    class="font-poppins
                                                           text-[10px]
                                                           text-[#999388]"
                                                >
                                                    {{ $evento['candidaturas_aprovadas'] }}
                                                    aprovadas
                                                </p>

                                            </div>

                                        </div>

                                    </div>


                                    {{-- AÇÕES --}}
                                    <div
                                        class="grid grid-cols-2 gap-2
                                               sm:flex"
                                    >
<x-botao-secundario :href="route('ong.candidatos', ['evento'=>$evento['evento_id'], 'cenario'=>$evento['scenario'], 'contexto'=>request()->query('contexto','ong'), 'jornada'=>request()->query('jornada')])" size="sm">Candidaturas por Agenda</x-botao-secundario>
@if($presenceUrl = \App\Support\VisualFiveFixtures::managerUrl($evento['evento_id']))
    <x-botao-secundario :href="$presenceUrl" size="sm">Presença e encerramento</x-botao-secundario>
@endif


                                        <x-botao-primario :href="route(
                                                $evento['status_evento'] === 'ativo' ? 'eventos.show' : 'ong.eventos.editar',
                                                ['id'=>$evento['evento_id'], 'cenario'=>$evento['scenario'], 'contexto'=>request()->query('contexto','ong'), 'jornada'=>request()->query('jornada')]
                                            )" size="sm">
                                            {{ $evento['status_evento'] === 'ativo' ? '◉ Ver' : 'Revisar situação' }}
                                        </x-botao-primario>


                                        <x-botao-primario :href="route(
                                                'ong.eventos.editar',
                                                ['id'=>$evento['evento_id'], 'cenario'=>$evento['scenario'], 'contexto'=>request()->query('contexto','ong'), 'jornada'=>request()->query('jornada')]
                                            )" size="sm">
                                            ✎ Editar
                                        </x-botao-primario>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </article>

                @endforeach

            </section>


            {{-- ========================================================= --}}
            {{-- SEM RESULTADOS --}}
            {{-- ========================================================= --}}

            <div
                id="sem-eventos-ong"
                class="mt-6 {{ $integrated ? '' : 'hidden' }}
                       rounded-2xl
                       border border-[#dedbd1]
                       bg-white
                       p-10
                       text-center
                       shadow-sm"
            >

                <div class="text-3xl">
                    🔎
                </div>

                <p
                    class="mt-3
                           font-poppins
                           text-sm
                           font-semibold"
                >
                    {{ $integrated ? 'A consulta e a gestão de Eventos ainda estão indisponíveis.' : 'Nenhum evento encontrado' }}
                </p>

                <p
                    class="mt-1
                           font-poppins
                           text-xs
                           text-[#817c70]"
                >
                    @unless($integrated)Tente alterar sua busca ou o filtro selecionado.@endunless
                </p>

            </div>

        </div>

    </main>


    {{-- ============================================================= --}}
    {{-- JAVASCRIPT --}}
    {{-- ============================================================= --}}

    @unless($integrated)
    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const busca =
                document.querySelector(
                    '#busca-eventos-ong'
                );

            const botoesStatus =
                document.querySelectorAll(
                    '.status-btn'
                );

            const eventos =
                document.querySelectorAll(
                    '[data-evento-ong]'
                );

            const semEventos =
                document.querySelector(
                    '#sem-eventos-ong'
                );


            let statusAtual =
                'todos';


            /*
            |--------------------------------------------------------------------------
            | FILTRAR EVENTOS
            |--------------------------------------------------------------------------
            */

            function filtrarEventos() {

                const texto =
                    busca.value
                        .trim()
                        .toLowerCase();


                let encontrados =
                    0;


                eventos.forEach(evento => {

                    const titulo =
                        evento.dataset.titulo;

                    const local =
                        evento.dataset.local;

                    const status =
                        evento.dataset.status;


                    const correspondeBusca =
                        !texto
                        ||
                        titulo.includes(texto)
                        ||
                        local.includes(texto);


                    const correspondeStatus =
                        statusAtual === 'todos'
                        ||
                        status === statusAtual;


                    const mostrar =
                        correspondeBusca
                        &&
                        correspondeStatus;


                    evento.classList.toggle(
                        'hidden',
                        !mostrar
                    );


                    if (mostrar) {
                        encontrados++;
                    }

                });


                semEventos.classList.toggle(
                    'hidden',
                    encontrados > 0
                );

            }


            /*
            |--------------------------------------------------------------------------
            | BUSCA
            |--------------------------------------------------------------------------
            */

            busca.addEventListener(
                'input',
                filtrarEventos
            );


            /*
            |--------------------------------------------------------------------------
            | FILTROS DE STATUS
            |--------------------------------------------------------------------------
            */

            botoesStatus.forEach(botao => {

                botao.addEventListener(
                    'click',
                    () => {

                        statusAtual =
                            botao.dataset.status;


                        botoesStatus.forEach(item => {

                            item.classList.remove(
                                'bg-[#17392a]',
                                'text-white'
                            );

                            item.classList.add(
                                'border',
                                'border-[#d8d4ca]',
                                'bg-white',
                                'text-[#625e55]'
                            );

                        });


                        botao.classList.remove(
                            'border',
                            'border-[#d8d4ca]',
                            'bg-white',
                            'text-[#625e55]'
                        );

                        botao.classList.add(
                            'bg-[#17392a]',
                            'text-white'
                        );


                        filtrarEventos();

                    }
                );

            });


            /*
            |--------------------------------------------------------------------------
            | INICIALIZAÇÃO
            |--------------------------------------------------------------------------
            */

            filtrarEventos();

        });
    </script>
    @endunless

</body>

</html>
