@props(['active' => 'dashboard'])

<aside class="sidebar">
  <div>
    <a href="{{ route('dashboard') }}" class="logo">Ajud<span class="logo-accent">ae</span></a>
    <span class="role-pill">Organizador</span>

    <div class="nav-group">
      <p class="nav-label">Principal</p>

      <a href="{{ route('dashboard') }}" class="nav-item {{ $active === 'dashboard' ? 'active' : '' }}">
        <svg class="nav-icon w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/>
          <rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>
        </svg>
        Dashboard
      </a>

      <a href="{{ route('eventos.index') }}" class="nav-item {{ $active === 'eventos' ? 'active' : '' }}">
        <svg class="nav-icon w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h10"/>
        </svg>
        <span>Meus eventos</span>
        <span class="badge badge-neutral">{{ $eventosCount ?? 3 }}</span>
      </a>

      <a href="{{ route('candidatos.index') }}" class="nav-item {{ $active === 'candidatos' ? 'active' : '' }}">
        <svg class="nav-icon w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-1a4 4 0 00-3-3.87M9 20H4v-1a4 4 0 013-3.87m5-2.13a4 4 0 100-8 4 4 0 000 8zm6 0a4 4 0 10-1-7.87"/>
        </svg>
        <span>Candidatos</span>
        <span class="badge badge-neutral">{{ $candidatosCount ?? 12 }}</span>
      </a>
    </div>

    <div class="nav-group">
      <p class="nav-label">Conta</p>

      <a href="{{ route('perfil') }}" class="nav-item {{ $active === 'perfil' ? 'active' : '' }}">
        <svg class="nav-icon w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="8" r="4"/><path stroke-linecap="round" d="M4 20c0-3.9 3.6-7 8-7s8 3.1 8 7"/>
        </svg>
        Meu perfil
      </a>

      <a href="{{ route('notificacoes') }}" class="nav-item {{ $active === 'notificacoes' ? 'active' : '' }}">
        <svg class="nav-icon w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 00-4-5.65V5a2 2 0 10-4 0v.35A6 6 0 006 11v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <span>Notificações</span>
        @if(($notificacoesCount ?? 2) > 0)
          <span class="badge badge-alert">{{ $notificacoesCount ?? 2 }}</span>
        @endif
      </a>

      <a href="{{ route('configuracoes') }}" class="nav-item {{ $active === 'configuracoes' ? 'active' : '' }}">
        <svg class="nav-icon w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="3"/>
          <path stroke-linecap="round" stroke-linejoin="round" d="M19.4 15a1.7 1.7 0 00.34 1.87l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.7 1.7 0 00-1.87-.34 1.7 1.7 0 00-1 1.55V21a2 2 0 11-4 0v-.09a1.7 1.7 0 00-1-1.55 1.7 1.7 0 00-1.87.34l-.06.06a2 2 0 11-2.83-2.83l.06-.06a1.7 1.7 0 00.34-1.87 1.7 1.7 0 00-1.55-1H3a2 2 0 110-4h.09a1.7 1.7 0 001.55-1 1.7 1.7 0 00-.34-1.87l-.06-.06a2 2 0 112.83-2.83l.06.06a1.7 1.7 0 001.87.34H9a1.7 1.7 0 001-1.55V3a2 2 0 114 0v.09a1.7 1.7 0 001 1.55 1.7 1.7 0 001.87-.34l.06-.06a2 2 0 112.83 2.83l-.06.06a1.7 1.7 0 00-.34 1.87V9a1.7 1.7 0 001.55 1H21a2 2 0 110 4h-.09a1.7 1.7 0 00-1.55 1z"/>
        </svg>
        Configurações
      </a>
    </div>
  </div>

  <div class="sidebar-user">
    <div class="avatar avatar-user">{{ auth()->user()->initials() ?? 'CF' }}</div>
    <div>
      <div class="user-name">{{ auth()->user()->name ?? 'Carlos Ferreira' }}</div>
      <div class="user-org">{{ auth()->user()->organizacao->nome ?? 'Instituto Esperança' }}</div>
    </div>
  </div>
</aside>