<input type="checkbox" id="ong-menu" class="ong-menu-check">
<label for="ong-menu" class="ong-menu-btn">☰</label>
<label for="ong-menu" class="ong-menu-bg"></label>

<aside class="ong-sidebar admin-sidebar">
    <a href="admin" class="ong-sidebar-brand">
        <span class="ong-sidebar-nome"><span class="brand-aju-white">Ajud</span><span class="brand-dae">ae</span></span>
    </a>
    <hr class="admin-sidebar-rule">
    <span class="ong-sidebar-org">
        <svg class="admin-org-ico" viewBox="0 0 24 24" aria-hidden="true">
            <path fill="currentColor" d="M19.14 12.94c.04-.31.06-.63.06-.94s-.02-.63-.06-.94l2.03-1.58a.5.5 0 0 0 .12-.64l-1.92-3.32a.5.5 0 0 0-.6-.22l-2.39.96a7.07 7.07 0 0 0-1.63-.94l-.36-2.54A.5.5 0 0 0 13.9 2h-3.8a.5.5 0 0 0-.5.42l-.36 2.54c-.59.24-1.13.55-1.63.94l-2.39-.96a.5.5 0 0 0-.6.22L2.7 8.48a.5.5 0 0 0 .12.64l2.03 1.58c-.04.31-.06.63-.06.94s.02.63.06.94L2.82 14.16a.5.5 0 0 0-.12.64l1.92 3.32c.13.23.4.32.64.22l2.39-.96c.5.39 1.04.7 1.63.94l.36 2.54c.05.24.26.42.5.42h3.8c.24 0 .45-.18.5-.42l.36-2.54c.59-.24 1.13-.55 1.63-.94l2.39.96c.24.1.51 0 .64-.22l1.92-3.32a.5.5 0 0 0-.12-.64l-2.03-1.58ZM12 15.6A3.6 3.6 0 1 1 12 8.4a3.6 3.6 0 0 1 0 7.2Z"/>
        </svg>
        Administrador
    </span>

    <p class="ong-sidebar-label">Moderação</p>
    <nav class="ong-sidebar-nav">
        <a href="admin" @class(['ong-sidebar-link', 'is-active' => request()->is('admin')])><span class="ong-sidebar-ico">▦</span> Painel admin</a>
        <a href="fila-eventos" @class(['ong-sidebar-link', 'is-active' => request()->is('fila-eventos')])><span class="ong-sidebar-ico">📋</span> Fila de eventos <span class="ong-badge">7</span></a>
        <a href="cadastros-ong" @class(['ong-sidebar-link', 'is-active' => request()->is('cadastros-ong')])><span class="ong-sidebar-ico">🏢</span> Cadastros ONG <span class="ong-badge">1</span></a>
        <a href="upgrades-org" @class(['ong-sidebar-link', 'is-active' => request()->is('upgrades-org')])><span class="ong-sidebar-ico">⬆️</span> Upgrades org. <span class="ong-badge">3</span></a>
        <a href="denuncias" @class(['ong-sidebar-link', 'is-active' => request()->is('denuncias')])><span class="ong-sidebar-ico">🚩</span> Denúncias <span class="ong-badge ong-badge-red">2</span></a>
        <a href="solicitacoes-lgpd" @class(['ong-sidebar-link', 'is-active' => request()->is('solicitacoes-lgpd')])><span class="ong-sidebar-ico">🔒</span> Solicit. LGPD <span class="ong-badge">4</span></a>
    </nav>

    <p class="ong-sidebar-label">Plataforma</p>
    <nav class="ong-sidebar-nav">
        <a href="usuarios" @class(['ong-sidebar-link', 'is-active' => request()->is('usuarios')])><span class="ong-sidebar-ico">👥</span> Usuários</a>
        <a href="relatorios" @class(['ong-sidebar-link', 'is-active' => request()->is('relatorios')])><span class="ong-sidebar-ico">📊</span> Relatórios</a>
        <a href="log-acoes" @class(['ong-sidebar-link', 'is-active' => request()->is('log-acoes')])><span class="ong-sidebar-ico">📜</span> Log de ações</a>
        <a href="configuracoes" @class(['ong-sidebar-link', 'is-active' => request()->is('configuracoes')])><span class="ong-sidebar-ico">⚙️</span> Configurações</a>
    </nav>

    <div class="ong-sidebar-user">
        <span class="ong-avatar">AD</span>
        <div>
            <strong>Admin</strong>
            <small>Superadministrador</small>
        </div>
    </div>
</aside>