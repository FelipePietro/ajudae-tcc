<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Meu Perfil | Ajudae</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>

<body class="min-h-screen bg-[#f7f5ef] font-outfit text-[#17392a]">

    {{-- =========================
         NAVBAR
    ========================== --}}
    @include('/components/navbar-feed')


    {{-- =========================
         CÁLCULOS DO PERFIL
    ========================== --}}
    @php
        $xpRestante = max(
            0,
            $xp_proximo_nivel - $xp_user
        );

        $porcentagemXp = $xp_proximo_nivel > 0
            ? min(100, ($xp_user / $xp_proximo_nivel) * 100)
            : 0;
    @endphp


    {{-- =========================
         CONTEÚDO
    ========================== --}}
    <main class="px-4 py-6 sm:px-6 md:px-8 md:py-10">

        <div class="mx-auto w-full max-w-5xl">

            {{-- =========================
                 CARD PRINCIPAL
            ========================== --}}
            <section
                class="rounded-xl border border-neutral-200 bg-white p-5 shadow-sm md:p-7"
            >

                <div
                    class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between"
                >

                    {{-- Usuário --}}
                    <div class="flex min-w-0 items-center gap-4">

                        {{-- Foto --}}
                        <div
                            class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-full bg-[#e5f1d7] md:h-20 md:w-20"
                        >

                            <img
                                src="{{ $foto_user }}"
                                alt="Foto de {{ $nome_user }}"
                                class="h-full w-full object-cover"
                            >

                        </div>


                        {{-- Informações --}}
                        <div class="min-w-0">

                            <h1
                                class="break-words text-xl font-bold text-black md:text-2xl"
                            >
                                {{ $nome_user }}
                            </h1>

                            <p
                                class="mt-1 break-words font-poppins text-sm text-neutral-500"
                            >
                                📍 {{ $cidade_user }}, {{ $uf_user }}
                            </p>


                            {{-- Badges principais --}}
                            <div class="mt-3 flex flex-wrap gap-2 font-poppins">

                                <span
                                    class="rounded-full bg-[#fff0be] px-3 py-1 text-xs font-medium text-[#bd7c00]"
                                >
                                    🔥 Nível {{ $nivel_user }}
                                    · {{ $titulo_nivel }}
                                </span>

                                <span
                                    class="rounded-full bg-[#e3f4e8] px-3 py-1 text-xs font-medium text-[#1A3D2B]"
                                >
                                    {{ $xp_user }} XP
                                </span>

                                <span
                                    class="rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-600"
                                >
                                    {{ $tipo_user }}
                                </span>

                            </div>

                        </div>

                    </div>


                    {{-- Ações --}}
                    <div
                        class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:flex-wrap"
                    >

                        <button
                            type="button"
                            id="btn-compartilhar"
                            class="w-full rounded-full border border-neutral-300 px-4 py-2 text-center text-sm font-medium text-neutral-700 transition hover:bg-neutral-100 sm:w-auto"
                        >
                            Compartilhar perfil
                        </button>

                        <a
                            href="/perfil/editar"
                            class="w-full rounded-full border border-[#17392a] px-4 py-2 text-center text-sm font-medium text-[#17392a] transition hover:bg-[#17392a] hover:text-white sm:w-auto"
                        >
                            ✏ Editar perfil
                        </a>

                    </div>

                </div>

            </section>


            {{-- =========================
                 ESTATÍSTICAS
            ========================== --}}
            <section
                class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3"
            >

                {{-- Eventos --}}
                <div
                    class="rounded-xl border border-neutral-200 bg-white p-5 text-center shadow-sm"
                >

                    <span class="text-xl">
                        🎯
                    </span>

                    <p
                        class="mt-2 text-2xl font-bold text-black"
                    >
                        {{ $eventos_user }}
                    </p>

                    <p
                        class="font-poppins text-xs text-neutral-500"
                    >
                        Eventos concluídos
                    </p>

                </div>


                {{-- Badges --}}
                <div
                    class="rounded-xl border border-neutral-200 bg-white p-5 text-center shadow-sm"
                >

                    <span class="text-xl">
                        🏅
                    </span>

                    <p
                        class="mt-2 text-2xl font-bold text-black"
                    >
                        {{ $badges_user }}
                    </p>

                    <p
                        class="font-poppins text-xs text-neutral-500"
                    >
                        Badges conquistadas
                    </p>

                </div>


                {{-- XP --}}
                <div
                    class="rounded-xl border border-neutral-200 bg-white p-5 text-center shadow-sm"
                >

                    <span class="text-xl">
                        ⭐
                    </span>

                    <p
                        class="mt-2 text-2xl font-bold text-black"
                    >
                        {{ $xp_user }}
                    </p>

                    <p
                        class="font-poppins text-xs text-neutral-500"
                    >
                        XP acumulado
                    </p>

                </div>

            </section>


            {{-- =========================
                 CONTEÚDO PRINCIPAL
            ========================== --}}
            <section
                class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-[2fr_1fr]"
            >


                {{-- =========================
                     COLUNA ESQUERDA
                ========================== --}}
                <div
                    class="min-w-0 rounded-xl border border-neutral-200 bg-white p-5 shadow-sm md:p-6"
                >

                    {{-- Abas --}}
                    <div
                        class="flex gap-6 overflow-x-auto border-b border-neutral-200"
                    >

                        <button
                            type="button"
                            data-tab="sobre"
                            class="tab-button whitespace-nowrap border-b-2 border-[#17392a] pb-3 text-sm font-semibold text-[#17392a]"
                        >
                            Sobre
                        </button>

                        <button
                            type="button"
                            data-tab="badges"
                            class="tab-button whitespace-nowrap pb-3 text-sm text-neutral-500 transition hover:text-[#17392a]"
                        >
                            Badges
                        </button>

                        <button
                            type="button"
                            data-tab="historico"
                            class="tab-button whitespace-nowrap pb-3 text-sm text-neutral-500 transition hover:text-[#17392a]"
                        >
                            Histórico
                        </button>

                    </div>


                    {{-- =========================
                         TAB SOBRE
                    ========================== --}}
                    <div
                        id="tab-sobre"
                        class="tab-content"
                    >

                        {{-- Bio --}}
                        <div class="mt-6">

                            <h2
                                class="text-sm font-semibold text-neutral-500"
                            >
                                Bio
                            </h2>

                            <p
                                class="mt-2 break-words font-poppins text-sm leading-6 text-neutral-700"
                            >
                                {{ $bio_user }}
                            </p>

                        </div>


                        <hr class="my-6 border-neutral-200">


                        {{-- =========================
                             PREFERÊNCIAS DO PERFIL
                        ========================== --}}
                        <div
                            class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                        >

                            <div class="min-w-0">

                                <h2
                                    class="text-base font-semibold text-[#17392a]"
                                >
                                    Preferências do perfil
                                </h2>

                                <p
                                    class="mt-1 font-poppins text-xs leading-5 text-neutral-500"
                                >
                                    Gerencie suas habilidades, recursos e causas de interesse.
                                </p>

                            </div>


                            <div class="shrink-0">

                                <x-botao-secundario
                                    href="/perfil/preferencias"
                                    texto="Alterar preferências"
                                />

                            </div>

                        </div>


                        {{-- Habilidades --}}
                        <div>

                            <h2
                                class="text-sm font-semibold text-neutral-500"
                            >
                                Habilidades
                            </h2>


                            <div
                                class="mt-3 flex flex-wrap gap-2"
                            >

                                @forelse ($habilidades_user as $habilidade)

                                    <span
                                        class="rounded-md bg-neutral-100 px-3 py-1.5 font-poppins text-xs text-neutral-700"
                                        title="Nível {{ $habilidade['nivel'] }}"
                                    >
                                        {{ $habilidade['nome'] }}
                                    </span>

                                @empty

                                    <p
                                        class="font-poppins text-xs text-neutral-400"
                                    >
                                        Nenhuma habilidade cadastrada.
                                    </p>

                                @endforelse

                            </div>

                        </div>


                        {{-- Recursos --}}
                        <div class="mt-6">

                            <h2
                                class="text-sm font-semibold text-neutral-500"
                            >
                                Recursos disponíveis
                            </h2>


                            <div
                                class="mt-3 flex flex-wrap gap-2"
                            >

                                @forelse ($recursos_user as $recurso)

                                    <span
                                        class="rounded-md bg-neutral-100 px-3 py-1.5 font-poppins text-xs text-neutral-700"
                                        title="{{ $recurso['detalhes'] }}"
                                    >
                                        {{ $recurso['nome'] }}
                                    </span>

                                @empty

                                    <p
                                        class="font-poppins text-xs text-neutral-400"
                                    >
                                        Nenhum recurso cadastrado.
                                    </p>

                                @endforelse

                            </div>

                        </div>


                        {{-- Causas --}}
                        <div class="mt-6">

                            <h2
                                class="text-sm font-semibold text-neutral-500"
                            >
                                Categorias de interesse
                            </h2>


                            <div
                                class="mt-3 flex flex-wrap gap-2"
                            >

                                @forelse ($causas_user as $causa)

                                    <span
                                        class="rounded-full bg-green-100 px-3 py-1 font-poppins text-xs font-medium text-green-700"
                                    >
                                        {{ $causa['icone'] }}
                                        {{ $causa['nome'] }}
                                    </span>

                                @empty

                                    <p
                                        class="font-poppins text-xs text-neutral-400"
                                    >
                                        Nenhuma causa cadastrada.
                                    </p>

                                @endforelse

                            </div>

                        </div>


                        {{-- Organizador --}}
                        <div
                            class="mt-6 rounded-lg border-l-4 border-[#e8a40c] bg-[#fff9ed] p-4"
                        >

                            <h3
                                class="text-sm font-semibold text-black"
                            >
                                Quer ser Organizador?
                            </h3>

                            <p
                                class="mt-1 font-poppins text-xs leading-5 text-neutral-600"
                            >
                                Aprenda como você pode criar eventos
                                e assumir responsabilidades como organizador.
                            </p>

                            <a
                                href="#"
                                class="mt-3 inline-block font-poppins text-xs font-semibold text-[#bd7c00] hover:underline"
                            >
                                Saiba mais →
                            </a>

                        </div>

                    </div>


                    {{-- =========================
                         TAB BADGES
                    ========================== --}}
                    <div
                        id="tab-badges"
                        class="tab-content hidden"
                    >

                        <div class="py-8">

                            <h2
                                class="text-lg font-bold text-black"
                            >
                                Badges conquistadas
                            </h2>

                            <p
                                class="mt-2 font-poppins text-sm text-neutral-500"
                            >
                                Suas conquistas na plataforma.
                            </p>


                            <div
                                class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2"
                            >

                                @forelse ($badges as $badge)

                                    <div
                                        class="rounded-xl border border-neutral-200 bg-neutral-50 p-4"
                                    >

                                        <div
                                            class="flex min-w-0 items-start gap-3"
                                        >

                                            <div
                                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#fff0be] text-xl"
                                            >
                                                {{ $badge['icone'] }}
                                            </div>


                                            <div class="min-w-0">

                                                <h3
                                                    class="break-words text-sm font-semibold text-black"
                                                >
                                                    {{ $badge['nome'] }}
                                                </h3>

                                                <p
                                                    class="mt-1 break-words font-poppins text-xs leading-5 text-neutral-500"
                                                >
                                                    {{ $badge['descricao'] }}
                                                </p>

                                            </div>

                                        </div>

                                    </div>

                                @empty

                                    <p
                                        class="font-poppins text-sm text-neutral-400"
                                    >
                                        Nenhuma badge conquistada.
                                    </p>

                                @endforelse

                            </div>

                        </div>

                    </div>


                    {{-- =========================
                         TAB HISTÓRICO
                    ========================== --}}
                    <div
                        id="tab-historico"
                        class="tab-content hidden"
                    >

                        <div class="py-8">

                            <h2
                                class="text-lg font-bold text-black"
                            >
                                Histórico
                            </h2>

                            <p
                                class="mt-2 font-poppins text-sm text-neutral-500"
                            >
                                Eventos dos quais você participou.
                            </p>


                            <div
                                class="mt-6 space-y-3"
                            >

                                @forelse ($historico as $item)

                                    <div
                                        class="flex flex-col gap-3 rounded-xl border border-neutral-200 bg-neutral-50 p-4 sm:flex-row sm:items-center sm:justify-between"
                                    >

                                        <div class="min-w-0">

                                            <h3
                                                class="break-words text-sm font-semibold text-black"
                                            >
                                                {{ $item['nome_evento'] }}
                                            </h3>

                                            <p
                                                class="mt-1 font-poppins text-xs text-neutral-500"
                                            >
                                                {{ $item['data'] }}
                                            </p>

                                        </div>


                                        <div
                                            class="flex shrink-0 flex-wrap items-center gap-3"
                                        >

                                            <span
                                                class="rounded-full bg-green-100 px-3 py-1 font-poppins text-xs font-medium text-green-700"
                                            >
                                                {{ $item['status'] }}
                                            </span>

                                            <span
                                                class="font-poppins text-xs font-semibold text-[#d18d00]"
                                            >
                                                +{{ $item['xp_recebido'] }} XP
                                            </span>

                                        </div>

                                    </div>

                                @empty

                                    <p
                                        class="font-poppins text-sm text-neutral-400"
                                    >
                                        Nenhum evento registrado no histórico.
                                    </p>

                                @endforelse

                            </div>

                        </div>

                    </div>

                </div>


                {{-- =========================
                     COLUNA DIREITA
                ========================== --}}
                <aside
                    class="flex min-w-0 flex-col gap-4"
                >


                    {{-- Ranking --}}
                    <div
                        class="rounded-xl border border-neutral-200 bg-white p-5 shadow-sm"
                    >

                        <h2
                            class="text-sm font-bold text-black"
                        >
                            📊 Sua posição
                        </h2>


                        <div
                            class="mt-4 space-y-2"
                        >

                            @foreach ($ranking as $usuario)

                                <div
                                    class="flex min-w-0 items-center justify-between gap-3 rounded-lg px-4 py-3
                                    {{ $usuario['posicao'] === 1 ? 'bg-[#fff8e8]' : 'bg-neutral-50' }}"
                                >

                                    <div
                                        class="flex min-w-0 items-center gap-2"
                                    >

                                        <span class="shrink-0">

                                            @if ($usuario['posicao'] === 1)
                                                🥇

                                            @elseif ($usuario['posicao'] === 2)
                                                🥈

                                            @elseif ($usuario['posicao'] === 3)
                                                🥉
                                            @endif

                                        </span>


                                        <span
                                            class="min-w-0 truncate font-poppins text-xs"
                                        >
                                            {{ $usuario['nome'] }}
                                        </span>

                                    </div>


                                    <span
                                        class="shrink-0 font-poppins text-xs font-semibold text-[#d18d00]"
                                    >
                                        {{ number_format(
                                            $usuario['xp'],
                                            0,
                                            ',',
                                            '.'
                                        ) }} XP
                                    </span>

                                </div>

                            @endforeach


                            {{-- Usuário atual --}}
                            <div
                                class="flex min-w-0 items-center justify-between gap-3 rounded-lg bg-[#e6f4eb] px-4 py-3"
                            >

                                <div
                                    class="flex min-w-0 items-center gap-2"
                                >

                                    <span
                                        class="shrink-0 rounded bg-[#17392a] px-2 py-1 text-[10px] font-bold text-white"
                                    >
                                        #{{ $posicao_user }}
                                    </span>

                                    <span
                                        class="min-w-0 truncate font-poppins text-xs font-semibold"
                                    >
                                        Você
                                    </span>

                                </div>


                                <span
                                    class="shrink-0 font-poppins text-xs font-semibold text-[#d18d00]"
                                >
                                    {{ number_format(
                                        $xp_user,
                                        0,
                                        ',',
                                        '.'
                                    ) }} XP
                                </span>

                            </div>

                        </div>

                    </div>


                    {{-- Próximo nível --}}
                    <div
                        class="rounded-xl border border-neutral-200 bg-white p-5 shadow-sm"
                    >

                        <h2
                            class="text-sm font-bold text-black"
                        >
                            🎯 Próximo nível
                        </h2>


                        <p
                            class="mt-4 font-poppins text-xs text-neutral-600"
                        >
                            {{ $xpRestante }} XP para
                            Nível {{ $nivel_user + 1 }}
                            — {{ $titulo_proximo_nivel }}
                        </p>


                        {{-- Barra de XP --}}
                        <div
                            class="mt-3 h-2 w-full overflow-hidden rounded-full bg-neutral-200"
                        >

                            <div
                                class="h-full rounded-full bg-[#e3a31a] transition-all duration-500"
                                style="width: {{ $porcentagemXp }}%"
                            ></div>

                        </div>


                        <p
                            class="mt-3 font-poppins text-xs leading-5 text-neutral-500"
                        >
                            Continue participando de eventos
                            para atingir o próximo nível.
                        </p>

                    </div>


                    {{-- Certificados --}}
                    <div
                        class="rounded-xl border border-neutral-200 bg-white p-5 shadow-sm"
                    >

                        <div
                            class="flex items-center justify-between gap-3"
                        >

                            <h2
                                class="text-sm font-bold text-black"
                            >
                                📜 Certificados
                            </h2>

                            <a
                                href="#"
                                class="shrink-0 font-poppins text-xs font-medium text-[#17392a] hover:underline"
                            >
                                Ver todos
                            </a>

                        </div>


                        <div
                            class="mt-4 space-y-3"
                        >

                            @forelse ($certificados as $certificado)

                                <a
                                    href="{{ $certificado['url'] }}"
                                    class="flex min-w-0 items-center justify-between gap-3 rounded-lg bg-neutral-50 px-4 py-3 transition hover:bg-neutral-100"
                                >

                                    <div class="min-w-0">

                                        <p
                                            class="truncate font-poppins text-xs font-medium text-black"
                                        >
                                            {{ $certificado['nome_evento'] }}
                                        </p>

                                        <p
                                            class="mt-1 font-poppins text-[10px] text-neutral-500"
                                        >
                                            {{ $certificado['data'] }}
                                        </p>

                                    </div>


                                    <span
                                        class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-neutral-300 text-xs"
                                    >
                                        →
                                    </span>

                                </a>

                            @empty

                                <p
                                    class="font-poppins text-xs text-neutral-400"
                                >
                                    Nenhum certificado disponível.
                                </p>

                            @endforelse

                        </div>

                    </div>

                </aside>

            </section>

        </div>

    </main>


    {{-- =========================
         JAVASCRIPT
    ========================== --}}
    <script>

        document.addEventListener('DOMContentLoaded', () => {

            /*
            |--------------------------------------------------------------------------
            | Abas
            |--------------------------------------------------------------------------
            */

            const buttons =
                document.querySelectorAll('.tab-button');

            const contents =
                document.querySelectorAll('.tab-content');


            buttons.forEach(button => {

                button.addEventListener('click', () => {

                    const tab =
                        button.dataset.tab;


                    /*
                    |--------------------------------------------------------------------------
                    | Esconde conteúdos
                    |--------------------------------------------------------------------------
                    */

                    contents.forEach(content => {

                        content.classList.add('hidden');

                    });


                    /*
                    |--------------------------------------------------------------------------
                    | Remove estado ativo
                    |--------------------------------------------------------------------------
                    */

                    buttons.forEach(item => {

                        item.classList.remove(
                            'border-b-2',
                            'border-[#17392a]',
                            'font-semibold',
                            'text-[#17392a]'
                        );

                        item.classList.add(
                            'text-neutral-500'
                        );

                    });


                    /*
                    |--------------------------------------------------------------------------
                    | Exibe tab escolhida
                    |--------------------------------------------------------------------------
                    */

                    const content =
                        document.querySelector(
                            `#tab-${tab}`
                        );

                    content?.classList.remove(
                        'hidden'
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Ativa botão
                    |--------------------------------------------------------------------------
                    */

                    button.classList.remove(
                        'text-neutral-500'
                    );

                    button.classList.add(
                        'border-b-2',
                        'border-[#17392a]',
                        'font-semibold',
                        'text-[#17392a]'
                    );

                });

            });


            /*
            |--------------------------------------------------------------------------
            | Compartilhar perfil
            |--------------------------------------------------------------------------
            */

            const compartilhar =
                document.querySelector(
                    '#btn-compartilhar'
                );


            compartilhar?.addEventListener(
                'click',
                async () => {

                    const url =
                        window.location.href;


                    try {

                        if (navigator.share) {

                            await navigator.share({

                                title:
                                    'Perfil de {{ $nome_user }} no Ajudae',

                                text:
                                    'Conheça meu perfil de voluntariado no Ajudae.',

                                url: url

                            });

                            return;
                        }


                        await navigator
                            .clipboard
                            .writeText(url);


                        alert(
                            'Link do perfil copiado!'
                        );


                    } catch (error) {

                        console.error(
                            'Erro ao compartilhar perfil:',
                            error
                        );

                    }

                }
            );

        });

    </script>

</body>

</html>