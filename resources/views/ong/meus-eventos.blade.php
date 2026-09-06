<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Meus eventos | Ajudaê</title>

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

    <x-navbar-ong ativo="eventos" />


    <main
        class="min-h-screen w-full
               lg:ml-[230px]
               lg:w-[calc(100%-230px)]"
    >

        <x-breadcrumb-ong
            :itens="[
                ['label' => 'Meus eventos']
            ]"
        />


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
                        Gerencie os eventos que você organiza
                        como voluntário da ONG
                    </p>

                </div>


                <a
                    href="{{ route('ong.eventos.criar') }}"
                    class="flex w-full
                           items-center justify-center
                           rounded-full
                           bg-[#17392a]
                           px-6 py-3
                           font-poppins
                           text-sm font-semibold
                           text-white
                           transition
                           hover:bg-[#102f22]
                           md:w-auto"
                >
                    + Criar evento
                </a>

            </div>


            {{-- ========================================================= --}}
            {{-- CARDS DE RESUMO --}}
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
                                {{ $resumo['ativos'] }}
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
                                {{ $resumo['aguardando'] }}
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
                                {{ $resumo['candidaturas_pendentes'] }}
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
                                {{ $resumo['realizados'] }}
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
                        id="busca-eventos-ong"
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

                    <button
                        type="button"
                        data-status="todos"
                        class="status-btn
                               whitespace-nowrap
                               rounded-full
                               bg-[#17392a]
                               px-5 py-2.5
                               font-poppins
                               text-xs
                               font-semibold
                               text-white"
                    >
                        Todos
                    </button>


                    <button
                        type="button"
                        data-status="publicado"
                        class="status-btn
                               whitespace-nowrap
                               rounded-full
                               border border-[#d8d4ca]
                               bg-white
                               px-5 py-2.5
                               font-poppins
                               text-xs
                               font-semibold
                               text-[#625e55]"
                    >
                        Publicados
                    </button>


                    <button
                        type="button"
                        data-status="aguardando"
                        class="status-btn
                               whitespace-nowrap
                               rounded-full
                               border border-[#d8d4ca]
                               bg-white
                               px-5 py-2.5
                               font-poppins
                               text-xs
                               font-semibold
                               text-[#625e55]"
                    >
                        Aguardando
                    </button>


                    <button
                        type="button"
                        data-status="rascunho"
                        class="status-btn
                               whitespace-nowrap
                               rounded-full
                               border border-[#d8d4ca]
                               bg-white
                               px-5 py-2.5
                               font-poppins
                               text-xs
                               font-semibold
                               text-[#625e55]"
                    >
                        Rascunhos
                    </button>

                </div>

            </section>


            {{-- ========================================================= --}}
            {{-- LISTA DE EVENTOS --}}
            {{-- ========================================================= --}}

            <section
                id="lista-eventos-ong"
                class="mt-6 space-y-4"
            >

                @foreach($eventos as $evento)

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
                                    @if($evento['status_evento'] === 'publicado')

                                        <span
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

                                    @elseif($evento['status_evento'] === 'aguardando')

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

                                    @elseif($evento['status_evento'] === 'rascunho')

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
                                            ◷ Rascunho
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
                                            {{ ucfirst(
                                                $evento['status_evento']
                                            ) }}
                                        </span>

                                    @endif

                                </div>


                                {{-- DESCRIÇÃO --}}
                                <p
                                    class="mt-3
                                           font-poppins
                                           text-sm
                                           leading-6
                                           text-[#625e55]"
                                >
                                    {{ $evento['descricao_resumida'] }}
                                </p>


                                {{-- LINHA --}}
                                <div
                                    class="my-4
                                           h-px
                                           bg-[#ebe7de]"
                                ></div>


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
                                                {{ $evento['responsavel']['iniciais'] }}
                                            </div>


                                            <div>

                                                <p
                                                    class="font-poppins
                                                           text-xs
                                                           font-semibold"
                                                >
                                                    {{ $evento['responsavel']['nome'] }}
                                                </p>

                                                <p
                                                    class="font-poppins
                                                           text-[10px]
                                                           text-[#999388]"
                                                >
                                                    Organizador
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

                                        <a
                                            href="{{ route(
                                                'eventos.show',
                                                $evento['evento_id']
                                            ) }}"
                                            class="flex items-center
                                                   justify-center
                                                   rounded-full
                                                   border border-[#17392a]
                                                   px-4 py-2
                                                   font-poppins
                                                   text-xs
                                                   font-medium
                                                   text-[#17392a]
                                                   transition
                                                   hover:bg-[#17392a]
                                                   hover:text-white"
                                        >
                                            ◉ Ver
                                        </a>


                                        <a
                                            href="{{ route(
                                                'ong.eventos.editar',
                                                $evento['evento_id']
                                            ) }}"
                                            class="flex items-center
                                                   justify-center
                                                   rounded-full
                                                   border border-[#17392a]
                                                   px-4 py-2
                                                   font-poppins
                                                   text-xs
                                                   font-medium
                                                   text-[#17392a]
                                                   transition
                                                   hover:bg-[#17392a]
                                                   hover:text-white"
                                        >
                                            ✎ Editar
                                        </a>

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
                class="mt-6 hidden
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
                    Nenhum evento encontrado
                </p>

                <p
                    class="mt-1
                           font-poppins
                           text-xs
                           text-[#817c70]"
                >
                    Tente alterar sua busca ou o filtro selecionado.
                </p>

            </div>

        </div>

    </main>


    {{-- ============================================================= --}}
    {{-- JAVASCRIPT --}}
    {{-- ============================================================= --}}

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

</body>

</html>