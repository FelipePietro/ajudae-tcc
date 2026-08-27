<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Editar evento | Ajudaê</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>


<body
    class="min-h-screen overflow-x-hidden
           bg-[#f7f5ef] font-outfit text-[#26251f]"
>

    <x-navbar-ong ativo="eventos" />


    <main
        class="min-h-screen w-full
               lg:ml-[230px]
               lg:w-[calc(100%-230px)]"
    >

        {{-- BREADCRUMB --}}
        <header
            class="border-b border-[#dedbd1]
                   bg-white px-4 py-4
                   sm:px-6
                   lg:px-8 lg:py-5"
        >

            <div
                class="flex flex-wrap items-center
                       gap-x-2 gap-y-1
                       font-poppins text-xs text-neutral-500
                       sm:text-sm"
            >

                <a
                    href="{{ route('ong.dashboard') }}"
                    class="transition hover:text-[#174b36]"
                >
                    Dashboard
                </a>

                <span>›</span>

                <a
                    href="{{ route('ong.eventos.index') }}"
                    class="transition hover:text-[#174b36]"
                >
                    Meus eventos
                </a>

                <span>›</span>

                <span class="font-medium text-neutral-800">
                    Editar evento
                </span>

            </div>

        </header>


        <form
            action="{{ route('ong.eventos.atualizar', $evento->evento_id) }}"
            method="POST"
            enctype="multipart/form-data"
        >

            @csrf
            @method('PUT')


            <div
                class="mx-auto w-full max-w-[1500px]
                       px-4 py-6
                       sm:px-6 sm:py-8
                       lg:px-8"
            >

                {{-- TÍTULO --}}
                <div
                    class="mb-6 flex flex-col gap-4
                           sm:mb-8
                           md:flex-row
                           md:items-start
                           md:justify-between"
                >

                    <div>

                        <h1
                            class="text-2xl font-semibold leading-tight
                                   sm:text-3xl"
                        >
                            Editar evento
                        </h1>

                        <p
                            class="mt-2 max-w-2xl
                                   font-poppins text-sm leading-6
                                   text-neutral-500"
                        >
                            Atualize as informações do evento.
                            As alterações poderão passar por uma nova
                            revisão antes de serem publicadas.
                        </p>

                    </div>


                    <span
                        class="inline-flex w-fit
                               rounded-full
                               bg-[#edf7f2]
                               px-4 py-2
                               font-poppins text-xs
                               font-semibold
                               text-[#174b36]"
                    >
                        {{ ucfirst($evento->status_evento ?? 'publicado') }}
                    </span>

                </div>


                <div
                    class="grid min-w-0 grid-cols-1
                           gap-5
                           xl:grid-cols-[minmax(0,1fr)_350px]
                           xl:gap-6"
                >

                    {{-- COLUNA PRINCIPAL --}}
                    <div class="min-w-0 space-y-5 sm:space-y-6">


                        {{-- INFORMAÇÕES BÁSICAS --}}
                        <section
                            class="overflow-hidden rounded-2xl
                                   border border-[#dedbd1]
                                   bg-white shadow-sm"
                        >

                            <div
                                class="flex items-center gap-3
                                       border-b border-[#dedbd1]
                                       px-4 py-4
                                       sm:px-6 sm:py-5"
                            >

                                <div
                                    class="flex h-10 w-10
                                           items-center justify-center
                                           rounded-xl
                                           bg-[#dcefe5]"
                                >
                                    📝
                                </div>

                                <div>

                                    <h2 class="font-semibold">
                                        Informações básicas
                                    </h2>

                                    <p
                                        class="font-poppins
                                               text-xs text-neutral-500"
                                    >
                                        Título, categoria, vagas e descrição
                                    </p>

                                </div>

                            </div>


                            <div class="space-y-5 p-4 sm:p-6">

                                {{-- Título --}}
                                <div>

                                    <label
                                        for="nm_evento"
                                        class="mb-2 block
                                               font-poppins text-sm
                                               font-medium"
                                    >
                                        Título do evento *
                                    </label>

                                    <input
                                        type="text"
                                        id="nm_evento"
                                        name="nm_evento"
                                        required
                                        value="{{ old('nm_evento', $evento->nm_evento) }}"
                                        class="w-full rounded-xl border
                                               border-[#d8d4ca]
                                               px-4 py-3
                                               font-poppins text-sm
                                               outline-none
                                               focus:border-[#174b36]"
                                    >

                                </div>


                                <div
                                    class="grid grid-cols-1 gap-4
                                           sm:grid-cols-2
                                           lg:grid-cols-3"
                                >

                                    {{-- Categoria --}}
                                    <div>

                                        <label
                                            for="cat_evento_id"
                                            class="mb-2 block
                                                   font-poppins text-sm
                                                   font-medium"
                                        >
                                            Categoria *
                                        </label>

                                        <select
                                            id="cat_evento_id"
                                            name="cat_evento_id"
                                            required
                                            class="w-full rounded-xl
                                                   border border-[#d8d4ca]
                                                   bg-white px-4 py-3
                                                   font-poppins text-sm"
                                        >

                                            @foreach($categorias ?? [] as $categoria)

                                                <option
                                                    value="{{ $categoria->cat_evento_id }}"
                                                    @selected(
                                                        old(
                                                            'cat_evento_id',
                                                            $evento->cat_evento_id ?? null
                                                        ) == $categoria->cat_evento_id
                                                    )
                                                >
                                                    {{ $categoria->nome_categoria }}
                                                </option>

                                            @endforeach

                                        </select>

                                    </div>


                                    {{-- Vagas --}}
                                    <div>

                                        <label
                                            for="vagas_evento"
                                            class="mb-2 block
                                                   font-poppins text-sm
                                                   font-medium"
                                        >
                                            Número de vagas *
                                        </label>

                                        <input
                                            type="number"
                                            id="vagas_evento"
                                            name="vagas_evento"
                                            min="1"
                                            required
                                            value="{{ old('vagas_evento', $evento->vagas_evento) }}"
                                            class="w-full rounded-xl
                                                   border border-[#d8d4ca]
                                                   px-4 py-3
                                                   font-poppins text-sm"
                                        >

                                    </div>


                                    {{-- Modalidade --}}
                                    <div class="sm:col-span-2 lg:col-span-1">

                                        <label
                                            for="modalidade_evento"
                                            class="mb-2 block
                                                   font-poppins text-sm
                                                   font-medium"
                                        >
                                            Modalidade *
                                        </label>

                                        <select
                                            id="modalidade_evento"
                                            name="modalidade_evento"
                                            required
                                            class="w-full rounded-xl
                                                   border border-[#d8d4ca]
                                                   bg-white px-4 py-3
                                                   font-poppins text-sm"
                                        >

                                            <option
                                                value="presencial"
                                                @selected(
                                                    old(
                                                        'modalidade_evento',
                                                        $evento->modalidade_evento
                                                    ) === 'presencial'
                                                )
                                            >
                                                Presencial
                                            </option>

                                            <option
                                                value="online"
                                                @selected(
                                                    old(
                                                        'modalidade_evento',
                                                        $evento->modalidade_evento
                                                    ) === 'online'
                                                )
                                            >
                                                Online
                                            </option>

                                            <option
                                                value="hibrido"
                                                @selected(
                                                    old(
                                                        'modalidade_evento',
                                                        $evento->modalidade_evento
                                                    ) === 'hibrido'
                                                )
                                            >
                                                Híbrido
                                            </option>

                                        </select>

                                    </div>

                                </div>


                                {{-- Descrição --}}
                                <div>

                                    <label
                                        for="descricao_evento"
                                        class="mb-2 block
                                               font-poppins text-sm
                                               font-medium"
                                    >
                                        Descrição *
                                    </label>

                                    <textarea
                                        id="descricao_evento"
                                        name="descricao_evento"
                                        rows="5"
                                        required
                                        class="w-full resize-none
                                               rounded-xl border
                                               border-[#d8d4ca]
                                               px-4 py-3
                                               font-poppins text-sm
                                               leading-6"
                                    >{{ old('descricao_evento', $evento->descricao_evento) }}</textarea>

                                </div>

                            </div>

                        </section>


                        {{-- DATA E LOCAL --}}
                        <section
                            class="overflow-hidden rounded-2xl
                                   border border-[#dedbd1]
                                   bg-white shadow-sm"
                        >

                            <div
                                class="flex items-center gap-3
                                       border-b border-[#dedbd1]
                                       px-4 py-4
                                       sm:px-6 sm:py-5"
                            >

                                <div
                                    class="flex h-10 w-10
                                           items-center justify-center
                                           rounded-xl
                                           bg-[#fff0cf]"
                                >
                                    📍
                                </div>

                                <div>

                                    <h2 class="font-semibold">
                                        Data, horário e local
                                    </h2>

                                    <p
                                        class="font-poppins
                                               text-xs text-neutral-500"
                                    >
                                        Atualize quando e onde
                                        acontecerá o evento
                                    </p>

                                </div>

                            </div>


                            <div class="space-y-5 p-4 sm:p-6">

                                {{-- Datas --}}
                                <div
                                    class="grid grid-cols-1 gap-4
                                           md:grid-cols-2"
                                >

                                    <div>

                                        <label
                                            for="data_inicio"
                                            class="mb-2 block
                                                   font-poppins text-sm
                                                   font-medium"
                                        >
                                            Início *
                                        </label>

                                        <input
                                            type="datetime-local"
                                            id="data_inicio"
                                            name="data_inicio"
                                            required
                                            value="{{ old(
                                                'data_inicio',
                                                $agenda?->data_inicio?->format('Y-m-d\TH:i')
                                            ) }}"
                                            class="w-full rounded-xl
                                                   border border-[#d8d4ca]
                                                   px-4 py-3
                                                   font-poppins text-sm"
                                        >

                                    </div>


                                    <div>

                                        <label
                                            for="data_fim"
                                            class="mb-2 block
                                                   font-poppins text-sm
                                                   font-medium"
                                        >
                                            Término *
                                        </label>

                                        <input
                                            type="datetime-local"
                                            id="data_fim"
                                            name="data_fim"
                                            required
                                            value="{{ old(
                                                'data_fim',
                                                $agenda?->data_fim?->format('Y-m-d\TH:i')
                                            ) }}"
                                            class="w-full rounded-xl
                                                   border border-[#d8d4ca]
                                                   px-4 py-3
                                                   font-poppins text-sm"
                                        >

                                    </div>

                                </div>


                                {{-- Endereço --}}
                                <div
                                    class="grid grid-cols-1 gap-4
                                           md:grid-cols-[180px_minmax(0,1fr)]"
                                >

                                    <div>

                                        <label
                                            for="cep_evento"
                                            class="mb-2 block
                                                   font-poppins text-sm
                                                   font-medium"
                                        >
                                            CEP *
                                        </label>

                                        <input
                                            type="text"
                                            id="cep_evento"
                                            name="cep_evento"
                                            maxlength="8"
                                            required
                                            value="{{ old('cep_evento', $evento->cep_evento) }}"
                                            class="w-full rounded-xl
                                                   border border-[#d8d4ca]
                                                   px-4 py-3
                                                   font-poppins text-sm"
                                        >

                                    </div>


                                    <div>

                                        <label
                                            for="logradouro_evento"
                                            class="mb-2 block
                                                   font-poppins text-sm
                                                   font-medium"
                                        >
                                            Logradouro *
                                        </label>

                                        <input
                                            type="text"
                                            id="logradouro_evento"
                                            name="logradouro_evento"
                                            required
                                            value="{{ old(
                                                'logradouro_evento',
                                                $evento->logradouro_evento
                                            ) }}"
                                            class="w-full rounded-xl
                                                   border border-[#d8d4ca]
                                                   px-4 py-3
                                                   font-poppins text-sm"
                                        >

                                    </div>

                                </div>


                                <div>

                                    <label
                                        for="compl_evento"
                                        class="mb-2 block
                                               font-poppins text-sm
                                               font-medium"
                                    >
                                        Número / complemento
                                    </label>

                                    <input
                                        type="text"
                                        id="compl_evento"
                                        name="compl_evento"
                                        value="{{ old(
                                            'compl_evento',
                                            $evento->compl_evento
                                        ) }}"
                                        class="w-full rounded-xl border
                                               border-[#d8d4ca]
                                               px-4 py-3
                                               font-poppins text-sm"
                                    >

                                </div>

                            </div>

                        </section>


                        {{-- HABILIDADES E IMAGEM --}}
                        <section
                            class="overflow-hidden rounded-2xl
                                   border border-[#dedbd1]
                                   bg-white shadow-sm"
                        >

                            <div
                                class="border-b border-[#dedbd1]
                                       px-4 py-4
                                       sm:px-6 sm:py-5"
                            >

                                <h2 class="font-semibold">
                                    🎯 Perfil desejado e imagem
                                </h2>

                            </div>


                            <div class="space-y-6 p-4 sm:p-6">

                                {{-- Habilidades --}}
                                <div>

                                    <label
                                        class="font-poppins
                                               text-sm font-medium"
                                    >
                                        Habilidades desejadas
                                    </label>

                                    <div class="mt-3 flex flex-wrap gap-2">

                                        @foreach($habilidades ?? [] as $habilidade)

                                            @php
                                                $selecionada =
                                                    collect(
                                                        $evento->habilidades ?? []
                                                    )
                                                    ->pluck('habilidade_id')
                                                    ->contains(
                                                        $habilidade->habilidade_id
                                                    );
                                            @endphp

                                            <label class="cursor-pointer">

                                                <input
                                                    type="checkbox"
                                                    name="habilidades[]"
                                                    value="{{ $habilidade->habilidade_id }}"
                                                    class="peer hidden"
                                                    @checked($selecionada)
                                                >

                                                <span
                                                    class="block rounded-full
                                                           border
                                                           border-[#d4d0c6]
                                                           px-4 py-2
                                                           font-poppins
                                                           text-xs
                                                           peer-checked:border-[#afd5bf]
                                                           peer-checked:bg-[#dcefe5]
                                                           peer-checked:text-[#174b36]"
                                                >
                                                    {{ $habilidade->nome_habilidade }}
                                                </span>

                                            </label>

                                        @endforeach

                                    </div>

                                </div>


                                {{-- Imagem atual --}}
                                <div>

                                    <label
                                        class="font-poppins
                                               text-sm font-medium"
                                    >
                                        Imagem do evento
                                    </label>


                                    @if(!empty($evento->imagem_evento_link))

                                        <div
                                            class="mt-3 overflow-hidden
                                                   rounded-xl"
                                        >
                                            <img
                                                src="{{ $evento->imagem_evento_link }}"
                                                alt="{{ $evento->nm_evento }}"
                                                class="h-48 w-full
                                                       object-cover"
                                            >
                                        </div>

                                    @endif


                                    <label
                                        class="mt-3 flex min-h-32
                                               cursor-pointer
                                               flex-col items-center
                                               justify-center
                                               rounded-xl border
                                               border-dashed
                                               border-[#c9c4b8]
                                               p-4 text-center
                                               hover:bg-neutral-50"
                                    >

                                        <span class="text-2xl">
                                            🖼️
                                        </span>

                                        <span
                                            class="mt-2
                                                   font-poppins text-sm"
                                        >
                                            Alterar imagem
                                        </span>

                                        <input
                                            type="file"
                                            name="imagem_evento"
                                            accept="image/png,image/jpeg"
                                            class="hidden"
                                        >

                                    </label>

                                </div>

                            </div>

                        </section>

                    </div>


                    {{-- SIDEBAR --}}
                    <aside class="space-y-5">

                        <div
                            class="rounded-2xl border
                                   border-[#dedbd1]
                                   bg-white p-5 shadow-sm"
                        >

                            <p
                                class="font-poppins
                                       text-xs text-neutral-500"
                            >
                                Evento atual
                            </p>

                            <h3
                                class="mt-2 font-semibold"
                            >
                                {{ $evento->nm_evento }}
                            </h3>


                            <div
                                class="mt-5 space-y-3
                                       font-poppins text-sm"
                            >

                                <div
                                    class="flex justify-between
                                           gap-3"
                                >
                                    <span class="text-neutral-500">
                                        Status
                                    </span>

                                    <strong>
                                        {{ ucfirst(
                                            $evento->status_evento
                                            ?? 'publicado'
                                        ) }}
                                    </strong>
                                </div>


                                <div
                                    class="flex justify-between
                                           gap-3"
                                >
                                    <span class="text-neutral-500">
                                        Vagas
                                    </span>

                                    <strong>
                                        {{ $evento->vagas_evento }}
                                    </strong>
                                </div>


                                <div
                                    class="flex justify-between
                                           gap-3"
                                >
                                    <span class="text-neutral-500">
                                        Modalidade
                                    </span>

                                    <strong>
                                        {{ ucfirst(
                                            $evento->modalidade_evento
                                        ) }}
                                    </strong>
                                </div>

                            </div>

                        </div>


                        <div
                            class="rounded-2xl border
                                   border-[#edcf9f]
                                   bg-[#fff8eb]
                                   p-5
                                   font-poppins
                                   text-sm leading-6
                                   text-[#81602d]"
                        >
                            Alterações importantes podem fazer
                            o evento voltar para análise antes
                            de aparecer novamente no feed.
                        </div>

                    </aside>

                </div>

            </div>


            {{-- BOTÕES --}}
            <div
                class="sticky bottom-0 z-20
                       border-t border-[#dedbd1]
                       bg-[#f7f5ef]/95
                       px-4 py-4
                       backdrop-blur
                       sm:px-6
                       lg:px-8"
            >

                <div
                    class="mx-auto flex
                           max-w-[1500px]
                           flex-col gap-3
                           sm:flex-row
                           sm:justify-end"
                >

                    <a
                        href="{{ route('ong.eventos.index') }}"
                        class="flex items-center justify-center
                               rounded-full border
                               border-[#c9c4b8]
                               bg-white
                               px-6 py-3
                               font-poppins text-sm"
                    >
                        Cancelar
                    </a>


                    <button
                        type="submit"
                        class="rounded-full
                               bg-[#174b36]
                               px-7 py-3
                               font-poppins text-sm
                               font-semibold
                               text-white
                               transition
                               hover:bg-[#123b2b]"
                    >
                        Salvar alterações
                    </button>

                </div>

            </div>

        </form>

    </main>

</body>
</html>