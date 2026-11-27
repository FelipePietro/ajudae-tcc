@php
    $integrated = config('identity.enabled');
@endphp
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
        $integrated ? 'resources/js/identity.js' : 'resources/js/app.js'
    ])
</head>

<body @unless($integrated) data-events-page @endunless class="min-h-screen bg-[#F7F6F2]">

    {{-- ========================================================= --}}
    {{-- NAVBAR ONG --}}
    {{-- ========================================================= --}}

    <x-context-navbar default-context="ong" :surface="$integrated ? 'institution' : 'context'" />


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
@unless($integrated)<x-event-state :flow="$flow" />@endunless

        <div class="px-4 py-6 sm:px-6 lg:px-7">


            {{-- ================================================= --}}
            {{-- EVENTO --}}
            {{-- ================================================= --}}

            <h1 class="text-2xl font-semibold mb-5">Gestão de candidatos</h1>@unless($integrated)<x-organizer-scope :flow="$flow" />@endunless
            @if($integrated)
                <x-managed-events candidates-only />
            @else
            @foreach($flow['candidate_events'] as $candidateEvent)
                <x-agenda-candidates :flow="$candidateEvent" :expanded="$loop->first" />
            @endforeach
            @endif
        </div>
    </main>


    {{-- ========================================================= --}}
    {{-- FILTROS VISUAIS --}}
    {{-- ========================================================= --}}



</body>

</html>
