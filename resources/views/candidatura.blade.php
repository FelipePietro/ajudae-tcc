@php
    $limiteVisivel = 3;

    $candidaturasPendentes = [
        [
            'icone' => '🌿',
            'titulo' => 'Limpeza de Parques',
            'organizacao' => 'ONG Verde SP · Carlos Ferreira (Organizador)',
            'tags' => ['📅 15 mai 2025 · 08h–16h', '📍 Horto Florestal, SP'],
            'mensagem' => 'Tenho muito interesse em causas ambientais e já participei de ações similares. Gostaria de contribuir com minha energia...',
            'idEvento' => 1,
            'idCandidatura' => 1,
            'dataRodape' => 'Candidatura enviada em 28 abr. 2025',
        ],
        [
            'icone' => '📚',
            'titulo' => 'Aulas de Reforço Escolar',
            'organizacao' => 'Org. Educar · Ana Marques',
            'tags' => ['📅 18 mai 2025 · 14h–17h', '📍 Santo André, SP'],
            'mensagem' => 'Sou estudante de pedagogia e adoraria aplicar meu conhecimento ajudando crianças com dificuldades de aprendizagem...',
            'idEvento' => 2,
            'idCandidatura' => 2,
            'dataRodape' => 'Candidatura enviada em 27 abr. 2025',
        ],
    ];

    $candidaturasAprovadas = [
        [
            'icone' => '💉',
            'titulo' => 'Campanha de Vacinação Comunitária',
            'organizacao' => 'Cruz Vermelho BR',
            'tags' => ['📅 10 abr. 2025 · Concluído'],
            'xp' => 150,
            'idEvento' => 3,
            'termoUrl' => '/termo/1',
            'dataRodape' => 'Aprovado em 05 abr. 2025 · Concluído',
        ],
        [
            'icone' => '🍲',
            'titulo' => 'Distribuição de Marmitas',
            'organizacao' => 'Ação Fome Zero',
            'tags' => ['📅 22 mar. 2025 · Concluído'],
            'xp' => 120,
            'idEvento' => 5,
            'termoUrl' => '/termo/2',
            'dataRodape' => 'Aprovado em 18 mar. 2025 · Concluído',
        ],
        [
            'icone' => '📖',
            'titulo' => 'Contação de Histórias Infantil',
            'organizacao' => 'Biblioteca Comunitária Vila Nova',
            'tags' => ['📅 05 mar. 2025 · Concluído'],
            'xp' => 90,
            'idEvento' => 6,
            'termoUrl' => '/termo/3',
            'dataRodape' => 'Aprovado em 01 mar. 2025 · Concluído',
        ],
        [
            'icone' => '🐾',
            'titulo' => 'Feira de Adoção de Animais',
            'organizacao' => 'ONG Patas Solidárias',
            'tags' => ['📅 14 fev. 2025 · Concluído'],
            'xp' => 100,
            'idEvento' => 7,
            'termoUrl' => '/termo/4',
            'dataRodape' => 'Aprovado em 10 fev. 2025 · Concluído',
        ],
        [
            'icone' => '🧓',
            'titulo' => 'Visita a Abrigo de Idosos',
            'organizacao' => 'Lar São Vicente',
            'tags' => ['📅 20 jan. 2025 · Concluído'],
            'xp' => 110,
            'idEvento' => 8,
            'termoUrl' => '/termo/5',
            'dataRodape' => 'Aprovado em 16 jan. 2025 · Concluído',
        ],
    ];

    $candidaturasRecusadas = [
        [
            'icone' => '🎨',
            'titulo' => 'Mutirão de Pintura',
            'organizacao' => 'Organizador ind. · Carlos Ferreira',
            'tags' => ['📅 02 mai 2025'],
            'motivoRecusa' => 'Vagas preenchidas por perfis com maior compatibilidade de habilidades. Seu perfil pode ser mais adequado a eventos de Meio Ambiente.',
            'idEvento' => 4,
            'idsEventosSimilares' => 4,
            'dataRodape' => 'Recusado em 30 abr. 2025',
        ],
    ];

    $candidaturasDesistidas = [];
@endphp

<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Candidaturas — Ajudaê</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

    {{-- NAVBAR --}}
    <nav class="flex w-full min-w-0 items-center justify-between border-b border-gray-200 bg-[#f5f5f0] px-4 py-3 sm:h-[60px] sm:px-8">
        <a href="/" class="shrink-0 font-serif text-lg font-bold no-underline">
            <span class="brand-aju">Ajud</span><span class="brand-dae">aê</span>
        </a>
        <span class="hidden min-w-0 text-sm text-gray-600 sm:block">&lt; Feed de eventos</span>
        <div class="ml-auto flex shrink-0 items-center gap-2.5">
            <a href="/perfil" class="flex h-8 w-8 items-center justify-center rounded-full bg-verde-600 text-xs font-bold uppercase text-white no-underline">LP</a>
            <span class="hidden text-sm font-medium text-verde-900 sm:block">Lucas Pereira</span>
        </div>
    </nav>

    {{-- BARRA DE XP --}}
    <div class="flex w-full min-w-0 items-center gap-2 border-b border-gray-200 bg-[#f5f5f0] px-4 py-2 text-[0.8rem] text-gray-600 sm:gap-3 sm:px-8">
        <span class="hidden sm:block">Nível 3 — Aprendiz</span>
        <span class="sm:hidden text-xs">Nv.3</span>
        <div class="min-w-0 flex-1 overflow-hidden rounded-full bg-gray-300 sm:max-w-[160px]">
            <div class="h-2 rounded-full bg-verde-900" style="width: 75%"></div>
        </div>
        <span class="shrink-0 whitespace-nowrap text-[0.78rem] text-gray-400">450 / 600 XP</span>
    </div>

    {{-- WRAPPER — responsivo: 1 coluna mobile, 2 colunas desktop --}}
    <div class="mx-auto my-5 flex w-full min-w-0 max-w-[1100px] flex-col gap-5 px-4 sm:my-6 sm:gap-6 sm:px-6 lg:my-8 lg:grid lg:grid-cols-[minmax(0,1fr)_300px] lg:gap-8">

        {{-- COLUNA PRINCIPAL --}}
        <div>

            <h1 class="m-0 mb-1 text-xl font-bold text-verde-900">Minhas candidaturas</h1>
            <p class="m-0 mb-5 text-[0.85rem] text-gray-400 sm:mb-6">Acompanhe o status de todas as suas candidaturas</p>

            {{-- RESUMO — 2 colunas mobile, 4 desktop --}}
            <div class="mb-5 grid min-w-0 grid-cols-2 gap-3 sm:mb-6 sm:grid-cols-4">
                <div class="flex min-w-0 flex-col items-center gap-1 rounded-[10px] border border-gray-200 bg-white p-3 text-center sm:p-4">
                    <span class="text-[1.35rem] font-bold text-verde-900 sm:text-2xl">{{ count($candidaturasPendentes) }}</span>
                    <span class="text-xs leading-tight text-gray-400">Pendentes</span>
                </div>
                <div class="flex min-w-0 flex-col items-center gap-1 rounded-[10px] border border-gray-200 bg-white p-3 text-center sm:p-4">
                    <span class="text-[1.35rem] font-bold text-verde-900 sm:text-2xl">{{ count($candidaturasAprovadas) }}</span>
                    <span class="text-xs leading-tight text-gray-400">Aprovados</span>
                </div>
                <div class="flex min-w-0 flex-col items-center gap-1 rounded-[10px] border border-gray-200 bg-white p-3 text-center sm:p-4">
                    <span class="text-[1.35rem] font-bold text-recusado-600 sm:text-2xl">{{ count($candidaturasRecusadas) }}</span>
                    <span class="text-xs leading-tight text-gray-400">Recusados</span>
                </div>
                <div class="flex min-w-0 flex-col items-center gap-1 rounded-[10px] border border-gray-200 bg-white p-3 text-center sm:p-4">
                    <span class="text-[1.35rem] font-bold text-verde-900 sm:text-2xl">{{ count($candidaturasDesistidas) }}</span>
                    <span class="text-xs leading-tight text-gray-400">Desistências</span>
                </div>
            </div>

            {{-- ABAS — scroll horizontal em mobile --}}
            <div class="overflow-x-auto">
                <div class="mb-5 flex w-max min-w-full gap-0 border-b-2 border-gray-200" data-mc-tabs>
                    <button type="button" class="shrink-0 whitespace-nowrap border-0 border-b-2 border-transparent bg-transparent px-3 py-2.5 text-[0.82rem] text-gray-400 -mb-0.5 cursor-pointer font-[Outfit,sans-serif] sm:px-4 sm:text-[0.85rem] aba ativa" data-aba="pendente">Pendente ({{ count($candidaturasPendentes) }})</button>
                    <button type="button" class="shrink-0 whitespace-nowrap border-0 border-b-2 border-transparent bg-transparent px-3 py-2.5 text-[0.82rem] text-gray-400 -mb-0.5 cursor-pointer font-[Outfit,sans-serif] sm:px-4 sm:text-[0.85rem] aba" data-aba="aprovado">Aprovado ({{ count($candidaturasAprovadas) }})</button>
                    <button type="button" class="shrink-0 whitespace-nowrap border-0 border-b-2 border-transparent bg-transparent px-3 py-2.5 text-[0.82rem] text-gray-400 -mb-0.5 cursor-pointer font-[Outfit,sans-serif] sm:px-4 sm:text-[0.85rem] aba" data-aba="recusado">Recusado ({{ count($candidaturasRecusadas) }})</button>
                    <button type="button" class="shrink-0 whitespace-nowrap border-0 border-b-2 border-transparent bg-transparent px-3 py-2.5 text-[0.82rem] text-gray-400 -mb-0.5 cursor-pointer font-[Outfit,sans-serif] sm:px-4 sm:text-[0.85rem] aba" data-aba="desistencias">Desistências ({{ count($candidaturasDesistidas) }})</button>
                </div>
            </div>

            {{-- SEÇÃO PENDENTES --}}
            <section data-secao="pendente" class="min-w-0">
                <p class="m-0 mb-2.5 mt-1 text-[0.82rem] leading-relaxed text-gray-400">Candidaturas pendentes ({{ count($candidaturasPendentes) }})</p>

                @foreach (array_slice($candidaturasPendentes, 0, $limiteVisivel) as $c)
                    <x-candidatura-card
                        :icone="$c['icone']"
                        :titulo="$c['titulo']"
                        :organizacao="$c['organizacao']"
                        status="pendente"
                        :tags="$c['tags']"
                        :mensagem="$c['mensagem']"
                        :idEvento="$c['idEvento']"
                        :idCandidatura="$c['idCandidatura']"
                        :dataRodape="$c['dataRodape']"
                    />
                @endforeach

                @if (count($candidaturasPendentes) > $limiteVisivel)
                    <div data-extra class="hidden">
                        @foreach (array_slice($candidaturasPendentes, $limiteVisivel) as $c)
                            <x-candidatura-card
                                :icone="$c['icone']"
                                :titulo="$c['titulo']"
                                :organizacao="$c['organizacao']"
                                status="pendente"
                                :tags="$c['tags']"
                                :mensagem="$c['mensagem']"
                                :idEvento="$c['idEvento']"
                                :idCandidatura="$c['idCandidatura']"
                                :dataRodape="$c['dataRodape']"
                            />
                        @endforeach
                    </div>
                    <div class="text-center my-2 mb-4">
                        <button type="button" class="btn-secundario btn-sm" data-toggle-extra
                            data-label-mais="Ver todas as {{ count($candidaturasPendentes) }} pendentes →"
                            data-label-menos="Ver menos ↑">
                            Ver todas as {{ count($candidaturasPendentes) }} pendentes →
                        </button>
                    </div>
                @endif
            </section>

            {{-- SEÇÃO APROVADOS --}}
            <section data-secao="aprovado" class="hidden min-w-0">
                <p class="m-0 mb-2.5 mt-1 text-[0.82rem] leading-relaxed text-gray-400">Candidaturas aprovadas ({{ count($candidaturasAprovadas) }})</p>

                @foreach (array_slice($candidaturasAprovadas, 0, $limiteVisivel) as $c)
                    <x-candidatura-card
                        :icone="$c['icone']"
                        :titulo="$c['titulo']"
                        :organizacao="$c['organizacao']"
                        status="aprovado"
                        :tags="$c['tags']"
                        :xp="$c['xp']"
                        :idEvento="$c['idEvento']"
                        :termoUrl="$c['termoUrl']"
                        :dataRodape="$c['dataRodape']"
                    />
                @endforeach

                @if (count($candidaturasAprovadas) > $limiteVisivel)
                    <div data-extra class="hidden">
                        @foreach (array_slice($candidaturasAprovadas, $limiteVisivel) as $c)
                            <x-candidatura-card
                                :icone="$c['icone']"
                                :titulo="$c['titulo']"
                                :organizacao="$c['organizacao']"
                                status="aprovado"
                                :tags="$c['tags']"
                                :xp="$c['xp']"
                                :idEvento="$c['idEvento']"
                                :termoUrl="$c['termoUrl']"
                                :dataRodape="$c['dataRodape']"
                            />
                        @endforeach
                    </div>
                    <div class="text-center my-2 mb-4">
                        <button type="button" class="btn-secundario btn-sm" data-toggle-extra
                            data-label-mais="Ver todas as {{ count($candidaturasAprovadas) }} aprovadas →"
                            data-label-menos="Ver menos ↑">
                            Ver todas as {{ count($candidaturasAprovadas) }} aprovadas →
                        </button>
                    </div>
                @endif
            </section>

            {{-- SEÇÃO RECUSADOS --}}
            <section data-secao="recusado" class="hidden min-w-0">
                <p class="m-0 mb-2.5 mt-1 text-[0.82rem] leading-relaxed text-gray-400">Candidaturas recusadas ({{ count($candidaturasRecusadas) }})</p>

                @foreach (array_slice($candidaturasRecusadas, 0, $limiteVisivel) as $c)
                    <x-candidatura-card
                        :icone="$c['icone']"
                        :titulo="$c['titulo']"
                        :organizacao="$c['organizacao']"
                        status="recusado"
                        :tags="$c['tags']"
                        :motivoRecusa="$c['motivoRecusa']"
                        :idEvento="$c['idEvento']"
                        :idsEventosSimilares="$c['idsEventosSimilares']"
                        :dataRodape="$c['dataRodape']"
                    />
                @endforeach
            </section>

            {{-- SEÇÃO DESISTÊNCIAS --}}
            <section data-secao="desistencias" class="hidden min-w-0">
                <p class="m-0 mb-2.5 mt-1 text-[0.82rem] leading-relaxed text-gray-400">Desistências ({{ count($candidaturasDesistidas) }})</p>

                @if (count($candidaturasDesistidas) === 0)
                    <div class="rounded-xl border border-dashed border-gray-200 py-10 text-center text-sm text-gray-400">
                        Você ainda não desistiu de nenhuma candidatura.
                    </div>
                @endif
            </section>

        </div>

        {{-- SIDEBAR — aparece abaixo em mobile, à direita em desktop --}}
        <aside class="w-full min-w-0">

            <div class="mb-4 flex min-w-0 flex-col gap-3 rounded-xl border border-gray-200 bg-white p-4 sm:p-5">
                <p class="m-0 text-[0.85rem] font-medium text-verde-900">Seu progresso</p>
                <div class="flex items-center justify-between text-[0.85rem]">
                    <span><strong>Nível 3 — Aprendiz</strong></span>
                    <span class="text-[0.82rem] text-gray-400">450 XP</span>
                </div>
                <div class="h-2 w-full overflow-hidden rounded-full bg-gray-300">
                    <div class="h-full rounded-full bg-verde-900" style="width: 75%"></div>
                </div>
                <div class="flex justify-between gap-2 text-[0.7rem] text-gray-400">
                    <span>0 XP</span>
                    <span>150 XP para Nível 4</span>
                    <span>600 XP</span>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="flex min-w-0 flex-col items-center gap-0.5 rounded-[10px] border border-gray-100 bg-[#f5f5f0] p-3">
                        <span class="text-xl font-bold text-verde-900">8</span>
                        <span class="text-xs text-gray-400">Eventos</span>
                    </div>
                    <div class="flex min-w-0 flex-col items-center gap-0.5 rounded-[10px] border border-gray-100 bg-[#f5f5f0] p-3">
                        <span class="text-xl font-bold text-verde-900">3</span>
                        <span class="text-xs text-gray-400">Badges</span>
                    </div>
                </div>
            </div>

            <div class="mb-4 flex min-w-0 flex-col gap-3 rounded-xl border border-gray-200 bg-white p-4 sm:p-5">
                <p class="m-0 text-[0.85rem] font-medium text-verde-900">Atividade recente</p>
                <ul class="m-0 flex list-none flex-col gap-3 p-0">
                    <li class="flex items-start gap-2.5">
                        <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-dourado-600"></span>
                        <div>
                            <p class="m-0 mb-0.5 text-[0.8rem] text-gray-700">Candidatura enviada — Limpeza de Parques</p>
                            <span>28 abr</span>
                        </div>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-dourado-600"></span>
                        <div>
                            <p class="m-0 mb-0.5 text-[0.8rem] text-gray-700">Candidatura enviada — Aulas de Reforço</p>
                            <span>27 abr</span>
                        </div>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-verde-600"></span>
                        <div>
                            <p class="m-0 mb-0.5 text-[0.8rem] text-gray-700">+150 XP — Campanha de Vacinação</p>
                            <span>10 abr</span>
                        </div>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-verde-600"></span>
                        <div>
                            <p class="m-0 mb-0.5 text-[0.8rem] text-gray-700">Badge 🌱 conquistado — Plantador de Sementes</p>
                            <span>10 abr</span>
                        </div>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-recusado-600"></span>
                        <div>
                            <p class="m-0 mb-0.5 text-[0.8rem] text-gray-700">Candidatura recusada — Mutirão de Pintura</p>
                            <span>30 mar</span>
                        </div>
                    </li>
                </ul>
                <a href="/feed" class="mt-2 block rounded-full bg-verde-900 px-6 py-2.5 text-center text-sm font-medium text-white no-underline">🌐 Buscar novos eventos</a>
            </div>

        </aside>

    </div>

    <x-footer />

    <script>
        (function () {
            const tabsWrapper = document.querySelector('[data-mc-tabs]');
            if (!tabsWrapper) return;

            const abas = tabsWrapper.querySelectorAll('[data-aba]');
            const secoes = document.querySelectorAll('[data-secao]');

            function ativarAba(status) {
                abas.forEach(function (aba) {
                    const ativa = aba.dataset.aba === status;
                    aba.classList.toggle('text-verde-900', ativa);
                    aba.classList.toggle('font-semibold', ativa);
                    aba.classList.toggle('border-verde-900', ativa);
                    aba.classList.toggle('text-gray-400', !ativa);
                });
                secoes.forEach(function (secao) {
                    secao.classList.toggle('hidden', secao.dataset.secao !== status);
                });
            }

            abas.forEach(function (aba) {
                aba.addEventListener('click', function () {
                    ativarAba(aba.dataset.aba);
                });
            });

            document.querySelectorAll('[data-toggle-extra]').forEach(function (botao) {
                const secao = botao.closest('[data-secao]');
                const extra = secao ? secao.querySelector('[data-extra]') : null;
                if (!extra) return;

                botao.addEventListener('click', function () {
                    const estaEscondido = extra.classList.toggle('hidden');
                    botao.textContent = estaEscondido
                        ? botao.dataset.labelMais
                        : botao.dataset.labelMenos;
                });
            });
        })();
    </script>

</body>

</html>