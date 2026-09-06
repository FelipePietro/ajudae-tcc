<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Painel da ONG - Ajudae</title>
@vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/painel_ong.css'])
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="app">

  <!-- SIDEBAR -->
  <x-sidebar
    active="painel-ong"
    :eventos-count="$eventosCount ?? null"
    :candidatos-count="$candidatosCount ?? null"
    :notificacoes-count="$notificacoesCount ?? null"
  />

  <!-- MAIN -->
  <main class="main">

    <div class="breadcrumb">Dashboard <span class="sep">›</span> <span class="current">Painel da ONG</span></div>

    <!-- ORG CARD -->
    <section class="org-card">
      <div class="org-identity">
        <div class="org-logo">{{ $ong['icone'] ?? '🌱' }}</div>
        <div>
          <h1 class="org-name">{{ $ong['nome'] }}</h1>
          <p class="org-sub">{{ $ong['nome_fantasia'] }} · {{ $ong['cidade'] }}, {{ $ong['estado'] }}</p>
          <div class="org-tags">
            @if (!empty($ong['ativa']))
              <span class="tag tag-active">✓ Ativa</span>
            @endif
            <span class="tag tag-cnpj">CNPJ {{ $ong['cnpj'] }}</span>
            <span class="tag tag-plain">{{ $ong['area_atuacao'] }}</span>
            @if (!empty($ong['verificada']))
              <span class="tag tag-verified">Conta verificada</span>
            @endif
          </div>
        </div>
      </div>
      <div class="org-actions">
        <a href="{{ route('perfil-ong.editar') }}" class="btn btn-outline">Editar perfil</a>
        <button class="btn btn-primary">+ Criar evento</button>
      </div>
    </section>

    <!-- STATS -->
    <section class="stats-grid">
      <div class="stat-card">
        <div class="stat-value stat-dark">{{ $stats['eventos_ativos'] ?? count($eventos) }}</div>
        <div class="stat-label">Eventos ativos</div>
      </div>
      <div class="stat-card">
        <div class="stat-value stat-orange">{{ $stats['funcionarios'] ?? count($funcionarios) }}</div>
        <div class="stat-label">Funcionários</div>
      </div>
      <div class="stat-card">
        <div class="stat-value stat-blue">{{ $stats['total_voluntarios'] ?? 0 }}</div>
        <div class="stat-label">Total voluntários</div>
      </div>
      <div class="stat-card">
        <div class="stat-value stat-dark">{{ $stats['eventos_realizados'] ?? 0 }}</div>
        <div class="stat-label">Eventos realizados</div>
      </div>
      <div class="stat-card">
        <div class="stat-value stat-green">{{ $stats['horas_doadas'] ?? 0 }}</div>
        <div class="stat-label">Horas doadas (est.)</div>
      </div>
    </section>

    <!-- TABS -->
    <nav class="tabs">
      <button class="tab active">Eventos</button>
      <button class="tab">Funcionários</button>
      <button class="tab">Perfil da ONG</button>
    </nav>

    <!-- CONTENT GRID -->
    <div class="content-grid">

      <!-- LEFT COLUMN -->
      <div class="left-col">

        <!-- EVENTS TABLE -->
        <section class="panel">
          <div class="panel-header" style="margin-bottom: 1.25rem;">
            <div>
              <h2 class="panel-title" style="margin-bottom: 0.35rem;">Eventos da ONG</h2>
              <p class="panel-sub">
                {{ collect($eventos)->where('status_class', 'status-published')->count() }} ativos ·
                {{ collect($eventos)->where('status_class', 'status-waiting')->count() }} aguardando aprovação
              </p>
            </div>
            <button class="btn btn-primary">+ Criar evento</button>
          </div>

          <div class="space-y-4">
            @forelse ($eventos as $evento)
              <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 bg-[#EEF7F2] border border-[#c8e0d4] rounded-xl px-5 py-4">
                <div class="text-3xl bg-[#d4eddf] p-2.5 rounded-[10px] shrink-0">
                  {{ $evento['icone'] ?? '🌿' }}
                </div>
                <div class="flex-1 min-w-0">
                  <strong class="text-sm text-[#1a3a2a] block">
                    {{ $evento['nome'] ?? 'Evento' }}
                  </strong>
                  <p class="text-[0.82rem] text-gray-500 mt-1 flex flex-col sm:flex-row sm:gap-2">
                    <span>📅 {{ $evento['data'] ?? 'Data não informada' }}</span>
                    <span>📍 {{ $evento['local'] ?? 'Local não informado' }}</span>
                    <span>👥 {{ $evento['candidatos'] ?? '0 pend.' }}</span>
                  </p>
                  <div class="mt-2 flex flex-wrap items-center gap-2">
                    <span class="status {{ $evento['status_class'] ?? 'status-published' }}">{{ $evento['status'] ?? 'Publicado' }}</span>
                    @if (!empty($evento['responsavel']))
                      <span class="text-[11px] font-semibold text-[#2d5c4d] bg-[#dfeee8] rounded-full px-2 py-1">
                        {{ $evento['responsavel'] }} · {{ $evento['cargo'] }}
                      </span>
                    @else
                      <span class="text-[11px] font-semibold text-[#7a4a00] bg-[#fff1d6] rounded-full px-2 py-1">
                        Sem responsável
                      </span>
                    @endif
                  </div>
                </div>
                <div class="flex gap-2 self-end sm:self-center">
                  <button class="btn btn-sm btn-outline">Ver</button>
                  <button class="btn btn-sm btn-outline">{{ $evento['responsavel'] ? 'Editar' : 'Atribuir' }}</button>
                </div>
              </div>
            @empty
              <p class="text-sm text-gray-500">Nenhum evento cadastrado ainda.</p>
            @endforelse
          </div>
        </section>

        <!-- STAFF LIST -->
        <section class="panel">
          <div class="panel-header">
            <div>
              <h2 class="panel-title">Funcionários da ONG</h2>
              <p class="panel-sub">
                {{ collect($funcionarios)->where('status', 'accepted')->count() }} ativos ·
                {{ collect($funcionarios)->where('status', 'pending')->count() }} convite(s) pendente(s)
              </p>
            </div>
            <button class="btn btn-outline">Convidar organizador</button>
          </div>

          <ul class="staff-list">
            @forelse ($funcionarios as $funcionario)
              <li class="staff-row">
                <div class="staff-identity">
                  <div class="avatar avatar-{{ $funcionario['cor'] ?? 'green' }}">{{ $funcionario['iniciais'] ?? '??' }}</div>
                  <div>
                    <div class="staff-name">{{ $funcionario['nome'] ?? 'Sem nome' }}</div>
                    <div class="staff-meta">{{ $funcionario['meta'] ?? '' }}</div>
                  </div>
                </div>
                <div class="staff-actions">
                  @if (($funcionario['status'] ?? '') === 'accepted')
                    <span class="pill pill-accepted">Aceito</span>
                    <button class="btn btn-sm btn-danger">Remover</button>
                  @else
                    <span class="pill pill-pending">Pendente</span>
                    <button class="btn btn-sm btn-outline">Remover</button>
                  @endif
                </div>
              </li>
            @empty
              <p class="text-sm text-gray-500">Nenhum funcionário cadastrado ainda.</p>
            @endforelse
          </ul>

          <div class="notice">
            <span class="notice-icon">⚠</span>
            A remoção de um funcionário é bloqueada se ele estiver designado como responsável em evento ativo. Faça a substituição primeiro.
          </div>
        </section>

      </div>

      <!-- RIGHT COLUMN -->
      <div class="right-col">

        <section class="panel">
          <h2 class="panel-title">Dados cadastrais</h2>
          <ul class="info-list">
            <li>
              <span class="info-icon">🏛</span>
              <div>
                <div class="info-label">Razão social</div>
                <div class="info-value">{{ $ong['nome'] }}</div>
              </div>
            </li>
            <li>
              <span class="info-icon">🪪</span>
              <div>
                <div class="info-label">CNPJ</div>
                <div class="info-value">{{ $ong['cnpj'] }}</div>
              </div>
            </li>
            <li>
              <span class="info-icon">📍</span>
              <div>
                <div class="info-label">Cidade / Estado</div>
                <div class="info-value">{{ $ong['cidade'] }}, {{ $ong['estado'] }}</div>
              </div>
            </li>
            <li>
              <span class="info-icon">🌐</span>
              <div>
                <div class="info-label">Site</div>
                <div class="info-value info-link">{{ $ong['site'] }}</div>
              </div>
            </li>
            <li>
              <span class="info-icon">📅</span>
              <div>
                <div class="info-label">Cadastro aprovado em</div>
                <div class="info-value">{{ $ong['aprovado_em'] }}</div>
              </div>
            </li>
          </ul>
        </section>

        <section class="panel">
          <h2 class="panel-title">Atividade recente</h2>
          <ul class="activity-list">
            @forelse ($atividades ?? [] as $atividade)
              <li class="activity-row">
                <span class="dot dot-{{ $atividade['cor'] ?? 'green' }}"></span>
                <div>
                  <div class="activity-text">{!! $atividade['texto'] ?? '' !!}</div>
                  <div class="activity-time">{{ $atividade['tempo'] ?? '' }}</div>
                </div>
              </li>
            @empty
              <p class="text-sm text-gray-500">Nenhuma atividade recente.</p>
            @endforelse
          </ul>
        </section>

        <section class="panel">
          <h2 class="panel-title">Acesso rápido</h2>
          <div class="quick-actions">
            <button class="btn btn-primary btn-block">+ Criar novo evento</button>
            <button class="btn btn-outline btn-block">Convidar organizador</button>
            <button class="btn btn-outline btn-block">Ver relatório completo</button>
          </div>
        </section>

      </div>

    </div>

  </main>
</div>

</body>
</html>