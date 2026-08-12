<nav class="navbar">
    <a href="/" class="navbar-brand">
        <span class="brand-aju">Ajud</span><span class="brand-dae">aê</span>
    </a>

    <ul class="navbar-links">
        <li><a href="/feed">Feed</a></li>
        <li><a href="/candidaturas">Candidaturas</a></li>
        <li><a href="/perfil">Meu Perfil</a></li>
        <li><a href="/ranking">Ranking</a></li>
        <li>
            <a href="/notificacoes" class="btn-icone">🔔</a>
        </li>
        <li>
            <a href="/perfil" class="avatar">LP</a>
            {{-- Para usar depois: <a href="/perfil" class="avatar">{{ substr(Auth::user()->nm_pessoa, 0, 2) }}</a>--}}
        </li>
    </ul>
</nav>
