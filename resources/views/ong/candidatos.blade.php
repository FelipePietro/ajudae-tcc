<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Candidatos — Ajudae</title>

    @vite([
        'resources/css/app.css',
        'resources/css/painel_ong.css',
        'resources/js/app.js'
    ])
</head>

<body data-events-page class="min-h-screen bg-[#F7F6F2]">

    {{-- ========================================================= --}}
    {{-- NAVBAR ONG --}}
    {{-- ========================================================= --}}

    <x-context-navbar default-context="ong" />


    {{-- ========================================================= --}}
    {{-- CONTEÚDO --}}
    {{-- ========================================================= --}}



    <main class="ong-page-content">


            <x-breadcrumb-ong


        :itens="[
            [
                'label' => 'Meus eventos',
                'route' => 'ong.eventos.index'
            ],
            [
                'label' => 'Candidatos'
            ]
        ]"
    />
<x-event-state :flow="$flow" />

        <div class="px-4 py-6 sm:px-6 lg:px-7">


            {{-- ================================================= --}}
            {{-- EVENTO --}}
            {{-- ================================================= --}}

            <h1 class="text-2xl font-semibold mb-5">Gestão de candidatos</h1><x-organizer-scope :flow="$flow" />
            @foreach($flow['candidate_events'] as $candidateEvent)
                <x-agenda-candidates :flow="$candidateEvent" :expanded="$loop->first" />
            @endforeach
        </div>
    </main>


    {{-- ========================================================= --}}
    {{-- FILTROS VISUAIS --}}
    {{-- ========================================================= --}}



</body>

</html>
