<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Feed de eventos | Ajudaê</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>

<body
    class="min-h-screen overflow-x-hidden
           bg-[#f7f5ef]
           font-outfit
           text-[#292820]"
>

    @include('/components/navbar-feed')


    <main
        class="mx-auto w-full max-w-[1180px]
               px-4 py-7
               sm:px-6 sm:py-9
               lg:px-8"
    >

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

                        <input
                            type="search"
                            id="busca-eventos"
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


                    <button
                        type="button"
                        id="btn-filtros"
                        class="flex items-center justify-center
                               gap-2 rounded-xl
                               border border-[#d8d4ca]
                               bg-white
                               px-5 py-3
                               font-poppins text-sm
                               transition
                               hover:bg-[#faf9f5]"
                    >
                        ☰ Filtros
                    </button>

                </div>


                {{-- ================================================= --}}
                {{-- PERÍODO --}}
                {{-- ================================================= --}}

                <div
                    class="mt-4 flex max-w-full
                           gap-2 overflow-x-auto pb-1"
                >

                    <button
                        type="button"
                        data-periodo="todos"
                        class="periodo-btn
                               whitespace-nowrap
                               rounded-full
                               bg-[#17392a]
                               px-4 py-2
                               font-poppins text-xs
                               font-medium text-white"
                    >
                        Todos
                    </button>


                    <button
                        type="button"
                        data-periodo="hoje"
                        class="periodo-btn
                               whitespace-nowrap
                               rounded-full
                               border border-[#d8d4ca]
                               bg-white
                               px-4 py-2
                               font-poppins text-xs
                               text-[#5f5b52]"
                    >
                        Hoje
                    </button>


                    <button
                        type="button"
                        data-periodo="semana"
                        class="periodo-btn
                               whitespace-nowrap
                               rounded-full
                               border border-[#d8d4ca]
                               bg-white
                               px-4 py-2
                               font-poppins text-xs
                               text-[#5f5b52]"
                    >
                        Esta semana
                    </button>


                    <button
                        type="button"
                        data-periodo="data"
                        class="periodo-btn
                               whitespace-nowrap
                               rounded-full
                               border border-[#d8d4ca]
                               bg-white
                               px-4 py-2
                               font-poppins text-xs
                               text-[#5f5b52]"
                    >
                        Por data
                    </button>

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
                        id="filtro-categoria"
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

                        @foreach($categorias as $categoria)

                            <option
                                value="{{ $categoria['categoria_id'] }}"
                            >
                                {{ $categoria['nome_categoria'] }}
                            </option>

                        @endforeach

                    </select>


                    <select
                        id="filtro-cidade"
                        class="rounded-xl
                               border border-[#d8d4ca]
                               bg-white
                               px-4 py-3
                               font-poppins text-xs
                               outline-none
                               focus:border-[#17392a]"
                    >

                        <option value="todos">
                            📍 Todas as cidades
                        </option>

                        @foreach($cidades as $cidade)

                            <option value="{{ $cidade }}">
                                📍 {{ $cidade }}
                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- ================================================= --}}
                {{-- PAINEL DE FILTROS AVANÇADOS --}}
                {{-- ================================================= --}}

                <div
                    id="painel-filtros"
                    class="mt-3 hidden
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
                                id="filtro-modalidade"
                                class="w-full rounded-xl
                                       border border-[#d8d4ca]
                                       bg-white
                                       px-4 py-3
                                       font-poppins text-sm
                                       outline-none
                                       focus:border-[#17392a]"
                            >

                                <option value="todos">
                                    Todas
                                </option>

                                <option value="presencial">
                                    Presencial
                                </option>

                                <option value="online">
                                    Online
                                </option>

                                <option value="hibrido">
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

                        <button
                            type="button"
                            id="btn-limpar-filtros"
                            class="rounded-full
                                   border border-[#d8d4ca]
                                   bg-white
                                   px-5 py-2.5
                                   font-poppins text-xs
                                   transition
                                   hover:bg-[#faf9f5]"
                        >
                            Limpar filtros
                        </button>


                        <button
                            type="button"
                            id="btn-fechar-filtros"
                            class="rounded-full
                                   bg-[#17392a]
                                   px-5 py-2.5
                                   font-poppins text-xs
                                   font-medium
                                   text-white"
                        >
                            Aplicar filtros
                        </button>

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

                <div
                    id="lista-eventos"
                    class="mt-6 grid
                           grid-cols-1 gap-4
                           md:grid-cols-2"
                >

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
                                $evento['vagas_evento'] > 0
                                    ? (
                                        $evento['confirmados']
                                        /
                                        $evento['vagas_evento']
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
                                    {{ $evento['vagas_disponiveis'] }}
                                    vagas
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
                                </h2>


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
                                                {{ $evento['vagas_evento'] }}
                                                confirmados
                                            </p>

                                        </div>


                                        <a
                                            href="{{ route(
                                                'eventos.show',
                                                $evento['evento_id']
                                            ) }}"
                                            class="shrink-0
                                                   rounded-full
                                                   bg-[#17392a]
                                                   px-4 py-2
                                                   font-poppins
                                                   text-xs font-medium
                                                   text-white
                                                   transition
                                                   hover:bg-[#102f22]"
                                        >
                                            Ver evento
                                        </a>

                                    </div>

                                </div>

                            </div>

                        </article>

                    @endforeach

                </div>


                {{-- NENHUM RESULTADO --}}
                <div
                    id="sem-resultados"
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


                    <a
                        href="/ranking"
                        class="mt-3
                               flex items-center
                               justify-center
                               rounded-full
                               border border-[#17392a]
                               px-4 py-2
                               font-poppins
                               text-xs font-medium
                               text-[#17392a]
                               transition
                               hover:bg-[#17392a]
                               hover:text-white"
                    >
                        Ver ranking completo
                    </a>

                </section>


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


                    <a
                        href="/perfil"
                        class="mt-4
                               flex items-center
                               justify-center
                               rounded-full
                               border border-[#d8d4ca]
                               px-4 py-2
                               font-poppins
                               text-xs
                               transition
                               hover:bg-[#faf9f5]"
                    >
                        Ver todos os badges
                    </a>

                </section>


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

            </aside>

        </div>

    </main>


    {{-- ============================================================= --}}
    {{-- JAVASCRIPT --}}
    {{-- ============================================================= --}}

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

                }
            );


            btnFecharFiltros.addEventListener(
                'click',
                () => {

                    painelFiltros
                        .classList
                        .add(
                            'hidden'
                        );

                    filtrarEventos();

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

</body>

</html>