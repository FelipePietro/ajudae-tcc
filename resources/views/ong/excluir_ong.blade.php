<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir conta da ONG | Ajudae</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>

<body class="min-h-screen bg-[#F7F5F0] font-outfit text-[#26251f]">



        @php
            /*
             * MOCK PARA A PRÉ-BANCA.
             *
             * Depois, substitua pelo número real de eventos ativos
             * retornado pelo backend.
             */
            $eventosAtivos = $eventosAtivos ?? 0;
            $podeExcluir = $eventosAtivos === 0;
        @endphp

        <div class="mx-auto w-full max-w-[760px] px-4 py-8 sm:px-6 sm:py-10 lg:px-8">

            <a
                href="{{ route('ong.perfil.editar') }}"
                class="inline-flex items-center gap-2 font-poppins text-sm text-[#6F716B] transition hover:text-[#17392A]"
            >
                ← Voltar para editar perfil
            </a>

            <section
                class="mt-6 overflow-hidden rounded-3xl border border-red-200 bg-white shadow-sm"
            >
                <div class="border-b border-red-100 bg-red-50/60 px-6 py-6 sm:px-8">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 text-xl"
                        aria-hidden="true"
                    >
                        ⚠️
                    </div>

                    <h1 class="mt-4 font-fraunces text-3xl font-bold text-[#292820]">
                        Excluir conta da ONG
                    </h1>

                    <p class="mt-3 max-w-2xl font-poppins text-sm leading-6 text-[#777269]">
                        Esta ação inicia o processo de exclusão da organização no Ajudae.
                        Confira as condições antes de continuar.
                    </p>
                </div>

                <div class="space-y-6 p-6 sm:p-8">

                    <div>
                        <p class="font-poppins text-xs font-semibold uppercase tracking-[0.12em] text-[#969087]">
                            Organização
                        </p>

                        <h2 class="mt-2 text-lg font-semibold text-[#17392A]">
                            {{ $ong['nome_fantasia'] ?? $ong['razao_social'] ?? 'Sua ONG' }}
                        </h2>

                        @if(!empty($ong['cnpj']))
                            <p class="mt-1 font-poppins text-xs text-neutral-500">
                                CNPJ {{ $ong['cnpj'] }}
                            </p>
                        @endif
                    </div>


                    <div class="rounded-2xl border border-[#E5E1D8] bg-[#FAF9F5] p-5">
                        <h2 class="font-semibold text-[#292820]">
                            Regras para exclusão
                        </h2>

                        <ul class="mt-3 space-y-3 font-poppins text-sm leading-6 text-[#68645C]">
                            <li class="flex gap-3">
                                <span class="mt-0.5">•</span>
                                <span>
                                    A ONG <strong>não pode possuir eventos ativos</strong>
                                    no momento da solicitação.
                                </span>
                            </li>

                            <li class="flex gap-3">
                                <span class="mt-0.5">•</span>
                                <span>
                                    Eventos em andamento precisam ser encerrados ou cancelados
                                    antes que a exclusão seja solicitada.
                                </span>
                            </li>

                            <li class="flex gap-3">
                                <span class="mt-0.5">•</span>
                                <span>
                                    O histórico necessário para segurança, obrigações legais
                                    e exercício de direitos poderá ser preservado quando aplicável.
                                </span>
                            </li>

                            <li class="flex gap-3">
                                <span class="mt-0.5">•</span>
                                <span>
                                    Após a solicitação, haverá um
                                    <strong>período de segurança de 7 dias</strong>.
                                    Durante esse período, a exclusão poderá ser cancelada.
                                </span>
                            </li>
                        </ul>
                    </div>


                    @if($podeExcluir)

                        <div class="rounded-2xl border border-green-200 bg-[#EEF7F2] p-5">
                            <div class="flex gap-3">
                                <span class="text-lg">✓</span>

                                <div>
                                    <h2 class="font-semibold text-[#24563F]">
                                        Sua ONG pode solicitar a exclusão
                                    </h2>

                                    <p class="mt-1 font-poppins text-xs leading-5 text-[#51705F]">
                                        Nenhum evento ativo foi encontrado para esta organização.
                                    </p>
                                </div>
                            </div>
                        </div>


                        <div class="space-y-5 rounded-2xl border border-red-200 bg-red-50/40 p-5">

                            <label class="flex cursor-pointer items-start gap-3">
                                <input
                                    type="checkbox"
                                    id="confirmar-ciencia"
                                    class="mt-1 h-4 w-4 accent-red-600"
                                >

                                <span class="font-poppins text-sm leading-6 text-[#655F58]">
                                    Estou ciente de que a conta da ONG será programada para
                                    exclusão e que terei 7 dias para cancelar a solicitação.
                                </span>
                            </label>


                            <div>
                                <label
                                    for="confirmacao-texto"
                                    class="block font-poppins text-sm font-semibold text-[#5C3D36]"
                                >
                                    Confirmação de segurança
                                </label>

                                <p
                                    class="mt-1 font-poppins text-xs leading-5 text-[#7C6A64]"
                                >
                                    Para continuar, digite exatamente:
                                </p>

                                <div
                                    class="mt-3 rounded-xl border border-red-200 bg-white px-4 py-3 font-poppins text-sm font-semibold text-red-700"
                                >
                                    Estou ciente e desejo excluir minha conta
                                </div>

                                <input
                                    type="text"
                                    id="confirmacao-texto"
                                    autocomplete="off"
                                    spellcheck="false"
                                    placeholder="Digite a frase de confirmação"
                                    class="mt-3 w-full rounded-xl border border-[#D8D2C9] bg-white px-4 py-3 font-poppins text-sm text-[#292820] outline-none transition focus:border-red-400 focus:ring-2 focus:ring-red-100"
                                >

                                <p
                                    id="erro-confirmacao-texto"
                                    class="mt-2 hidden font-poppins text-xs font-medium text-red-600"
                                >
                                    A frase digitada não corresponde à confirmação exigida.
                                </p>
                            </div>

                        </div>


                        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                            <a
                                href="{{ route('ong.perfil.editar') }}"
                                class="inline-flex items-center justify-center rounded-full border border-[#CFCAC0] bg-white px-6 py-3 font-poppins text-sm text-[#5F5C55] transition hover:bg-neutral-50"
                            >
                                Cancelar
                            </a>

                            <button
                                type="button"
                                id="solicitar-exclusao"
                                disabled
                                class="inline-flex cursor-not-allowed items-center justify-center rounded-full bg-red-600 px-6 py-3 font-poppins text-sm font-semibold text-white opacity-50 transition enabled:cursor-pointer enabled:opacity-100 enabled:hover:bg-red-700"
                            >
                                Solicitar exclusão da ONG
                            </button>
                        </div>

                    @else

                        <div class="rounded-2xl border border-[#E7C784] bg-[#FFF8E8] p-5">
                            <div class="flex gap-3">
                                <span class="text-lg">⛔</span>

                                <div>
                                    <h2 class="font-semibold text-[#7B5A1D]">
                                        Exclusão indisponível
                                    </h2>

                                    <p class="mt-1 font-poppins text-sm leading-6 text-[#816B40]">
                                        Sua ONG possui
                                        <strong>{{ $eventosAtivos }} {{ $eventosAtivos === 1 ? 'evento ativo' : 'eventos ativos' }}</strong>.
                                        Você precisa encerrar ou cancelar
                                        {{ $eventosAtivos === 1 ? 'esse evento' : 'esses eventos' }}
                                        antes de solicitar a exclusão.
                                    </p>

                                    <a
                                        href="{{ route('ong.eventos.index') }}"
                                        class="mt-4 inline-flex items-center justify-center rounded-full bg-[#174B36] px-5 py-2.5 font-poppins text-sm font-semibold text-white transition hover:bg-[#123B2B]"
                                    >
                                        Ver meus eventos →
                                    </a>
                                </div>
                            </div>
                        </div>

                    @endif

                </div>
            </section>

        </div>
    </main>


    {{-- CONFIRMAÇÃO FINAL --}}
    <div
        id="modal-confirmar-exclusao"
        class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/45 px-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="titulo-modal-exclusao"
    >
        <div class="w-full max-w-md rounded-3xl border border-red-100 bg-white p-6 shadow-xl sm:p-8">

            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-2xl">
                ⚠️
            </div>

            <h2
                id="titulo-modal-exclusao"
                class="mt-5 text-center font-fraunces text-2xl font-bold text-[#292820]"
            >
                Confirmar solicitação?
            </h2>

            <p class="mt-3 text-center font-poppins text-sm leading-6 text-neutral-500">
                A ONG entrará em processo de exclusão por 7 dias.
                Você poderá reverter a solicitação durante esse período.
            </p>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-center">
                <button
                    type="button"
                    id="fechar-modal-exclusao"
                    class="rounded-full border border-[#CFCAC0] px-5 py-3 font-poppins text-sm"
                >
                    Voltar
                </button>

                <button
                    type="button"
                    id="confirmar-exclusao-final"
                    class="rounded-full bg-red-600 px-5 py-3 font-poppins text-sm font-semibold text-white transition hover:bg-red-700"
                >
                    Sim, solicitar exclusão
                </button>
            </div>

        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const checkbox =
                document.getElementById('confirmar-ciencia');

            const solicitar =
                document.getElementById('solicitar-exclusao');

            const campoConfirmacao =
                document.getElementById('confirmacao-texto');

            const erroConfirmacao =
                document.getElementById('erro-confirmacao-texto');

            const fraseConfirmacao =
                'Estou ciente e desejo excluir minha conta';

            const modal =
                document.getElementById('modal-confirmar-exclusao');

            const fechar =
                document.getElementById('fechar-modal-exclusao');

            const confirmar =
                document.getElementById('confirmar-exclusao-final');


            function atualizarEstadoBotao() {
                const checkboxMarcado =
                    checkbox?.checked === true;

                const fraseCorreta =
                    campoConfirmacao?.value === fraseConfirmacao;

                solicitar.disabled =
                    !(checkboxMarcado && fraseCorreta);

                if (fraseCorreta) {
                    erroConfirmacao?.classList.add('hidden');
                }
            }


            checkbox?.addEventListener(
                'change',
                atualizarEstadoBotao
            );


            campoConfirmacao?.addEventListener(
                'input',
                atualizarEstadoBotao
            );


            campoConfirmacao?.addEventListener(
                'blur',
                function () {
                    if (
                        campoConfirmacao.value &&
                        campoConfirmacao.value !== fraseConfirmacao
                    ) {
                        erroConfirmacao?.classList.remove('hidden');
                    } else {
                        erroConfirmacao?.classList.add('hidden');
                    }
                }
            );


            solicitar?.addEventListener('click', function () {

                if (
                    !checkbox?.checked ||
                    campoConfirmacao?.value !== fraseConfirmacao
                ) {
                    erroConfirmacao?.classList.remove('hidden');
                    return;
                }
                modal?.classList.remove('hidden');
                modal?.classList.add('flex');

                document.body.classList.add('overflow-hidden');
            });


            function fecharModal() {
                modal?.classList.add('hidden');
                modal?.classList.remove('flex');

                document.body.classList.remove('overflow-hidden');
            }


            fechar?.addEventListener('click', fecharModal);


            modal?.addEventListener('click', function (event) {
                if (event.target === modal) {
                    fecharModal();
                }
            });


            document.addEventListener('keydown', function (event) {
                if (
                    event.key === 'Escape' &&
                    modal &&
                    !modal.classList.contains('hidden')
                ) {
                    fecharModal();
                }
            });


            /*
             * MOCK DA PRÉ-BANCA:
             * depois deve chamar o endpoint real de exclusão da ONG.
             */
            confirmar?.addEventListener('click', function () {
                window.location.href =
                    @json(route('ong.perfil.exclusao-pendente'));
            });
        });
    </script>

</body>
</html>
