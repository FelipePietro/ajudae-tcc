<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Ajudaê | Voluntariado com propósito</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

</head>


<body
    class="overflow-x-hidden
           bg-[#f8f6f0]
           font-outfit
           text-[#292820]"
>

    @include('/components/navbar')


    {{-- ============================================================= --}}
    {{-- HERO --}}
    {{-- ============================================================= --}}

    <main>

        <section
            class="relative overflow-hidden
                   bg-[#f8f6f0]
                   pt-[68px]"
        >

            {{-- FUNDO --}}
            <div
                class="pointer-events-none
                       absolute right-0 top-0
                       h-full w-[45%]
                       bg-gradient-to-bl
                       from-[#dcefe5]
                       via-[#edf5ef]
                       to-transparent"
            ></div>


            <div
                class="relative mx-auto
                       grid min-h-[650px]
                       w-full max-w-[1440px]
                       grid-cols-1
                       items-center gap-12
                       px-5 py-16
                       sm:px-8
                       md:py-20
                       lg:grid-cols-2
                       lg:gap-20
                       lg:px-12
                       lg:py-24"
            >

                {{-- TEXTO --}}
                <div class="max-w-[620px]">

                    <span
                        class="inline-flex items-center
                               rounded-full
                               bg-[#fff0d6]
                               px-4 py-2
                               font-poppins
                               text-xs font-semibold
                               text-[#c47813]"
                    >
                        ✦ Gamificação social · Verificado e seguro
                    </span>


                    <h1
                        class="mt-6
                               text-[42px]
                               font-bold
                               leading-[1.04]
                               tracking-[-0.025em]
                               text-[#24231e]
                               sm:text-[52px]
                               lg:text-[62px]"
                        style="font-family: 'Fraunces', serif;"
                    >
                        Faça a diferença.

                        <span
                            class="block italic
                                   text-[#174b36]"
                        >
                            Ganhe o reconhecimento
                        </span>

                        que você merece.
                    </h1>


                    <p
                        class="mt-6 max-w-[520px]
                               font-poppins
                               text-sm leading-7
                               text-[#656158]
                               sm:text-base"
                    >
                        Conectamos voluntários a ONGs e organizadores
                        verificados. Acumule XP, conquiste badges e suba
                        no ranking enquanto transforma comunidades.
                    </p>


                    {{-- BOTÕES --}}
                    <div
                        class="mt-8 flex flex-col gap-3
                               sm:flex-row"
                    >

                        <a
                            href="/cadastro"
                            class="flex items-center justify-center
                                   rounded-full
                                   bg-[#174b36]
                                   px-7 py-3.5
                                   font-poppins
                                   text-sm font-semibold
                                   text-white
                                   transition
                                   hover:bg-[#103b2a]"
                        >
                            Começar agora — é grátis
                        </a>


                        <a
                            href="/cadastro/ong"
                            class="flex items-center justify-center
                                   rounded-full
                                   border border-[#174b36]
                                   bg-white/40
                                   px-7 py-3.5
                                   font-poppins
                                   text-sm font-medium
                                   text-[#174b36]
                                   transition
                                   hover:bg-white"
                        >
                            Cadastrar minha ONG
                        </a>

                    </div>


                    {{-- USUÁRIOS --}}
                    <div
                        class="mt-8 flex
                               items-center gap-3"
                    >

                        <div class="flex -space-x-2">

                            <div
                                class="flex h-9 w-9
                                       items-center justify-center
                                       rounded-full border-2
                                       border-[#f8f6f0]
                                       bg-[#dcefe5]
                                       text-[10px]
                                       font-semibold
                                       text-[#174b36]"
                            >
                                AM
                            </div>

                            <div
                                class="flex h-9 w-9
                                       items-center justify-center
                                       rounded-full border-2
                                       border-[#f8f6f0]
                                       bg-[#fff0d6]
                                       text-[10px]
                                       font-semibold"
                            >
                                RC
                            </div>

                            <div
                                class="flex h-9 w-9
                                       items-center justify-center
                                       rounded-full border-2
                                       border-[#f8f6f0]
                                       bg-[#dce8f5]
                                       text-[10px]
                                       font-semibold"
                            >
                                DS
                            </div>

                            <div
                                class="flex h-9 w-9
                                       items-center justify-center
                                       rounded-full border-2
                                       border-[#f8f6f0]
                                       bg-[#eadff1]
                                       text-[10px]
                                       font-semibold"
                            >
                                BF
                            </div>

                        </div>

                        <span
                            class="font-poppins
                                   text-xs text-[#777268]"
                        >
                            +2.400 voluntários já na plataforma
                        </span>

                    </div>

                </div>


                {{-- ================================================= --}}
                {{-- MOCKUP DO SISTEMA --}}
                {{-- ================================================= --}}

                <div
                    class="mx-auto w-full
                           max-w-[520px]
                           lg:ml-auto"
                >

                    <div
                        class="overflow-hidden
                               rounded-[22px]
                               border border-[#d7ddd7]
                               bg-white
                               shadow-[0_24px_70px_rgba(23,57,42,0.15)]"
                    >

                        {{-- CABEÇALHO --}}
                        <div
                            class="flex h-14
                                   items-center gap-2
                                   bg-[#174b36]
                                   px-5"
                        >

                            <span
                                class="h-3 w-3
                                       rounded-full
                                       bg-[#ff6b6b]"
                            ></span>

                            <span
                                class="h-3 w-3
                                       rounded-full
                                       bg-[#ffd35a]"
                            ></span>

                            <span
                                class="h-3 w-3
                                       rounded-full
                                       bg-[#6fd481]"
                            ></span>

                            <span
                                class="ml-3
                                       font-poppins
                                       text-xs
                                       font-medium
                                       text-white"
                            >
                                Ajudae.app · Feed de eventos
                            </span>

                        </div>


                        <div class="space-y-4 p-5">

                            {{-- PROGRESSO --}}
                            <div
                                class="rounded-xl
                                       bg-[#edf7f2]
                                       p-4"
                            >

                                <div
                                    class="flex items-center gap-3"
                                >

                                    <span class="text-2xl">
                                        🌟
                                    </span>

                                    <div class="flex-1">

                                        <p
                                            class="font-poppins
                                                   text-[10px]
                                                   text-[#777268]"
                                        >
                                            Seu progresso
                                        </p>

                                        <p
                                            class="mt-1
                                                   font-poppins
                                                   text-xs
                                                   font-semibold"
                                        >
                                            Nível 3 — Aprendiz · 450 / 600 XP
                                        </p>

                                        <div
                                            class="mt-2 h-1.5
                                                   overflow-hidden
                                                   rounded-full
                                                   bg-[#d0e5d9]"
                                        >
                                            <div
                                                class="h-full w-[75%]
                                                       rounded-full
                                                       bg-[#e6a11a]"
                                            ></div>
                                        </div>

                                    </div>

                                </div>

                            </div>


                            {{-- EVENTO 1 --}}
                            <div
                                class="rounded-xl
                                       border border-[#ddd9cf]
                                       p-4"
                            >

                                <div
                                    class="flex items-start gap-3"
                                >

                                    <div
                                        class="flex h-11 w-11
                                               shrink-0
                                               items-center justify-center
                                               rounded-xl
                                               bg-[#dcefe5]"
                                    >
                                        🌿
                                    </div>


                                    <div class="min-w-0 flex-1">

                                        <div
                                            class="flex items-start
                                                   justify-between gap-2"
                                        >

                                            <div>

                                                <p
                                                    class="font-poppins
                                                           text-xs
                                                           font-semibold"
                                                >
                                                    Limpeza de Parques — Cantareira
                                                </p>

                                                <p
                                                    class="mt-1
                                                           font-poppins
                                                           text-[10px]
                                                           text-[#777268]"
                                                >
                                                    ONG Verde SP · 15 mai · São Paulo
                                                </p>

                                            </div>


                                            <span
                                                class="shrink-0
                                                       rounded-full
                                                       bg-[#fff0d6]
                                                       px-2 py-1
                                                       font-poppins
                                                       text-[9px]
                                                       font-semibold
                                                       text-[#c47813]"
                                            >
                                                +80 XP
                                            </span>

                                        </div>


                                        <span
                                            class="mt-3 inline-flex
                                                   rounded-full
                                                   bg-[#dcefe5]
                                                   px-3 py-1
                                                   font-poppins
                                                   text-[9px]
                                                   text-[#174b36]"
                                        >
                                            Meio Ambiente
                                        </span>

                                    </div>

                                </div>

                            </div>


                            {{-- EVENTO 2 --}}
                            <div
                                class="rounded-xl
                                       border border-[#ddd9cf]
                                       p-4"
                            >

                                <div
                                    class="flex items-start gap-3"
                                >

                                    <div
                                        class="flex h-11 w-11
                                               shrink-0
                                               items-center justify-center
                                               rounded-xl
                                               bg-[#fff0d6]"
                                    >
                                        📚
                                    </div>


                                    <div class="min-w-0 flex-1">

                                        <div
                                            class="flex items-start
                                                   justify-between gap-2"
                                        >

                                            <div>

                                                <p
                                                    class="font-poppins
                                                           text-xs
                                                           font-semibold"
                                                >
                                                    Aulas de Reforço — Capão Redondo
                                                </p>

                                                <p
                                                    class="mt-1
                                                           font-poppins
                                                           text-[10px]
                                                           text-[#777268]"
                                                >
                                                    Org. Educar · 18 mai · São Paulo
                                                </p>

                                            </div>


                                            <span
                                                class="shrink-0
                                                       rounded-full
                                                       bg-[#fff0d6]
                                                       px-2 py-1
                                                       font-poppins
                                                       text-[9px]
                                                       font-semibold
                                                       text-[#c47813]"
                                            >
                                                +60 XP
                                            </span>

                                        </div>


                                        <span
                                            class="mt-3 inline-flex
                                                   rounded-full
                                                   bg-[#fff0d6]
                                                   px-3 py-1
                                                   font-poppins
                                                   text-[9px]
                                                   text-[#a96713]"
                                        >
                                            Educação
                                        </span>

                                    </div>

                                </div>

                            </div>


                            {{-- BADGES --}}
                            <div>

                                <p
                                    class="mb-3
                                           font-poppins
                                           text-[10px]
                                           text-[#777268]"
                                >
                                    Seus badges conquistados
                                </p>


                                <div
                                    class="grid grid-cols-2 gap-2
                                           sm:grid-cols-4"
                                >

                                    @foreach([
                                        ['🌱', 'Plantador'],
                                        ['🤝', 'Aliado'],
                                        ['❤️', 'Solidário'],
                                        ['🏆', 'Campeão']
                                    ] as $badge)

                                        <div
                                            class="rounded-xl
                                                   border border-[#e2ded5]
                                                   p-3 text-center"
                                        >

                                            <div class="text-lg">
                                                {{ $badge[0] }}
                                            </div>

                                            <p
                                                class="mt-1
                                                       font-poppins
                                                       text-[9px]
                                                       text-[#777268]"
                                            >
                                                {{ $badge[1] }}
                                            </p>

                                        </div>

                                    @endforeach

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </section>


        {{-- ============================================================= --}}
        {{-- POR QUE AJUDAÊ --}}
        {{-- ============================================================= --}}

        <section class="bg-white px-5 py-20 sm:px-8 lg:px-12 lg:py-28">

            <div class="mx-auto max-w-[1200px]">

                <div class="text-center">

                    <span
                        class="font-poppins
                               text-xs font-semibold
                               tracking-[0.15em]
                               text-[#d78c1e]"
                    >
                        Por que Ajudaê?
                    </span>


                    <h2
                        class="mt-4
                               text-3xl font-bold
                               sm:text-4xl"
                        style="font-family: 'Fraunces', serif;"
                    >
                        Voluntariado com propósito e reconhecimento
                    </h2>


                    <p
                        class="mx-auto mt-4 max-w-[560px]
                               font-poppins
                               text-sm leading-6
                               text-[#777268]"
                    >
                        Muito mais do que uma lista de eventos.
                        Uma plataforma que valoriza cada hora do seu tempo.
                    </p>

                </div>


                @php
                    $recursos = [
                        [
                            '🎮',
                            'Gamificação real',
                            'XP, níveis, badges e ranking público. Seu histórico de voluntariado vira um portfólio de impacto que organizadores e ONGs podem ver antes de aprovar sua candidatura.'
                        ],
                        [
                            '🔒',
                            'Segurança e rastreabilidade',
                            'Todos os termos são assinados digitalmente com coleta de IP, geolocalização e timestamp imutável.'
                        ],
                        [
                            '🏢',
                            'ONGs verificadas',
                            'Todo cadastro de ONG é verificado pelo administrador via CNPJ ativo. Só eventos de organizações legítimas chegam ao feed.'
                        ],
                        [
                            '🎯',
                            'Compatibilidade de perfil',
                            'Ao se candidatar, o organizador vê sua foto, bio, habilidades, XP e histórico completo. A seleção é baseada em compatibilidade.'
                        ],
                        [
                            '📜',
                            'Certificados automáticos',
                            'Após a confirmação de presença pelo organizador, você recebe XP, badges e um certificado de participação emitido automaticamente.'
                        ],
                        [
                            '📍',
                            'Ranking por região',
                            'Dispute posição global, por categoria de evento, por cidade e por estado. Atualizado diariamente para todos os usuários.'
                        ],
                    ];
                @endphp


                <div
                    class="mt-14 grid
                           grid-cols-1 gap-4
                           md:grid-cols-2
                           lg:grid-cols-3"
                >

                    @foreach($recursos as $recurso)

                        <article
                            class="rounded-2xl
                                   border border-[#dedbd2]
                                   bg-white
                                   p-6
                                   transition
                                   hover:-translate-y-1
                                   hover:shadow-lg"
                        >

                            <div
                                class="flex h-11 w-11
                                       items-center justify-center
                                       rounded-xl
                                       bg-[#edf7f2]
                                       text-xl"
                            >
                                {{ $recurso[0] }}
                            </div>


                            <h3
                                class="mt-5
                                       font-poppins
                                       text-base font-semibold"
                            >
                                {{ $recurso[1] }}
                            </h3>


                            <p
                                class="mt-3
                                       font-poppins
                                       text-sm leading-6
                                       text-[#777268]"
                            >
                                {{ $recurso[2] }}
                            </p>

                        </article>

                    @endforeach

                </div>

            </div>

        </section>


        {{-- ============================================================= --}}
        {{-- COMO FUNCIONA --}}
        {{-- ============================================================= --}}

        <section class="bg-[#f8f6f0] px-5 py-20 sm:px-8 lg:px-12 lg:py-28">

            <div class="mx-auto max-w-[1200px]">

                <div class="text-center">

                    <span
                        class="font-poppins
                               text-xs font-semibold
                               tracking-[0.15em]
                               text-[#d78c1e]"
                    >
                        Como funciona
                    </span>


                    <h2
                        class="mt-4
                               text-3xl font-bold
                               sm:text-4xl"
                        style="font-family: 'Fraunces', serif;"
                    >
                        De voluntário a campeão em 3 passos
                    </h2>


                    <p
                        class="mx-auto mt-4
                               font-poppins text-sm
                               text-[#777268]"
                    >
                        Cadastro rápido, eventos verificados,
                        reconhecimento real.
                    </p>

                </div>


                <div
                    class="relative mt-16
                           grid grid-cols-1
                           gap-10
                           md:grid-cols-3"
                >

                    {{-- LINHA DESKTOP --}}
                    <div
                        class="absolute left-[16%] right-[16%]
                               top-7 hidden h-px
                               bg-[#c9ddd1]
                               md:block"
                    ></div>


                    @foreach([
                        [
                            '1',
                            'Crie seu perfil',
                            'Preencha seus dados, envie uma foto, assine os Termos Digitais e configure suas habilidades e interesses.'
                        ],
                        [
                            '2',
                            'Encontre e candidate-se',
                            'Explore eventos verificados por categoria, cidade ou data. Candidate-se com uma mensagem de motivação.'
                        ],
                        [
                            '3',
                            'Participe e ganhe XP',
                            'Compareça ao evento, tenha presença confirmada e receba XP, badges e certificado automaticamente.'
                        ]
                    ] as $index => $passo)

                        <div
                            class="relative z-10
                                   text-center"
                        >

                            <div
                                class="mx-auto flex h-14 w-14
                                       items-center justify-center
                                       rounded-full border-2
                                       {{ $index === 2
                                            ? 'border-[#e1a027] bg-[#fff6e5] text-[#c47b13]'
                                            : 'border-[#c5dfd1] bg-white text-[#174b36]' }}
                                       text-lg font-bold"
                            >
                                {{ $passo[0] }}
                            </div>


                            <h3
                                class="mt-5
                                       font-poppins
                                       text-sm font-semibold"
                            >
                                {{ $passo[1] }}
                            </h3>


                            <p
                                class="mx-auto mt-3
                                       max-w-[330px]
                                       font-poppins
                                       text-xs leading-6
                                       text-[#777268]"
                            >
                                {{ $passo[2] }}
                            </p>

                        </div>

                    @endforeach

                </div>

            </div>

        </section>


        {{-- ============================================================= --}}
        {{-- CTA --}}
        {{-- ============================================================= --}}

        <section
            class="relative overflow-hidden
                   bg-[#174b36]
                   px-5 py-20
                   text-white
                   sm:px-8
                   lg:px-12 lg:py-24"
        >

            <div
                class="absolute -right-16 -top-20
                       h-72 w-72
                       rounded-[70px]
                       bg-[#566b2e]/40"
            ></div>


            <div
                class="relative mx-auto
                       max-w-[900px]
                       text-center"
            >

                <h2
                    class="text-3xl font-bold
                           sm:text-4xl"
                    style="font-family: 'Fraunces', serif;"
                >
                    Pronto para fazer a diferença?
                </h2>


                <p
                    class="mx-auto mt-4
                           max-w-[600px]
                           font-poppins
                           text-sm leading-6
                           text-white/70"
                >
                    Junte-se a milhares de voluntários que já estão
                    impactando comunidades.
                </p>


                <div
                    class="mx-auto mt-9
                           grid max-w-[600px]
                           grid-cols-1 gap-4
                           sm:grid-cols-2"
                >

                    <a
                        href="/cadastro"
                        class="rounded-2xl
                               border border-white/20
                               bg-white/10
                               p-6
                               transition
                               hover:bg-white/15"
                    >

                        <div class="text-2xl">
                            🙋
                        </div>

                        <p
                            class="mt-3
                                   font-poppins
                                   text-sm font-semibold"
                        >
                            Sou voluntário
                        </p>

                        <p
                            class="mt-2
                                   font-poppins
                                   text-xs text-white/65"
                        >
                            Quero me candidatar a eventos e acumular XP
                        </p>

                    </a>


                    <a
                        href="/cadastro/ong"
                        class="rounded-2xl
                               border border-white/20
                               bg-white/10
                               p-6
                               transition
                               hover:bg-white/15"
                    >

                        <div class="text-2xl">
                            🏢
                        </div>

                        <p
                            class="mt-3
                                   font-poppins
                                   text-sm font-semibold"
                        >
                            Sou uma ONG
                        </p>

                        <p
                            class="mt-2
                                   font-poppins
                                   text-xs text-white/65"
                        >
                            Quero publicar eventos e encontrar voluntários
                        </p>

                    </a>

                </div>

            </div>

        </section>


        {{-- ============================================================= --}}
        {{-- FOOTER --}}
        {{-- ============================================================= --}}

        <footer
            class="bg-[#0e2c20]
                   px-5 py-10
                   text-white
                   sm:px-8
                   lg:px-12"
        >

            <div class="mx-auto max-w-[1200px]">

                <div
                    class="flex flex-col gap-8
                           md:flex-row
                           md:items-start
                           md:justify-between"
                >

                    <div>

                        <a
                            href="/"
                            class="text-xl font-semibold"
                            style="font-family: 'Fraunces', serif;"
                        >
                            Ajud<span class="text-[#e3a62f]">ae</span>
                        </a>

                        <p
                            class="mt-2
                                   font-poppins
                                   text-xs
                                   text-white/50"
                        >
                            Plataforma de voluntariado gamificado
                        </p>

                    </div>


                    <div
                        class="flex flex-wrap gap-x-6 gap-y-3
                               font-poppins
                               text-xs text-white/55"
                    >

                        <a href="/termos" class="hover:text-white">
                            Termos de Uso
                        </a>

                        <a href="/privacidade" class="hover:text-white">
                            Política de Privacidade (LGPD)
                        </a>

                        <a href="/cadastro/ong" class="hover:text-white">
                            Para ONGs
                        </a>

                        <a href="#" class="hover:text-white">
                            Contato
                        </a>

                    </div>

                </div>


                <div
                    class="mt-8
                           border-t border-white/10
                           pt-6
                           text-center"
                >

                    <p
                        class="font-poppins
                               text-[11px]
                               leading-5
                               text-white/40"
                    >
                        © 2026 Ajudaê — Projeto TCC.
                        DS. Para fins acadêmicos.
                    </p>

                </div>

            </div>

        </footer>

    </main>

</body>
</html>