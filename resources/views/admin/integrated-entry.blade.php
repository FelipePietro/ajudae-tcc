<header class="fila-cabecalho">
    <div>
        <h1>{{ $title }}</h1>
        @if(isset(\App\Support\WebNavigation::for(request())->adminPermissions['lgpd.responder']))
            <p>Esta conta tem permissão para consultar e responder solicitações de privacidade.</p>
        @else
            <p>Esta conta tem permissão para consultar solicitações de privacidade.</p>
        @endif
    </div>
</header>
<div class="admin-fila admin-review-queue">
    <section class="admin-card">
        <header class="admin-card-topo"><h2>Solicitações de titulares</h2></header>
        <p class="admin-desc">A fila de solicitações ainda está indisponível.</p>
    </section>
</div>
