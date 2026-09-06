<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Meu perfil - Ajudae</title>
@vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/painel_ong.css', 'resources/css/perfil_ong.css'])
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:ital,wght@0,600;0,700;1,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="app">

  <x-sidebar active="perfil" />

  <main class="main">
    <div class="breadcrumb">Dashboard <span class="sep">/</span> <span class="current">Meu perfil</span></div>

    <section class="org-card" style="padding: 22px 26px; margin-bottom: 18px;">
      <div class="org-identity" style="display: flex; align-items: center; gap: 18px; width: 100%;">
        <div class="org-logo" style="width: 72px; height: 72px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #cfe3d3; color: #1d3f2f; font-weight: 700; font-size: 32px;">
          {{ $usuario['iniciais'] ?? '??' }}
        </div>

        <div style="flex: 1; min-width: 0;">
          <h1 class="org-name" style="font-size: 42px; line-height: 1.1; margin: 0 0 8px;">{{ $usuario['nome'] }}</h1>

          <div class="org-sub" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; font-size: 15px; color: #6b7269;">
            <span>📍 {{ $usuario['cidade'] }}, {{ $usuario['estado'] }}</span>
            <span>•</span>
            <span>{{ $usuario['tipo'] ?? 'Organizador independente' }}</span>
            <span>•</span>
            <span>Desde {{ $usuario['membro_desde'] }}</span>
          </div>

          <div class="org-tags" style="margin-top: 14px; gap: 10px;">
            @if (!empty($usuario['verificado']))
              <span class="tag tag-active">✓ Verificado</span>
            @endif
            <span class="tag tag-cnpj">{{ $usuario['papel'] ?? 'Organizador' }}</span>
            @foreach ($usuario['areas'] ?? [] as $area)
              <span class="tag tag-plain">{{ $area }}</span>
            @endforeach
          </div>
        </div>
      </div>

      <div class="org-actions" style="margin-left: auto;">
        <button class="btn btn-outline">Compartilhar</button>
      </div>
    </section>

    <section class="stats-grid">
      <div class="stat-card">
        <div class="stat-value stat-dark">{{ $stats['eventos_organizados'] ?? 0 }}</div>
        <div class="stat-label">Eventos organizados</div>
      </div>
      <div class="stat-card">
        <div class="stat-value stat-dark">{{ $stats['voluntarios_gerenciados'] ?? 0 }}</div>
        <div class="stat-label">Voluntários gerenciados</div>
      </div>
      <div class="stat-card">
        <div class="stat-value stat-orange">{{ $stats['badges'] ?? 0 }}</div>
        <div class="stat-label">Badges conquistados</div>
      </div>
    </section>

    <nav class="tabs">
      <button class="tab active">Sobre</button>
      <button class="tab">Eventos criados</button>
      <button class="tab">Badges</button>
      <button class="tab">Configurações de conta</button>
    </nav>

    <div class="content-grid">
      <div class="left-col">
        @forelse ($eventosCriados as $evento)
          <section class="panel" style="background: #edf6ef; border-color: #dfeee3; padding: 18px 18px 12px;">
            <div class="panel-header" style="margin-bottom: 12px;">
              <div>
                <h2 class="panel-title" style="font-size: 18px;">{{ $evento['nome'] }}</h2>
              </div>
            </div>

            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 bg-[#EEF7F2] border border-[#c8e0d4] rounded-xl px-5 py-4">
              <div class="text-3xl bg-[#d4eddf] p-2.5 rounded-[10px] shrink-0">
                {{ $evento['icone'] ?? '🌿' }}
              </div>
              <div class="flex-1 min-w-0">
                <strong class="text-sm text-[#1a3a2a] block">{{ $evento['nome'] }}</strong>
                <p class="text-[0.82rem] text-gray-500 mt-1 flex flex-col sm:flex-row sm:gap-2">
                  <span>📅 {{ $evento['data'] ?? 'Data não informada' }}</span>
                  <span>📍 {{ $evento['local'] ?? 'Local não informado' }}</span>
                  <span>👥 {{ $evento['vagas'] ?? '0 vagas restantes' }}</span>
                </p>
              </div>
            </div>
          </section>
        @empty
          <section class="panel" style="padding: 18px;">
            <p class="text-sm text-gray-500">Nenhum evento criado ainda.</p>
          </section>
        @endforelse
      </div>

      <div class="right-col">
        <section class="panel">
          <h2 class="panel-title">Informações pessoais</h2>
          <div class="grid grid-cols-2 gap-x-4 gap-y-4 pt-3">
            <div>
              <p class="text-[12.5px] text-muted m-0 mb-1">E-mail</p>
              <p class="text-sm font-semibold m-0">{{ $usuario['email'] }}</p>
            </div>
            <div>
              <p class="text-[12.5px] text-muted m-0 mb-1">Telefone</p>
              <p class="text-sm font-semibold m-0">{{ $usuario['telefone'] }}</p>
            </div>
          </div>
        </section>
      </div>
    </div>
  </main>
</div>

</body>
</html>