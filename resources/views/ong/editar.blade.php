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


                        {{-- PRÉVIA DA EDIÇÃO --}}
                        <div
                            class="overflow-hidden rounded-2xl
                                   border border-[#dedbd1]
                                   bg-white shadow-sm"
                        >

                            <div
                                id="preview-edicao-imagem-wrapper"
                                class="relative flex h-36 items-center
                                       justify-center overflow-hidden
                                       bg-[#dcefe5]
                                       sm:h-44"
                            >
                                <img
                                    id="preview-edicao-imagem"
                                    src="{{ $evento->imagem_evento_link ?? '' }}"
                                    alt="Prévia da edição do evento"
                                    class="{{ !empty($evento->imagem_evento_link) ? '' : 'hidden' }} h-full w-full object-cover"
                                >

                                <span
                                    id="preview-edicao-placeholder"
                                    class="{{ !empty($evento->imagem_evento_link) ? 'hidden' : '' }} text-4xl sm:text-5xl"
                                    aria-hidden="true"
                                >
                                    🌿
                                </span>
                            </div>


                            <div class="p-4 sm:p-5">

                                <p
                                    class="font-poppins
                                           text-xs text-neutral-500"
                                >
                                    Prévia da edição
                                </p>

                                <h3
                                    id="preview-edicao-titulo"
                                    class="mt-1 text-lg font-semibold
                                           leading-snug text-[#17392a]"
                                >
                                    {{ old('nm_evento', $evento->nm_evento) }}
                                </h3>


                                <div
                                    class="mt-3 flex flex-wrap gap-2
                                           font-poppins text-[11px]"
                                >
                                    <span
                                        id="preview-edicao-categoria"
                                        class="rounded-full bg-[#eef7f2]
                                               px-2.5 py-1 text-[#2d6a4f]"
                                    >
                                        Categoria
                                    </span>

                                    <span
                                        id="preview-edicao-modalidade"
                                        class="rounded-full bg-[#fff5dd]
                                               px-2.5 py-1 text-[#9a6410]"
                                    >
                                        {{ ucfirst(old('modalidade_evento', $evento->modalidade_evento ?? 'presencial')) }}
                                    </span>

                                    <span
                                        id="preview-edicao-vagas"
                                        class="rounded-full bg-neutral-100
                                               px-2.5 py-1 text-neutral-600"
                                    >
                                        {{ old('vagas_evento', $evento->vagas_evento) }} vagas
                                    </span>
                                </div>


                                <p
                                    id="preview-edicao-descricao"
                                    class="mt-3
                                           font-poppins text-xs
                                           leading-5
                                           text-neutral-500"
                                >
                                    {{ old('descricao_evento', $evento->descricao_evento) }}
                                </p>


                                <div
                                    class="mt-4 space-y-2 border-t
                                           border-[#ece9e1] pt-4
                                           font-poppins text-xs
                                           text-neutral-600"
                                >

                                    <p
                                        id="preview-edicao-data"
                                        class="flex gap-2"
                                    >
                                        <span aria-hidden="true">📅</span>
                                        <span>Data e horário do evento</span>
                                    </p>

                                    <p
                                        id="preview-edicao-local"
                                        class="flex gap-2"
                                    >
                                        <span aria-hidden="true">📍</span>
                                        <span>Local do evento</span>
                                    </p>

                                </div>


                                <div
                                    id="preview-edicao-habilidades-wrapper"
                                    class="mt-4"
                                >
                                    <p
                                        class="mb-2 font-poppins
                                               text-[11px] font-medium
                                               text-neutral-500"
                                    >
                                        Habilidades desejadas
                                    </p>

                                    <div
                                        id="preview-edicao-habilidades"
                                        class="flex flex-wrap gap-1.5"
                                    ></div>
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


    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const formulario =
                document.querySelector(
                    'form[action="{{ route('ong.eventos.atualizar', $evento->evento_id) }}"]'
                );

            const campoTitulo =
                document.getElementById('nm_evento');

            const campoCategoria =
                document.getElementById('cat_evento_id');

            const campoVagas =
                document.getElementById('vagas_evento');

            const campoModalidade =
                document.getElementById('modalidade_evento');

            const campoDescricao =
                document.getElementById('descricao_evento');

            const campoDataInicio =
                document.getElementById('data_inicio');

            const campoDataFim =
                document.getElementById('data_fim');

            const campoCep =
                document.getElementById('cep_evento');

            const campoLogradouro =
                document.getElementById('logradouro_evento');

            const campoComplemento =
                document.getElementById('compl_evento');

            const campoImagem =
                document.querySelector('input[name="imagem_evento"]');

            const camposHabilidades =
                document.querySelectorAll(
                    'input[name="habilidades[]"]'
                );


            const previewTitulo =
                document.getElementById(
                    'preview-edicao-titulo'
                );

            const previewCategoria =
                document.getElementById(
                    'preview-edicao-categoria'
                );

            const previewVagas =
                document.getElementById(
                    'preview-edicao-vagas'
                );

            const previewModalidade =
                document.getElementById(
                    'preview-edicao-modalidade'
                );

            const previewDescricao =
                document.getElementById(
                    'preview-edicao-descricao'
                );

            const previewData =
                document.querySelector(
                    '#preview-edicao-data span:last-child'
                );

            const previewLocal =
                document.querySelector(
                    '#preview-edicao-local span:last-child'
                );

            const previewImagem =
                document.getElementById(
                    'preview-edicao-imagem'
                );

            const previewPlaceholder =
                document.getElementById(
                    'preview-edicao-placeholder'
                );

            const previewHabilidades =
                document.getElementById(
                    'preview-edicao-habilidades'
                );

            const previewHabilidadesWrapper =
                document.getElementById(
                    'preview-edicao-habilidades-wrapper'
                );

            const modalSucesso =
                document.getElementById(
                    'modal-sucesso-edicao'
                );


            function textoOuPadrao(valor, padrao) {
                const texto = (valor ?? '').trim();

                return texto.length
                    ? texto
                    : padrao;
            }


            function formatarModalidade(valor) {
                const opcoes = {
                    presencial: 'Presencial',
                    online: 'Online',
                    hibrido: 'Híbrido'
                };

                return opcoes[valor] ?? 'Presencial';
            }


            function formatarData(valor) {
                if (!valor) {
                    return null;
                }

                const data = new Date(valor);

                if (Number.isNaN(data.getTime())) {
                    return null;
                }

                return new Intl.DateTimeFormat(
                    'pt-BR',
                    {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    }
                ).format(data);
            }


            function atualizarTitulo() {
                previewTitulo.textContent =
                    textoOuPadrao(
                        campoTitulo?.value,
                        'Evento sem título'
                    );
            }


            function atualizarCategoria() {
                if (!campoCategoria) {
                    return;
                }

                const opcao =
                    campoCategoria.options[
                        campoCategoria.selectedIndex
                    ];

                previewCategoria.textContent =
                    opcao?.textContent?.trim()
                    || 'Categoria';
            }


            function atualizarVagas() {
                const vagas =
                    Number(campoVagas?.value);

                if (!vagas || vagas < 1) {
                    previewVagas.textContent =
                        'Vagas a definir';

                    return;
                }

                previewVagas.textContent =
                    vagas === 1
                        ? '1 vaga'
                        : `${vagas} vagas`;
            }


            function atualizarModalidade() {
                previewModalidade.textContent =
                    formatarModalidade(
                        campoModalidade?.value
                    );
            }


            function atualizarDescricao() {
                previewDescricao.textContent =
                    textoOuPadrao(
                        campoDescricao?.value,
                        'Descrição do evento'
                    );
            }


            function atualizarData() {
                const inicio =
                    formatarData(
                        campoDataInicio?.value
                    );

                const fim =
                    formatarData(
                        campoDataFim?.value
                    );

                if (!inicio && !fim) {
                    previewData.textContent =
                        'Data e horário a definir';

                    return;
                }

                if (inicio && fim) {
                    previewData.textContent =
                        `${inicio} → ${fim}`;

                    return;
                }

                previewData.textContent =
                    inicio ?? fim;
            }


            function atualizarLocal() {
                const partes = [];

                const logradouro =
                    campoLogradouro?.value.trim();

                const complemento =
                    campoComplemento?.value.trim();

                const cep =
                    campoCep?.value.trim();

                if (logradouro) {
                    partes.push(logradouro);
                }

                if (complemento) {
                    partes.push(complemento);
                }

                if (cep) {
                    partes.push(`CEP ${cep}`);
                }

                previewLocal.textContent =
                    partes.length
                        ? partes.join(' · ')
                        : 'Local a definir';
            }


            function atualizarHabilidades() {
                if (
                    !previewHabilidades ||
                    !previewHabilidadesWrapper
                ) {
                    return;
                }

                previewHabilidades.innerHTML = '';

                const selecionadas =
                    Array.from(camposHabilidades)
                        .filter(
                            checkbox =>
                                checkbox.checked
                        );

                if (!selecionadas.length) {
                    previewHabilidadesWrapper
                        .classList
                        .add('hidden');

                    return;
                }

                selecionadas.forEach(
                    checkbox => {

                        const label =
                            checkbox.closest('label');

                        const nome =
                            label
                                ?.querySelector('span')
                                ?.textContent
                                ?.trim();

                        if (!nome) {
                            return;
                        }

                        const tag =
                            document.createElement('span');

                        tag.className =
                            'rounded-full border ' +
                            'border-[#d4d0c6] ' +
                            'bg-[#f7f5ef] px-2.5 py-1 ' +
                            'font-poppins text-[10px] ' +
                            'text-neutral-600';

                        tag.textContent = nome;

                        previewHabilidades
                            .appendChild(tag);
                    }
                );

                previewHabilidadesWrapper
                    .classList
                    .remove('hidden');
            }


            function atualizarImagem() {
                const arquivo =
                    campoImagem?.files?.[0];

                if (!arquivo) {
                    return;
                }

                if (
                    !arquivo.type.startsWith('image/')
                ) {
                    return;
                }

                const leitor =
                    new FileReader();

                leitor.onload =
                    function (event) {

                        previewImagem.src =
                            event.target.result;

                        previewImagem
                            .classList
                            .remove('hidden');

                        previewPlaceholder
                            .classList
                            .add('hidden');
                    };

                leitor.readAsDataURL(arquivo);
            }


            function validarDatas() {
                if (
                    !campoDataInicio ||
                    !campoDataFim
                ) {
                    return true;
                }

                const inicio =
                    campoDataInicio.value;

                const fim =
                    campoDataFim.value;

                campoDataFim.min =
                    inicio || '';

                campoDataFim
                    .setCustomValidity('');

                if (
                    inicio &&
                    fim &&
                    fim < inicio
                ) {
                    campoDataFim
                        .setCustomValidity(
                            'A data de término não pode ser anterior à data de início.'
                        );

                    return false;
                }

                return true;
            }


            function atualizarTudo() {
                atualizarTitulo();
                atualizarCategoria();
                atualizarVagas();
                atualizarModalidade();
                atualizarDescricao();
                validarDatas();
                atualizarData();
                atualizarLocal();
                atualizarHabilidades();
            }


            campoTitulo?.addEventListener(
                'input',
                atualizarTitulo
            );

            campoCategoria?.addEventListener(
                'change',
                atualizarCategoria
            );

            campoVagas?.addEventListener(
                'input',
                atualizarVagas
            );

            campoModalidade?.addEventListener(
                'change',
                atualizarModalidade
            );

            campoDescricao?.addEventListener(
                'input',
                atualizarDescricao
            );

            campoDataInicio?.addEventListener(
                'change',
                function () {

                    validarDatas();

                    if (
                        campoDataFim?.value &&
                        campoDataInicio?.value &&
                        campoDataFim.value <
                        campoDataInicio.value
                    ) {
                        campoDataFim.value = '';

                        campoDataFim
                            .setCustomValidity('');
                    }

                    atualizarData();
                }
            );

            campoDataFim?.addEventListener(
                'change',
                function () {

                    validarDatas();
                    atualizarData();

                    if (
                        !campoDataFim
                            .checkValidity()
                    ) {
                        campoDataFim
                            .reportValidity();
                    }
                }
            );

            campoCep?.addEventListener(
                'input',
                atualizarLocal
            );

            campoLogradouro?.addEventListener(
                'input',
                atualizarLocal
            );

            campoComplemento?.addEventListener(
                'input',
                atualizarLocal
            );

            campoImagem?.addEventListener(
                'change',
                atualizarImagem
            );

            camposHabilidades.forEach(
                checkbox => {

                    checkbox.addEventListener(
                        'change',
                        atualizarHabilidades
                    );
                }
            );


            /*
             * MOCK DA PRÉ-BANCA:
             * impede o PUT real e mostra o modal.
             */
            formulario?.addEventListener(
                'submit',
                function (event) {

                    event.preventDefault();

                    if (!validarDatas()) {
                        campoDataFim
                            ?.reportValidity();

                        return;
                    }

                    if (
                        !formulario
                            .checkValidity()
                    ) {
                        formulario
                            .reportValidity();

                        return;
                    }

                    modalSucesso
                        ?.classList
                        .remove('hidden');

                    modalSucesso
                        ?.classList
                        .add('flex');

                    document.body
                        .classList
                        .add('overflow-hidden');
                }
            );


            /*
             * Clique fora leva para Meus eventos.
             */
            modalSucesso?.addEventListener(
                'click',
                function (event) {

                    if (
                        event.target ===
                        modalSucesso
                    ) {
                        window.location.href =
                            @json(
                                route(
                                    'ong.eventos.index'
                                )
                            );
                    }
                }
            );


            /*
             * ESC também leva para Meus eventos.
             */
            document.addEventListener(
                'keydown',
                function (event) {

                    if (
                        event.key === 'Escape' &&
                        modalSucesso &&
                        !modalSucesso
                            .classList
                            .contains('hidden')
                    ) {
                        window.location.href =
                            @json(
                                route(
                                    'ong.eventos.index'
                                )
                            );
                    }
                }
            );


            atualizarTudo();
        });
    </script>


    {{-- MODAL DE SUCESSO --}}
    <div
        id="modal-sucesso-edicao"
        class="fixed inset-0 z-[100] hidden
               items-center justify-center
               bg-black/45 px-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="modal-sucesso-edicao-titulo"
    >
        <div
            class="w-full max-w-md
                   rounded-3xl border border-[#dedbd1]
                   bg-white p-6
                   text-center shadow-xl
                   sm:p-8"
        >
            <div
                class="mx-auto flex h-16 w-16
                       items-center justify-center
                       rounded-full bg-[#dcefe5]
                       text-3xl font-bold
                       text-[#174b36]"
                aria-hidden="true"
            >
                ✓
            </div>

            <h2
                id="modal-sucesso-edicao-titulo"
                class="mt-5 font-fraunces
                       text-2xl font-bold
                       text-[#17392a]"
            >
                Alterações salvas!
            </h2>

            <p
                class="mt-3 font-poppins
                       text-sm leading-6
                       text-neutral-500"
            >
                As alterações do evento foram enviadas com sucesso.
            </p>

            <div
                class="mt-4 rounded-2xl
                       border border-[#f0dfb8]
                       bg-[#fff8e8]
                       px-4 py-3
                       font-poppins text-xs
                       leading-5 text-[#805f21]"
            >
                Dependendo das mudanças realizadas, o evento poderá
                voltar para análise antes de aparecer novamente no feed.
            </div>

            <div class="mt-6">
                <a
                    href="{{ route('ong.eventos.index') }}"
                    class="flex w-full
                           items-center justify-center
                           rounded-full
                           bg-[#174b36]
                           px-6 py-3
                           font-poppins text-sm
                           font-semibold text-white
                           transition
                           hover:bg-[#123b2b]"
                >
                    Ir para meus eventos →
                </a>
            </div>
        </div>
    </div>


</body>
</html>