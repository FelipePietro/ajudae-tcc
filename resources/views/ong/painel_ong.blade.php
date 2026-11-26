@php
    $integrated = isset($institutionalEntry);
@endphp
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
        $integrated ? 'resources/js/identity.js' : 'resources/js/app.js'
    ])
</head>

<body class="min-h-screen bg-[#F7F6F2]">

    @if($integrated)
        <a class="account-skip" href="#dashboard-main">Ir para o conteúdo</a>
        <x-navbar-ong :web-navigation="\App\Support\WebNavigation::for(request())" />
    @else
    <x-navbar-ong
        ativo="ong"
        :nome="$nomeUsuario ?? 'Carlos Ferreira'"
        :ong="$ong['nome_fantasia'] ?? 'Instituto Esperança'"
        :iniciais="$iniciaisUsuario ?? 'CF'"
        :eventos-pendentes="$eventosCount ?? 3"
        :candidatos-pendentes="$candidatosCount ?? 12"
        :notificacoes="$notificacoesCount ?? 4"
    />
    @endif

    {{-- ========================================================= --}}
    {{-- CONTEÚDO PRINCIPAL --}}
    {{-- ========================================================= --}}

    <main id="dashboard-main" tabindex="-1" class="ong-page-content">
        @if($integrated)
            <x-breadcrumb-ong :raiz-href="route('ong.painel')" raiz-label="Dashboard" :itens="[['label' => 'Painel da ONG']]" />
        @endif

        <div class="px-4 py-6 sm:px-6 lg:px-7">


            {{-- ================================================= --}}
            {{-- CABEÇALHO DA ONG --}}
            {{-- ================================================= --}}

            <section class="org-card">

                <div class="org-identity">

                    <div class="org-logo text-white" aria-hidden="true">
                        {{ $integrated ? $institutionalEntry['initials'] : ($ong['icone'] ?? '🌱') }}
                    </div>

                    <div>

                        <h1 class="org-name break-words">
                            {{ $integrated ? $institutionalEntry['name'] : ($ong['nome'] ?? 'Instituto Esperança Social') }}
                        </h1>

                        <p class="org-sub">

                            @if($integrated)
                                {{ $institutionalEntry['place'] }}
                            @else
                            {{ $ong['nome_fantasia'] ?? 'Esperança Social' }}

                            ·

                            {{ $ong['cidade'] ?? 'São Paulo' }},
                            {{ $ong['estado'] ?? 'SP' }}
                            @endif

                        </p>


                        <div class="org-tags">

                            @if($ong['ativa'] ?? true)

                                <span class="tag tag-active">
                                    ✓ Ativa
                                </span>

                            @endif


                            @if($integrated)
                                @if($institutionalEntry['cnpj'])
                                    <span class="tag tag-cnpj">CNPJ {{ $institutionalEntry['cnpj'] }}</span>
                                @endif
                            @else
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
                            @endif

                        </div>

                    </div>

                </div>


                <div class="org-actions">
                    @if($integrated)
                        <x-botao-secundario :href="route('ong.perfil')">Minha organização</x-botao-secundario>
                        <x-botao-primario :href="route('ong.eventos.criar')">+ Criar evento</x-botao-primario>
                    @else
                    <x-botao-secundario :href="route('ong.perfil', ['ong'=>1, 'contexto'=>'ong'])">Ver perfil da ONG</x-botao-secundario>

                    <x-botao-secundario :href="route('ong.perfil.editar')">
                        Editar perfil
                    </x-botao-secundario>

                    <x-botao-primario :href="route('ong.eventos.criar')">
                        + Criar evento
                    </x-botao-primario>
                    @endif

                </div>

            </section>


            {{-- ================================================= --}}
            {{-- ESTATÍSTICAS --}}
            {{-- ================================================= --}}

            @if($integrated)<p id="painel-indicadores" class="mb-2 text-sm text-[#56594f]">Indicadores operacionais ainda indisponíveis.</p>@endif
            <section class="stats-grid" aria-label="Indicadores da organização" @if($integrated) aria-describedby="painel-indicadores" @endif>

                <div class="stat-card">

                    <div class="stat-value stat-dark">
                        {{ $integrated ? '—' : ($stats['eventos_ativos'] ?? count($eventos ?? [])) }}
                    </div>

                    <div class="stat-label">
                        Eventos ativos
                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-value stat-orange" @if($integrated) data-active-organizer-count @endif>
                        {{ $integrated ? '—' : (collect($organizerWork['links'])->where('status','ativo')->count()) }}
                    </div>

                    <div class="stat-label">
                        Organizadores
                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-value stat-blue">
                        {{ $integrated ? '—' : ($stats['total_voluntarios'] ?? 128) }}
                    </div>

                    <div class="stat-label">
                        Total voluntários
                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-value stat-dark">
                        {{ $integrated ? '—' : ($stats['eventos_realizados'] ?? 32) }}
                    </div>

                    <div class="stat-label">
                        Eventos realizados
                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-value stat-green">

                        {{ $integrated ? '—' : (number_format(
                            $stats['horas_doadas'] ?? 2580,
                            0,
                            ',',
                            '.'
                        )) }}

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

                    <section class="panel" id="eventos-ong" tabindex="-1">

                        <div class="panel-header">

                            <div>

                                <h2 class="panel-title">
                                    Eventos da ONG
                                </h2>

                                @unless($integrated)
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
                                @endunless

                            </div>

                            @unless($integrated)
                            <x-botao-primario :href="route('ong.eventos.criar')">
                                + Criar evento
                            </x-botao-primario>
                            @endunless

                        </div>



                        @if($integrated)
                            <p class="mt-4 text-sm text-[#56594f]">A consulta e a gestão de Eventos ainda estão indisponíveis.</p>
                        @else
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

                                                    <x-botao-secundario :href="route(
                                                                'eventos.show',
                                                                ['id'=>$eventoId, 'contexto'=>'ong']
                                                            )" size="sm">
                                                        Ver
                                                    </x-botao-secundario>


                                                    <x-botao-secundario :href="route(
                                                                'ong.eventos.editar',
                                                                $eventoId
                                                            )" size="sm">

                                                        {{
                                                            $temResponsavel
                                                                ? 'Editar'
                                                                : 'Atribuir'
                                                        }}

                                                    </x-botao-secundario>

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

                                        <x-botao-secundario :href="route(
                                                    'eventos.show',
                                                    ['id'=>$eventoId, 'contexto'=>'ong']
                                                )" class="flex-1">
                                            Ver
                                        </x-botao-secundario>


                                        <x-botao-secundario :href="route(
                                                    'ong.eventos.editar',
                                                    $eventoId
                                                )" class="flex-1">

                                            {{
                                                $temResponsavel
                                                    ? 'Editar'
                                                    : 'Atribuir'
                                            }}

                                        </x-botao-secundario>

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

                        @endif
                    </section>



                    {{-- ========================================= --}}
                    {{-- FUNCIONÁRIOS --}}
                    {{-- ========================================= --}}

                    @if($integrated)
                        @include('auth.institutional-panel')
                    @else
                    <section class="panel"><div class="panel-header"><div><h2 class="panel-title">Organizadores vinculados</h2><p class="panel-sub">{{ collect($organizerWork['links'])->where('status','ativo')->count() }} ativos · {{ collect($organizerWork['links'])->where('status','convite_pendente')->count() }} convite(s) pendente(s)</p></div><x-botao-primario :href="route('ong.organizadores',['contexto'=>'ong'])" size="sm">Gerenciar organizadores</x-botao-primario></div><ul class="ff-list">@foreach($organizerWork['links'] as $link)<li class="ff-item"><div><p class="font-semibold">{{ collect($organizerWork['people'])->firstWhere('pessoa_id',$link['pessoa_id'])['nm_pessoa'] }}</p><p class="text-sm mt-2">{{ ['ativo'=>'Vínculo ativo','convite_pendente'=>'Convite pendente','recusado'=>'Convite recusado','encerrado'=>'Vínculo encerrado'][$link['status']] }}</p></div></li>@endforeach</ul></section>
                    @endif

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
                        @if($integrated)
                            @foreach([
                                ['🏛', 'Nome da organização', $institutionalEntry['name']],
                                ['🪪', 'CNPJ', $institutionalEntry['cnpj']],
                                ['📍', 'Cidade / Estado', $institutionalEntry['place']],
                                ['🌐', 'Site', $institutionalEntry['site']],
                                ['✉', 'Contato institucional', $institutionalEntry['email']],
                            ] as [$icon, $label, $value])
                                <li><span class="info-icon" aria-hidden="true">{{ $icon }}</span><div>
                                    <div class="info-label">{{ $label }}</div>
                                    <div class="info-value break-all">{{ $value ?: 'Não informado' }}</div>
                                </div></li>
                            @endforeach
                        @else

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

                        @endif
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
                        @if($integrated)
                            <li class="py-4 text-sm text-[#56594f]">O histórico de atividades ainda está indisponível.</li>
                        @else

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

                        @endif
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
                            @if($integrated)
                                <x-botao-secundario :href="route('ong.perfil')">Minha organização</x-botao-secundario>
                                <x-botao-secundario :href="route('feed')">Explorar eventos</x-botao-secundario>
                                <x-botao-secundario :href="route('ong.eventos.index')">Ver todos os eventos</x-botao-secundario>
                            @else

                            <x-botao-primario :href="route('ong.eventos.criar')">
                                + Criar novo evento
                            </x-botao-primario>


                            <x-botao-secundario :href="route('ong.candidatos')">
                                Ver candidatos
                            </x-botao-secundario>


                            <x-botao-secundario :href="route('ong.eventos.index')">
                                Ver todos os eventos
                            </x-botao-secundario>
                            @endif

                        </div>

                    </section>

                </aside>

            </div>

        </div>
    </main>

</body>

</html>
