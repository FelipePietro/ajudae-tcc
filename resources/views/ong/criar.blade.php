@php
    $integrated = config('identity.enabled');
    $organizerManagement = $organizerManagement ?? false;
    $listRoute = $organizerManagement ? 'organizador.eventos' : 'ong.eventos.index';
    $managementParams = $integrated ? [] : ['contexto' => request()->query('contexto', 'ong'), 'jornada' => request()->query('jornada')];
    $workspaceUrl = $organizerManagement ? route('organizador.workspace') : null;
@endphp
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Criar evento | Ajudae</title>

    @vite([
        'resources/css/app.css',
        $integrated ? 'resources/js/identity.js' : 'resources/js/app.js'
    ])
</head>


<body @unless($integrated) data-events-page @endunless
    class="min-h-screen overflow-x-hidden
           bg-[#f7f5ef] font-outfit text-[#26251f]"
>

    {{-- NAVBAR ONG --}}
    <x-context-navbar default-context="ong" :surface="$integrated && !$organizerManagement ? 'institution' : 'context'" />


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
        @class(['min-h-screen w-full', 'lg:ml-[230px] lg:w-[calc(100%-230px)]' => !$organizerManagement])
    >


    <x-breadcrumb-ong :raiz-href="$workspaceUrl" :raiz-label="$organizerManagement ? 'Minha atuação' : 'Dashboard'"
    :itens="[
        [
            'label' => 'Meus eventos',
            'route' => $listRoute
        ],
        [
            'label' => 'Criar evento'
        ]
    ]"
/>


        {{-- ============================================================= --}}
        {{-- FORMULÁRIO --}}
        {{-- ============================================================= --}}

        @if($integrated || ($flow['management_allowed'] ?? true))
        @if($integrated)
            <div>
            <p class="mx-auto mt-5 max-w-[1450px] px-4 text-sm sm:px-6 lg:px-8" id="event-editor-status">O envio do Evento para aprovação ainda está indisponível.</p>
            <fieldset disabled class="min-w-0" aria-describedby="event-editor-status">
            <legend class="sr-only">Campos do evento</legend>
        @else
        <form data-event-editor onsubmit="return false;"
            action="{{ route('ong.eventos.store') }}"
            method="POST"
            enctype="multipart/form-data"
        >


            @csrf
        @endif


            <div
                class="mx-auto w-full max-w-[1500px]
                       px-4 py-6
                       sm:px-6 sm:py-8
                       lg:px-8"
            >
@unless($integrated)<x-organizer-scope :flow="$flow" />@endunless
@unless($integrated)<x-event-state :flow="$flow" :management="true" />@endunless

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
                            @unless($integrated)Preencha as informações abaixo.@endunless
                            O evento será revisado pelo administrador
                            antes de ser publicado.
                        </p>

                    </div>


                    {{-- Salvar rascunho desktop/tablet --}}
                    <x-botao-secundario :href="route($listRoute, $managementParams)" class="w-full shrink-0 sm:w-auto">Voltar aos eventos</x-botao-secundario>

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
                                        name="nm_evento" maxlength="64"
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
                                            Vagas informativas do evento *
                                        </label>

                                        <input
                                            type="number"
                                            id="vagas_evento"
                                            name="vagas_evento"
                                            min="1"
                                            required
                                            value="{{ $integrated ? '' : old('vagas_evento', 30) }}"
                                            class="w-full min-w-0
                                                   rounded-xl border
                                                   border-[#d8d4ca]
                                                   px-4 py-3
                                                   font-poppins text-sm
                                                   outline-none
                                                   focus:border-[#174b36]"
                                        >

                                    </div>


                                    <p class="text-xs text-[#817c70]">As vagas do evento são informativas. Cada Agenda tem sua capacidade independente.</p>
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
                                        name="descricao_evento" maxlength="256"
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

                                <x-agenda-editor :flow="$flow ?? ['catalogs' => []]" :disabled="$integrated" />
<div class="grid grid-cols-1 md:grid-cols-2 gap-4"><label class="text-sm">Cidade<input name="cidade_evento" @unless($integrated) value="Guarulhos" @endunless class="block w-full rounded-xl border border-[#d8d4ca] px-4 py-3 mt-2" maxlength="64"></label><label class="text-sm">Bairro<input name="bairro_evento" @unless($integrated) value="Bairro Exemplo" @endunless class="block w-full rounded-xl border border-[#d8d4ca] px-4 py-3 mt-2" maxlength="64"></label><label class="text-sm">UF<input name="uf_evento" @unless($integrated) value="SP" @endunless maxlength="2" class="block w-full rounded-xl border border-[#d8d4ca] px-4 py-3 mt-2"></label></div>
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
                                            name="cep_evento" data-event-postcode inputmode="numeric" autocomplete="postal-code" maxlength="9" pattern="[0-9]{5}-?[0-9]{3}" aria-describedby="event-postcode-hint"
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
                                                Confira o endereço informado
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
                                            Selecionar imagem
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

                            {{-- Imagem da prévia --}}
                            <div
                                id="preview-imagem-wrapper"
                                class="relative flex h-36 items-center
                                       justify-center overflow-hidden
                                       bg-[#dcefe5]
                                       sm:h-44"
                            >
                                <img
                                    id="preview-imagem"
                                    src=""
                                    alt="Prévia da imagem do evento"
                                    class="hidden h-full w-full object-cover"
                                >

                                <span
                                    id="preview-imagem-placeholder"
                                    class="text-4xl sm:text-5xl"
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
                                    {{ $integrated ? ($organizerManagement ? 'Organizador independente' : $institutionName) : $flow['owner_label'] }}
                                </p>

                                <h3
                                    id="preview-titulo"
                                    class="mt-1 text-lg font-semibold
                                           leading-snug text-[#17392a]"
                                >
                                    Prévia do evento
                                </h3>


                                <div
                                    class="mt-3 flex flex-wrap gap-2
                                           font-poppins text-[11px]"
                                >
                                    <span
                                        id="preview-categoria"
                                        class="rounded-full bg-[#eef7f2]
                                               px-2.5 py-1 text-[#2d6a4f]"
                                    >
                                        Categoria
                                    </span>

                                    <span
                                        id="preview-modalidade"
                                        class="rounded-full bg-[#fff5dd]
                                               px-2.5 py-1 text-[#9a6410]"
                                    >
                                        Presencial
                                    </span>

                                    <span
                                        id="preview-vagas"
                                        class="rounded-full bg-neutral-100
                                               px-2.5 py-1 text-neutral-600"
                                    >
                                        {{ $integrated ? 'Vagas a definir' : '30 vagas informativas' }}
                                    </span>
                                </div>


                                <p
                                    id="preview-descricao"
                                    class="mt-3
                                           font-poppins text-xs
                                           leading-5
                                           text-neutral-500"
                                >
                                    O resumo do evento aparecerá aqui.
                                </p>


                                <div
                                    class="mt-4 space-y-2 border-t
                                           border-[#ece9e1] pt-4
                                           font-poppins text-xs
                                           text-neutral-600"
                                >

                                    <p
                                        id="preview-data"
                                        class="flex gap-2"
                                    >
                                        <span aria-hidden="true">📅</span>
                                        <span>Data e horário a definir</span>
                                    </p>

                                    <p
                                        id="preview-local"
                                        class="flex gap-2"
                                    >
                                        <span aria-hidden="true">📍</span>
                                        <span>Local a definir</span>
                                    </p>

                                </div>


                                <div
                                    id="preview-habilidades-wrapper"
                                    class="mt-4 hidden"
                                >
                                    <p
                                        class="mb-2 font-poppins
                                               text-[11px] font-medium
                                               text-neutral-500"
                                    >
                                        Habilidades desejadas
                                    </p>

                                    <div
                                        id="preview-habilidades"
                                        class="flex flex-wrap gap-1.5"
                                    ></div>
                                </div>

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
                                            Revisão das informações
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
                                            Análise para aprovação
                                        </strong>

                                        <p
                                            class="mt-1 text-xs
                                                   leading-5
                                                   text-neutral-500"
                                        >
                                            Administração avalia os dados
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

                        <x-botao-secundario :href="route($listRoute, $managementParams)" class="w-full sm:w-auto">
                            Cancelar
                        </x-botao-secundario>



                        <x-botao-primario as="button" :href="null" type="submit" class="w-full sm:w-auto">
                            Enviar para aprovação →
                        </x-botao-primario>

                    </div>

                </div>

            </div>

        @if($integrated)</fieldset></div>@else</form>@endif
        @else
        <div class="px-4 py-6">@unless($integrated)<x-organizer-scope :flow="$flow" />@endunless</div>
        @endif

    </main>



    {{-- ========================================================= --}}
    {{-- MODAL DE SUCESSO --}}
    {{-- ========================================================= --}}

    @unless($integrated)
    <dialog id="modal-sucesso-evento" class="ajudae-dialog ajudae-dialog--compact ajudae-dialog--legacy" role="dialog" aria-modal="true" aria-labelledby="modal-sucesso-titulo"><x-dialog-close />
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
                id="modal-sucesso-titulo"
                class="mt-5 font-fraunces
                       text-2xl font-bold
                       text-[#17392a]"
            >Revisão do evento</h2>

            <p
                class="mt-3 font-poppins
                       text-sm leading-6
                       text-neutral-500"
            >Confira as informações. Nenhum evento ou alteração foi enviado por esta página.</p>

            <div
                class="mt-4 rounded-2xl
                       border border-[#f0dfb8]
                       bg-[#fff8e8]
                       px-4 py-3
                       font-poppins text-xs
                       leading-5 text-[#805f21]"
            >
                Enquanto estiver em análise,
                ele aparecerá com o status
                <strong>"Aguardando aprovação"</strong>
                em Meus eventos.
            </div>

            <div class="mt-6 flex flex-col gap-3">
                <x-botao-primario :href="route($listRoute, $managementParams)" class="w-full">
                    Ir para meus eventos →
                </x-botao-primario>
            </div>
        </div>
    </dialog>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const campoTitulo = document.getElementById('nm_evento');
            const campoCategoria = document.getElementById('cat_evento_id');
            const campoVagas = document.getElementById('vagas_evento');
            const campoModalidade = document.getElementById('modalidade_evento');
            const campoDescricao = document.getElementById('descricao_evento');
            const campoDataInicio = document.getElementById('data_inicio');
            const campoDataFim = document.getElementById('data_fim');
            const campoCep = document.getElementById('cep_evento');
            const campoLogradouro = document.getElementById('logradouro_evento');
            const campoComplemento = document.getElementById('compl_evento');
            const campoImagem = document.querySelector('input[name="imagem_evento"]');
            const camposHabilidades = document.querySelectorAll('input[name="habilidades[]"]');

            const previewTitulo = document.getElementById('preview-titulo');
            const previewCategoria = document.getElementById('preview-categoria');
            const previewVagas = document.getElementById('preview-vagas');
            const previewModalidade = document.getElementById('preview-modalidade');
            const previewDescricao = document.getElementById('preview-descricao');
            const previewData = document.querySelector('#preview-data span:last-child');
            const previewLocal = document.querySelector('#preview-local span:last-child');
            const previewImagem = document.getElementById('preview-imagem');
            const previewImagemPlaceholder = document.getElementById('preview-imagem-placeholder');
            const previewHabilidadesWrapper = document.getElementById('preview-habilidades-wrapper');
            const previewHabilidades = document.getElementById('preview-habilidades');

            function textoOuPadrao(valor, padrao) {
                const texto = (valor ?? '').trim();
                return texto.length ? texto : padrao;
            }

            function formatarModalidade(valor) {
                const modalidades = {
                    presencial: 'Presencial',
                    online: 'Online',
                    hibrido: 'Híbrido'
                };

                return modalidades[valor] ?? 'Presencial';
            }

            function formatarData(valor) {
                if (!valor) return null;

                const data = new Date(valor);

                if (Number.isNaN(data.getTime())) {
                    return null;
                }

                return new Intl.DateTimeFormat('pt-BR', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                }).format(data);
            }

            function atualizarTitulo() {
                previewTitulo.textContent = textoOuPadrao(
                    campoTitulo?.value,
                    'Prévia do evento'
                );
            }

            function atualizarCategoria() {
                if (!campoCategoria) return;

                const opcaoSelecionada =
                    campoCategoria.options[campoCategoria.selectedIndex];

                const texto =
                    opcaoSelecionada &&
                    opcaoSelecionada.value
                        ? opcaoSelecionada.textContent.trim()
                        : 'Categoria';

                previewCategoria.textContent = texto;
            }

            function atualizarVagas() {
                const valor = Number(campoVagas?.value);

                if (!valor || valor < 1) {
                    previewVagas.textContent = 'Vagas informativas a definir';
                    return;
                }

                previewVagas.textContent =
                    valor === 1
                        ? '1 vaga informativa'
                        : `${valor} vagas informativas`;
            }

            function atualizarModalidade() {
                previewModalidade.textContent =
                    formatarModalidade(campoModalidade?.value);
            }

            function atualizarDescricao() {
                previewDescricao.textContent = textoOuPadrao(
                    campoDescricao?.value,
                    'O resumo do evento aparecerá aqui.'
                );
            }

            function atualizarData() {
                const inicio = formatarData(campoDataInicio?.value);
                const fim = formatarData(campoDataFim?.value);

                if (!inicio && !fim) {
                    previewData.textContent = 'Data e horário a definir';
                    return;
                }

                if (inicio && fim) {
                    previewData.textContent = `${inicio} → ${fim}`;
                    return;
                }

                previewData.textContent = inicio ?? fim;
            }

            function atualizarLocal() {
                const partes = [];

                const logradouro = campoLogradouro?.value.trim();
                const complemento = campoComplemento?.value.trim();
                const cep = campoCep?.value.trim();

                if (logradouro) partes.push(logradouro);
                if (complemento) partes.push(complemento);
                if (cep) partes.push(`CEP ${cep}`);

                previewLocal.textContent =
                    partes.length
                        ? partes.join(' · ')
                        : 'Local a definir';
            }

            function atualizarHabilidades() {
                if (!previewHabilidades || !previewHabilidadesWrapper) {
                    return;
                }

                previewHabilidades.innerHTML = '';

                const selecionadas = Array.from(camposHabilidades)
                    .filter((checkbox) => checkbox.checked);

                if (!selecionadas.length) {
                    previewHabilidadesWrapper.classList.add('hidden');
                    return;
                }

                selecionadas.forEach((checkbox) => {
                    const label = checkbox.closest('label');
                    const nome = label
                        ?.querySelector('span')
                        ?.textContent
                        ?.trim();

                    if (!nome) return;

                    const tag = document.createElement('span');

                    tag.className =
                        'rounded-full border border-[#d4d0c6] ' +
                        'bg-[#f7f5ef] px-2.5 py-1 ' +
                        'font-poppins text-[10px] text-neutral-600';

                    tag.textContent = nome;

                    previewHabilidades.appendChild(tag);
                });

                previewHabilidadesWrapper.classList.remove('hidden');
            }

            function atualizarImagem() {
                const arquivo = campoImagem?.files?.[0];

                if (!arquivo) {
                    previewImagem.src = '';
                    previewImagem.classList.add('hidden');
                    previewImagemPlaceholder.classList.remove('hidden');
                    return;
                }

                if (!arquivo.type.startsWith('image/')) {
                    return;
                }

                const leitor = new FileReader();

                leitor.onload = function (event) {
                    previewImagem.src = event.target.result;
                    previewImagem.classList.remove('hidden');
                    previewImagemPlaceholder.classList.add('hidden');
                };

                leitor.readAsDataURL(arquivo);
            }

            function validarDatas() {
                if (!campoDataInicio || !campoDataFim) {
                    return true;
                }

                const inicio = campoDataInicio.value;
                const fim = campoDataFim.value;

                // O navegador passa a bloquear datas de término anteriores ao início.
                campoDataFim.min = inicio || '';

                // Remove mensagens antigas antes de validar novamente.
                campoDataFim.setCustomValidity('');

                if (inicio && fim && fim < inicio) {
                    campoDataFim.setCustomValidity(
                        'A data de término não pode ser anterior à data de início.'
                    );

                    return false;
                }

                return true;
            }

            function atualizarPreviewCompleta() {
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

            campoTitulo?.addEventListener('input', atualizarTitulo);
            campoCategoria?.addEventListener('change', atualizarCategoria);
            campoVagas?.addEventListener('input', atualizarVagas);
            campoModalidade?.addEventListener('change', atualizarModalidade);
            campoDescricao?.addEventListener('input', atualizarDescricao);

            campoDataInicio?.addEventListener('change', function () {
                validarDatas();

                // Se já houver uma data final inválida, limpa o campo.
                if (
                    campoDataFim?.value &&
                    campoDataInicio?.value &&
                    campoDataFim.value < campoDataInicio.value
                ) {
                    campoDataFim.value = '';
                    campoDataFim.setCustomValidity('');
                }

                atualizarData();
            });

            campoDataFim?.addEventListener('change', function () {
                validarDatas();
                atualizarData();

                if (!campoDataFim.checkValidity()) {
                    campoDataFim.reportValidity();
                }
            });

            campoCep?.addEventListener('input', atualizarLocal);
            campoLogradouro?.addEventListener('input', atualizarLocal);
            campoComplemento?.addEventListener('input', atualizarLocal);

            campoImagem?.addEventListener('change', atualizarImagem);

            camposHabilidades.forEach((checkbox) => {
                checkbox.addEventListener('change', atualizarHabilidades);
            });

            const formularioEvento = document.querySelector(
                'form[action="{{ route('ong.eventos.store') }}"]'
            );

            const modalSucesso =
                document.getElementById('modal-sucesso-evento');

            const urlMeusEventos =
                @js(route($listRoute, $managementParams));


            formularioEvento?.addEventListener('submit', function (event) {

                // MOCK DA PRÉ-BANCA:
                // impede o POST real e mostra a confirmação visual.
                event.preventDefault();

                if (!validarDatas()) {
                    campoDataFim?.reportValidity();
                    return;
                }

                if (!formularioEvento.checkValidity()) {
                    formularioEvento.reportValidity();
                    return;
                }

                modalSucesso?.showModal();



            });


            atualizarPreviewCompleta();
        });
    </script>
    @endunless

</body>

</html>
