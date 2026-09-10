<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Exclusão da ONG em andamento | Ajudae</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>

<body class="min-h-screen bg-[#F7F5F0] font-outfit text-[#26251F]">

    @php
        $nomeOng =
            $ong['nome_fantasia']
            ?? $ong['razao_social']
            ?? 'Sua ONG';

        $dataExclusao =
            $ong['deletar_em']
            ?? now()->addDays(7)->toISOString();
    @endphp

    <main class="flex min-h-screen items-center justify-center px-4 py-10">

        <section
            class="w-full max-w-[680px] overflow-hidden
                   rounded-3xl border border-[#DED9CE]
                   bg-white shadow-sm"
        >

            <div
                class="border-b border-[#E7E2D8]
                       bg-[#FFF8E8]
                       px-6 py-8 text-center
                       sm:px-10"
            >
                <div
                    class="mx-auto flex h-16 w-16
                           items-center justify-center
                           rounded-full bg-[#FBE8C2]
                           text-3xl"
                    aria-hidden="true"
                >
                    ⏳
                </div>

                <p
                    class="mt-5 font-poppins text-xs
                           font-semibold uppercase
                           tracking-[0.14em]
                           text-[#A07322]"
                >
                    Exclusão programada
                </p>

                <h1
                    class="mt-2 font-fraunces text-3xl
                           font-bold text-[#17392A]
                           sm:text-4xl"
                >
                    A conta da ONG está sendo excluída
                </h1>

                <p
                    class="mx-auto mt-4 max-w-xl
                           font-poppins text-sm
                           leading-6 text-[#6E695F]"
                >
                    A solicitação para excluir
                    <strong>{{ $nomeOng }}</strong>
                    foi registrada. A remoção definitiva acontecerá
                    ao final do período de segurança.
                </p>
            </div>


            <div class="space-y-6 p-6 sm:p-10">

                <div
                    class="rounded-2xl border border-[#E4DED3]
                           bg-[#FAF9F5] p-5"
                >
                    <div
                        class="flex flex-col gap-4
                               sm:flex-row sm:items-center
                               sm:justify-between"
                    >
                        <div>
                            <p
                                class="font-poppins text-xs
                                       text-neutral-500"
                            >
                                Exclusão definitiva prevista para
                            </p>

                            <p
                                id="data-exclusao"
                                data-exclusao="{{ $dataExclusao }}"
                                class="mt-1 text-lg font-semibold
                                       text-[#292820]"
                            >
                                {{ $dataExclusao }}
                            </p>
                        </div>

                        <span
                            class="w-fit rounded-full
                                   bg-[#FFF0CF]
                                   px-3 py-1.5
                                   font-poppins text-xs
                                   font-semibold text-[#9A6410]"
                        >
                            Período de segurança
                        </span>
                    </div>
                </div>


                <div
                    class="rounded-2xl border border-[#CFE2D7]
                           bg-[#EEF7F2] p-5"
                >
                    <h2 class="font-semibold text-[#24563F]">
                        Mudou de ideia?
                    </h2>

                    <p
                        class="mt-2 font-poppins text-sm
                               leading-6 text-[#527061]"
                    >
                        Enquanto o prazo de 7 dias não terminar,
                        você pode cancelar a exclusão. A conta da ONG
                        voltará ao estado normal e continuará disponível
                        na plataforma.
                    </p>
                </div>


                <div
                    class="rounded-2xl border border-[#E8E3D9]
                           bg-white p-5"
                >
                    <h2 class="font-semibold text-[#292820]">
                        Durante este período
                    </h2>

                    <ul
                        class="mt-3 space-y-2
                               font-poppins text-sm
                               leading-6 text-[#716C63]"
                    >
                        <li>• A exclusão ainda não é definitiva.</li>
                        <li>• A solicitação pode ser revertida antes do prazo final.</li>
                        <li>• Registros que precisem ser preservados por obrigação legal ou segurança poderão ser mantidos conforme aplicável.</li>
                    </ul>
                </div>


                <button
                    type="button"
                    id="reverter-exclusao"
                    class="flex w-full items-center
                           justify-center rounded-full
                           bg-[#174B36] px-6 py-3.5
                           font-poppins text-sm
                           font-semibold text-white
                           transition hover:bg-[#123B2B]"
                >
                    Reverter exclusão da ONG
                </button>


                <p
                    class="text-center font-poppins
                           text-xs leading-5
                           text-neutral-400"
                >
                    Se nenhuma ação for tomada, a exclusão seguirá
                    automaticamente ao final do período informado.
                </p>

            </div>

        </section>

    </main>


    {{-- MODAL DE REVERSÃO --}}
    <div
        id="modal-reverter"
        class="fixed inset-0 z-[100] hidden
               items-center justify-center
               bg-black/45 px-4"
    >
        <div
            class="w-full max-w-md rounded-3xl
                   bg-white p-6 text-center
                   shadow-xl sm:p-8"
        >
            <div
                class="mx-auto flex h-14 w-14
                       items-center justify-center
                       rounded-full bg-[#DCEFE5]
                       text-2xl"
            >
                ↩
            </div>

            <h2
                class="mt-5 font-fraunces
                       text-2xl font-bold
                       text-[#17392A]"
            >
                Reverter exclusão?
            </h2>

            <p
                class="mt-3 font-poppins text-sm
                       leading-6 text-neutral-500"
            >
                A solicitação de exclusão será cancelada
                e a conta da ONG continuará ativa.
            </p>

            <div
                class="mt-6 flex flex-col-reverse
                       gap-3 sm:flex-row
                       sm:justify-center"
            >
                <button
                    type="button"
                    id="cancelar-reversao"
                    class="rounded-full border
                           border-[#CFCAC0]
                           px-5 py-3
                           font-poppins text-sm"
                >
                    Manter exclusão
                </button>

                <button
                    type="button"
                    id="confirmar-reversao"
                    class="rounded-full bg-[#174B36]
                           px-5 py-3
                           font-poppins text-sm
                           font-semibold text-white"
                >
                    Sim, manter minha ONG
                </button>
            </div>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const data =
                document.getElementById('data-exclusao');

            const botao =
                document.getElementById('reverter-exclusao');

            const modal =
                document.getElementById('modal-reverter');

            const cancelar =
                document.getElementById('cancelar-reversao');

            const confirmar =
                document.getElementById('confirmar-reversao');


            if (data?.dataset.exclusao) {
                const valor =
                    new Date(data.dataset.exclusao);

                if (!Number.isNaN(valor.getTime())) {
                    data.textContent =
                        new Intl.DateTimeFormat(
                            'pt-BR',
                            {
                                day: '2-digit',
                                month: 'long',
                                year: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            }
                        ).format(valor);
                }
            }


            botao?.addEventListener('click', function () {
                modal?.classList.remove('hidden');
                modal?.classList.add('flex');
            });


            cancelar?.addEventListener('click', function () {
                modal?.classList.add('hidden');
                modal?.classList.remove('flex');
            });


            /*
             * MOCK DA PRÉ-BANCA:
             * depois deve chamar cancelarExclusao da ONG.
             */
            confirmar?.addEventListener('click', function () {
                window.location.href =
                    @json(route('ong.perfil'));
            });
        });
    </script>

</body>
</html>
