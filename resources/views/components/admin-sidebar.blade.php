<input type="checkbox" id="admin-menu" class="ong-menu-check">

<label for="admin-menu" class="ong-menu-btn">☰</label>

<label for="admin-menu" class="ong-menu-bg"></label>

<aside class="ong-sidebar admin-sidebar">

    <a href="{{ route('admin.admin') }}" class="ong-sidebar-brand">
        <span class="ong-sidebar-nome">
            <span class="brand-aju-white">Ajud</span><span class="brand-dae">ae</span>
        </span>
    </a>

    <hr class="admin-sidebar-rule">

    <span class="ong-sidebar-org">
        <svg class="admin-org-ico" viewBox="0 0 24 24" aria-hidden="true">
            <path
                fill="currentColor"
                d="M19.14 12.94c.04-.31.06-.63.06-.94s-.02-.63-.06-.94l2.03-1.58a.5.5 0 0 0 .12-.64l-1.92-3.32a.5.5 0 0 0-.6-.22l-2.39.96a7.07 7.07 0 0 0-1.63-.94l-.36-2.54A.5.5 0 0 0 13.9 2h-3.8a.5.5 0 0 0-.5.42l-.36 2.54c-.59.24-1.13.55-1.63.94l-2.39-.96a.5.5 0 0 0-.6.22L2.7 8.48a.5.5 0 0 0 .12.64l2.03 1.58c-.04.31-.06.63-.06.94s.02.63.06.94L2.82 14.16a.5.5 0 0 0-.12.64l1.92 3.32c.13.23.4.32.64.22l2.39-.96c.5.39 1.04.7 1.63.94l.36 2.54c.05.24.26.42.5.42h3.8c.24 0 .45-.18.5-.42l.36-2.54c.59-.24 1.13-.55 1.63-.94l2.39.96c.24.1.51 0 .64-.22l1.92-3.32a.5.5 0 0 0-.12-.64l-2.03-1.58ZM12 15.6A3.6 3.6 0 1 1 12 8.4a3.6 3.6 0 0 1 0 7.2Z"
            />
        </svg>

        Administrador
    </span>

    <p class="ong-sidebar-label">
        Moderação
    </p>

    <nav class="ong-sidebar-nav">

        <a
            href="{{ route('admin.admin') }}"
            @class([
                'ong-sidebar-link',
                'is-active' => request()->routeIs('admin.admin')
            ])
        >
            <span class="ong-sidebar-ico">▦</span>
            Painel admin
        </a>

        <a
            href="{{ route('admin.fila-eventos') }}"
            @class([
                'ong-sidebar-link',
                'is-active' => request()->routeIs('admin.fila-eventos')
            ])
        >
            <span class="ong-sidebar-ico">📋</span>
            Fila de eventos
            <span class="ong-badge">7</span>
        </a>

        <a
            href="{{ route('admin.cadastros-ong') }}"
            @class([
                'ong-sidebar-link',
                'is-active' => request()->routeIs('admin.cadastros-ong')
            ])
        >
            <span class="ong-sidebar-ico">🏢</span>
            Cadastros ONG
            <span class="ong-badge">1</span>
        </a>

        <a
            href="{{ route('admin.upgrades-org') }}"
            @class([
                'ong-sidebar-link',
                'is-active' => request()->routeIs('admin.upgrades-org')
            ])
        >
            <span class="ong-sidebar-ico">⬆️</span>
            Upgrades org.
            <span class="ong-badge">3</span>
        </a>

        <a
            href="{{ route('admin.denuncias') }}"
            @class([
                'ong-sidebar-link',
                'is-active' => request()->routeIs('admin.denuncias')
            ])
        >
            <span class="ong-sidebar-ico">🚩</span>
            Denúncias
            <span class="ong-badge ong-badge-red">2</span>
        </a>

        <a
            href="{{ route('admin.solicitacoes-lgpd') }}"
            @class([
                'ong-sidebar-link',
                'is-active' => request()->routeIs('admin.solicitacoes-lgpd')
            ])
        >
            <span class="ong-sidebar-ico">🔒</span>
            Solicit. LGPD
            <span class="ong-badge">4</span>
        </a>

    </nav>

    <p class="ong-sidebar-label">
        Plataforma
    </p>

    <nav class="ong-sidebar-nav">

        <a
            href="{{ route('admin.usuarios') }}"
            @class([
                'ong-sidebar-link',
                'is-active' => request()->routeIs('admin.usuarios')
            ])
        >
            <span class="ong-sidebar-ico">👥</span>
            Usuários
        </a>

        <a
            href="{{ route('admin.relatorios') }}"
            @class([
                'ong-sidebar-link',
                'is-active' => request()->routeIs('admin.relatorios')
            ])
        >
            <span class="ong-sidebar-ico">📊</span>
            Relatórios
        </a>

        <a
            href="{{ route('admin.log-acoes') }}"
            @class([
                'ong-sidebar-link',
                'is-active' => request()->routeIs('admin.log-acoes')
            ])
        >
            <span class="ong-sidebar-ico">📜</span>
            Log de ações
        </a>

        <a
            href="{{ route('admin.configuracoes') }}"
            @class([
                'ong-sidebar-link',
                'is-active' => request()->routeIs('admin.configuracoes')
            ])
        >
            <span class="ong-sidebar-ico">⚙️</span>
            Configurações
        </a>

    </nav>

    <div class="ong-sidebar-user">
        <span class="ong-avatar">
            AD
        </span>

        <div>
            <strong>Admin</strong>
            <small>Superadministrador</small>
        </div>
    </div>

</aside>