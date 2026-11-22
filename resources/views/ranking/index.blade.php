<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Ranking | Ajudae</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body class="min-h-screen bg-[#f7f5ef] font-outfit text-[#292820]">
<x-context-navbar default-context="visitante" />
<main class="mx-auto w-full max-w-[900px] px-4 py-8 sm:px-6 sm:py-10 lg:py-12" data-ranking>
    @php $rankingData = ['fixtures'=>$fixtures,'people'=>$people,'ownId'=>in_array(request()->query('contexto'), \App\Support\VisualFiveFixtures::PERSON_CONTEXTS, true) ? 1 : null]; @endphp
    <script type="application/json" data-ranking-fixture>@json($rankingData)</script>
    <h1 class="text-3xl font-semibold sm:text-4xl" style="font-family: 'Fraunces', serif">🏆 Ranking</h1>
    <p class="mt-2 font-poppins text-sm text-[#5d594f]">Classificação exclusivamente por XP acumulado nas participações deste percurso.</p>
    <noscript>Ative o JavaScript para consultar a classificação.</noscript>
    <p data-ranking-error role="alert" hidden>Não foi possível consultar a classificação. Recarregue para tentar novamente.</p>
    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="account-field flex-1 min-w-0"><label for="ranking-city">Cidade</label><select id="ranking-city" data-ranking-city><option value="">Global · Todas as cidades</option><option>Guarulhos</option><option>São Paulo</option><option>Campinas</option></select></div>
        <div class="account-field flex-1 min-w-0"><label for="ranking-search">Buscar pessoa</label><input id="ranking-search" type="search" maxlength="80" data-ranking-search placeholder="Nome da pessoa"></div>
    </div>
    <div data-ranking-content hidden>
        @if(in_array(request()->query('contexto'), \App\Support\VisualFiveFixtures::PERSON_CONTEXTS, true))
            <section class="mt-6 rounded-xl bg-[#17392a] px-5 py-4 text-white shadow-sm"><p data-ranking-own></p></section>
        @endif
        <section class="mt-5 rounded-2xl border border-[#dedacf] bg-white p-5 shadow-sm sm:p-8" data-podium-section>
            <h2 class="text-center font-poppins text-sm text-[#5d594f]" data-ranking-scope>Ranking global</h2>
            <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-3 sm:items-end">
                @foreach([1,0,2] as $index)
                    <div data-podium="{{ $index }}" class="text-center {{ $index === 0 ? 'order-1 sm:order-2' : ($index === 1 ? 'order-2 sm:order-1' : 'order-3') }}">
                        @if($index === 0)<span class="text-2xl" aria-hidden="true">👑</span>@endif
                        <div class="mx-auto mt-1 flex items-center justify-center rounded-full bg-[#dcefe5] text-lg font-semibold text-[#17392a] {{ $index === 0 ? 'h-20 w-20 border-2 border-[#e5a11b]' : 'h-16 w-16' }}" data-initials aria-hidden="true"></div>
                        <p class="mt-3 font-poppins text-sm font-semibold break-words" data-name></p>
                        <p class="mt-1 font-poppins text-xs text-[#5d594f]" data-city></p>
                        <span class="mt-3 inline-flex rounded-full bg-[#fff2d8] px-3 py-1 font-poppins text-xs font-semibold text-[#785012]" data-xp></span>
                        <div class="mx-auto mt-4 flex max-w-[150px] items-center justify-center rounded-t-xl text-xl {{ $index === 0 ? 'h-24 bg-[#f3b31f]' : ($index === 1 ? 'h-16 bg-[#d6d3c9]' : 'h-12 bg-[#b98a58]') }}" aria-label="{{ $index+1 }}º lugar">{{ ['🥇','🥈','🥉'][$index] }}</div>
                    </div>
                @endforeach
            </div>
        </section>
        <section class="mt-5 overflow-hidden rounded-2xl border border-[#dedacf] bg-white shadow-sm" aria-label="Classificação por XP">
            <div class="grid grid-cols-[36px_1fr_85px] gap-2 border-b border-[#e5e1d8] bg-[#faf9f6] px-4 py-3 font-poppins text-xs text-[#5d594f]"><span>#</span><span>Pessoa</span><span class="text-right">XP</span></div>
            <ol class="list-none p-0" data-ranking-rows></ol>
            <p class="p-5 text-sm" role="status" data-ranking-empty hidden>Nenhuma pessoa encontrada. Escolha outra cidade ou altere a busca.</p>
        </section>
        <p class="mt-4 text-sm" role="status" aria-live="polite" data-ranking-count></p>
        <nav class="flex flex-wrap gap-3 mt-3" aria-label="Páginas do ranking"><x-botao-secundario as="button" data-ranking-prev disabled>Anterior</x-botao-secundario><x-botao-secundario as="button" data-ranking-next>Próxima</x-botao-secundario></nav>
        <p class="mt-5 rounded-xl border border-[#cfe4d8] bg-[#edf7f2] px-4 py-3 font-poppins text-xs leading-5 text-[#365541]">Somente Pessoas aprovadas. Empates seguem o identificador estável de cadastro em ordem crescente. A busca mantém a posição no recorte da cidade. Nível indisponível. Avaliação e confiabilidade são independentes da classificação.</p>
    </div>
    <template><li class="grid grid-cols-[36px_1fr_85px] items-center gap-2 border-b border-[#ebe7de] px-4 py-4 last:border-b-0"><strong class="text-sm text-[#31513e]" data-position></strong><div class="min-w-0"><p class="font-poppins text-sm font-medium break-words" data-name></p><p class="mt-1 text-xs text-[#5d594f]" data-city></p></div><strong class="text-right text-sm text-[#17392a]" data-xp></strong></li></template>
</main>
</body></html>
