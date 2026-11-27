<footer class="footer">
    <div class="footer-top">
        <div class="footer-brand">
            <a aria-label="Ajudae — início" data-brand-home href="{{ config('identity.enabled') ? route('inicio') : \App\Support\NavigationContext::homeUrl() }}" class="navbar-brand">
                <span class="brand-aju-white">Ajud</span><span class="brand-dae">ae</span>
            </a>
            <p>Plataforma de voluntariado gamificado</p>
        </div>

        <ul class="footer-links">
            <li><a href="/termos">Termos de Uso</a></li>
            <li><a href="/privacidade">Política de Privacidade (LGPD)</a></li>
            <li><a href="{{ route('ong.cadastrar.form') }}">Para ONGs</a></li>
            <li><a href="{{ route('privacidade') }}#contato">Informações de contato</a></li>
        </ul>
    </div>

    <div class="footer-bottom">
        <p>© {{ date('Y') }} Ajudae — Pessoas, causas e oportunidades.</p>
    </div>
</footer>
