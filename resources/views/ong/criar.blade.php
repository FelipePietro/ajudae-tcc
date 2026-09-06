<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Criar evento | Ajudaê</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>


<body
    class="min-h-screen overflow-x-hidden
           bg-[#f7f5ef] font-outfit text-[#26251f]"
>

    {{-- NAVBAR ONG --}}
    <x-navbar-ong ativo="criar-evento" />


    {{--
    |--------------------------------------------------------------------------
    | CONTEÚDO
    |--------------------------------------------------------------------------
    |
    | No desktop abre espaço para a sidebar.
    | No mobile ocupa a tela inteira.
    |
    --}}

    <main
        class="min-h-screen w-full
               lg:ml-[230px]
               lg:w-[calc(100%-230px)]"
    >

    <x-breadcrumb-ong
    :itens="[
        [
            'label' => 'Meus eventos',
            'route' => 'ong.eventos.index'
        ],
        [
            'label' => 'Criar evento'
        ]
    ]"
/>


        {{-- ============================================================= --}}
        {{-- FORMULÁRIO --}}
        {{-- ============================================================= --}}

        <form
            action="{{ route('ong.eventos.store') }}"
            method="POST"
            enctype="multipart/form-data"
        >

            @csrf


            <div
                class="mx-auto w-full max-w-[1500px]
                       px-4 py-6
                       sm:px-6 sm:py-8
                       lg:px-8"
            >

                {{-- ===================================================== --}}
                {{-- TÍTULO --}}
                {{-- ===================================================== --}}

                <div
                    class="mb-6 flex flex-col gap-4
                           sm:mb-8
                           md:flex-row
                           md:items-start
                           md:justify-between"
                >

                    <div class="min-w-0">

                        <h1
                            class="text-2xl font-semibold leading-tight
                                   sm:text-3xl"
                        >
                            Criar novo evento
                        </h1>

                        <p
                            class="mt-2 max-w-2xl
                                   font-poppins text-sm leading-6
                                   text-neutral-500"
                        >
                            Preencha as informações abaixo.
                            O evento será revisado pelo administrador
                            antes de ser publicado.
                        </p>

                    </div>


                    {{-- Salvar rascunho desktop/tablet --}}
                    <button
                        type="button"
                        class="w-full shrink-0 rounded-full
                               border border-[#c9c4b8]
                               bg-white px-6 py-3
                               font-poppins text-sm
                               transition hover:bg-neutral-50
                               sm:w-auto"
                    >
                        Salvar rascunho
                    </button>

                </div>


                {{-- ===================================================== --}}
                {{-- GRID PRINCIPAL --}}
                {{-- ===================================================== --}}

                <div
                    class="grid min-w-0 grid-cols-1
                           gap-5
                           xl:grid-cols-[minmax(0,1fr)_350px]
                           xl:gap-6"
                >

                    {{-- ================================================= --}}
                    {{-- COLUNA PRINCIPAL --}}
                    {{-- ================================================= --}}

                    <div class="min-w-0 space-y-5 sm:space-y-6">


                        {{-- ================================================= --}}
                        {{-- INFORMAÇÕES BÁSICAS --}}
                        {{-- ================================================= --}}

                        <section
                            class="min-w-0 overflow-hidden
                                   rounded-2xl border
                                   border-[#dedbd1]
                                   bg-white shadow-sm"
                        >

                            {{-- Cabeçalho --}}
                            <div
                                class="flex items-center gap-3
                                       border-b border-[#dedbd1]
                                       px-4 py-4
                                       sm:gap-4 sm:px-6 sm:py-5"
                            >

                                <div
                                    class="flex h-9 w-9 shrink-0
                                           items-center justify-center
                                           rounded-xl bg-[#dcefe5]
                                           sm:h-10 sm:w-10"
                                >
                                    📝
                                </div>


                                <div class="min-w-0">

                                    <h2
                                        class="font-semibold"
                                    >
                                        Informações básicas
                                    </h2>

                                    <p
                                        class="mt-0.5 font-poppins
                                               text-xs text-neutral-500"
                                    >
                                        Título, categoria, vagas e descrição
                                    </p>

                                </div>

                            </div>


                            {{-- Conteúdo --}}
                            <div
                                class="space-y-5 p-4
                                       sm:p-6"
                            >

                                {{-- Título --}}
                                <div>

                                    <label
                                        for="nm_evento"
                                        class="mb-2 block
                                               font-poppins text-sm
                                               font-medium"
                                    >
                                        Título do evento

                                        <span class="text-[#d98b00]">
                                            *
                                        </span>
                                    </label>

                                    <input
                                        type="text"
                                        id="nm_evento"
                                        name="nm_evento"
                                        value="{{ old('nm_evento') }}"
                                        required
                                        placeholder="Limpeza da Praia de Santos"
                                        class="w-full min-w-0
                                               rounded-xl border
                                               border-[#d8d4ca]
                                               px-4 py-3
                                               font-poppins text-sm
                                               outline-none transition
                                               placeholder:text-neutral-400
                                               focus:border-[#174b36]"
                                    >

                                </div>


                                {{-- Categoria / vagas / modalidade --}}
                                <div
                                    class="grid grid-cols-1 gap-4
                                           sm:grid-cols-2
                                           lg:grid-cols-3"
                                >

                                    {{-- Categoria --}}
                                    <div class="min-w-0">

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
                                            class="w-full min-w-0
                                                   rounded-xl border
                                                   border-[#d8d4ca]
                                                   bg-white px-4 py-3
                                                   font-poppins text-sm
                                                   outline-none
                                                   focus:border-[#174b36]"
                                        >

                                            <option value="">
                                                Selecione
                                            </option>


                                            @foreach($categorias ?? [] as $categoria)

                                                <option
                                                    value="{{ $categoria->cat_evento_id }}"
                                                >
                                                    {{ $categoria->nome_categoria }}
                                                </option>

                                            @endforeach

                                        </select>

                                    </div>


                                    {{-- Vagas --}}
                                    <div class="min-w-0">

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
                                            value="{{ old('vagas_evento', 30) }}"
                                            class="w-full min-w-0
                                                   rounded-xl border
                                                   border-[#d8d4ca]
                                                   px-4 py-3
                                                   font-poppins text-sm
                                                   outline-none
                                                   focus:border-[#174b36]"
                                        >

                                    </div>


                                    {{-- Modalidade --}}
                                    <div
                                        class="min-w-0
                                               sm:col-span-2
                                               lg:col-span-1"
                                    >

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
                                            class="w-full min-w-0
                                                   rounded-xl border
                                                   border-[#d8d4ca]
                                                   bg-white px-4 py-3
                                                   font-poppins text-sm
                                                   outline-none
                                                   focus:border-[#174b36]"
                                        >

                                            <option value="presencial">
                                                Presencial
                                            </option>

                                            <option value="online">
                                                Online
                                            </option>

                                            <option value="hibrido">
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
                                        maxlength="1000"
                                        required
                                        placeholder="Junte-se a nós para uma manhã de limpeza e preservação..."
                                        class="w-full min-w-0 resize-none
                                               rounded-xl border
                                               border-[#d8d4ca]
                                               px-4 py-3
                                               font-poppins text-sm
                                               leading-6 outline-none
                                               placeholder:text-neutral-400
                                               focus:border-[#174b36]"
                                    >{{ old('descricao_evento') }}</textarea>

                                    <p
                                        class="mt-2 font-poppins
                                               text-xs leading-5
                                               text-neutral-500"
                                    >
                                        Seja objetivo: descreva o que
                                        o voluntário irá fazer e o que
                                        deve levar.
                                    </p>

                                </div>

                            </div>

                        </section>


                        {{-- ================================================= --}}
                        {{-- DATA, HORÁRIO E LOCAL --}}
                        {{-- ================================================= --}}

                        <section
                            class="min-w-0 overflow-hidden
                                   rounded-2xl border
                                   border-[#dedbd1]
                                   bg-white shadow-sm"
                        >

                            <div
                                class="flex items-center gap-3
                                       border-b border-[#dedbd1]
                                       px-4 py-4
                                       sm:gap-4 sm:px-6 sm:py-5"
                            >

                                <div
                                    class="flex h-9 w-9 shrink-0
                                           items-center justify-center
                                           rounded-xl bg-[#fff0cf]
                                           sm:h-10 sm:w-10"
                                >
                                    📍
                                </div>


                                <div class="min-w-0">

                                    <h2 class="font-semibold">
                                        Data, horário e local
                                    </h2>

                                    <p
                                        class="mt-0.5 font-poppins
                                               text-xs text-neutral-500"
                                    >
                                        Informe quando e onde
                                        acontece o evento
                                    </p>

                                </div>

                            </div>


                            <div
                                class="space-y-5 p-4
                                       sm:p-6"
                            >

                                {{-- Datas --}}
                                <div
                                    class="grid grid-cols-1 gap-4
                                           md:grid-cols-2"
                                >

                                    <div class="min-w-0">

                                        <label
                                            for="data_inicio"
                                            class="mb-2 block
                                                   font-poppins text-sm
                                                   font-medium"
                                        >
                                            Data e hora de início *
                                        </label>

                                        <input
                                            type="datetime-local"
                                            id="data_inicio"
                                            name="data_inicio"
                                            required
                                            class="w-full min-w-0
                                                   rounded-xl border
                                                   border-[#d8d4ca]
                                                   px-3 py-3
                                                   font-poppins text-sm
                                                   outline-none
                                                   focus:border-[#174b36]
                                                   sm:px-4"
                                        >

                                    </div>


                                    <div class="min-w-0">

                                        <label
                                            for="data_fim"
                                            class="mb-2 block
                                                   font-poppins text-sm
                                                   font-medium"
                                        >
                                            Data e hora de término *
                                        </label>

                                        <input
                                            type="datetime-local"
                                            id="data_fim"
                                            name="data_fim"
                                            required
                                            class="w-full min-w-0
                                                   rounded-xl border
                                                   border-[#d8d4ca]
                                                   px-3 py-3
                                                   font-poppins text-sm
                                                   outline-none
                                                   focus:border-[#174b36]
                                                   sm:px-4"
                                        >

                                    </div>

                                </div>


                                {{-- CEP --}}
                                <div
                                    class="grid grid-cols-1 gap-4
                                           md:grid-cols-[180px_minmax(0,1fr)]"
                                >

                                    <div class="min-w-0">

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
                                            placeholder="11070100"
                                            class="w-full min-w-0
                                                   rounded-xl border
                                                   border-[#d8d4ca]
                                                   px-4 py-3
                                                   font-poppins text-sm
                                                   outline-none
                                                   focus:border-[#174b36]"
                                        >

                                    </div>


                                    <div class="min-w-0">

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
                                            placeholder="Av. Ana Costa"
                                            class="w-full min-w-0
                                                   rounded-xl border
                                                   border-[#d8d4ca]
                                                   px-4 py-3
                                                   font-poppins text-sm
                                                   outline-none
                                                   focus:border-[#174b36]"
                                        >

                                    </div>

                                </div>


                                {{-- Complemento --}}
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
                                        maxlength="64"
                                        placeholder="Ex.: 555, entrada pelo portão 2"
                                        class="w-full min-w-0
                                               rounded-xl border
                                               border-[#d8d4ca]
                                               px-4 py-3
                                               font-poppins text-sm
                                               outline-none
                                               placeholder:text-neutral-400
                                               focus:border-[#174b36]"
                                    >

                                </div>


                                {{-- MAPA --}}
                                <div>

                                    <p
                                        class="mb-2 font-poppins
                                               text-sm font-medium"
                                    >
                                        Pré-visualização no mapa
                                    </p>

                                    <div
                                        class="flex min-h-32
                                               items-center justify-center
                                               rounded-xl border
                                               border-dashed
                                               border-[#bfd8ca]
                                               bg-[#eaf5ef]
                                               p-4
                                               sm:h-36"
                                    >

                                        <div
                                            class="text-center"
                                        >

                                            <div class="text-2xl">
                                                🗺️
                                            </div>

                                            <p
                                                class="mt-2
                                                       font-poppins text-xs
                                                       text-[#38634e]"
                                            >
                                                Mapa gerado ao
                                                confirmar o CEP
                                            </p>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </section>


                        {{-- ================================================= --}}
                        {{-- HABILIDADES E IMAGEM --}}
                        {{-- ================================================= --}}

                        <section
                            class="min-w-0 overflow-hidden
                                   rounded-2xl border
                                   border-[#dedbd1]
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

                                <p
                                    class="mt-1 font-poppins
                                           text-xs text-neutral-500"
                                >
                                    Habilidades esperadas e foto
                                    do evento
                                </p>

                            </div>


                            <div
                                class="space-y-6 p-4
                                       sm:p-6"
                            >

                                {{-- Habilidades --}}
                                <div>

                                    <label
                                        class="font-poppins
                                               text-sm font-medium"
                                    >
                                        Habilidades desejadas
                                    </label>


                                    <div
                                        class="mt-3 flex
                                               flex-wrap gap-2"
                                    >

                                        @foreach($habilidades ?? [] as $habilidade)

                                            <label
                                                class="max-w-full
                                                       cursor-pointer"
                                            >

                                                <input
                                                    type="checkbox"
                                                    name="habilidades[]"
                                                    value="{{ $habilidade->habilidade_id }}"
                                                    class="peer hidden"
                                                >

                                                <span
                                                    class="block max-w-full
                                                           break-words
                                                           rounded-full
                                                           border
                                                           border-[#d4d0c6]
                                                           px-3 py-2
                                                           font-poppins
                                                           text-xs
                                                           transition
                                                           peer-checked:border-[#afd5bf]
                                                           peer-checked:bg-[#dcefe5]
                                                           peer-checked:text-[#174b36]
                                                           sm:px-4"
                                                >
                                                    {{ $habilidade->nome_habilidade }}
                                                </span>

                                            </label>

                                        @endforeach

                                    </div>

                                </div>


                                {{-- Upload --}}
                                <div>

                                    <label
                                        class="font-poppins
                                               text-sm font-medium"
                                    >
                                        Imagem do evento
                                    </label>


                                    <label
                                        class="mt-3 flex min-h-32
                                               cursor-pointer
                                               flex-col items-center
                                               justify-center
                                               rounded-xl border
                                               border-dashed
                                               border-[#c9c4b8]
                                               p-4 text-center
                                               transition
                                               hover:bg-neutral-50"
                                    >

                                        <span class="text-2xl">
                                            🖼️
                                        </span>

                                        <span
                                            class="mt-2
                                                   font-poppins text-sm"
                                        >
                                            Clique para fazer upload
                                        </span>

                                        <span
                                            class="mt-1
                                                   font-poppins text-xs
                                                   leading-5
                                                   text-neutral-400"
                                        >
                                            PNG ou JPG · Máximo 5 MB · 16:9
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


                    {{-- ================================================= --}}
                    {{-- SIDEBAR DIREITA --}}
                    {{-- ================================================= --}}

                    <aside
                        class="min-w-0 space-y-5"
                    >

                        {{-- Preview --}}
                        <div
                            class="overflow-hidden rounded-2xl
                                   border border-[#dedbd1]
                                   bg-white shadow-sm"
                        >

                            <div
                                class="flex h-28 items-center
                                       justify-center
                                       bg-[#dcefe5]
                                       text-4xl
                                       sm:h-32 sm:text-5xl"
                            >
                                🌿
                            </div>


                            <div class="p-4 sm:p-5">

                                <p
                                    class="font-poppins
                                           text-xs text-neutral-500"
                                >
                                    Sua ONG
                                </p>

                                <h3
                                    class="mt-1 font-semibold"
                                >
                                    Prévia do evento
                                </h3>

                                <p
                                    class="mt-3
                                           font-poppins text-xs
                                           leading-5
                                           text-neutral-500"
                                >
                                    O resumo do evento
                                    aparecerá aqui.
                                </p>

                            </div>

                        </div>


                        {{-- Aviso --}}
                        <div
                            class="rounded-2xl border
                                   border-[#dedbd1]
                                   bg-[#fffaf0] p-4
                                   font-poppins text-sm
                                   leading-6
                                   sm:p-5"
                        >
                            ℹ️ Após enviar, o evento ficará
                            com status
                            <strong>
                                "aguardando aprovação"
                            </strong>
                            até o administrador revisar.
                        </div>


                        {{-- Fluxo --}}
                        <div
                            class="overflow-hidden rounded-2xl
                                   border border-[#dedbd1]
                                   bg-white shadow-sm"
                        >

                            <div
                                class="border-b border-[#dedbd1]
                                       px-4 py-4 font-semibold
                                       sm:px-5"
                            >
                                🔄 Fluxo de aprovação
                            </div>


                            <div
                                class="space-y-5 p-4
                                       font-poppins text-sm
                                       sm:p-5"
                            >

                                <div
                                    class="flex min-w-0 gap-3"
                                >

                                    <span class="shrink-0">
                                        ✅
                                    </span>

                                    <div class="min-w-0">

                                        <strong>
                                            Rascunho criado
                                        </strong>

                                        <p
                                            class="mt-1 text-xs
                                                   leading-5
                                                   text-neutral-500"
                                        >
                                            Formulário preenchido
                                            pelo organizador
                                        </p>

                                    </div>

                                </div>


                                <div
                                    class="flex min-w-0 gap-3"
                                >

                                    <span class="shrink-0">
                                        🟠
                                    </span>

                                    <div class="min-w-0">

                                        <strong>
                                            Enviado para aprovação
                                        </strong>

                                        <p
                                            class="mt-1 text-xs
                                                   leading-5
                                                   text-neutral-500"
                                        >
                                            Admin recebe notificação
                                            para revisar
                                        </p>

                                    </div>

                                </div>


                                <div
                                    class="flex min-w-0 gap-3
                                           opacity-50"
                                >

                                    <span class="shrink-0">
                                        ③
                                    </span>

                                    <div class="min-w-0">

                                        <strong>
                                            Publicado no feed
                                        </strong>

                                        <p
                                            class="mt-1 text-xs
                                                   leading-5
                                                   text-neutral-500"
                                        >
                                            Voluntários podem
                                            se candidatar
                                        </p>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </aside>

                </div>

            </div>


            {{-- ========================================================= --}}
            {{-- BARRA DE AÇÕES --}}
            {{-- ========================================================= --}}

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
                    class="mx-auto flex w-full max-w-[1500px]
                           flex-col gap-4
                           sm:flex-row
                           sm:items-center
                           sm:justify-between"
                >

                    <p
                        class="hidden
                               font-poppins text-sm
                               text-neutral-500
                               md:block"
                    >
                        Evento:

                        <strong
                            class="text-neutral-800"
                        >
                            Novo evento
                        </strong>
                    </p>


                    <div
                        class="grid w-full
                               grid-cols-1 gap-2
                               sm:flex sm:w-auto
                               sm:flex-wrap
                               sm:justify-end
                               sm:gap-3"
                    >

                        <a
                            href="{{ route('ong.eventos.index') }}"
                            class="flex w-full items-center
                                   justify-center
                                   rounded-full border
                                   border-[#c9c4b8]
                                   bg-white px-5 py-3
                                   font-poppins text-sm
                                   transition
                                   hover:bg-neutral-50
                                   sm:w-auto"
                        >
                            Cancelar
                        </a>



                        <button
                            type="submit"
                            class="w-full rounded-full
                                   bg-[#174b36]
                                   px-6 py-3
                                   font-poppins text-sm
                                   font-semibold
                                   text-white
                                   transition
                                   hover:bg-[#123b2b]
                                   sm:w-auto"
                        >
                            Enviar para aprovação →
                        </button>

                    </div>

                </div>

            </div>

        </form>

    </main>

</body>

</html>