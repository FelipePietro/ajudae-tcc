<input type="checkbox" id="ong-menu" class="ong-menu-check">
<label for="ong-menu" class="ong-menu-btn">☰</label>
<label for="ong-menu" class="ong-menu-bg"></label>

<aside class="ong-sidebar">
    <a href="/dashboard" class="ong-sidebar-brand">
        <span class="ong-sidebar-logo" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none">
                <path d="M12 3c1.8 3.2 2.2 5.4 1.4 7.2C12.6 12 11 13 9.5 14.2 8 15.4 7 17 7 19c2.4-1 4.4-1.2 6.2-.4 1.6.7 2.8 2.2 3.8 4.2 1.6-4.6.6-8.2-1.4-10.2C13.8 10.8 12.6 9.4 12 3Z" fill="#E6C56A"/>
            </svg>
        </span>
        <span class="ong-sidebar-nome">Ajudaê</span>
    </a>

    <button type="button" class="ong-sidebar-org">+ Organizador</button>

    <p class="ong-sidebar-label">Principal</p>
    <nav class="ong-sidebar-nav">
        <a href="/dashboard" class="ong-sidebar-link {{ request()->is('dashboard') ? 'is-active' : '' }}"><span class="ong-sidebar-ico">▣</span> Dashboard</a>
        <a href="#" class="ong-sidebar-link"><span class="ong-sidebar-ico">☰</span> Meus eventos <span class="ong-badge">3</span></a>
        <a href="/candidatos" class="ong-sidebar-link {{ request()->is('candidatos') ? 'is-active' : '' }}"><span class="ong-sidebar-ico">☺</span> Candidatos <span class="ong-badge">12</span></a>
    </nav>

    <p class="ong-sidebar-label">ONG</p>
    <nav class="ong-sidebar-nav">
        <a href="#" class="ong-sidebar-link"><span class="ong-sidebar-ico">⌂</span> Painel da ONG</a>
        <a href="#" class="ong-sidebar-link"><span class="ong-sidebar-ico">+</span> Criar evento</a>
    </nav>

    <p class="ong-sidebar-label">Conta</p>
    <nav class="ong-sidebar-nav">
        <a href="#" class="ong-sidebar-link"><span class="ong-sidebar-ico">○</span> Meu perfil</a>
        <a href="#" class="ong-sidebar-link"><span class="ong-sidebar-ico">◉</span> Notificações <span class="ong-badge">4</span></a>
        <a href="#" class="ong-sidebar-link"><span class="ong-sidebar-ico">⚙</span> Configurações</a>
    </nav>

    <div class="ong-sidebar-user">
        <span class="ong-avatar">CF</span>
        <div>
            <strong>Carlos Ferreira</strong>
            <small>Instituto Esperança</small>
        </div>
    </div>
</aside>