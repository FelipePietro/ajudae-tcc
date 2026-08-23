<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Exclusão pendente | Ajudaê</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>

<body class="min-h-screen bg-[#f7f5ef] font-outfit text-[#292820]">

    @include('/components/navbar-feed')

    @php

        $dataExclusao = \Carbon\Carbon::parse(
            $pessoa['deletar_em']
        );

        $diasRestantes = max(
            0,
            now()->startOfDay()->diffInDays(
                $dataExclusao->copy()->startOfDay(),
                false
            )
        );

    @endphp


    <main
        class="flex min-h-[75vh] items-center justify-center px-4 py-10 md:px-8"
    >

        <div class="w-full max-w-[680px]">

            {{-- CARD --}}
            <section
                class="rounded-2xl border border-[#e3ded3] bg-white p-6 shadow-sm md:p-10"
            >

                {{-- Ícone --}}
                <div
                    class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-[#fff3dc] text-3xl"
                >
                    ⏳
                </div>


                {{-- Cabeçalho --}}
                <div class="mt-6 text-center">

                    <span
                        class="inline-flex rounded-full bg-[#fff0d8] px-4 py-2 font-poppins text-xs font-semibold uppercase tracking-wide text-[#b66e11]"
                    >
                        Exclusão pendente
                    </span>


                    <h1
                        class="mt-4 text-3xl font-bold leading-tight text-[#292820] md:text-4xl"
                    >
                        Sua conta está em processo de exclusão
                    </h1>


                    <p
                        class="mx-auto mt-3 max-w-[540px] font-poppins text-sm leading-6 text-[#817c70]"
                    >
                        A solicitação de exclusão foi registrada.
                        Caso nenhuma ação seja tomada, sua conta será removida
                        automaticamente ao final do período de segurança.
                    </p>

                </div>


                {{-- CONTAGEM --}}
                <div
                    class="mt-8 rounded-2xl border border-[#edcf9f] bg-[#fff8eb] p-6 text-center"
                >

                    <p
                        class="font-poppins text-xs font-semibold uppercase tracking-wide text-[#9d6b25]"
                    >
                        Tempo restante
                    </p>


                    <p
                        class="mt-2 text-5xl font-bold text-[#292820]"
                    >
                        {{ $diasRestantes }}
                    </p>


                    <p
                        class="mt-1 font-poppins text-sm text-[#817c70]"
                    >
                        {{ $diasRestantes === 1 ? 'dia restante' : 'dias restantes' }}
                    </p>


                    <div
                        class="mx-auto my-5 h-px max-w-[380px] bg-[#ead8bc]"
                    ></div>


                    <p
                        class="font-poppins text-xs text-[#817c70]"
                    >
                        Exclusão prevista para
                    </p>


                    <p
                        class="mt-1 text-lg font-semibold text-[#292820]"
                    >
                        {{ $dataExclusao
                            ->locale('pt_BR')
                            ->translatedFormat('d \d\e F \d\e Y')
                        }}
                    </p>

                </div>


                {{-- INFORMAÇÃO --}}
                <div
                    class="mt-6 rounded-xl border border-[#d9e4da] bg-[#f2f7f2] p-5"
                >

                    <h2
                        class="font-poppins text-sm font-semibold text-[#315c34]"
                    >
                        Você mudou de ideia?
                    </h2>


                    <p
                        class="mt-2 font-poppins text-sm leading-6 text-[#667566]"
                    >
                        Durante o período de segurança, você pode cancelar
                        a exclusão e recuperar normalmente o acesso à sua conta.
                    </p>

                </div>


                {{-- BOTÕES --}}
                <div
                    class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center"
                >

                    <x-botao-primario
                        href="/perfil"
                        texto="Cancelar exclusão"
                    />


                    <x-botao-secundario
                        href="/"
                        texto="Sair da conta"
                    />

                </div>


                {{-- AVISO --}}
                <p
                    class="mx-auto mt-6 max-w-[520px] text-center font-poppins text-xs leading-5 text-[#999388]"
                >
                    Se nenhuma ação for tomada até a data indicada,
                    a exclusão da conta seguirá automaticamente.
                </p>

            </section>

        </div>

    </main>


    @include('/components/footer')

</body>

</html>