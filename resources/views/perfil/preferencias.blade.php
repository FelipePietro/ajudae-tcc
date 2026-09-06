<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Preferências do Perfil | Ajudaê</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>

<body class="min-h-screen bg-[#f7f5ef] font-outfit text-[#17392a]">

    {{-- NAVBAR --}}
    @include('/components/navbar-feed')


    <main class="px-4 py-6 sm:px-6 md:px-8 md:py-10">

        <div class="mx-auto w-full max-w-4xl">

            {{-- CABEÇALHO --}}
            <div class="mb-6">

                <a
                    href="/perfil"
                    class="mb-4 inline-flex items-center gap-2 font-poppins text-sm text-neutral-500 transition hover:text-[#17392a]"
                >
                    ← Voltar ao perfil
                </a>

                <h1 class="text-2xl font-bold text-[#17392a] sm:text-3xl">
                    Preferências do perfil
                </h1>

                <p class="mt-2 max-w-2xl font-poppins text-sm leading-6 text-neutral-500">
                    Personalize suas habilidades, recursos disponíveis e causas de interesse.
                    Essas informações ajudam os organizadores a entender melhor seu perfil.
                </p>

            </div>


            {{-- =========================================================
                 HABILIDADES
            ========================================================== --}}
            <section
                class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-5 md:p-6"
            >

                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">

                    <div class="min-w-0">

                        <h2 class="text-lg font-semibold text-[#17392a]">
                            Habilidades
                        </h2>

                        <p class="mt-1 font-poppins text-xs leading-5 text-neutral-500">
                            Informe suas principais habilidades e o nível de domínio de cada uma.
                        </p>

                    </div>

                </div>


                {{-- Habilidades atuais --}}
                <div class="mt-6 space-y-3">

                    @forelse ($habilidades_pessoa['data'] as $habilidade)

                        <div
                            class="flex flex-col gap-4 rounded-xl border border-neutral-200 bg-neutral-50 p-4
                                   sm:flex-row sm:items-center sm:justify-between"
                        >

                            <div class="min-w-0">

                                <h3 class="break-words text-sm font-semibold text-neutral-800">
                                    {{ $habilidade['nome_habilidade'] }}
                                </h3>

                                <p class="mt-1 break-words font-poppins text-xs leading-5 text-neutral-500">
                                    {{ $habilidade['descricao_habilidade'] }}
                                </p>


                                {{-- Nível --}}
                                <div class="mt-3 flex flex-wrap items-center gap-1">

                                    @for ($i = 1; $i <= 5; $i++)

                                        <span
                                            class="text-lg {{ $i <= $habilidade['pivot']['nivel_habilidade']
                                                ? 'text-[#e8a40c]'
                                                : 'text-neutral-300' }}"
                                        >
                                            ★
                                        </span>

                                    @endfor

                                    <span class="ml-1 font-poppins text-xs text-neutral-500 sm:ml-2">
                                        Nível {{ $habilidade['pivot']['nivel_habilidade'] }}/5
                                    </span>

                                </div>

                            </div>


                            <div class="w-full shrink-0 sm:w-auto">

                                <x-botao-primario
                                    href="#"
                                    texto="Remover"
                                />

                            </div>

                        </div>

                    @empty

                        <div
                            class="rounded-xl border border-dashed border-neutral-300 p-5 text-center sm:p-6"
                        >
                            <p class="font-poppins text-sm text-neutral-400">
                                Nenhuma habilidade adicionada.
                            </p>
                        </div>

                    @endforelse

                </div>


                {{-- Adicionar habilidade --}}
                <div class="mt-6 border-t border-neutral-200 pt-6">

                    <h3 class="text-sm font-semibold text-[#17392a]">
                        Adicionar habilidade
                    </h3>

                    <div
                        class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-[minmax(0,1fr)_180px_auto] md:items-end"
                    >

                        <div class="min-w-0">

                            <label
                                for="habilidade"
                                class="mb-2 block font-poppins text-xs font-medium text-neutral-600"
                            >
                                Habilidade
                            </label>

                            <select
                                id="habilidade"
                                name="habilidade_id"
                                class="w-full min-w-0 rounded-lg border border-neutral-300 bg-white px-4 py-3
                                       font-poppins text-sm outline-none transition focus:border-[#17392a]"
                            >

                                <option value="">
                                    Selecione uma habilidade
                                </option>

                                @foreach ($habilidades_disponiveis as $habilidade)

                                    <option value="{{ $habilidade['habilidade_id'] }}">
                                        {{ $habilidade['nome_habilidade'] }}
                                    </option>

                                @endforeach

                            </select>

                        </div>


                        <div class="min-w-0">

                            <label
                                for="nivel_habilidade"
                                class="mb-2 block font-poppins text-xs font-medium text-neutral-600"
                            >
                                Nível
                            </label>

                            <select
                                id="nivel_habilidade"
                                name="nivel_habilidade"
                                class="w-full min-w-0 rounded-lg border border-neutral-300 bg-white px-4 py-3
                                       font-poppins text-sm outline-none transition focus:border-[#17392a]"
                            >

                                <option value="1">1 - Iniciante</option>
                                <option value="2">2 - Básico</option>
                                <option value="3">3 - Intermediário</option>
                                <option value="4">4 - Avançado</option>
                                <option value="5">5 - Especialista</option>

                            </select>

                        </div>


                        <div class="w-full md:w-auto">

                            <x-botao-primario
                                href="#"
                                texto="Adicionar"
                            />

                        </div>

                    </div>

                </div>

            </section>


            {{-- =========================================================
                 RECURSOS
            ========================================================== --}}
            <section
                class="mt-5 rounded-xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-5 md:p-6"
            >

                <div class="min-w-0">

                    <h2 class="text-lg font-semibold text-[#17392a]">
                        Recursos disponíveis
                    </h2>

                    <p class="mt-1 font-poppins text-xs leading-5 text-neutral-500">
                        Informe recursos que você pode disponibilizar durante trabalhos voluntários.
                    </p>

                </div>


                {{-- Recursos atuais --}}
                <div class="mt-6 space-y-3">

                    @forelse ($recursos_pessoa['data'] as $recurso)

                        <div
                            class="flex flex-col gap-4 rounded-xl border border-neutral-200 bg-neutral-50 p-4
                                   sm:flex-row sm:items-center sm:justify-between"
                        >

                            <div class="min-w-0 flex-1">

                                <h3 class="break-words text-sm font-semibold text-neutral-800">
                                    {{ $recurso['nome_recurso'] }}
                                </h3>

                                <p class="mt-1 break-words font-poppins text-xs leading-5 text-neutral-500">
                                    {{ $recurso['descricao_recurso'] }}
                                </p>

                                <div
                                    class="mt-3 break-words rounded-lg bg-white px-3 py-2 font-poppins
                                           text-xs leading-5 text-neutral-600"
                                >
                                    <strong>Detalhes:</strong>
                                    {{ $recurso['pivot']['detalhes_recurso'] }}
                                </div>

                            </div>


                            <div class="w-full shrink-0 sm:w-auto">

                                <x-botao-primario
                                    href="#"
                                    texto="Remover"
                                />

                            </div>

                        </div>

                    @empty

                        <div
                            class="rounded-xl border border-dashed border-neutral-300 p-5 text-center sm:p-6"
                        >
                            <p class="font-poppins text-sm text-neutral-400">
                                Nenhum recurso adicionado.
                            </p>
                        </div>

                    @endforelse

                </div>


                {{-- Adicionar recurso --}}
                <div class="mt-6 border-t border-neutral-200 pt-6">

                    <h3 class="text-sm font-semibold text-[#17392a]">
                        Adicionar recurso
                    </h3>


                    <div class="mt-4 grid grid-cols-1 gap-4">

                        <div class="min-w-0">

                            <label
                                for="recurso"
                                class="mb-2 block font-poppins text-xs font-medium text-neutral-600"
                            >
                                Recurso
                            </label>

                            <select
                                id="recurso"
                                name="recurso_id"
                                class="w-full min-w-0 rounded-lg border border-neutral-300 bg-white px-4 py-3
                                       font-poppins text-sm outline-none transition focus:border-[#17392a]"
                            >

                                <option value="">
                                    Selecione um recurso
                                </option>

                                @foreach ($recursos_disponiveis as $recurso)

                                    <option value="{{ $recurso['recurso_id'] }}">
                                        {{ $recurso['nome_recurso'] }}
                                    </option>

                                @endforeach

                            </select>

                        </div>


                        <div class="min-w-0">

                            <label
                                for="detalhes_recurso"
                                class="mb-2 block font-poppins text-xs font-medium text-neutral-600"
                            >
                                Detalhes
                            </label>

                            <textarea
                                id="detalhes_recurso"
                                name="detalhes_recurso"
                                maxlength="255"
                                rows="3"
                                placeholder="Ex.: Veículo disponível aos finais de semana."
                                class="w-full min-w-0 resize-none rounded-lg border border-neutral-300 bg-white px-4 py-3
                                       font-poppins text-sm outline-none transition placeholder:text-neutral-400
                                       focus:border-[#17392a]"
                            ></textarea>

                        </div>


                        <div class="w-full sm:w-auto">

                            <x-botao-primario
                                href="#"
                                texto="Adicionar recurso"
                            />

                        </div>

                    </div>

                </div>

            </section>


            {{-- =========================================================
                 CAUSAS
            ========================================================== --}}
            <section
                class="mt-5 rounded-xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-5 md:p-6"
            >

                <div class="min-w-0">

                    <h2 class="text-lg font-semibold text-[#17392a]">
                        Causas de interesse
                    </h2>

                    <p class="mt-1 font-poppins text-xs leading-5 text-neutral-500">
                        Selecione as causas sociais com as quais você mais se identifica.
                    </p>

                </div>


                {{-- Causas atuais --}}
                <div class="mt-6 flex flex-wrap gap-2 sm:gap-3">

                    @forelse ($causas_pessoa['data'] as $causa)

                        <div
                            class="flex max-w-full items-center gap-2 rounded-full border border-[#b9d7c7]
                                   bg-[#eef7f2] px-3 py-2 sm:gap-3 sm:px-4"
                        >

                            <span class="min-w-0 break-words text-sm font-medium text-[#17392a]">
                                {{ $causa['nome_causa'] }}
                            </span>

                            <button
                                type="button"
                                class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full
                                       text-xs text-neutral-500 transition hover:bg-red-100 hover:text-red-600"
                                title="Remover causa"
                            >
                                ×
                            </button>

                        </div>

                    @empty

                        <p class="font-poppins text-sm text-neutral-400">
                            Nenhuma causa adicionada.
                        </p>

                    @endforelse

                </div>


                {{-- Causas disponíveis --}}
                <div class="mt-6 border-t border-neutral-200 pt-6">

                    <h3 class="text-sm font-semibold text-[#17392a]">
                        Adicionar causa
                    </h3>

                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">

                        @foreach ($causas_disponiveis as $causa)

                            <label
                                class="flex min-w-0 cursor-pointer items-start gap-3 rounded-xl
                                       border border-neutral-200 p-4 transition
                                       hover:border-[#7aad91] hover:bg-[#f6faf8]"
                            >

                                <input
                                    type="checkbox"
                                    name="causa_id"
                                    value="{{ $causa['causa_id'] }}"
                                    class="mt-1 h-4 w-4 shrink-0 accent-[#17392a]"
                                >

                                <div class="min-w-0">

                                    <p class="break-words text-sm font-semibold text-neutral-800">
                                        {{ $causa['nome_causa'] }}
                                    </p>

                                    <p class="mt-1 break-words font-poppins text-xs leading-5 text-neutral-500">
                                        {{ $causa['descricao_causa'] }}
                                    </p>

                                </div>

                            </label>

                        @endforeach

                    </div>


                    <div class="mt-5 w-full sm:w-auto">

                        <x-botao-primario
                            href="#"
                            texto="Adicionar causas"
                        />

                    </div>

                </div>

            </section>


            {{-- AÇÕES FINAIS --}}
            <div
                class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"
            >

                <x-botao-secundario
                    href="/perfil"
                    texto="Voltar"
                />

                <x-botao-primario
                    href="/perfil"
                    texto="Concluir alterações"
                />

            </div>

        </div>

    </main>

</body>

</html>