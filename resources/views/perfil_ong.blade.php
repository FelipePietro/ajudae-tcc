<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Meu perfil - Ajudae</title>
@vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/perfil_ong.css'])
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:ital,wght@0,600;0,700;1,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="m-0 bg-bg text-text-main font-[family-name:var(--font-body)] antialiased">

<div class="flex min-h-screen">

  <!-- SIDEBAR -->
  <aside class="w-60 shrink-0 bg-sidebar flex flex-col">
    <div class="px-6 py-6">
      <a href="#" class="no-underline font-[family-name:var(--font-display)] font-bold text-[22px] text-white">
        Ajud<span class="text-accent">ae</span>
      </a>
    </div>

    <div class="px-4">
      <button type="button" class="w-full flex items-center gap-2 justify-center px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-white/10 hover:bg-white/15 transition">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
        Organizador
      </button>
    </div>

    <nav class="px-4 mt-7 flex-1">
      <p class="px-2 text-[11px] font-semibold uppercase tracking-[0.08em] text-sidebar-muted mb-2">Principal</p>
      <ul class="flex flex-col gap-1 mb-6">
        <li>
          <a href="#" class="no-underline flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-sidebar-muted hover:bg-sidebar-hover hover:text-white transition">
            <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
            Dashboard
          </a>
        </li>
        <li>
          <a href="#" class="no-underline flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-sidebar-muted hover:bg-sidebar-hover hover:text-white transition">
            <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h10"/></svg>
            <span class="flex-1">Meus eventos</span>
            <span class="text-[11px] font-semibold text-white bg-accent rounded-full px-2 py-0.5">3</span>
          </a>
        </li>
        <li>
          <a href="#" class="no-underline flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-sidebar-muted hover:bg-sidebar-hover hover:text-white transition">
            <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-1a4 4 0 00-3-3.87M9 20H4v-1a4 4 0 013-3.87m5-2.13a4 4 0 100-8 4 4 0 000 8zm6 0a4 4 0 10-1-7.87"/></svg>
            <span class="flex-1">Candidatos</span>
            <span class="text-[11px] font-semibold text-white bg-accent rounded-full px-2 py-0.5">12</span>
          </a>
        </li>
      </ul>

      <p class="px-2 text-[11px] font-semibold uppercase tracking-[0.08em] text-sidebar-muted mb-2">Conta</p>
      <ul class="flex flex-col gap-1">
        <li>
          <a href="#" class="no-underline flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-white bg-sidebar-hover">
            <svg class="w-[18px] h-[18px] text-accent" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path stroke-linecap="round" d="M4 20c0-3.9 3.6-7 8-7s8 3.1 8 7"/></svg>
            Meu perfil
          </a>
        </li>
        <li>
          <a href="#" class="no-underline flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-sidebar-muted hover:bg-sidebar-hover hover:text-white transition">
            <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 00-4-5.65V5a2 2 0 10-4 0v.35A6 6 0 006 11v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            <span class="flex-1">Notificações</span>
            <span class="text-[11px] font-semibold text-white bg-green-text rounded-full px-2 py-0.5">2</span>
          </a>
        </li>
        <li>
          <a href="#" class="no-underline flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-sidebar-muted hover:bg-sidebar-hover hover:text-white transition">
            <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.4 15a1.7 1.7 0 00.34 1.87l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.7 1.7 0 00-1.87-.34 1.7 1.7 0 00-1 1.55V21a2 2 0 11-4 0v-.09a1.7 1.7 0 00-1-1.55 1.7 1.7 0 00-1.87.34l-.06.06a2 2 0 11-2.83-2.83l.06-.06a1.7 1.7 0 00.34-1.87 1.7 1.7 0 00-1.55-1H3a2 2 0 110-4h.09a1.7 1.7 0 001.55-1 1.7 1.7 0 00-.34-1.87l-.06-.06a2 2 0 112.83-2.83l.06.06a1.7 1.7 0 001.87.34H9a1.7 1.7 0 001-1.55V3a2 2 0 114 0v.09a1.7 1.7 0 001 1.55 1.7 1.7 0 001.87-.34l.06-.06a2 2 0 112.83 2.83l-.06.06a1.7 1.7 0 00-.34 1.87V9a1.7 1.7 0 001.55 1H21a2 2 0 110 4h-.09a1.7 1.7 0 00-1.55 1z"/></svg>
            Configurações
          </a>
        </li>
      </ul>
    </nav>

    <div class="px-4 py-4 border-t border-sidebar-border flex items-center gap-3">
      <div class="w-9 h-9 rounded-full bg-accent text-white text-[13px] font-bold flex items-center justify-center shrink-0">CF</div>
      <div class="leading-tight">
        <p class="text-sm font-semibold text-white m-0">Carlos Ferreira</p>
        <p class="text-[12px] text-sidebar-muted m-0">Instituto Esperança</p>
      </div>
    </div>
  </aside>

  <!-- MAIN -->
  <div class="flex-1 flex flex-col min-w-0">

    <!-- TOPBAR -->
    <header class="flex items-center justify-between px-8 py-4 border-b border-border">
      <div class="flex items-center gap-2 text-sm">
        <a href="#" class="no-underline text-text-muted hover:text-text-main">Dashboard</a>
        <span class="text-text-muted">/</span>
        <span class="font-semibold text-text-main">Meu perfil</span>
      </div>
      <div class="flex items-center gap-3">
        <span class="inline-flex items-center gap-1.5 bg-accent-soft text-accent-dark text-[13px] font-semibold px-3.5 py-1.5 rounded-full">🏆 #18 Global</span>
        <button type="button" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-sm font-semibold text-white bg-green-dark hover:opacity-90 transition">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2v-5M18.5 2.5a2.1 2.1 0 013 3L12 15l-4 1 1-4z"/></svg>
          Editar perfil
        </button>
      </div>
    </header>

    <main class="flex-1 px-8 py-7">

      <!-- PROFILE CARD -->
      <section class="bg-card border border-border rounded-2xl mb-6 overflow-hidden">
        <div class="h-1.5 bg-gradient-to-r from-accent to-accent-dark"></div>
        <div class="p-7">
          <div class="flex items-start justify-between">
            <div class="flex items-start gap-5">
              <div class="relative shrink-0">
                <div class="w-20 h-20 rounded-full bg-mint text-green-text text-2xl font-bold flex items-center justify-center border-4 border-card">CF</div>
              </div>
              <div>
                <h1 class="font-[family-name:var(--font-serif)] font-bold text-[26px] m-0">Carlos Ferreira</h1>
                <div class="flex items-center gap-1.5 text-sm text-text-muted mt-1.5">
                  <span class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s7-6.5 7-12a7 7 0 10-14 0c0 5.5 7 12 7 12z"/><circle cx="12" cy="9" r="2.5"/></svg>
                    São Paulo, SP
                  </span>
                  <span>·</span>
                  <span>Organizador independente</span>
                  <span>·</span>
                  <span>Desde jan. 2024</span>
                </div>
                <div class="flex flex-wrap items-center gap-2 mt-3">
                  <span class="inline-flex items-center gap-1 text-[12.5px] font-semibold text-green-text bg-green-soft rounded-full px-3 py-1">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Verificado
                  </span>
                  <span class="text-[12.5px] font-semibold text-blue-text bg-blue-soft rounded-full px-3 py-1">Organizador</span>
                  <span class="text-[12.5px] font-semibold text-text-muted bg-field border border-border rounded-full px-3 py-1">Meio Ambiente</span>
                  <span class="text-[12.5px] font-semibold text-text-muted bg-field border border-border rounded-full px-3 py-1">Educação</span>
                </div>
              </div>
            </div>
            <button type="button" class="shrink-0 inline-flex items-center px-5 py-2.5 rounded-full text-sm font-semibold border border-text-main text-text-main hover:bg-black/[0.04] transition">Compartilhar</button>
          </div>
      </section>

      <!-- STATS -->
      <section class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6 max-w-4xl mx-auto">
        <div class="bg-card border border-border rounded-2xl p-6 text-center">
          <p class="font-[family-name:var(--font-serif)] font-bold text-3xl m-0">14</p>
          <p class="text-[13px] text-text-muted mt-1.5 mb-0">Eventos organizados</p>
        </div>
        <div class="bg-card border border-border rounded-2xl p-6 text-center">
          <p class="font-[family-name:var(--font-serif)] font-bold text-3xl m-0">87</p>
          <p class="text-[13px] text-text-muted mt-1.5 mb-0">Voluntários gerenciados</p>
        </div>
        <div class="bg-card border border-border rounded-2xl p-6 text-center">
          <p class="font-[family-name:var(--font-serif)] font-bold text-3xl text-accent-dark m-0">8</p>
          <p class="text-[13px] text-text-muted mt-1.5 mb-0">Badges conquistados</p>
        </div>
      </section>

      <!-- TABS + CONTENT -->
      <section class="bg-card border border-border rounded-2xl">
        <div class="flex items-center gap-7 px-7 border-b border-border overflow-x-auto">
          <a href="#" class="no-underline whitespace-nowrap text-sm font-semibold text-text-main border-b-2 border-text-main py-4">Sobre</a>
          <a href="#" class="no-underline whitespace-nowrap text-sm text-text-muted hover:text-text-main py-4">Eventos criados</a>
          <a href="#" class="no-underline whitespace-nowrap text-sm text-text-muted hover:text-text-main py-4">Badges</a>
          <a href="#" class="no-underline whitespace-nowrap text-sm text-text-muted hover:text-text-main py-4">Histórico de XP</a>
          <a href="#" class="no-underline whitespace-nowrap text-sm text-text-muted hover:text-text-main py-4">Configurações de conta</a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-[1fr_380px] gap-8 p-7">

          <!-- LEFT COLUMN -->
          <div>
            <div class="pb-6 border-b border-border">
              <h3 class="text-sm font-semibold m-0 mb-3">Bio</h3>
              <p class="text-[14.5px] text-text-main leading-relaxed m-0">
                Apaixonado por impacto social e gestão de projetos voluntários. Organizo eventos ambientais e
                educacionais em São Paulo há mais de um ano. Acredito que voluntariado bem estruturado
                transforma tanto a comunidade quanto o próprio organizador.
              </p>
            </div>

            <div class="py-6 border-b border-border">
              <h3 class="text-sm font-semibold m-0 mb-3">Habilidades</h3>
              <div class="flex flex-wrap gap-2">
                <span class="text-[12.5px] font-semibold text-green-text bg-green-soft rounded-full px-3 py-1">Liderança</span>
                <span class="text-[12.5px] font-semibold text-green-text bg-green-soft rounded-full px-3 py-1">Gestão de pessoas</span>
                <span class="text-[12.5px] font-semibold text-green-text bg-green-soft rounded-full px-3 py-1">Comunicação</span>
                <span class="text-[12.5px] font-semibold text-green-text bg-green-soft rounded-full px-3 py-1">Logística de eventos</span>
                <span class="text-[12.5px] font-semibold text-green-text bg-green-soft rounded-full px-3 py-1">Primeiros socorros</span>
              </div>
            </div>

            <div class="pt-6">
              <h3 class="text-sm font-semibold m-0 mb-3">Interesses</h3>
              <div class="flex flex-wrap gap-2">
                <span class="text-[12.5px] font-semibold text-accent-dark bg-accent-soft rounded-full px-3 py-1">Meio Ambiente</span>
                <span class="text-[12.5px] font-semibold text-accent-dark bg-accent-soft rounded-full px-3 py-1">Educação</span>
                <span class="text-[12.5px] font-semibold text-accent-dark bg-accent-soft rounded-full px-3 py-1">Habitação</span>
              </div>
            </div>
          </div>

          <!-- RIGHT COLUMN -->
          <div>
            <h3 class="text-sm font-semibold m-0 mb-4">Informações pessoais</h3>
            <div class="grid grid-cols-2 gap-x-4 gap-y-4 pb-6 border-b border-border">
              <div>
                <p class="text-[12.5px] text-text-muted m-0 mb-1">E-mail</p>
                <p class="text-sm font-semibold m-0">carlos@email.com</p>
              </div>
              <div>
                <p class="text-[12.5px] text-text-muted m-0 mb-1">Telefone</p>
                <p class="text-sm font-semibold m-0">(11) 99999-0000</p>
              </div>
              <div>
                <p class="text-[12.5px] text-text-muted m-0 mb-1">Cidade</p>
                <p class="text-sm font-semibold m-0">São Paulo, SP</p>
              </div>
              <div>
                <p class="text-[12.5px] text-text-muted m-0 mb-1">Membro desde</p>
                <p class="text-sm font-semibold m-0">Janeiro de 2024</p>
              </div>
              <div>
                <p class="text-[12.5px] text-text-muted m-0 mb-1">CPF</p>
                <p class="text-sm font-semibold m-0">000.***.**-00 <span class="font-normal text-text-muted">(protegido)</span></p>
              </div>
              <div>
                <p class="text-[12.5px] text-text-muted m-0 mb-1">Papel atual</p>
                <p class="text-sm font-semibold m-0">Organizador independente</p>
              </div>
            </div>

            <h3 class="text-sm font-semibold m-0 mt-6 mb-3">ONG vinculada</h3>
            <div class="flex items-center gap-3 border border-border rounded-xl p-3.5 mb-4">
              <div class="w-10 h-10 rounded-lg bg-mint flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-green-text" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3C7 8 4 12 4 15.5A8 8 0 0012 21a8 8 0 008-8.5C20 9 17 8 12 3z"/></svg>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold m-0 truncate">Instituto Esperança Social</p>
                <p class="text-[12.5px] text-text-muted m-0">Funcionário · 2 eventos designados</p>
              </div>
              <span class="text-[12px] font-semibold text-green-text bg-green-soft rounded-full px-3 py-1 shrink-0">Aceito</span>
            </div>

            <div class="flex items-start gap-2.5 bg-blue-soft rounded-xl p-4">
              <span class="shrink-0">ℹ️</span>
              <p class="text-[13px] text-blue-text leading-relaxed m-0">
                Como <strong>Organizador</strong>, você pode criar e gerenciar eventos vinculados ou de forma
                independente. Seu perfil é visível para voluntários que se candidatam aos seus eventos.
              </p>
            </div>
          </div>

        </div>
      </section>

    </main>
  </div>
</div>

</body>
</html>