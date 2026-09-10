<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Notificações | Ajudae</title>

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
        class="mx-auto w-full
               max-w-[900px]
               px-4 py-8
               sm:px-6 sm:py-10
               lg:py-12"
    >

        {{-- ========================================================= --}}
        {{-- CABEÇALHO --}}
        {{-- ========================================================= --}}

        <div
            class="flex flex-col gap-4
                   sm:flex-row
                   sm:items-start
                   sm:justify-between"
        >

            <div>

                <h1
                    class="text-3xl font-semibold
                           sm:text-4xl"
                    style="font-family: 'Fraunces', serif;"
                >
                    🔔 Notificações
                </h1>

                <p
                    id="contador-nao-lidas"
                    class="mt-2
                           font-poppins
                           text-sm text-[#817c70]"
                >
                    {{ $naoLidas }} não lidas
                </p>

            </div>


            <button
                type="button"
                id="btn-marcar-todas"
                class="w-full rounded-full
                       border border-[#c9c4b8]
                       bg-white
                       px-5 py-2.5
                       font-poppins
                       text-sm
                       transition
                       hover:bg-[#faf9f6]
                       sm:w-auto"
            >
                ✓ Marcar todas como lidas
            </button>

        </div>


        {{-- ========================================================= --}}
        {{-- RESUMO --}}
        {{-- ========================================================= --}}

        <section
            class="mt-7
                   overflow-x-auto
                   rounded-2xl
                   border border-[#dedacf]
                   bg-white
                   px-4 py-4
                   shadow-sm"
        >

            <div
                class="flex min-w-max
                       items-center gap-5
                       font-poppins text-xs"
            >

                <span class="flex items-center gap-2">

                    <span
                        class="h-2.5 w-2.5
                               rounded-full
                               bg-[#e8a21a]"
                    ></span>

                    Não lidas

                    <strong>
                        {{ $naoLidas }}
                    </strong>

                </span>


                <span class="text-[#ddd7ce]">
                    |
                </span>


                <span class="flex items-center gap-2">

                    <span
                        class="h-2.5 w-2.5
                               rounded-full
                               bg-[#174b36]"
                    ></span>

                    Aprovações

                    <strong>2</strong>

                </span>


                <span class="text-[#ddd7ce]">
                    |
                </span>


                <span class="flex items-center gap-2">

                    <span
                        class="h-2.5 w-2.5
                               rounded-full
                               bg-[#e7a11b]"
                    ></span>

                    Badges

                    <strong>1</strong>

                </span>


                <span class="text-[#ddd7ce]">
                    |
                </span>


                <span class="flex items-center gap-2">

                    <span
                        class="h-2.5 w-2.5
                               rounded-full
                               bg-red-500"
                    ></span>

                    Recusas

                    <strong>1</strong>

                </span>


                <span class="text-[#ddd7ce]">
                    |
                </span>


                <span class="flex items-center gap-2">

                    <span
                        class="h-2.5 w-2.5
                               rounded-full
                               bg-blue-500"
                    ></span>

                    Convites

                    <strong>1</strong>

                </span>

            </div>

        </section>


        {{-- ========================================================= --}}
        {{-- FILTROS --}}
        {{-- ========================================================= --}}

        <div
            class="mt-5 overflow-x-auto
                   border-b border-[#dedacf]"
        >

            <div
                class="flex min-w-max gap-1"
            >

                <button
                    data-filter="todos"
                    class="notification-filter
                           border-b-2
                           border-[#17392a]
                           px-4 py-3
                           font-poppins
                           text-sm font-semibold
                           text-[#17392a]"
                >
                    Todas
                </button>

                <button
                    data-filter="candidatura"
                    class="notification-filter
                           border-b-2
                           border-transparent
                           px-4 py-3
                           font-poppins
                           text-sm
                           text-[#817c70]"
                >
                    Candidaturas
                </button>

                <button
                    data-filter="gamificacao"
                    class="notification-filter
                           border-b-2
                           border-transparent
                           px-4 py-3
                           font-poppins
                           text-sm
                           text-[#817c70]"
                >
                    Badges & XP
                </button>

                <button
                    data-filter="sistema"
                    class="notification-filter
                           border-b-2
                           border-transparent
                           px-4 py-3
                           font-poppins
                           text-sm
                           text-[#817c70]"
                >
                    Sistema
                </button>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- LISTA --}}
        {{-- ========================================================= --}}

        <section
            class="overflow-hidden
                   rounded-b-2xl
                   border-x border-b
                   border-[#dedacf]
                   bg-white
                   shadow-sm"
        >

            @foreach($notificacoes as $notificacao)

                @if($notificacao['grupo'])
                    <div
                        class="bg-[#f9f7f2]
                               px-5 py-2
                               font-poppins
                               text-[11px]
                               text-[#817c70]"
                    >
                        {{ $notificacao['grupo'] }}
                    </div>
                @endif


                <article
                    data-notification
                    data-tipo="{{ $notificacao['tipo'] }}"
                    data-lida="{{ $notificacao['lida'] ? '1' : '0' }}"
                    class="notification-item
                           relative
                           border-b border-[#e9e5dc]
                           px-4 py-5
                           last:border-b-0
                           sm:px-6"
                >

                    @if(!$notificacao['lida'])

                        <span
                            class="unread-dot
                                   absolute right-4 top-5
                                   h-2.5 w-2.5
                                   rounded-full
                                   bg-[#e8a21a]
                                   sm:right-6"
                        ></span>

                    @endif


                    <div
                        class="flex gap-3
                               sm:gap-4"
                    >

                        {{-- ÍCONE --}}
                        <div
                            class="flex h-11 w-11
                                   shrink-0
                                   items-center justify-center
                                   rounded-full
                                   {{ $notificacao['cor_icone'] }}
                                   text-lg"
                        >
                            {{ $notificacao['icone'] }}
                        </div>


                        <div
                            class="min-w-0 flex-1
                                   pr-3"
                        >

                            <h2
                                class="font-poppins
                                       text-sm font-semibold"
                            >
                                {{ $notificacao['titulo'] }}
                            </h2>


                            <p
                                class="mt-1
                                       font-poppins
                                       text-sm leading-6
                                       text-[#57544d]"
                            >
                                {!! $notificacao['mensagem'] !!}
                            </p>


                            <div
                                class="mt-2
                                       flex flex-wrap
                                       items-center gap-2"
                            >

                                <span
                                    class="font-poppins
                                           text-[10px]
                                           text-[#999388]"
                                >
                                    {{ $notificacao['tempo'] }}
                                </span>


                                <span
                                    class="rounded-full
                                           px-2.5 py-1
                                           font-poppins
                                           text-[9px]
                                           font-semibold
                                           {{ $notificacao['cor_tag'] }}"
                                >
                                    {{ $notificacao['tag'] }}
                                </span>

                            </div>


                            @if(!empty($notificacao['acoes']))

                                <div
                                    class="mt-4
                                           flex flex-col
                                           gap-2
                                           sm:flex-row
                                           sm:flex-wrap"
                                >

                                    @foreach($notificacao['acoes'] as $acao)

                                        <a
                                            href="{{ $acao['href'] }}"
                                            class="flex items-center
                                                   justify-center
                                                   rounded-full
                                                   px-4 py-2
                                                   font-poppins
                                                   text-xs
                                                   font-medium
                                                   {{ $acao['classe'] }}"
                                        >
                                            {{ $acao['texto'] }}
                                        </a>

                                    @endforeach

                                </div>

                            @endif

                        </div>

                    </div>

                </article>

            @endforeach

        </section>

    </main>


    {{-- ============================================================= --}}
    {{-- JS MOCK --}}
    {{-- ============================================================= --}}

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const filtros =
                document.querySelectorAll(
                    '.notification-filter'
                );

            const notificacoes =
                document.querySelectorAll(
                    '[data-notification]'
                );

            const btnTodas =
                document.querySelector(
                    '#btn-marcar-todas'
                );

            const contador =
                document.querySelector(
                    '#contador-nao-lidas'
                );


            /*
            |--------------------------------------------------------------------------
            | FILTROS
            |--------------------------------------------------------------------------
            */

            filtros.forEach(filtro => {

                filtro.addEventListener('click', () => {

                    filtros.forEach(item => {

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


                    filtro.classList.remove(
                        'border-transparent',
                        'text-[#817c70]'
                    );

                    filtro.classList.add(
                        'border-[#17392a]',
                        'font-semibold',
                        'text-[#17392a]'
                    );


                    const tipo =
                        filtro.dataset.filter;


                    notificacoes.forEach(item => {

                        const corresponde =
                            tipo === 'todos' ||
                            item.dataset.tipo === tipo;


                        item.classList.toggle(
                            'hidden',
                            !corresponde
                        );

                    });

                });

            });


            /*
            |--------------------------------------------------------------------------
            | MARCAR TODAS
            |--------------------------------------------------------------------------
            */

            btnTodas.addEventListener('click', () => {

                notificacoes.forEach(item => {

                    item.dataset.lida = '1';

                    const ponto =
                        item.querySelector(
                            '.unread-dot'
                        );

                    if (ponto) {
                        ponto.remove();
                    }

                });


                contador.textContent =
                    '0 não lidas';

            });

        });
    </script>

</body>

</html>