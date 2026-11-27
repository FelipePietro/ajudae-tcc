<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Candidaturas — Ajudae</title>
    @vite(['resources/css/app.css', config('identity.enabled') ? 'resources/js/identity.js' : 'resources/js/app.js'])
</head>

<body @unless(config('identity.enabled')) data-events-page @endunless>

    {{-- NAVBAR --}}
    <x-context-navbar />

    @if(config('identity.enabled'))
        <main class="mx-auto grid max-w-[1100px] gap-8 px-6 py-8 lg:grid-cols-[1fr_300px]">
            <section>
                <h1 class="mb-1 text-xl font-bold text-verde-900">Minhas candidaturas</h1>
                <p class="mb-6 text-sm text-[#56594f]">Acompanhe o status de todas as suas candidaturas</p>
                <x-botao-secundario :href="route('participacoes')" size="sm" class="mb-5">Minhas participações</x-botao-secundario>
                <div data-application-history data-url="{{ route('identity.application.index') }}" data-csrf="{{ csrf_token() }}">
                    <p data-history-status role="status"></p>
                    <div data-history-list class="grid gap-4"></div>
                    <x-botao-secundario as="button" type="button" data-history-more hidden>Carregar mais</x-botao-secundario>
                    <template>
                        <article class="account-card">
                            <h2 class="text-lg font-semibold"></h2>
                            <p data-time></p><p data-state class="capitalize font-semibold"></p>
                            <details class="my-3"><summary>Carta de apresentação</summary><p class="whitespace-pre-wrap break-words"></p></details>
                            <x-botao-declined as="button" type="button" size="sm">Desistir desta Agenda</x-botao-declined>
                        </article>
                    </template>
                    <dialog class="m-auto w-[min(448px,calc(100%-2rem))] rounded-xl p-6 backdrop:bg-black/50" aria-labelledby="withdraw-heading">
                        <h2 id="withdraw-heading" class="text-lg font-semibold">Desistir desta Agenda?</h2>
                        <p class="my-3">A desistência fica registrada no histórico. Se houver penalidade, o horário e as regras da Agenda serão considerados.</p>
                        <div class="flex flex-wrap gap-2">
                            <form method="dialog"><x-botao-secundario as="button" type="submit">Voltar</x-botao-secundario></form>
                            <x-botao-declined as="button" type="button" data-withdraw>Confirmar desistência</x-botao-declined>
                        </div>
                        <p data-withdraw-error role="alert"></p>
                    </dialog>
                </div>
            </section>
            <aside>
                <section class="sidebar-card"><h2 class="sidebar-titulo">Seu progresso</h2><p>O progresso e as conquistas ainda estão indisponíveis.</p></section>
                <section class="sidebar-card"><h2 class="sidebar-titulo">Atividade recente</h2><p>A consulta da atividade ainda está indisponível.</p></section>
            </aside>
        </main>
    @elseif(isset($participation))
        <main class="max-w-[1100px] w-full mx-auto py-8 px-6">
            <header class="mb-6">
                <h1 class="text-xl font-bold text-verde-900">Minhas candidaturas</h1>
                <p class="my-3">{{ $participation['title'] }}</p>
                <x-botao-secundario :href="\App\Support\VisualFiveFixtures::personalUrl()">Minhas participações</x-botao-secundario>
            </header>
            <x-participation-agendas :participation="$participation" />
        </main>
    @else
    {{-- BARRA DE XP --}}
    <div class="barra-xp-wrapper">
        <span class="hidden sm:block">Nível 3 — Aprendiz</span>
        <span class="sm:hidden text-xs">Nv.3</span>
        <div class="barra-xp">
            <div class="barra-xp-preenchida" style="width: 75%"></div>
        </div>
        <span class="barra-xp-valor">450 / 600 XP</span>
    </div>

    {{-- MAIN — flex:1 garante que o footer fique no fundo --}}
    <main>
<x-event-state :flow="$flow" />
        <div class="max-w-[1100px] w-full mx-auto py-8 px-6 grid grid-cols-1 gap-8 lg:grid-cols-[1fr_300px]">

            {{-- COLUNA PRINCIPAL --}}
            <div>
                <h1 class="text-xl font-bold text-verde-900 m-0 mb-1">Minhas candidaturas</h1>
                <p class="text-[0.85rem] text-gray-400 m-0 mb-6">Acompanhe o status de todas as suas candidaturas</p>

                @if(request()->query('contexto') === 'pessoa')
                    <x-botao-secundario :href="\App\Support\VisualFiveFixtures::personalUrl()" size="sm" class="mb-5">Minhas participações</x-botao-secundario>
                @endif
                <x-agenda-applications :flow="$flow" />
            </div>
            {{-- SIDEBAR --}}
            <aside>
                <div class="sidebar-card">
                    <p class="sidebar-titulo">Seu progresso</p>
                    <div class="sidebar-nivel">
                        <span><strong>Nível 3 — Aprendiz</strong></span>
                        <span class="sidebar-xp">450 XP</span>
                    </div>
                    <div class="sidebar-barra">
                        <div class="sidebar-barra-fill" style="width: 75%"></div>
                    </div>
                    <div class="sidebar-barra-labels">
                        <span>0 XP</span>
                        <span>150 XP para Nível 4</span>
                        <span>600 XP</span>
                    </div>
                    <div class="sidebar-stats">
                        <div class="sidebar-stat">
                            <span class="sidebar-stat-numero">8</span>
                            <span class="sidebar-stat-label">Eventos</span>
                        </div>
                        <div class="sidebar-stat dourado">
                            <span class="sidebar-stat-numero">3</span>
                            <span class="sidebar-stat-label">Badges</span>
                        </div>
                    </div>
                </div>

                <div class="sidebar-card">
                    <p class="sidebar-titulo">Atividade recente</p>
                    <ul class="atividade-lista">
                        <li class="atividade-item">
                            <span class="atividade-dot pendente"></span>
                            <div>
                                <p>Candidatura enviada — Limpeza de Parques</p>
                                <span>28 abr</span>
                            </div>
                        </li>
                        <li class="atividade-item">
                            <span class="atividade-dot pendente"></span>
                            <div>
                                <p>Candidatura enviada — Aulas de Reforço</p>
                                <span>27 abr</span>
                            </div>
                        </li>
                        <li class="atividade-item">
                            <span class="atividade-dot xp"></span>
                            <div>
                                <p>+150 XP — Campanha de Vacinação</p>
                                <span>10 abr</span>
                            </div>
                        </li>
                        <li class="atividade-item">
                            <span class="atividade-dot xp"></span>
                            <div>
                                <p>Badge 🌱 conquistado — Plantador de Sementes</p>
                                <span>10 abr</span>
                            </div>
                        </li>
                        <li class="atividade-item">
                            <span class="atividade-dot recusado"></span>
                            <div>
                                <p>Candidatura recusada — Mutirão de Pintura</p>
                                <span>30 mar</span>
                            </div>
                        </li>
                    </ul>
                    <x-botao-primario href="/feed">🌐 Buscar novos eventos</x-botao-primario>
                </div>
            </aside>

        </div>
    </main>
    @endif


    <x-footer />



</body>

</html>
