<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Meu perfil - Ajudae</title>

    @vite([
        'resources/css/app.css',
        'resources/css/painel_ong.css',
        'resources/css/perfil_ong.css',
        'resources/js/app.js'
    ])
</head>

<body class="min-h-screen bg-[#F7F6F2]">

    {{-- ========================================================= --}}
    {{-- NAVBAR ONG --}}
    {{-- ========================================================= --}}

    <x-navbar-ong
        ativo="perfil"
        :nome="$usuario['nome'] ?? 'Carlos Ferreira'"
        :ong="$ong['nome_fantasia'] ?? 'Instituto Esperança'"
        :iniciais="$usuario['iniciais'] ?? 'CF'"
        :eventos-pendentes="$eventosCount ?? 3"
        :candidatos-pendentes="$candidatosCount ?? 12"
        :notificacoes="$notificacoesCount ?? 4"
    />


    {{-- ========================================================= --}}
    {{-- CONTEÚDO --}}
    {{-- ========================================================= --}}

    <main class="ong-page-content">

    <x-breadcrumb-ong
    :itens="[
        ['label' => 'Meu perfil']
    ]"
/>

        <div class="px-4 py-6 sm:px-6 lg:px-7">


            {{-- ================================================= --}}
            {{-- PERFIL --}}
            {{-- ================================================= --}}

            <section class="org-card">

                <div class="org-identity">

                    {{-- AVATAR --}}
                    <div
                        class="
                            flex h-[72px] w-[72px]
                            shrink-0
                            items-center justify-center
                            rounded-full
                            bg-[#CFE3D3]
                            text-[26px]
                            font-bold
                            text-[#1D3F2F]
                        "
                    >
                        {{ $usuario['iniciais'] ?? '??' }}
                    </div>


                    {{-- DADOS --}}
                    <div class="min-w-0 flex-1">

                        <h1 class="org-name">
                            {{ $usuario['nome'] ?? 'Carlos Ferreira' }}
                        </h1>


                        <div
                            class="
                                flex flex-wrap
                                items-center gap-x-2 gap-y-1
                                text-[13px]
                                text-[#6B7269]
                            "
                        >

                            <span>
                                📍
                                {{ $usuario['cidade'] ?? 'São Paulo' }},
                                {{ $usuario['estado'] ?? 'SP' }}
                            </span>

                            <span class="hidden sm:inline">
                                •
                            </span>

                            <span>
                                {{ $usuario['tipo'] ?? 'Organizador independente' }}
                            </span>

                            <span class="hidden sm:inline">
                                •
                            </span>

                            <span>
                                Desde
                                {{ $usuario['membro_desde'] ?? '2026' }}
                            </span>

                        </div>


                        <div class="org-tags mt-3">

                            @if(!empty($usuario['verificado']))

                                <span class="tag tag-active">
                                    ✓ Verificado
                                </span>

                            @endif


                            <span class="tag tag-cnpj">
                                {{ $usuario['papel'] ?? 'Organizador' }}
                            </span>


                            @foreach($usuario['areas'] ?? [] as $area)

                                <span class="tag tag-plain">
                                    {{ $area }}
                                </span>

                            @endforeach

                        </div>

                    </div>

                </div>


                {{-- AÇÕES --}}
                <div class="org-actions">

                    <a
                        href="{{ route('ong.perfil.editar') }}"
                        class="btn btn-primary"
                    >
                        Editar perfil
                    </a>

                </div>

            </section>


            {{-- ================================================= --}}
            {{-- ESTATÍSTICAS --}}
            {{-- ================================================= --}}

            <section
                class="
                    mb-[22px]
                    grid grid-cols-1
                    gap-3.5
                    sm:grid-cols-3
                "
            >

                <div class="stat-card">

                    <div class="stat-value stat-dark">
                        {{ $stats['eventos_organizados'] ?? 0 }}
                    </div>

                    <div class="stat-label">
                        Eventos organizados
                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-value stat-blue">
                        {{ $stats['voluntarios_gerenciados'] ?? 0 }}
                    </div>

                    <div class="stat-label">
                        Voluntários gerenciados
                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-value stat-orange">
                        {{ $stats['badges'] ?? 0 }}
                    </div>

                    <div class="stat-label">
                        Badges conquistados
                    </div>

                </div>

            </section>


            {{-- ================================================= --}}
            {{-- GRID PRINCIPAL --}}
            {{-- ================================================= --}}

            <div class="content-grid">

                {{-- ================================================= --}}
                {{-- COLUNA ESQUERDA --}}
                {{-- ================================================= --}}

                <div class="left-col">


                    {{-- ================================================= --}}
                    {{-- SOBRE --}}
                    {{-- ================================================= --}}

                    <section class="panel">

                        <div class="panel-header">

                            <div>

                                <h2 class="panel-title">
                                    Sobre
                                </h2>

                                <p class="panel-sub">
                                    Informações do organizador
                                </p>

                            </div>

                        </div>


                        <div
                            class="
                                grid grid-cols-1
                                gap-5
                                sm:grid-cols-2
                            "
                        >

                            <div>

                                <p
                                    class="
                                        mb-1
                                        text-xs
                                        text-[#6B7269]
                                    "
                                >
                                    Tipo de conta
                                </p>

                                <p
                                    class="
                                        text-sm
                                        font-semibold
                                        text-[#1F2420]
                                    "
                                >
                                    {{ $usuario['tipo'] ?? 'Organizador independente' }}
                                </p>

                            </div>


                            <div>

                                <p
                                    class="
                                        mb-1
                                        text-xs
                                        text-[#6B7269]
                                    "
                                >
                                    Papel
                                </p>

                                <p
                                    class="
                                        text-sm
                                        font-semibold
                                        text-[#1F2420]
                                    "
                                >
                                    {{ $usuario['papel'] ?? 'Organizador' }}
                                </p>

                            </div>


                            <div>

                                <p
                                    class="
                                        mb-1
                                        text-xs
                                        text-[#6B7269]
                                    "
                                >
                                    Cidade
                                </p>

                                <p
                                    class="
                                        text-sm
                                        font-semibold
                                        text-[#1F2420]
                                    "
                                >
                                    {{ $usuario['cidade'] ?? 'São Paulo' }},
                                    {{ $usuario['estado'] ?? 'SP' }}
                                </p>

                            </div>


                            <div>

                                <p
                                    class="
                                        mb-1
                                        text-xs
                                        text-[#6B7269]
                                    "
                                >
                                    Membro desde
                                </p>

                                <p
                                    class="
                                        text-sm
                                        font-semibold
                                        text-[#1F2420]
                                    "
                                >
                                    {{ $usuario['membro_desde'] ?? '2026' }}
                                </p>

                            </div>

                        </div>


                        @if(!empty($usuario['areas']))

                            <div
                                class="
                                    mt-6
                                    border-t border-[#E7E5DF]
                                    pt-5
                                "
                            >

                                <p
                                    class="
                                        mb-3
                                        text-xs
                                        font-medium
                                        text-[#6B7269]
                                    "
                                >
                                    Áreas de atuação
                                </p>


                                <div class="flex flex-wrap gap-2">

                                    @foreach($usuario['areas'] as $area)

                                        <span class="tag tag-plain">
                                            {{ $area }}
                                        </span>

                                    @endforeach

                                </div>

                            </div>

                        @endif

                    </section>



                    {{-- ================================================= --}}
                    {{-- EVENTOS CRIADOS --}}
                    {{-- ================================================= --}}

                    <section class="panel">

                        <div class="panel-header">

                            <div>

                                <h2 class="panel-title">
                                    Eventos criados
                                </h2>

                                <p class="panel-sub">

                                    {{
                                        count(
                                            $eventosCriados ?? []
                                        )
                                    }}

                                    evento(s)

                                </p>

                            </div>


                            <a
                                href="{{ route('ong.eventos.index') }}"
                                class="btn btn-outline"
                            >
                                Ver todos
                            </a>

                        </div>


                        <div class="space-y-3">

                            @forelse($eventosCriados ?? [] as $evento)

                                @php

                                    $eventoId =
                                        $evento['evento_id']
                                        ?? $evento['id']
                                        ?? 1;

                                @endphp


                                <article
                                    class="
                                        rounded-xl
                                        border border-[#DCEBE2]
                                        bg-[#F2F8F4]
                                        p-4
                                    "
                                >

                                    <div
                                        class="
                                            flex flex-col
                                            gap-4
                                            sm:flex-row
                                            sm:items-center
                                        "
                                    >

                                        <div
                                            class="
                                                flex h-12 w-12
                                                shrink-0
                                                items-center justify-center
                                                rounded-xl
                                                bg-[#D4EDDF]
                                                text-2xl
                                            "
                                        >
                                            {{ $evento['icone'] ?? '🌿' }}
                                        </div>


                                        <div class="min-w-0 flex-1">

                                            <h3
                                                class="
                                                    font-semibold
                                                    text-[#1A3A2A]
                                                "
                                            >
                                                {{ $evento['nome'] ?? 'Evento' }}
                                            </h3>


                                            <div
                                                class="
                                                    mt-1.5
                                                    flex flex-wrap
                                                    gap-x-4 gap-y-1
                                                    text-xs
                                                    text-[#6B7269]
                                                "
                                            >

                                                <span>
                                                    📅
                                                    {{ $evento['data'] ?? 'Data não informada' }}
                                                </span>

                                                <span>
                                                    📍
                                                    {{ $evento['local'] ?? 'Local não informado' }}
                                                </span>

                                                <span>
                                                    👥
                                                    {{ $evento['vagas'] ?? '0 vagas restantes' }}
                                                </span>

                                            </div>

                                        </div>


                                        <div
                                            class="
                                                flex shrink-0 gap-2
                                            "
                                        >

                                            <a
                                                href="{{ route('eventos.show', $eventoId) }}"
                                                class="btn btn-sm btn-outline"
                                            >
                                                Ver
                                            </a>

                                            <a
                                                href="{{ route('ong.eventos.editar', $eventoId) }}"
                                                class="btn btn-sm btn-outline"
                                            >
                                                Editar
                                            </a>

                                        </div>

                                    </div>

                                </article>

                            @empty

                                <div
                                    class="
                                        rounded-xl
                                        border border-dashed
                                        border-[#D8D8D2]
                                        px-5 py-10
                                        text-center
                                    "
                                >

                                    <p
                                        class="
                                            text-sm
                                            text-[#6B7269]
                                        "
                                    >
                                        Nenhum evento criado ainda.
                                    </p>


                                    <a
                                        href="{{ route('ong.eventos.criar') }}"
                                        class="btn btn-primary mt-4"
                                    >
                                        + Criar primeiro evento
                                    </a>

                                </div>

                            @endforelse

                        </div>

                    </section>

                </div>



                {{-- ================================================= --}}
                {{-- COLUNA DIREITA --}}
                {{-- ================================================= --}}

                <aside class="right-col">


                    {{-- ================================================= --}}
                    {{-- INFORMAÇÕES PESSOAIS --}}
                    {{-- ================================================= --}}

                    <section class="panel">

                        <h2 class="panel-title">
                            Informações pessoais
                        </h2>


                        <div class="info-list">

                            <div>

                                <div class="info-label">
                                    E-mail
                                </div>

                                <div class="info-value break-all">
                                    {{ $usuario['email'] ?? 'email@exemplo.com' }}
                                </div>

                            </div>


                            <div>

                                <div class="info-label">
                                    Telefone
                                </div>

                                <div class="info-value">
                                    {{ $usuario['telefone'] ?? 'Não informado' }}
                                </div>

                            </div>


                            <div>

                                <div class="info-label">
                                    Cidade / Estado
                                </div>

                                <div class="info-value">

                                    {{ $usuario['cidade'] ?? 'São Paulo' }},
                                    {{ $usuario['estado'] ?? 'SP' }}

                                </div>

                            </div>

                        </div>

                    </section>



                    {{-- ================================================= --}}
                    {{-- CONTA --}}
                    {{-- ================================================= --}}

                    <section class="panel">

                        <h2 class="panel-title">
                            Conta
                        </h2>


                        <div class="quick-actions">

                            <a
                                href="{{ route('ong.perfil.editar') }}"
                                class="btn btn-primary btn-block"
                            >
                                Editar perfil
                            </a>


                            <a
                                href="{{ route('ong.configuracoes') }}"
                                class="btn btn-outline btn-block"
                            >
                                Configurações
                            </a>

                        </div>

                    </section>



                    {{-- ================================================= --}}
                    {{-- STATUS --}}
                    {{-- ================================================= --}}

                    <section class="panel">

                        <h2 class="panel-title">
                            Status da conta
                        </h2>


                        <div
                            class="
                                mt-4
                                rounded-xl
                                border border-[#CFE6D7]
                                bg-[#EFF8F2]
                                p-4
                            "
                        >

                            <div
                                class="
                                    flex items-start gap-3
                                "
                            >

                                <div
                                    class="
                                        flex h-9 w-9
                                        shrink-0
                                        items-center justify-center
                                        rounded-full
                                        bg-[#DFF2E4]
                                        text-[#2F7A4C]
                                    "
                                >
                                    ✓
                                </div>


                                <div>

                                    <p
                                        class="
                                            text-sm
                                            font-semibold
                                            text-[#1A3D2B]
                                        "
                                    >
                                        Conta ativa
                                    </p>


                                    <p
                                        class="
                                            mt-1
                                            text-xs
                                            leading-relaxed
                                            text-[#6B7269]
                                        "
                                    >
                                        Seu perfil está ativo e pode
                                        organizar eventos no Ajudae.
                                    </p>

                                </div>

                            </div>

                        </div>

                    </section>

                </aside>

            </div>

        </div>

    </main>

</body>

</html>