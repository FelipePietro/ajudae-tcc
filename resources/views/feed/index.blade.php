@php
    $integrated = isset($publicEvents);
@endphp
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Feed de eventos | Ajudae</title>

    @vite([
        'resources/css/app.css',
        $integrated ? 'resources/js/identity.js' : 'resources/js/app.js'
    ])
</head>

<body
    class="min-h-screen overflow-x-hidden
           bg-[#f7f5ef]
           font-outfit
           text-[#292820]"
>

    <x-context-navbar :surface="$integrated ? 'institution' : 'context'" />


    <main
        class="mx-auto w-full max-w-[1180px]
               px-4 py-7
               sm:px-6 sm:py-9
               lg:px-8"
    >
        @unless($integrated)
            <x-event-state :flow="$flow" />
        @endunless

        <div
            class="grid grid-cols-1 gap-6
                   xl:grid-cols-[minmax(0,1fr)_290px]"
        >

            {{-- ===================================================== --}}
            {{-- CONTEÚDO PRINCIPAL --}}
            {{-- ===================================================== --}}

            <section class="min-w-0">

                {{-- CABEÇALHO --}}
                <div>
                    <h1
                        class="text-2xl font-semibold
                               sm:text-3xl"
                    >
                        Encontre eventos
                    </h1>

                    <p
                        class="mt-1
                               font-poppins
                               text-sm
                               text-[#817c70]"
                    >
                        Descubra oportunidades de voluntariado perto de você
                    </p>
                </div>


                {{-- ================================================= --}}
                {{-- BUSCA --}}
                {{-- ================================================= --}}

                @if($integrated)
                    <form action="{{ route('feed') }}" method="GET" data-public-feed-filters aria-label="Busca e filtros">
                @endif
                <div
                    class="mt-5 flex flex-col gap-3
                           sm:flex-row"
                >

                    <div class="relative min-w-0 flex-1">

                        <span
                            class="pointer-events-none
                                   absolute left-4 top-1/2
                                   -translate-y-1/2
                                   text-[#817c70]"
                        >
                            🔎
                        </span>

                        <label for="busca-eventos" class="sr-only">Buscar eventos por título ou cidade</label>
                        <input
                            type="search"
                            id="busca-eventos" @if($integrated) name="q" maxlength="120" value="{{ $publicFilters['q'] }}" @endif
                            placeholder="Buscar eventos por título ou cidade..."
                            class="w-full rounded-xl
                                   border border-[#d8d4ca]
                                   bg-white
                                   py-3 pl-11 pr-4
                                   font-poppins text-sm
                                   outline-none
                                   transition
                                   placeholder:text-[#aaa59b]
                                   focus:border-[#17392a]"
                        >

                    </div>


                    @if($integrated)
                        <x-botao-primario as="button" type="submit">Buscar</x-botao-primario>
                    @endif
                    <x-botao-secundario as="button" :href="null" type="button"
                        id="btn-filtros" aria-controls="painel-filtros" :aria-expanded="$integrated && $publicFilters['modalidade'] !== '' ? 'true' : 'false'">
                        ☰ Filtros
                    </x-botao-secundario>

                </div>


                {{-- ================================================= --}}
                {{-- PERÍODO --}}
                {{-- ================================================= --}}

                <div
                    class="mt-4 flex max-w-full
                           gap-2 overflow-x-auto pb-1"
                >

                    <x-botao-secundario as="button" :href="null" type="button"
                        data-periodo="todos" :class="$integrated ? 'periodo-btn shrink-0 whitespace-nowrap' : 'periodo-btn'" size="sm" aria-pressed="true">
                        Todos
                    </x-botao-secundario>


                    <x-botao-secundario as="button" :href="null" type="button"
                        data-periodo="hoje" :disabled="$integrated" :aria-describedby="$integrated ? 'filtros-pendentes' : null" :class="$integrated ? 'periodo-btn shrink-0 whitespace-nowrap' : 'periodo-btn'" size="sm" aria-pressed="false">
                        Hoje
                    </x-botao-secundario>


                    <x-botao-secundario as="button" :href="null" type="button"
                        data-periodo="semana" :disabled="$integrated" :aria-describedby="$integrated ? 'filtros-pendentes' : null" :class="$integrated ? 'periodo-btn shrink-0 whitespace-nowrap' : 'periodo-btn'" size="sm" aria-pressed="false">
                        Esta semana
                    </x-botao-secundario>


                    <x-botao-secundario as="button" :href="null" type="button"
                        data-periodo="data" :disabled="$integrated" :aria-describedby="$integrated ? 'filtros-pendentes' : null" :class="$integrated ? 'periodo-btn shrink-0 whitespace-nowrap' : 'periodo-btn'" size="sm" aria-pressed="false">
                        Por data
                    </x-botao-secundario>

                </div>


                {{-- ================================================= --}}
                {{-- FILTROS PRINCIPAIS --}}
                {{-- ================================================= --}}

                <div
                    class="mt-4 grid grid-cols-1
                           gap-2
                           sm:grid-cols-2"
                >

                    <select
                        id="filtro-categoria" @if($integrated) disabled aria-describedby="filtros-pendentes" @endif
                        aria-label="Categoria do evento"
                        class="rounded-xl
                               border border-[#d8d4ca]
                               bg-white
                               px-4 py-3
                               font-poppins text-xs
                               outline-none
                               focus:border-[#17392a]"
                    >

                        <option value="todos">
                            Categoria: Todas
                        </option>

                        @foreach($categorias ?? [] as $categoria)

                            <option
                                value="{{ $categoria['categoria_id'] }}"
                            >
                                {{ $categoria['nome_categoria'] }}
                            </option>

                        @endforeach

                    </select>


                    <select
                        id="filtro-cidade" @if($integrated) name="cidade" @endif
                        aria-label="Cidade do evento"
                        class="rounded-xl
                               border border-[#d8d4ca]
                               bg-white
                               px-4 py-3
                               font-poppins text-xs
                               outline-none
                               focus:border-[#17392a]"
                    >

                        <option value="{{ $integrated ? '' : 'todos' }}">
                            📍 Todas as cidades
                        </option>

                        @foreach($cidades as $cidade)

                            <option value="{{ $cidade }}" @selected($integrated && $publicFilters['cidade'] === $cidade)>
                                📍 {{ $cidade }}
                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- ================================================= --}}
                {{-- PAINEL DE FILTROS AVANÇADOS --}}
                {{-- ================================================= --}}

                <div
                    id="painel-filtros" @if($integrated && $publicFilters['modalidade'] === '') hidden @endif
                    class="mt-3 {{ $integrated ? '' : 'hidden' }}
                           rounded-2xl
                           border border-[#dedacf]
                           bg-white
                           p-4 shadow-sm
                           sm:p-5"
                >

                    <div
                        class="grid grid-cols-1 gap-4
                               md:grid-cols-2"
                    >

                        {{-- MODALIDADE --}}
                        <div>

                            <label
                                for="filtro-modalidade"
                                class="mb-2 block
                                       font-poppins
                                       text-xs font-medium
                                       text-[#817c70]"
                            >
                                Modalidade
                            </label>

                            <select
                                id="filtro-modalidade" @if($integrated) name="modalidade" @endif
                                class="w-full rounded-xl
                                       border border-[#d8d4ca]
                                       bg-white
                                       px-4 py-3
                                       font-poppins text-sm
                                       outline-none
                                       focus:border-[#17392a]"
                            >

                                <option value="{{ $integrated ? '' : 'todos' }}">
                                    Todas
                                </option>

                                <option value="presencial" @selected($integrated && $publicFilters['modalidade'] === 'presencial')>
                                    Presencial
                                </option>

                                <option value="online" @selected($integrated && $publicFilters['modalidade'] === 'online')>
                                    Online
                                </option>

                                <option value="hibrido" @selected($integrated && $publicFilters['modalidade'] === 'hibrido')>
                                    Híbrido
                                </option>

                            </select>

                        </div>


                        {{-- DATA ESPECÍFICA --}}
                        <div
                            id="container-data-especifica"
                            class="hidden"
                        >

                            <label
                                for="filtro-data"
                                class="mb-2 block
                                       font-poppins
                                       text-xs font-medium
                                       text-[#817c70]"
                            >
                                Data do evento
                            </label>

                            <input
                                type="date"
                                id="filtro-data"
                                class="w-full rounded-xl
                                       border border-[#d8d4ca]
                                       bg-white
                                       px-4 py-3
                                       font-poppins text-sm
                                       outline-none
                                       focus:border-[#17392a]"
                            >

                        </div>

                    </div>


                    <div
                        class="mt-4 flex
                               flex-col-reverse gap-2
                               sm:flex-row
                               sm:justify-end"
                    >

                        @if($integrated)
                            <x-botao-secundario :href="route('feed')" size="sm">Limpar filtros</x-botao-secundario>
                            <x-botao-primario as="button" type="submit" size="sm">Aplicar filtros</x-botao-primario>
                        @else
                        <x-botao-secundario as="button" :href="null" type="button"
                            id="btn-limpar-filtros" size="sm">
                            Limpar filtros
                        </x-botao-secundario>


                        <x-botao-primario as="button" :href="null" type="button"
                            id="btn-fechar-filtros" size="sm">
                            Aplicar filtros
                        </x-botao-primario>
                        @endif

                    </div>

                </div>


                {{-- ================================================= --}}
                {{-- RESUMO DOS FILTROS --}}
                {{-- ================================================= --}}

                <div
                    id="filtros-ativos"
                    class="mt-4 hidden
                           flex-wrap gap-2"
                ></div>


                {{-- ================================================= --}}
                {{-- GRID DE EVENTOS --}}
                {{-- ================================================= --}}

                @if($integrated)
                    <p id="filtros-pendentes" class="mt-3 text-xs text-[#56594f]">Os filtros de categoria e período ainda estão indisponíveis.</p>
                    </form>
                @endif
                <div
                    id="lista-eventos"
                    class="mt-6 grid
                           grid-cols-1 gap-4
                           md:grid-cols-2"
                >

                    @if($integrated)
                        @include('auth.public-feed')
                    @else
                    @foreach($eventos as $evento)

                        @php

                            $dataInicio =
                                \Carbon\Carbon::parse(
                                    $evento['data_inicio']
                                );

                            $dataFim =
                                \Carbon\Carbon::parse(
                                    $evento['data_fim']
                                );

                            $percentual =
                                $evento['agenda_capacity'] > 0
                                    ? (
                                        $evento['confirmados']
                                        /
                                        $evento['agenda_capacity']
                                    ) * 100
                                    : 0;

                        @endphp


                        <article
                            data-evento

                            data-id="{{ $evento['evento_id'] }}"

                            data-titulo="{{ strtolower($evento['nm_evento']) }}"

                            data-cidade="{{ $evento['cidade_evento'] }}"

                            data-categoria="{{ $evento['categoria']['categoria_id'] }}"

                            data-modalidade="{{ $evento['modalidade_evento'] }}"

                            data-data="{{ $dataInicio->format('Y-m-d') }}"

                            class="group flex min-h-[370px]
                                   flex-col overflow-hidden
                                   rounded-2xl
                                   border border-[#dedacf]
                                   bg-white
                                   shadow-sm
                                   transition
                                   hover:-translate-y-0.5
                                   hover:shadow-md"
                        >

                            {{-- IMAGEM --}}
                            <div
                                class="relative h-[185px]
                                       overflow-hidden
                                       bg-[#e7eee9]"
                            >

                                @if(!empty($evento['imagem_evento_link']))

                                    <img
                                        src="{{ $evento['imagem_evento_link'] }}"
                                        alt="{{ $evento['nm_evento'] }}"
                                        class="h-full w-full
                                               object-cover
                                               transition duration-300
                                               group-hover:scale-[1.02]"
                                    >

                                @else

                                    <div
                                        class="flex h-full
                                               items-center justify-center
                                               bg-gradient-to-br
                                               from-[#dbece2]
                                               to-[#f1eee5]
                                               text-5xl"
                                    >
                                        {{ $evento['icone'] ?? '🌱' }}
                                    </div>

                                @endif


                                <span
                                    class="absolute right-3 top-3
                                           rounded-full
                                           bg-white/90
                                           px-3 py-1.5
                                           font-poppins
                                           text-[10px]
                                           font-medium
                                           shadow-sm"
                                >
                                    {{ $evento['vagas_disponiveis'] }} vagas na próxima Agenda
                                </span>

                            </div>


                            {{-- CONTEÚDO --}}
                            <div
                                class="flex flex-1
                                       flex-col p-4"
                            >

                                {{-- TAGS --}}
                                <div
                                    class="flex flex-wrap gap-2"
                                >

                                    <span
                                        class="rounded-full
                                               bg-[#dcefe5]
                                               px-3 py-1
                                               font-poppins
                                               text-[10px]
                                               font-medium
                                               text-[#315f47]"
                                    >
                                        {{ $evento['tipo_organizador'] }}
                                    </span>


                                    <span
                                        class="rounded-full
                                               border border-[#dedacf]
                                               bg-[#faf9f5]
                                               px-3 py-1
                                               font-poppins
                                               text-[10px]"
                                    >
                                        {{ $evento['categoria']['nome_categoria'] }}
                                    </span>


                                    <span
                                        class="rounded-full
                                               border border-[#dedacf]
                                               bg-[#faf9f5]
                                               px-3 py-1
                                               font-poppins
                                               text-[10px]
                                               capitalize"
                                    >
                                        {{ $evento['modalidade_evento'] }}
                                    </span>

                                </div>


                                {{-- TÍTULO --}}
                                <h2
                                    class="mt-3
                                           font-poppins
                                           text-[15px]
                                           font-semibold
                                           leading-5"
                                >
                                    {{ $evento['nm_evento'] }}
                                </h2><p class="mt-2 text-xs text-neutral-500">{{ $evento['agenda_count'] }} {{ $evento['agenda_count'] === 1 ? 'Agenda disponível' : 'Agendas disponíveis' }}</p><p class="mt-1 text-xs text-neutral-500">{{ implode(' · ', array_merge($evento['causas_labels'], $evento['habilidades_labels'])) }}</p>


                                {{-- DADOS --}}
                                <div
                                    class="mt-3 space-y-1.5
                                           font-poppins
                                           text-[11px]
                                           text-[#69645b]"
                                >

                                    <p>
                                        📅
                                        {{ $dataInicio->locale('pt_BR')->translatedFormat('d M Y') }}

                                        ·

                                        {{ $dataInicio->format('H\hi') }}
                                        –
                                        {{ $dataFim->format('H\hi') }}
                                    </p>


                                    <p>
                                        📍
                                        {{ $evento['cidade_evento'] }},
                                        {{ $evento['uf_evento'] }}
                                    </p>


                                    <p class="truncate">
                                        ◉ {{ $evento['organizador_nome'] }}
                                    </p>

                                </div>


                                {{-- RODAPÉ --}}
                                <div class="mt-auto pt-5">

                                    <div
                                        class="flex items-end
                                               justify-between
                                               gap-4"
                                    >

                                        <div class="min-w-0 flex-1">

                                            <div
                                                class="h-2
                                                       overflow-hidden
                                                       rounded-full
                                                       bg-[#dcefe5]"
                                            >

                                                <div
                                                    class="h-full
                                                           rounded-full
                                                           {{ $percentual >= 85
                                                                ? 'bg-red-500'
                                                                : 'bg-[#17392a]' }}"
                                                    style="
                                                        width:
                                                        {{ min(100, $percentual) }}%;
                                                    "
                                                ></div>

                                            </div>


                                            <p
                                                class="mt-2
                                                       font-poppins
                                                       text-[10px]
                                                       {{ $percentual >= 85
                                                            ? 'text-red-500'
                                                            : 'text-[#817c70]' }}"
                                            >
                                                {{ $evento['confirmados'] }}
                                                /
                                                {{ $evento['agenda_capacity'] }}
                                                confirmados
                                            </p>

                                        </div>


                                        <x-botao-primario :href="route(
                                                'eventos.show',
                                                ['id'=>$evento['evento_id'], 'contexto'=>request()->query('contexto','pessoa'), 'cenario'=>$flow['scenario']]
                                            )" class="shrink-0" size="sm">
                                            Ver evento
                                        </x-botao-primario>

                                    </div>

                                </div>

                            </div>

                        </article>

                    @endforeach
                    @endif

                </div>


                @if($integrated)
                    <div class="mt-6">{{ $publicEvents->links() }}</div>
                @else
                <p data-feed-count role="status" aria-atomic="true" class="mt-4 text-sm text-[#56594f]"></p>
                {{-- NENHUM RESULTADO --}}
                <div
                    id="sem-resultados" role="status" aria-atomic="true" @if($flow['loading'] || $flow['error']) hidden @endif
                    class="mt-6 hidden
                           rounded-2xl
                           border border-[#dedacf]
                           bg-white
                           p-8 text-center"
                >

                    <div class="text-3xl">
                        🔎
                    </div>

                    <p
                        class="mt-3
                               font-poppins
                               text-sm font-semibold"
                    >
                        Nenhum evento encontrado
                    </p>

                    <p
                        class="mt-1
                               font-poppins
                               text-xs text-[#817c70]"
                    >
                        Tente alterar os filtros ou sua busca.
                    </p>

                </div>

                @endif
            </section>


            {{-- ===================================================== --}}
            {{-- SIDEBAR --}}
            {{-- ===================================================== --}}

            <aside
                class="space-y-4
                       xl:sticky
                       xl:top-6
                       xl:self-start"
            >

                @if($integrated)
                    <section class="rounded-2xl border border-[#dedacf] bg-white p-5 shadow-sm" aria-labelledby="feed-ranking">
                        <h2 id="feed-ranking" tabindex="-1" class="font-poppins text-sm font-semibold">🏆 Top ranking</h2>
                        <div class="mt-4 space-y-3"><p class="text-sm text-[#56594f]">A classificação ainda está indisponível.</p></div>
                        <x-botao-primario :href="route('ranking')" class="mt-3" size="sm">Ver ranking completo</x-botao-primario>
                    </section>
                    @if(\App\Support\WebNavigation::for(request())->profile === 'perfil')
                        <section class="rounded-2xl border border-[#dedacf] bg-white p-5 shadow-sm" aria-labelledby="feed-conquistas">
                            <h2 id="feed-conquistas" class="font-poppins text-sm font-semibold">Seus badges</h2>
                            <p class="mt-4 text-sm text-[#56594f]">As conquistas ainda estão indisponíveis nesta página.</p>
                        </section>
                    @endif
                @else
                {{-- TOP RANKING --}}
                <section
                    class="rounded-2xl
                           border border-[#dedacf]
                           bg-white
                           p-5 shadow-sm"
                >

                    <h2
                        class="font-poppins
                               text-sm font-semibold"
                    >
                        🏆 Top ranking
                    </h2>


                    <div class="mt-4 space-y-3">

                        @foreach($topRanking as $pessoa)

                            <div
                                class="flex items-center gap-3"
                            >

                                <span
                                    class="w-5 shrink-0
                                           font-poppins
                                           text-xs
                                           {{ $pessoa['posicao'] === 1
                                                ? 'text-[#d99519]'
                                                : 'text-[#8a857b]' }}"
                                >
                                    #{{ $pessoa['posicao'] }}
                                </span>


                                <div
                                    class="flex h-8 w-8
                                           shrink-0
                                           items-center justify-center
                                           rounded-full
                                           bg-[#e7eee9]
                                           font-poppins
                                           text-[10px]
                                           font-semibold"
                                >
                                    {{ $pessoa['iniciais'] }}
                                </div>


                                <p
                                    class="min-w-0
                                           flex-1 truncate
                                           font-poppins
                                           text-xs"
                                >
                                    {{ $pessoa['nome'] }}
                                </p>


                                <span
                                    class="rounded-full
                                           bg-[#fff2d8]
                                           px-2 py-1
                                           font-poppins
                                           text-[9px]
                                           font-medium"
                                >
                                    {{ $pessoa['xp'] }} XP
                                </span>

                            </div>

                        @endforeach

                    </div>


                    @if(in_array(request()->query('contexto'), ['pessoa','organizador','organizador-vinculado'], true))
                    <div
                        class="mt-5
                               rounded-lg
                               bg-[#f7f5ef]
                               px-3 py-2
                               text-center
                               font-poppins
                               text-[10px]
                               text-[#817c70]"
                    >
                        Sua posição:

                        <strong>
                            #{{ $usuario['posicao_ranking'] }}
                        </strong>

                        · {{ $usuario['xp'] }} XP
                    </div>


                    @endif

                    <x-botao-primario href="/ranking" class="mt-3" size="sm">
                        Ver ranking completo
                    </x-botao-primario>

                </section>


                @if(in_array(request()->query('contexto'), ['pessoa','organizador','organizador-vinculado'], true))
                {{-- BADGES --}}
                <section
                    class="rounded-2xl
                           border border-[#dedacf]
                           bg-white
                           p-5
                           shadow-sm"
                >

                    <h2
                        class="font-poppins
                               text-sm font-semibold"
                    >
                        Seus badges
                    </h2>


                    <div
                        class="mt-4 grid
                               grid-cols-3 gap-2"
                    >

                        @foreach($badges as $badge)

                            <div
                                class="flex aspect-square
                                       items-center justify-center
                                       rounded-xl
                                       border border-[#e3ded3]
                                       text-2xl
                                       {{ $badge['conquistado']
                                            ? 'bg-[#faf9f5]'
                                            : 'bg-[#fbfaf8] opacity-35' }}"
                                title="{{ $badge['nome'] }}"
                            >
                                {{ $badge['icone'] }}
                            </div>

                        @endforeach

                    </div>


                    <p
                        class="mt-3
                               font-poppins
                               text-[10px]
                               text-[#817c70]"
                    >
                        {{ $badgesConquistados }} conquistados ·
                        {{ count($badges) - $badgesConquistados }}
                        bloqueados
                    </p>


                    <x-botao-secundario href="/perfil" class="mt-4" size="sm">
                        Ver todos os badges
                    </x-botao-secundario>

                </section>


                @endif

                {{-- DICA --}}
                <div
                    class="rounded-xl
                           border-l-4
                           border-[#17392a]
                           bg-[#eaf5ef]
                           px-4 py-4
                           font-poppins
                           text-xs leading-5
                           text-[#4e695c]"
                >
                    💡
                    <strong>Dica:</strong>

                    Participe de eventos e desbloqueie novos badges.
                </div>

                @endif
                @if($integrated)
                    <div class="rounded-xl border border-[#17392a] bg-[#eaf5ef] px-4 py-4 font-poppins text-xs leading-5 text-[#4e695c]">
                        💡 <strong>Dica:</strong> use a cidade ou a modalidade para encontrar oportunidades.
                    </div>
                @endif
            </aside>

        </div>

    </main>


    {{-- ============================================================= --}}
    {{-- JAVASCRIPT --}}
    {{-- ============================================================= --}}

    @unless($integrated)
    <script>

        document.addEventListener('DOMContentLoaded', () => {

            /*
            |--------------------------------------------------------------------------
            | ELEMENTOS
            |--------------------------------------------------------------------------
            */

            const busca =
                document.querySelector('#busca-eventos');

            const categoria =
                document.querySelector('#filtro-categoria');

            const cidade =
                document.querySelector('#filtro-cidade');

            const modalidade =
                document.querySelector('#filtro-modalidade');

            const dataEspecifica =
                document.querySelector('#filtro-data');

            const containerData =
                document.querySelector('#container-data-especifica');

            const painelFiltros =
                document.querySelector('#painel-filtros');

            const btnFiltros =
                document.querySelector('#btn-filtros');

            const btnFecharFiltros =
                document.querySelector('#btn-fechar-filtros');

            const btnLimpar =
                document.querySelector('#btn-limpar-filtros');

            const periodoBtns =
                document.querySelectorAll('.periodo-btn');

            const cards =
                document.querySelectorAll('[data-evento]');

            const semResultados =
                document.querySelector('#sem-resultados');

            const filtrosAtivos =
                document.querySelector('#filtros-ativos');


            let periodoAtual =
                'todos';


            /*
            |--------------------------------------------------------------------------
            | DATAS
            |--------------------------------------------------------------------------
            */

            function dataLocalISO(data) {

                const ano =
                    data.getFullYear();

                const mes =
                    String(
                        data.getMonth() + 1
                    ).padStart(2, '0');

                const dia =
                    String(
                        data.getDate()
                    ).padStart(2, '0');


                return `${ano}-${mes}-${dia}`;

            }


            function hojeISO() {

                return dataLocalISO(
                    new Date()
                );

            }


            function inicioDaSemana() {

                const hoje =
                    new Date();

                const diaSemana =
                    hoje.getDay();

                const diferenca =
                    diaSemana === 0
                        ? -6
                        : 1 - diaSemana;


                const inicio =
                    new Date(hoje);

                inicio.setDate(
                    hoje.getDate() + diferenca
                );


                inicio.setHours(
                    0, 0, 0, 0
                );


                return inicio;

            }


            function fimDaSemana() {

                const inicio =
                    inicioDaSemana();

                const fim =
                    new Date(inicio);

                fim.setDate(
                    inicio.getDate() + 6
                );

                fim.setHours(
                    23, 59, 59, 999
                );


                return fim;

            }


            /*
            |--------------------------------------------------------------------------
            | FILTRO POR PERÍODO
            |--------------------------------------------------------------------------
            */

            function correspondePeriodo(
                dataEvento
            ) {

                if (
                    periodoAtual === 'todos'
                ) {
                    return true;
                }


                if (
                    periodoAtual === 'hoje'
                ) {

                    return (
                        dataEvento ===
                        hojeISO()
                    );

                }


                if (
                    periodoAtual === 'data'
                ) {

                    if (
                        !dataEspecifica.value
                    ) {
                        return true;
                    }


                    return (
                        dataEvento ===
                        dataEspecifica.value
                    );

                }


                if (
                    periodoAtual === 'semana'
                ) {

                    const data =
                        new Date(
                            `${dataEvento}T12:00:00`
                        );


                    return (
                        data >= inicioDaSemana()
                        &&
                        data <= fimDaSemana()
                    );

                }


                return true;

            }


            /*
            |--------------------------------------------------------------------------
            | FILTRAR
            |--------------------------------------------------------------------------
            */

            function filtrarEventos() {

                const texto =
                    busca.value
                        .trim()
                        .toLowerCase();

                const categoriaSelecionada =
                    categoria.value;

                const cidadeSelecionada =
                    cidade.value;

                const modalidadeSelecionada =
                    modalidade.value;


                let encontrados = 0;


                cards.forEach(card => {

                    const titulo =
                        card.dataset.titulo;

                    const cardCidade =
                        card.dataset.cidade;

                    const cardCategoria =
                        card.dataset.categoria;

                    const cardModalidade =
                        card.dataset.modalidade;

                    const cardData =
                        card.dataset.data;


                    const bateBusca =
                        !texto
                        ||
                        titulo.includes(texto)
                        ||
                        cardCidade
                            .toLowerCase()
                            .includes(texto);


                    const bateCategoria =
                        categoriaSelecionada === 'todos'
                        ||
                        cardCategoria ===
                        categoriaSelecionada;


                    const bateCidade =
                        cidadeSelecionada === 'todos'
                        ||
                        cardCidade ===
                        cidadeSelecionada;


                    const bateModalidade =
                        modalidadeSelecionada === 'todos'
                        ||
                        cardModalidade ===
                        modalidadeSelecionada;


                    const batePeriodo =
                        correspondePeriodo(
                            cardData
                        );


                    const mostrar =
                        bateBusca
                        &&
                        bateCategoria
                        &&
                        bateCidade
                        &&
                        bateModalidade
                        &&
                        batePeriodo;


                    card.classList.toggle(
                        'hidden',
                        !mostrar
                    );


                    if (mostrar) {
                        encontrados++;
                    }

                });


                semResultados.classList.toggle(
                    'hidden',
                    encontrados > 0
                );

                const unavailable = @json($flow['loading'] || $flow['error']);
                document.querySelector('[data-feed-count]').textContent = unavailable ? '' : `${encontrados} ${encontrados === 1 ? 'evento encontrado' : 'eventos encontrados'}.`;


                atualizarFiltrosAtivos();

            }


            /*
            |--------------------------------------------------------------------------
            | FILTROS ATIVOS
            |--------------------------------------------------------------------------
            */

            function adicionarTag(
                texto
            ) {

                const tag =
                    document.createElement(
                        'span'
                    );


                tag.className =
                    'rounded-full bg-[#edf7f2] px-3 py-1.5 font-poppins text-[10px] text-[#315f47]';


                tag.textContent =
                    texto;


                filtrosAtivos.appendChild(
                    tag
                );

            }


            function atualizarFiltrosAtivos() {

                filtrosAtivos.innerHTML =
                    '';


                if (
                    periodoAtual !== 'todos'
                ) {

                    const nomes = {
                        hoje: 'Hoje',
                        semana: 'Esta semana',
                        data: 'Data específica'
                    };


                    adicionarTag(
                        nomes[periodoAtual]
                    );

                }


                if (
                    categoria.value !== 'todos'
                ) {

                    adicionarTag(
                        categoria.options[
                            categoria.selectedIndex
                        ].text
                    );

                }


                if (
                    cidade.value !== 'todos'
                ) {

                    adicionarTag(
                        cidade.value
                    );

                }


                if (
                    modalidade.value !== 'todos'
                ) {

                    adicionarTag(
                        modalidade.options[
                            modalidade.selectedIndex
                        ].text
                    );

                }


                filtrosAtivos.classList.toggle(
                    'hidden',
                    filtrosAtivos.children.length === 0
                );


                if (
                    filtrosAtivos.children.length > 0
                ) {
                    filtrosAtivos.classList.add(
                        'flex'
                    );
                }

            }


            /*
            |--------------------------------------------------------------------------
            | PERÍODO
            |--------------------------------------------------------------------------
            */

            periodoBtns.forEach(btn => {

                btn.addEventListener(
                    'click',
                    () => {

                        periodoAtual =
                            btn.dataset.periodo;


                        periodoBtns.forEach(
                            item => {

                                item.classList.remove(
                                    'bg-[#17392a]',
                                    'text-white'
                                );

                                item.classList.add(
                                    'border',
                                    'border-[#d8d4ca]',
                                    'bg-white',
                                    'text-[#5f5b52]'
                                );

                            }
                        );


                        btn.classList.remove(
                            'border',
                            'border-[#d8d4ca]',
                            'bg-white',
                            'text-[#5f5b52]'
                        );

                        btn.classList.add(
                            'bg-[#17392a]',
                            'text-white'
                        );


                        containerData
                            .classList
                            .toggle(
                                'hidden',
                                periodoAtual !== 'data'
                            );


                        if (
                            periodoAtual === 'data'
                        ) {

                            painelFiltros
                                .classList
                                .remove(
                                    'hidden'
                                );

                        }


                        filtrarEventos();

                    }
                );

            });


            /*
            |--------------------------------------------------------------------------
            | PAINEL FILTROS
            |--------------------------------------------------------------------------
            */

            btnFiltros.addEventListener(
                'click',
                () => {

                    painelFiltros
                        .classList
                        .toggle(
                            'hidden'
                        );

                    btnFiltros.setAttribute('aria-expanded', String(!painelFiltros.classList.contains('hidden')));

                }
            );

            painelFiltros.addEventListener('keydown', event => {
                if (event.key === 'Escape') {
                    painelFiltros.classList.add('hidden');
                    btnFiltros.setAttribute('aria-expanded', 'false');
                    btnFiltros.focus();
                }
            });


            btnFecharFiltros.addEventListener(
                'click',
                () => {

                    painelFiltros
                        .classList
                        .add(
                            'hidden'
                        );

                    filtrarEventos();

                    btnFiltros.setAttribute('aria-expanded', 'false');
                    btnFiltros.focus();

                }
            );


            /*
            |--------------------------------------------------------------------------
            | LIMPAR
            |--------------------------------------------------------------------------
            */

            btnLimpar.addEventListener(
                'click',
                () => {

                    busca.value =
                        '';

                    categoria.value =
                        'todos';

                    cidade.value =
                        'todos';

                    modalidade.value =
                        'todos';

                    dataEspecifica.value =
                        '';

                    periodoAtual =
                        'todos';


                    containerData
                        .classList
                        .add(
                            'hidden'
                        );


                    periodoBtns.forEach(
                        item => {

                            const ativo =
                                item.dataset.periodo
                                === 'todos';


                            item.classList.toggle(
                                'bg-[#17392a]',
                                ativo
                            );

                            item.classList.toggle(
                                'text-white',
                                ativo
                            );


                            if (!ativo) {

                                item.classList.add(
                                    'border',
                                    'border-[#d8d4ca]',
                                    'bg-white',
                                    'text-[#5f5b52]'
                                );

                            }

                        }
                    );


                    filtrarEventos();

                }
            );


            /*
            |--------------------------------------------------------------------------
            | EVENTOS
            |--------------------------------------------------------------------------
            */

            busca.addEventListener(
                'input',
                filtrarEventos
            );


            categoria.addEventListener(
                'change',
                filtrarEventos
            );


            cidade.addEventListener(
                'change',
                filtrarEventos
            );


            modalidade.addEventListener(
                'change',
                filtrarEventos
            );


            dataEspecifica.addEventListener(
                'change',
                filtrarEventos
            );


            /*
            |--------------------------------------------------------------------------
            | PRIMEIRA EXECUÇÃO
            |--------------------------------------------------------------------------
            */

            filtrarEventos();

        });

    </script>
    @endunless

</body>

</html>
