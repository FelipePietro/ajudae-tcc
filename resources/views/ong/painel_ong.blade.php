<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Painel da ONG - Ajudae</title>

    @vite([
        'resources/css/app.css',
        'resources/css/painel_ong.css',
        'resources/js/app.js'
    ])
</head>

<body class="min-h-screen bg-[#F7F6F2]">

    <x-navbar-ong
        ativo="ong"
        :nome="$nomeUsuario ?? 'Carlos Ferreira'"
        :ong="$ong['nome_fantasia'] ?? 'Instituto Esperança'"
        :iniciais="$iniciaisUsuario ?? 'CF'"
        :eventos-pendentes="$eventosCount ?? 3"
        :candidatos-pendentes="$candidatosCount ?? 12"
        :notificacoes="$notificacoesCount ?? 4"
    />

    {{-- ========================================================= --}}
    {{-- CONTEÚDO PRINCIPAL --}}
    {{-- ========================================================= --}}

    <main class="ong-page-content">

        <div class="px-4 py-6 sm:px-6 lg:px-7">


            {{-- ================================================= --}}
            {{-- CABEÇALHO DA ONG --}}
            {{-- ================================================= --}}

            <section class="org-card">

                <div class="org-identity">

                    <div class="org-logo">
                        {{ $ong['icone'] ?? '🌱' }}
                    </div>

                    <div>

                        <h1 class="org-name">
                            {{ $ong['nome'] ?? 'Instituto Esperança Social' }}
                        </h1>

                        <p class="org-sub">

                            {{ $ong['nome_fantasia'] ?? 'Esperança Social' }}

                            ·

                            {{ $ong['cidade'] ?? 'São Paulo' }},
                            {{ $ong['estado'] ?? 'SP' }}

                        </p>


                        <div class="org-tags">

                            @if($ong['ativa'] ?? true)

                                <span class="tag tag-active">
                                    ✓ Ativa
                                </span>

                            @endif


                            <span class="tag tag-cnpj">

                                CNPJ
                                {{ $ong['cnpj'] ?? '11.222.333/0001-81' }}

                            </span>


                            <span class="tag tag-plain">

                                {{ $ong['area_atuacao'] ?? 'Assistência social · Educação' }}

                            </span>


                            @if($ong['verificada'] ?? true)

                                <span class="tag tag-verified">
                                    Conta verificada
                                </span>

                            @endif

                        </div>

                    </div>

                </div>


                <div class="org-actions">

                    <a
                        href="{{ route('ong.perfil.editar') }}"
                        class="btn btn-outline"
                    >
                        Editar perfil
                    </a>

                    <a
                        href="{{ route('ong.eventos.criar') }}"
                        class="btn btn-primary"
                    >
                        + Criar evento
                    </a>

                </div>

            </section>


            {{-- ================================================= --}}
            {{-- ESTATÍSTICAS --}}
            {{-- ================================================= --}}

            <section class="stats-grid">

                <div class="stat-card">

                    <div class="stat-value stat-dark">
                        {{ $stats['eventos_ativos'] ?? count($eventos ?? []) }}
                    </div>

                    <div class="stat-label">
                        Eventos ativos
                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-value stat-orange">
                        {{ $stats['funcionarios'] ?? count($funcionarios ?? []) }}
                    </div>

                    <div class="stat-label">
                        Funcionários
                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-value stat-blue">
                        {{ $stats['total_voluntarios'] ?? 128 }}
                    </div>

                    <div class="stat-label">
                        Total voluntários
                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-value stat-dark">
                        {{ $stats['eventos_realizados'] ?? 32 }}
                    </div>

                    <div class="stat-label">
                        Eventos realizados
                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-value stat-green">

                        {{ number_format(
                            $stats['horas_doadas'] ?? 2580,
                            0,
                            ',',
                            '.'
                        ) }}

                    </div>

                    <div class="stat-label">
                        Horas doadas (est.)
                    </div>

                </div>

            </section>


            {{-- ================================================= --}}
            {{-- GRID PRINCIPAL --}}
            {{-- ================================================= --}}

            <div class="content-grid">

                {{-- ============================================= --}}
                {{-- COLUNA ESQUERDA --}}
                {{-- ============================================= --}}

                <div class="left-col">


                    {{-- ========================================= --}}
                    {{-- EVENTOS --}}
                    {{-- ========================================= --}}

                    <section class="panel">

                        <div class="panel-header">

                            <div>

                                <h2 class="panel-title">
                                    Eventos da ONG
                                </h2>

                                <p class="panel-sub">

                                    {{
                                        collect($eventos ?? [])
                                            ->where(
                                                'status_class',
                                                'status-published'
                                            )
                                            ->count()
                                    }}

                                    ativos

                                    ·

                                    {{
                                        collect($eventos ?? [])
                                            ->where(
                                                'status_class',
                                                'status-waiting'
                                            )
                                            ->count()
                                    }}

                                    aguardando aprovação

                                </p>

                            </div>


                            <a
                                href="{{ route('ong.eventos.criar') }}"
                                class="btn btn-primary"
                            >
                                + Criar evento
                            </a>

                        </div>



                        {{-- ===================================== --}}
                        {{-- DESKTOP / TABLET --}}
                        {{-- ===================================== --}}

                        <div class="hidden overflow-x-auto md:block">

                            <table class="events-table">

                                <thead>

                                    <tr>

                                        <th>
                                            Evento
                                        </th>

                                        <th>
                                            Responsável
                                        </th>

                                        <th>
                                            Status
                                        </th>

                                        <th>
                                            Candidatos
                                        </th>

                                        <th>
                                            Ações
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                    @forelse($eventos ?? [] as $evento)

                                        @php

                                            $eventoId =
                                                $evento['evento_id']
                                                ?? $evento['id']
                                                ?? 1;

                                            $temResponsavel =
                                                !empty(
                                                    $evento['responsavel']
                                                );

                                        @endphp


                                        <tr>

                                            {{-- EVENTO --}}
                                            <td>

                                                <div class="event-name">

                                                    {{
                                                        $evento['nome']
                                                        ?? 'Evento'
                                                    }}

                                                </div>

                                                <div class="event-meta">

                                                    {{
                                                        $evento['data']
                                                        ?? 'Data não informada'
                                                    }}

                                                    ·

                                                    {{
                                                        $evento['local']
                                                        ?? 'Local não informado'
                                                    }}

                                                </div>

                                            </td>


                                            {{-- RESPONSÁVEL --}}
                                            <td>

                                                @if($temResponsavel)

                                                    <div class="resp-name">

                                                        {{
                                                            $evento['responsavel']
                                                        }}

                                                    </div>

                                                    <div class="resp-role">

                                                        {{
                                                            $evento['cargo']
                                                            ?? 'Organizador'
                                                        }}

                                                    </div>

                                                @else

                                                    <span class="resp-none">
                                                        — sem responsável
                                                    </span>

                                                @endif

                                            </td>


                                            {{-- STATUS --}}
                                            <td>

                                                <span
                                                    class="
                                                        status
                                                        {{
                                                            $evento['status_class']
                                                            ?? 'status-published'
                                                        }}
                                                    "
                                                >

                                                    {{
                                                        $evento['status']
                                                        ?? 'Publicado'
                                                    }}

                                                </span>

                                            </td>


                                            {{-- CANDIDATOS --}}
                                            <td>

                                                @php

                                                    $candidatos =
                                                        $evento['candidatos']
                                                        ?? 0;

                                                @endphp


                                                <span
                                                    class="
                                                        {{
                                                            $candidatos
                                                                ? 'count-pending'
                                                                : 'count-zero'
                                                        }}
                                                    "
                                                >

                                                    @if(
                                                        is_numeric(
                                                            $candidatos
                                                        )
                                                    )

                                                        {{ $candidatos }}
                                                        pend.

                                                    @else

                                                        {{ $candidatos }}

                                                    @endif

                                                </span>

                                            </td>


                                            {{-- AÇÕES --}}
                                            <td>

                                                <div class="actions-cell">

                                                    <a
                                                        href="{{
                                                            route(
                                                                'eventos.show',
                                                                $eventoId
                                                            )
                                                        }}"
                                                        class="
                                                            btn
                                                            btn-sm
                                                            btn-outline
                                                        "
                                                    >
                                                        Ver
                                                    </a>


                                                    <a
                                                        href="{{
                                                            route(
                                                                'ong.eventos.editar',
                                                                $eventoId
                                                            )
                                                        }}"
                                                        class="
                                                            btn
                                                            btn-sm
                                                            btn-outline
                                                        "
                                                    >

                                                        {{
                                                            $temResponsavel
                                                                ? 'Editar'
                                                                : 'Atribuir'
                                                        }}

                                                    </a>

                                                </div>

                                            </td>

                                        </tr>

                                    @empty

                                        <tr>

                                            <td
                                                colspan="5"
                                                class="
                                                    py-10
                                                    text-center
                                                    text-sm
                                                    text-gray-500
                                                "
                                            >
                                                Nenhum evento cadastrado ainda.
                                            </td>

                                        </tr>

                                    @endforelse

                                </tbody>

                            </table>

                        </div>



                        {{-- ===================================== --}}
                        {{-- MOBILE --}}
                        {{-- ===================================== --}}

                        <div class="space-y-3 md:hidden">

                            @forelse($eventos ?? [] as $evento)

                                @php

                                    $eventoId =
                                        $evento['evento_id']
                                        ?? $evento['id']
                                        ?? 1;

                                    $temResponsavel =
                                        !empty(
                                            $evento['responsavel']
                                        );

                                @endphp


                                <article
                                    class="
                                        rounded-xl
                                        border border-[#E7E5DF]
                                        bg-white
                                        p-4
                                    "
                                >

                                    <div
                                        class="
                                            flex
                                            items-start
                                            justify-between
                                            gap-3
                                        "
                                    >

                                        <div class="min-w-0">

                                            <h3
                                                class="
                                                    truncate
                                                    font-semibold
                                                    text-[#1F2420]
                                                "
                                            >

                                                {{
                                                    $evento['nome']
                                                    ?? 'Evento'
                                                }}

                                            </h3>


                                            <p
                                                class="
                                                    mt-1
                                                    text-xs
                                                    text-[#6B7269]
                                                "
                                            >

                                                {{
                                                    $evento['data']
                                                    ?? 'Data não informada'
                                                }}

                                            </p>

                                            <p
                                                class="
                                                    mt-0.5
                                                    text-xs
                                                    text-[#6B7269]
                                                "
                                            >

                                                {{
                                                    $evento['local']
                                                    ?? 'Local não informado'
                                                }}

                                            </p>

                                        </div>


                                        <span
                                            class="
                                                status
                                                {{
                                                    $evento['status_class']
                                                    ?? 'status-published'
                                                }}
                                            "
                                        >

                                            {{
                                                $evento['status']
                                                ?? 'Publicado'
                                            }}

                                        </span>

                                    </div>


                                    <div
                                        class="
                                            mt-4
                                            grid grid-cols-2
                                            gap-3
                                            text-xs
                                        "
                                    >

                                        <div>

                                            <p class="text-[#9AA094]">
                                                Responsável
                                            </p>

                                            <p
                                                class="
                                                    mt-1
                                                    font-medium
                                                    text-[#1F2420]
                                                "
                                            >

                                                {{
                                                    $evento['responsavel']
                                                    ?? 'Sem responsável'
                                                }}

                                            </p>

                                        </div>


                                        <div>

                                            <p class="text-[#9AA094]">
                                                Candidatos
                                            </p>

                                            <p
                                                class="
                                                    mt-1
                                                    font-semibold
                                                    text-[#E8920A]
                                                "
                                            >

                                                {{
                                                    $evento['candidatos']
                                                    ?? 0
                                                }}

                                            </p>

                                        </div>

                                    </div>


                                    <div
                                        class="
                                            mt-4
                                            flex gap-2
                                        "
                                    >

                                        <a
                                            href="{{
                                                route(
                                                    'eventos.show',
                                                    $eventoId
                                                )
                                            }}"
                                            class="
                                                btn
                                                btn-outline
                                                flex-1
                                                text-center
                                            "
                                        >
                                            Ver
                                        </a>


                                        <a
                                            href="{{
                                                route(
                                                    'ong.eventos.editar',
                                                    $eventoId
                                                )
                                            }}"
                                            class="
                                                btn
                                                btn-outline
                                                flex-1
                                                text-center
                                            "
                                        >

                                            {{
                                                $temResponsavel
                                                    ? 'Editar'
                                                    : 'Atribuir'
                                            }}

                                        </a>

                                    </div>

                                </article>

                            @empty

                                <p
                                    class="
                                        py-8
                                        text-center
                                        text-sm
                                        text-gray-500
                                    "
                                >
                                    Nenhum evento cadastrado ainda.
                                </p>

                            @endforelse

                        </div>

                    </section>



                    {{-- ========================================= --}}
                    {{-- FUNCIONÁRIOS --}}
                    {{-- ========================================= --}}

                    <section class="panel">

                        <div class="panel-header">

                            <div>

                                <h2 class="panel-title">
                                    Funcionários da ONG
                                </h2>

                                <p class="panel-sub">

                                    {{
                                        collect($funcionarios ?? [])
                                            ->where(
                                                'status',
                                                'accepted'
                                            )
                                            ->count()
                                    }}

                                    ativos

                                    ·

                                    {{
                                        collect($funcionarios ?? [])
                                            ->where(
                                                'status',
                                                'pending'
                                            )
                                            ->count()
                                    }}

                                    convite(s) pendente(s)

                                </p>

                            </div>


                            <button
                                type="button"
                                class="btn btn-outline"
                            >
                                Convidar organizador
                            </button>

                        </div>


                        <ul class="staff-list">

                            @forelse($funcionarios ?? [] as $funcionario)

                                <li class="staff-row">

                                    <div class="staff-identity">

                                        <div
                                            class="
                                                avatar
                                                avatar-{{
                                                    $funcionario['cor']
                                                    ?? 'green'
                                                }}
                                            "
                                        >

                                            {{
                                                $funcionario['iniciais']
                                                ?? '??'
                                            }}

                                        </div>


                                        <div>

                                            <div class="staff-name">

                                                {{
                                                    $funcionario['nome']
                                                    ?? 'Sem nome'
                                                }}

                                            </div>


                                            <div class="staff-meta">

                                                {{
                                                    $funcionario['meta']
                                                    ?? ''
                                                }}

                                            </div>

                                        </div>

                                    </div>


                                    <div class="staff-actions">

                                        @if(
                                            ($funcionario['status'] ?? '')
                                            === 'accepted'
                                        )

                                            <span
                                                class="
                                                    pill
                                                    pill-accepted
                                                "
                                            >
                                                Aceito
                                            </span>


                                            <button
                                                type="button"
                                                class="
                                                    btn
                                                    btn-sm
                                                    btn-danger
                                                "
                                            >
                                                Remover
                                            </button>

                                        @else

                                            <span
                                                class="
                                                    pill
                                                    pill-pending
                                                "
                                            >
                                                Pendente
                                            </span>


                                            <button
                                                type="button"
                                                class="
                                                    btn
                                                    btn-sm
                                                    btn-outline
                                                "
                                            >
                                                Remover
                                            </button>

                                        @endif

                                    </div>

                                </li>

                            @empty

                                <li
                                    class="
                                        py-8
                                        text-center
                                        text-sm
                                        text-gray-500
                                    "
                                >
                                    Nenhum funcionário cadastrado ainda.
                                </li>

                            @endforelse

                        </ul>


                        <div class="notice">

                            <span class="notice-icon">
                                ⚠
                            </span>

                            <span>
                                A remoção de um funcionário é bloqueada
                                se ele estiver designado como responsável
                                em um evento ativo. Faça a substituição
                                primeiro.
                            </span>

                        </div>

                    </section>

                </div>



                {{-- ============================================= --}}
                {{-- COLUNA DIREITA --}}
                {{-- ============================================= --}}

                <aside class="right-col">


                    {{-- ========================================= --}}
                    {{-- DADOS CADASTRAIS --}}
                    {{-- ========================================= --}}

                    <section class="panel">

                        <h2 class="panel-title">
                            Dados cadastrais
                        </h2>


                        <ul class="info-list">

                            <li>

                                <span class="info-icon">
                                    🏛
                                </span>

                                <div>

                                    <div class="info-label">
                                        Razão social
                                    </div>

                                    <div class="info-value">

                                        {{
                                            $ong['nome']
                                            ?? 'Instituto Esperança Social'
                                        }}

                                    </div>

                                </div>

                            </li>


                            <li>

                                <span class="info-icon">
                                    🪪
                                </span>

                                <div>

                                    <div class="info-label">
                                        CNPJ
                                    </div>

                                    <div class="info-value">

                                        {{
                                            $ong['cnpj']
                                            ?? '11.222.333/0001-81'
                                        }}

                                    </div>

                                </div>

                            </li>


                            <li>

                                <span class="info-icon">
                                    📍
                                </span>

                                <div>

                                    <div class="info-label">
                                        Cidade / Estado
                                    </div>

                                    <div class="info-value">

                                        {{
                                            $ong['cidade']
                                            ?? 'São Paulo'
                                        }},

                                        {{
                                            $ong['estado']
                                            ?? 'SP'
                                        }}

                                    </div>

                                </div>

                            </li>


                            <li>

                                <span class="info-icon">
                                    🌐
                                </span>

                                <div>

                                    <div class="info-label">
                                        Site
                                    </div>

                                    <div class="info-value info-link">

                                        {{
                                            $ong['site']
                                            ?? 'esperancasocial.org.br'
                                        }}

                                    </div>

                                </div>

                            </li>


                            <li>

                                <span class="info-icon">
                                    📅
                                </span>

                                <div>

                                    <div class="info-label">
                                        Cadastro aprovado em
                                    </div>

                                    <div class="info-value">

                                        {{
                                            $ong['aprovado_em']
                                            ?? '12 jan. 2024'
                                        }}

                                    </div>

                                </div>

                            </li>

                        </ul>

                    </section>



                    {{-- ========================================= --}}
                    {{-- ATIVIDADE RECENTE --}}
                    {{-- ========================================= --}}

                    <section class="panel">

                        <h2 class="panel-title">
                            Atividade recente
                        </h2>


                        <ul class="activity-list">

                            @forelse($atividades ?? [] as $atividade)

                                <li class="activity-row">

                                    <span
                                        class="
                                            dot
                                            dot-{{
                                                $atividade['cor']
                                                ?? 'green'
                                            }}
                                        "
                                    ></span>


                                    <div>

                                        <div class="activity-text">

                                            {!!
                                                $atividade['texto']
                                                ?? ''
                                            !!}

                                        </div>

                                        <div class="activity-time">

                                            {{
                                                $atividade['tempo']
                                                ?? ''
                                            }}

                                        </div>

                                    </div>

                                </li>

                            @empty

                                <li
                                    class="
                                        py-4
                                        text-sm
                                        text-gray-500
                                    "
                                >
                                    Nenhuma atividade recente.
                                </li>

                            @endforelse

                        </ul>

                    </section>



                    {{-- ========================================= --}}
                    {{-- ACESSO RÁPIDO --}}
                    {{-- ========================================= --}}

                    <section class="panel">

                        <h2 class="panel-title">
                            Acesso rápido
                        </h2>


                        <div class="quick-actions">

                            <a
                                href="{{ route('ong.eventos.criar') }}"
                                class="btn btn-primary btn-block"
                            >
                                + Criar novo evento
                            </a>


                            <a
                                href="{{ route('ong.candidatos') }}"
                                class="btn btn-outline btn-block"
                            >
                                Ver candidatos
                            </a>


                            <a
                                href="{{ route('ong.eventos.index') }}"
                                class="btn btn-outline btn-block"
                            >
                                Ver todos os eventos
                            </a>

                        </div>

                    </section>

                </aside>

            </div>

        </div>
    </main>

</body>

</html>
