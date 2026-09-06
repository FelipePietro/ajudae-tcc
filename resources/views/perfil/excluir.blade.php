<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Excluir conta | Ajudaê</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>

<body class="min-h-screen bg-[#f7f5ef] font-outfit text-[#292820]">

    @include('/components/navbar-feed')

    <main class="px-4 py-8 sm:px-6 md:px-8 md:py-14">

        <div class="mx-auto w-full max-w-[680px]">

            {{-- VOLTAR --}}
            <a
                href="/perfil/editar"
                class="mb-5 inline-flex items-center gap-2 font-poppins text-sm text-[#817c70] transition hover:text-[#17392a] md:mb-6"
            >
                ← Voltar para editar perfil
            </a>


            {{-- CARD PRINCIPAL --}}
            <section
                class="rounded-2xl border border-red-200 bg-white p-4 shadow-sm sm:p-5 md:p-8"
            >

                {{-- Ícone --}}
                <div
                    class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-50 text-2xl sm:h-16 sm:w-16 sm:text-3xl"
                >
                    ⚠
                </div>


                {{-- Cabeçalho --}}
                <div class="mt-5 text-center">

                    <span
                        class="inline-flex rounded-full bg-red-50 px-3 py-2 font-poppins text-[11px] font-semibold uppercase tracking-wide text-red-600 sm:px-4 sm:text-xs"
                    >
                        Zona de perigo
                    </span>

                    <h1
                        class="mt-4 break-words text-2xl font-bold leading-tight text-[#292820] sm:text-3xl md:text-4xl"
                    >
                        Excluir minha conta
                    </h1>

                    <p
                        class="mx-auto mt-3 max-w-[540px] break-words font-poppins text-sm leading-6 text-[#817c70]"
                    >
                        Você está prestes a solicitar a exclusão permanente da sua conta no Ajudaê.
                        Leia atentamente as informações abaixo antes de continuar.
                    </p>

                </div>


                {{-- O QUE ACONTECE --}}
                <div
                    class="mt-6 rounded-xl border border-[#edcf9f] bg-[#fff7ea] p-4 sm:mt-8 sm:p-5"
                >

                    <h2
                        class="font-poppins text-sm font-semibold text-[#9a6217]"
                    >
                        O que acontece depois da solicitação?
                    </h2>

                    <ul
                        class="mt-4 space-y-3 font-poppins text-sm leading-6 text-[#756b5c]"
                    >

                        <li class="flex min-w-0 gap-3">
                            <span class="mt-1 shrink-0 text-[#c77b18]">•</span>

                            <span class="min-w-0 break-words">
                                Sua conta entrará em processo de exclusão.
                            </span>
                        </li>

                        <li class="flex min-w-0 gap-3">
                            <span class="mt-1 shrink-0 text-[#c77b18]">•</span>

                            <span class="min-w-0 break-words">
                                Você terá <strong>7 dias</strong> para cancelar a solicitação.
                            </span>
                        </li>

                        <li class="flex min-w-0 gap-3">
                            <span class="mt-1 shrink-0 text-[#c77b18]">•</span>

                            <span class="min-w-0 break-words">
                                Depois desse prazo, sua conta será removida definitivamente.
                            </span>
                        </li>

                        <li class="flex min-w-0 gap-3">
                            <span class="mt-1 shrink-0 text-[#c77b18]">•</span>

                            <span class="min-w-0 break-words">
                                A exclusão poderá ser bloqueada caso você ainda seja responsável por eventos ativos.
                            </span>
                        </li>

                    </ul>

                </div>


                {{-- CONFIRMAÇÃO --}}
                <div class="mt-6 sm:mt-8">

                    <label
                        for="confirmacao-exclusao"
                        class="block font-poppins text-sm font-semibold text-[#343229]"
                    >
                        Para confirmar, digite
                        <span class="text-red-600">EXCLUIR</span>
                    </label>

                    <p
                        class="mt-1 font-poppins text-xs leading-5 text-[#8b867c]"
                    >
                        Essa confirmação ajuda a evitar exclusões acidentais.
                    </p>

                    <input
                        type="text"
                        id="confirmacao-exclusao"
                        placeholder="Digite EXCLUIR"
                        autocomplete="off"
                        class="mt-3 w-full min-w-0 rounded-lg border border-[#dedacf] bg-[#fbfaf7]
                               px-4 py-3 font-poppins text-sm outline-none transition
                               placeholder:text-[#aaa599] focus:border-red-400"
                    >

                </div>


                {{-- BOTÕES --}}
                <div
                    class="mt-6 flex flex-col-reverse gap-3 sm:mt-8 sm:flex-row sm:justify-end"
                >

                    <div class="w-full sm:w-auto">
                        <x-botao-secundario
                            href="/perfil/editar"
                            texto="Cancelar"
                        />
                    </div>

                    <button
                        type="button"
                        id="btn-confirmar-exclusao"
                        disabled
                        class="w-full cursor-not-allowed rounded-lg bg-red-300 px-5 py-3
                               font-poppins text-sm font-semibold text-white transition
                               sm:w-auto"
                    >
                        Solicitar exclusão
                    </button>

                </div>

            </section>


            {{-- AVISO FINAL --}}
            <div
                class="mt-5 rounded-xl border border-neutral-200 bg-white px-4 py-4 sm:px-5"
            >

                <p
                    class="break-words font-poppins text-xs leading-5 text-[#817c70]"
                >
                    Caso você mude de ideia durante o período de segurança,
                    basta acessar sua conta novamente e cancelar a exclusão antes
                    da data prevista.
                </p>

            </div>

        </div>

    </main>


    <script>

        document.addEventListener('DOMContentLoaded', () => {

            const input =
                document.querySelector('#confirmacao-exclusao');

            const botao =
                document.querySelector('#btn-confirmar-exclusao');


            input?.addEventListener('input', () => {

                const confirmado =
                    input.value.trim() === 'EXCLUIR';

                botao.disabled =
                    !confirmado;


                if (confirmado) {

                    botao.classList.remove(
                        'bg-red-300',
                        'cursor-not-allowed'
                    );

                    botao.classList.add(
                        'bg-red-600',
                        'hover:bg-red-700',
                        'cursor-pointer'
                    );

                } else {

                    botao.classList.remove(
                        'bg-red-600',
                        'hover:bg-red-700',
                        'cursor-pointer'
                    );

                    botao.classList.add(
                        'bg-red-300',
                        'cursor-not-allowed'
                    );

                }

            });


            /*
            |--------------------------------------------------------------------------
            | MOCK DA SOLICITAÇÃO
            |--------------------------------------------------------------------------
            */

            botao?.addEventListener('click', () => {

                if (botao.disabled) {
                    return;
                }

                window.location.href =
                    '/perfil/exclusao-pendente';

            });

        });

    </script>

</body>

</html>