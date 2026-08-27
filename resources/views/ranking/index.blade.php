<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Ranking | Ajudaê</title>

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
        class="mx-auto w-full max-w-[900px]
               px-4 py-8
               sm:px-6 sm:py-10
               lg:py-12"
    >

        {{-- CABEÇALHO --}}
        <div>

            <h1
                class="text-3xl font-semibold
                       sm:text-4xl"
                style="font-family: 'Fraunces', serif;"
            >
                🏆 Ranking
            </h1>

            <p
                class="mt-2
                       font-poppins
                       text-sm text-[#817c70]"
            >
                Atualizado diariamente · Baseado em XP acumulado
                por participações confirmadas
            </p>

        </div>


        {{-- ABAS --}}
        <div
            class="mt-8 overflow-x-auto
                   border-b border-[#dedacf]"
        >

            <div
                class="flex min-w-max gap-2
                       font-poppins text-sm"
            >

                <button
                    type="button"
                    data-ranking-tab="global"
                    class="ranking-tab
                           border-b-2
                           border-[#17392a]
                           px-5 py-3
                           font-semibold
                           text-[#17392a]"
                >
                    Global
                </button>

                <button
                    type="button"
                    data-ranking-tab="regiao"
                    class="ranking-tab
                           border-b-2
                           border-transparent
                           px-5 py-3
                           text-[#817c70]"
                >
                    Por região
                </button>

            </div>

        </div>


        {{-- FILTRO REGIÃO --}}
        <div
            id="ranking-filtro-regiao"
            class="mt-4 hidden"
        >

            <label
                for="regiao-ranking"
                class="mb-2 block
                       font-poppins
                       text-xs font-medium
                       text-[#817c70]"
            >
                Região
            </label>

            <select
                id="regiao-ranking"
                class="w-full rounded-xl
                       border border-[#d8d4ca]
                       bg-white
                       px-4 py-3
                       font-poppins text-sm
                       text-[#292820]
                       outline-none
                       transition
                       focus:border-[#17392a]
                       sm:max-w-[320px]"
            >

                <option value="sao-paulo">
                    São Paulo, SP
                </option>

                <option value="guarulhos">
                    Guarulhos, SP
                </option>

                <option value="campinas">
                    Campinas, SP
                </option>

                <option value="santo-andre">
                    Santo André, SP
                </option>

            </select>

        </div>


        {{-- POSIÇÃO DO USUÁRIO --}}
        <section
            class="mt-6
                   rounded-xl
                   bg-[#17392a]
                   px-5 py-4
                   text-white
                   shadow-sm"
        >

            <div
                class="flex flex-col gap-3
                       sm:flex-row
                       sm:items-center
                       sm:justify-between"
            >

                <div class="flex items-center gap-4">

                    <p
                        class="text-3xl
                               font-semibold
                               text-[#e5a11b]"
                        style="font-family: 'Fraunces', serif;"
                    >
                        #{{ $usuario['posicao'] }}
                    </p>

                    <div>

                        <p
                            id="texto-posicao"
                            class="font-poppins
                                   text-[11px]
                                   text-white/60"
                        >
                            Sua posição no ranking global
                        </p>

                        <p
                            class="mt-1
                                   font-poppins
                                   text-sm font-medium"
                        >
                            {{ $usuario['nome'] }}
                            — {{ $usuario['xp'] }} XP
                        </p>

                    </div>

                </div>


                <span
                    class="w-fit rounded-full
                           bg-white/10
                           px-4 py-2
                           font-poppins
                           text-xs text-white/80"
                >
                    Nível {{ $usuario['nivel'] }}
                    · {{ $usuario['titulo_nivel'] }}
                </span>

            </div>

        </section>


        {{-- TOP 3 --}}
        <section
            class="mt-5
                   rounded-2xl
                   border border-[#dedacf]
                   bg-white
                   p-5
                   shadow-sm
                   sm:p-8"
        >

            <p
                id="titulo-top3"
                class="text-center
                       font-poppins
                       text-xs text-[#817c70]"
            >
                Top 3 — Ranking Global
            </p>


            <div
                class="mt-8 grid
                       grid-cols-1 gap-5
                       sm:grid-cols-3
                       sm:items-end"
            >

                {{-- SEGUNDO --}}
                <div
                    class="order-2 text-center
                           sm:order-1"
                >

                    <div
                        class="mx-auto
                               flex h-16 w-16
                               items-center justify-center
                               rounded-full
                               bg-[#dcefe5]
                               text-lg font-semibold
                               text-[#315b45]"
                    >
                        {{ $top3[1]['iniciais'] }}
                    </div>

                    <p
                        class="mt-3
                               font-poppins
                               text-sm font-semibold"
                    >
                        {{ $top3[1]['nome'] }}
                    </p>

                    <p
                        class="mt-1
                               font-poppins
                               text-[11px]
                               text-[#817c70]"
                    >
                        {{ $top3[1]['cidade'] }}
                    </p>

                    <span
                        class="mt-3 inline-flex
                               rounded-full
                               bg-[#fff2d8]
                               px-3 py-1
                               font-poppins
                               text-xs font-semibold
                               text-[#c47c15]"
                    >
                        {{ $top3[1]['xp'] }} XP
                    </span>

                    <div
                        class="mx-auto mt-4
                               flex h-16
                               max-w-[150px]
                               items-center justify-center
                               rounded-t-xl
                               bg-[#d6d3c9]
                               text-xl"
                    >
                        🥈
                    </div>

                </div>


                {{-- PRIMEIRO --}}
                <div
                    class="order-1 text-center
                           sm:order-2"
                >

                    <span class="text-2xl">
                        👑
                    </span>

                    <div
                        class="mx-auto mt-1
                               flex h-20 w-20
                               items-center justify-center
                               rounded-full
                               border-2 border-[#e5a11b]
                               bg-[#dcefe5]
                               text-xl font-semibold
                               text-[#17392a]"
                    >
                        {{ $top3[0]['iniciais'] }}
                    </div>

                    <p
                        class="mt-3
                               font-poppins
                               text-sm font-semibold"
                    >
                        {{ $top3[0]['nome'] }}
                    </p>

                    <p
                        class="mt-1
                               font-poppins
                               text-[11px]
                               text-[#817c70]"
                    >
                        {{ $top3[0]['cidade'] }}
                    </p>

                    <span
                        class="mt-3 inline-flex
                               rounded-full
                               bg-[#e8a21a]
                               px-3 py-1
                               font-poppins
                               text-xs font-semibold
                               text-white"
                    >
                        {{ $top3[0]['xp'] }} XP
                    </span>

                    <div
                        class="mx-auto mt-4
                               flex h-24
                               max-w-[180px]
                               items-center justify-center
                               rounded-t-xl
                               bg-[#f3b31f]
                               text-2xl"
                    >
                        🥇
                    </div>

                </div>


                {{-- TERCEIRO --}}
                <div class="order-3 text-center">

                    <div
                        class="mx-auto
                               flex h-16 w-16
                               items-center justify-center
                               rounded-full
                               bg-[#eee8dd]
                               text-lg font-semibold
                               text-[#725a42]"
                    >
                        {{ $top3[2]['iniciais'] }}
                    </div>

                    <p
                        class="mt-3
                               font-poppins
                               text-sm font-semibold"
                    >
                        {{ $top3[2]['nome'] }}
                    </p>

                    <p
                        class="mt-1
                               font-poppins
                               text-[11px]
                               text-[#817c70]"
                    >
                        {{ $top3[2]['cidade'] }}
                    </p>

                    <span
                        class="mt-3 inline-flex
                               rounded-full
                               bg-[#f0e5d8]
                               px-3 py-1
                               font-poppins
                               text-xs font-semibold
                               text-[#956d43]"
                    >
                        {{ $top3[2]['xp'] }} XP
                    </span>

                    <div
                        class="mx-auto mt-4
                               flex h-12
                               max-w-[150px]
                               items-center justify-center
                               rounded-t-xl
                               bg-[#b98a58]
                               text-xl"
                    >
                        🥉
                    </div>

                </div>

            </div>

        </section>


        {{-- LISTA --}}
        <section
            class="mt-5 overflow-hidden
                   rounded-2xl
                   border border-[#dedacf]
                   bg-white
                   shadow-sm"
        >

            <div
                class="hidden
                       grid-cols-[55px_1fr_120px]
                       border-b border-[#e5e1d8]
                       bg-[#faf9f6]
                       px-5 py-3
                       font-poppins
                       text-[11px]
                       text-[#817c70]
                       sm:grid"
            >
                <span>#</span>
                <span>Voluntário</span>
                <span class="text-right">XP</span>
            </div>


            @foreach($ranking as $pessoa)

                <div
                    class="flex items-center gap-3
                           border-b border-[#ebe7de]
                           px-4 py-4
                           last:border-b-0
                           sm:grid
                           sm:grid-cols-[55px_1fr_120px]
                           sm:px-5"
                >

                    <strong
                        class="w-7 shrink-0
                               text-center
                               text-sm
                               text-[#31513e]
                               sm:w-auto"
                    >
                        {{ $pessoa['posicao'] }}
                    </strong>


                    <div
                        class="flex min-w-0 flex-1
                               items-center gap-3"
                    >

                        <div
                            class="flex h-10 w-10
                                   shrink-0
                                   items-center justify-center
                                   rounded-full
                                   bg-[#ebe5dd]
                                   font-poppins
                                   text-xs font-semibold
                                   text-[#635d55]"
                        >
                            {{ $pessoa['iniciais'] }}
                        </div>

                        <div class="min-w-0">

                            <p
                                class="truncate
                                       font-poppins
                                       text-sm font-medium"
                            >
                                {{ $pessoa['nome'] }}
                            </p>

                            <p
                                class="mt-1
                                       font-poppins
                                       text-[10px]
                                       text-[#817c70]"
                            >
                                Nível {{ $pessoa['nivel'] }}
                                · {{ $pessoa['badges'] }} badges
                            </p>

                        </div>

                    </div>


                    <div
                        class="ml-auto shrink-0
                               text-right
                               sm:ml-0"
                    >

                        <p
                            class="font-poppins
                                   text-sm font-semibold
                                   text-[#17392a]"
                        >
                            {{ $pessoa['xp'] }} XP
                        </p>

                        <span
                            class="mt-1 inline-flex
                                   rounded-full
                                   bg-[#fff2d8]
                                   px-2 py-1
                                   font-poppins
                                   text-[9px]
                                   text-[#c47c15]"
                        >
                            Nível {{ $pessoa['nivel'] }}
                        </span>

                    </div>

                </div>

            @endforeach

        </section>


        <div
            class="mt-5
                   rounded-xl
                   border border-[#cfe4d8]
                   bg-[#edf7f2]
                   px-4 py-3
                   font-poppins
                   text-xs leading-5
                   text-[#4f705f]"
        >
            ⓘ Ranking atualizado diariamente.
            XP obtido em participações ainda aguardando confirmação
            não conta para o ranking.
        </div>

    </main>


    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const tabs =
                document.querySelectorAll('.ranking-tab');

            const filtroRegiao =
                document.querySelector('#ranking-filtro-regiao');

            const textoPosicao =
                document.querySelector('#texto-posicao');

            const tituloTop3 =
                document.querySelector('#titulo-top3');


            tabs.forEach(tab => {

                tab.addEventListener('click', () => {

                    tabs.forEach(item => {

                        item.classList.remove(
                            'border-[#17392a]',
                            'font-semibold',
                            'text-[#17392a]'
                        );

                        item.classList.add(
                            'border-transparent',
                            'text-[#817c70]'
                        );

                    });


                    tab.classList.remove(
                        'border-transparent',
                        'text-[#817c70]'
                    );

                    tab.classList.add(
                        'border-[#17392a]',
                        'font-semibold',
                        'text-[#17392a]'
                    );


                    const tipo =
                        tab.dataset.rankingTab;


                    if (tipo === 'global') {

                        filtroRegiao.classList.add('hidden');

                        textoPosicao.textContent =
                            'Sua posição no ranking global';

                        tituloTop3.textContent =
                            'Top 3 — Ranking Global';

                    }


                    if (tipo === 'regiao') {

                        filtroRegiao.classList.remove('hidden');

                        textoPosicao.textContent =
                            'Sua posição no ranking da região';

                        tituloTop3.textContent =
                            'Top 3 — Ranking por Região';

                    }

                });

            });

        });
    </script>

</body>

</html>