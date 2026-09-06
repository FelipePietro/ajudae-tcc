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
    <nav class="navbar">
        <a href="/feed" class="navbar-brand">
            <span class="brand-aju">Ajud</span><span class="brand-dae">aê</span>
        </a>
        <a href="/feed" class="navbar-centro">
            <span class="hidden sm:inline">← Feed de eventos</span>
            <span class="sm:hidden">←</span>
        </a>

        <div class="navbar-direita">
            <a href="/perfil" class="avatar">LP</a>
            <span class="navbar-nome hidden sm:block">Lucas Pereira</span>
        </div>
    </nav>

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
        <div class="max-w-[1100px] w-full mx-auto py-8 px-6 grid grid-cols-1 gap-8 lg:grid-cols-[1fr_300px]">

            {{-- COLUNA PRINCIPAL --}}
            <div>
                <h1 class="text-xl font-bold text-verde-900 m-0 mb-1">Minhas candidaturas</h1>
                <p class="text-[0.85rem] text-gray-400 m-0 mb-6">Acompanhe o status de todas as suas candidaturas</p>

                {{-- RESUMO --}}
                <div class="grid grid-cols-2 gap-3 mb-6 sm:grid-cols-4">
                    <div class="resumo-card">
                        <span class="resumo-numero">{{ count($candidaturasPendentes) }}</span>
                        <span class="resumo-label">Pendentes</span>
                    </div>
                    <div class="resumo-card">
                        <span class="resumo-numero">{{ count($candidaturasAprovadas) }}</span>
                        <span class="resumo-label">Aprovados</span>
                    </div>
                    <div class="resumo-card">
                        <span class="resumo-numero recusado">{{ count($candidaturasRecusadas) }}</span>
                        <span class="resumo-label">Recusados</span>
                    </div>
                    <div class="resumo-card">
                        <span class="resumo-numero">{{ count($candidaturasDesistidas) }}</span>
                        <span class="resumo-label">Desistências</span>
                    </div>
                </div>

                {{-- ABAS --}}
                <div class="overflow-x-auto">
                    <div class="flex gap-0 border-b-2 border-gray-200 mb-5 min-w-max sm:min-w-0" data-mc-tabs>
                        <button type="button" class="aba ativa" data-aba="pendente">Pendente ({{ count($candidaturasPendentes) }})</button>
                        <button type="button" class="aba" data-aba="aprovado">Aprovado ({{ count($candidaturasAprovadas) }})</button>
                        <button type="button" class="aba" data-aba="recusado">Recusado ({{ count($candidaturasRecusadas) }})</button>
                        <button type="button" class="aba" data-aba="desistencias">Desistências ({{ count($candidaturasDesistidas) }})</button>
                    </div>
                </div>

                {{-- SEÇÃO PENDENTES --}}
                <section data-secao="pendente">
                    <p class="mc-secao-label">Candidaturas pendentes ({{ count($candidaturasPendentes) }})</p>

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
                <section data-secao="aprovado" class="hidden">
                    <p class="mc-secao-label">Candidaturas aprovadas ({{ count($candidaturasAprovadas) }})</p>

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
                <section data-secao="recusado" class="hidden">
                    <p class="mc-secao-label">Candidaturas recusadas ({{ count($candidaturasRecusadas) }})</p>

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
                <section data-secao="desistencias" class="hidden">
                    <p class="mc-secao-label">Desistências ({{ count($candidaturasDesistidas) }})</p>

                    @if (count($candidaturasDesistidas) === 0)
                        <div class="text-center text-sm text-gray-400 py-10 border border-dashed border-gray-200 rounded-xl">
                            Você ainda não desistiu de nenhuma candidatura.
                        </div>
                    @endif
                </section>
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
                    <a href="/feed" class="btn-primario btn-block">🌐 Buscar novos eventos</a>
                </div>
            </aside>

        </div>
    </main>

    <x-footer />

    <script>
        (function () {
            const tabsWrapper = document.querySelector('[data-mc-tabs]');
            if (!tabsWrapper) return;

            const abas = tabsWrapper.querySelectorAll('[data-aba]');
            const secoes = document.querySelectorAll('[data-secao]');

            function ativarAba(status) {
                abas.forEach(function (aba) {
                    aba.classList.toggle('ativa', aba.dataset.aba === status);
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