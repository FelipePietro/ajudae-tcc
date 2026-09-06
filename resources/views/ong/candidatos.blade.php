<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Candidatos — Ajudaê</title>

    @vite([
        'resources/css/app.css',
        'resources/css/painel_ong.css',
        'resources/js/app.js'
    ])
</head>

<body class="min-h-screen bg-[#F7F6F2]">

    {{-- ========================================================= --}}
    {{-- NAVBAR ONG --}}
    {{-- ========================================================= --}}

    <x-navbar-ong
        ativo="candidatos"
        :nome="$nomeUsuario ?? 'Carlos Ferreira'"
        :ong="$ong['nome_fantasia'] ?? 'Instituto Esperança'"
        :iniciais="$iniciaisUsuario ?? 'CF'"
        :eventos-pendentes="$eventosCount ?? 3"
        :candidatos-pendentes="$candidatosPendentes ?? 5"
        :notificacoes="$notificacoesCount ?? 4"
    />


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

        <div class="px-4 py-6 sm:px-6 lg:px-7">


            {{-- ================================================= --}}
            {{-- EVENTO --}}
            {{-- ================================================= --}}

            <section class="cand-evento">

                <div class="cand-evento-info">

                    <span
                        class="cand-evento-folha"
                        aria-hidden="true"
                    >

                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                        >
                            <path
                                d="M12 3c1.8 3.2 2.2 5.4 1.4 7.2C12.6 12 11 13 9.5 14.2 8 15.4 7 17 7 19c2.4-1 4.4-1.2 6.2-.4 1.6.7 2.8 2.2 3.8 4.2 1.6-4.6.6-8.2-1.4-10.2C13.8 10.8 12.6 9.4 12 3Z"
                                fill="#2F6A49"
                            />
                        </svg>

                    </span>


                    <div>

                        <h1>
                            {{ $evento['nome'] ?? 'Limpeza de Parques' }}
                        </h1>


                        <p>

                            <span>
                                {{ $evento['data'] ?? '15 maio 2025, 08:00–16:00' }}
                            </span>

                            <span>
                                {{ $evento['local'] ?? 'Horto Florestal, São Paulo, SP' }}
                            </span>

                            <span class="cand-status">
                                {{ $evento['status'] ?? 'Publicado' }}
                            </span>

                        </p>

                    </div>

                </div>


                <div class="cand-evento-stats">

                    <div>

                        <strong>
                            {{
                                collect($candidatos ?? [])
                                    ->where('status', 'pendente')
                                    ->count()
                            }}
                        </strong>

                        <span>
                            Pendentes
                        </span>

                    </div>


                    <div>

                        <strong>
                            {{
                                collect($candidatos ?? [])
                                    ->where('status', 'aprovado')
                                    ->count()
                            }}
                        </strong>

                        <span>
                            Aprovados
                        </span>

                    </div>


                    <div>

                        <strong>
                            {{ $evento['vagas_total'] ?? 30 }}
                        </strong>

                        <span>
                            Vagas total
                        </span>

                    </div>

                </div>

            </section>


            {{-- ================================================= --}}
            {{-- ABAS --}}
            {{-- ================================================= --}}

            <div class="cand-tabs">

                <button
                    type="button"
                    class="is-active"
                    data-tab="pendente"
                >

                    Pendente

                    <small>
                        {{
                            collect($candidatos ?? [])
                                ->where('status', 'pendente')
                                ->count()
                        }}
                    </small>

                </button>


                <button
                    type="button"
                    data-tab="aprovado"
                >

                    Aprovado

                    <small>
                        {{
                            collect($candidatos ?? [])
                                ->where('status', 'aprovado')
                                ->count()
                        }}
                    </small>

                </button>


                <button
                    type="button"
                    data-tab="recusado"
                >

                    Recusado

                    <small>
                        {{
                            collect($candidatos ?? [])
                                ->where('status', 'recusado')
                                ->count()
                        }}
                    </small>

                </button>


                <button
                    type="button"
                    data-tab="presenca"
                >

                    Confirmar presença

                    <small>
                        {{
                            collect($candidatos ?? [])
                                ->where('status', 'presenca')
                                ->count()
                        }}
                    </small>

                </button>

            </div>


            {{-- ================================================= --}}
            {{-- TOOLBAR --}}
            {{-- ================================================= --}}

            <div class="cand-toolbar">

                <label class="cand-busca">

                    <span aria-hidden="true">
                        ⌕
                    </span>

                    <input
                        id="busca-candidato"
                        type="search"
                        placeholder="Buscar candidato..."
                    >

                </label>


                <button
                    type="button"
                    class="cand-chip is-on"
                >
                    Todos os níveis
                </button>


                <button
                    type="button"
                    id="ordenar-xp"
                    class="cand-chip"
                >
                    Alta XP primeiro
                </button>


                <form
                    action="{{ route('ong.candidatos.aprovar-lote') }}"
                    method="POST"
                >

                    @csrf

                    <input
                        type="hidden"
                        name="evento_id"
                        value="{{ $evento['id'] ?? $evento['evento_id'] ?? 1 }}"
                    >

                    <button
                        type="submit"
                        class="cand-aprovar-lote"
                    >
                        Aprovar selecionados
                    </button>

                </form>

            </div>


            {{-- ================================================= --}}
            {{-- LISTA --}}
            {{-- ================================================= --}}

            <div
                id="lista-candidatos"
                class="cand-lista"
            >

                @forelse($candidatos ?? [] as $candidato)

                    @php

                        $candidatoId =
                            $candidato['id']
                            ?? $candidato['candidatura_id']
                            ?? 1;

                        $iniciais =
                            $candidato['iniciais']
                            ?? collect(
                                explode(
                                    ' ',
                                    $candidato['nome'] ?? 'Candidato'
                                )
                            )
                                ->filter()
                                ->map(
                                    fn ($parte) =>
                                        mb_strtoupper(
                                            mb_substr($parte, 0, 1)
                                        )
                                )
                                ->take(2)
                                ->implode('');

                    @endphp


                    <article
                        class="cand-card"
                        data-status="{{ $candidato['status'] ?? 'pendente' }}"
                        data-nome="{{ mb_strtolower($candidato['nome'] ?? '') }}"
                        data-xp="{{ $candidato['xp'] ?? 0 }}"
                    >

                        {{-- ================================================= --}}
                        {{-- TOPO --}}
                        {{-- ================================================= --}}

                        <header class="cand-card-topo">

                            <div class="cand-card-pessoa">

                                <span class="cand-avatar">
                                    {{ $iniciais }}
                                </span>


                                <div>

                                    <div class="cand-card-nome">

                                        <strong>
                                            {{ $candidato['nome'] ?? 'Candidato' }}
                                        </strong>

                                        <em>
                                            {{
                                                $candidato['nivel']
                                                ?? 'Nível 1 — Iniciante'
                                            }}
                                        </em>

                                    </div>


                                    <p class="cand-card-meta">

                                        <span>
                                            ★ {{ number_format(
                                                $candidato['xp'] ?? 0,
                                                0,
                                                ',',
                                                '.'
                                            ) }} XP
                                        </span>

                                        <span>
                                            🏅 {{ $candidato['badges'] ?? 0 }}
                                            badges
                                        </span>

                                        <span>
                                            🎟
                                            {{ $candidato['eventos_participados'] ?? 0 }}
                                            eventos participados
                                        </span>

                                    </p>

                                </div>

                            </div>


                            @if(($candidato['habilidades_match'] ?? 0) > 0)

                                <span class="cand-match">

                                    {{ $candidato['habilidades_match'] }}

                                    {{
                                        ($candidato['habilidades_match'] ?? 0) === 1
                                            ? 'habilidade coincide'
                                            : 'habilidades coincidem'
                                    }}

                                </span>

                            @endif

                        </header>


                        {{-- ================================================= --}}
                        {{-- HABILIDADES --}}
                        {{-- ================================================= --}}

                        <p class="cand-hab-label">
                            Habilidades
                        </p>


                        <div class="cand-habs">

                            @foreach($candidato['habilidades'] ?? [] as $habilidade)

                                <span
                                    class="
                                        cand-hab
                                        {{
                                            !empty($habilidade['match'])
                                                ? 'is-match'
                                                : ''
                                        }}
                                    "
                                >

                                    @if(!empty($habilidade['match']))
                                        ✓
                                    @endif

                                    {{
                                        $habilidade['nome']
                                        ?? $habilidade
                                    }}

                                </span>

                            @endforeach

                        </div>


                        {{-- ================================================= --}}
                        {{-- MENSAGEM --}}
                        {{-- ================================================= --}}

                        @if(!empty($candidato['mensagem']))

                            <blockquote>

                                “{{ $candidato['mensagem'] }}”


                                <small>

                                    Candidatura enviada em

                                    {{
                                        $candidato['enviado_em']
                                        ?? 'Data não informada'
                                    }}

                                </small>

                            </blockquote>

                        @endif


                        {{-- ================================================= --}}
                        {{-- AÇÕES --}}
                        {{-- ================================================= --}}

                        <footer class="cand-card-acoes">

                            <div>

                                @if(($candidato['status'] ?? 'pendente') === 'pendente')

                                    <form
                                        action="{{
                                            route(
                                                'ong.candidatos.aprovar',
                                                $candidatoId
                                            )
                                        }}"
                                        method="POST"
                                        class="inline-block"
                                    >

                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="btn btn-primary"
                                        >
                                            Aprovar candidatura
                                        </button>

                                    </form>


                                    <form
                                        action="{{
                                            route(
                                                'ong.candidatos.recusar',
                                                $candidatoId
                                            )
                                        }}"
                                        method="POST"
                                        class="inline-block"
                                    >

                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="btn btn-danger"
                                        >
                                            Recusar
                                        </button>

                                    </form>

                                @elseif(($candidato['status'] ?? '') === 'aprovado')

                                    <span class="status status-published">
                                        ✓ Aprovado
                                    </span>

                                @elseif(($candidato['status'] ?? '') === 'recusado')

                                    <span class="status status-refused">
                                        Recusado
                                    </span>

                                @endif


                                <a
                                    href="#"
                                    class="btn btn-outline"
                                >
                                    Ver perfil completo
                                </a>

                            </div>


                            <small>
                                {{
                                    $candidato['tempo']
                                    ?? ''
                                }}
                            </small>

                        </footer>

                    </article>

                @empty

                    <div
                        class="
                            rounded-xl
                            border border-dashed
                            border-[#D8D8D2]
                            bg-white
                            px-5 py-12
                            text-center
                        "
                    >

                        <p class="text-sm text-[#6B7269]">
                            Nenhum candidato encontrado.
                        </p>

                    </div>

                @endforelse

            </div>


            {{-- ================================================= --}}
            {{-- DICA --}}
            {{-- ================================================= --}}

            <p class="cand-dica">

                <span aria-hidden="true">
                    💡
                </span>

                Confirmar presença após o evento:
                acesse a aba “Confirmar presença” para marcar quem
                efetivamente compareceu. Isso concede XP e badges
                automaticamente aos voluntários confirmados.

            </p>

        </div>

    </main>


    {{-- ========================================================= --}}
    {{-- FILTROS VISUAIS --}}
    {{-- ========================================================= --}}

    <script>

        document.addEventListener('DOMContentLoaded', () => {

            const tabs =
                document.querySelectorAll(
                    '.cand-tabs [data-tab]'
                );

            const cards =
                document.querySelectorAll(
                    '.cand-card'
                );

            const busca =
                document.getElementById(
                    'busca-candidato'
                );

            const ordenarXp =
                document.getElementById(
                    'ordenar-xp'
                );

            const lista =
                document.getElementById(
                    'lista-candidatos'
                );


            let statusAtual = 'pendente';
            let xpDesc = false;


            function filtrar() {

                const termo =
                    (
                        busca?.value
                        ?? ''
                    )
                    .toLowerCase()
                    .trim();


                cards.forEach(card => {

                    const status =
                        card.dataset.status;

                    const nome =
                        card.dataset.nome;

                    const statusCombina =
                        statusAtual === 'todos'
                        || status === statusAtual;

                    const buscaCombina =
                        !termo
                        || nome.includes(termo);


                    card.style.display =
                        statusCombina && buscaCombina
                            ? ''
                            : 'none';

                });

            }


            tabs.forEach(tab => {

                tab.addEventListener(
                    'click',
                    () => {

                        tabs.forEach(
                            item =>
                                item.classList.remove(
                                    'is-active'
                                )
                        );

                        tab.classList.add(
                            'is-active'
                        );

                        statusAtual =
                            tab.dataset.tab;

                        filtrar();

                    }
                );

            });


            busca?.addEventListener(
                'input',
                filtrar
            );


            ordenarXp?.addEventListener(
                'click',
                () => {

                    xpDesc = !xpDesc;

                    ordenarXp.classList.toggle(
                        'is-on',
                        xpDesc
                    );


                    const ordenados =
                        Array
                            .from(cards)
                            .sort(
                                (a, b) => {

                                    const xpA =
                                        Number(
                                            a.dataset.xp
                                            ?? 0
                                        );

                                    const xpB =
                                        Number(
                                            b.dataset.xp
                                            ?? 0
                                        );

                                    return xpDesc
                                        ? xpB - xpA
                                        : xpA - xpB;

                                }
                            );


                    ordenados.forEach(
                        card =>
                            lista.appendChild(card)
                    );

                }
            );


            filtrar();

        });

    </script>

</body>

</html>