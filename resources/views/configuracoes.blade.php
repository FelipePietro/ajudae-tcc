<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações — Ajudaê</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="ong-body">
<div class="ong-shell">
    <x-admin-sidebar />

    <main class="ong-main admin-main">
        <div class="admin-topo">
            <nav class="admin-breadcrumb">
                <span>Dashboard</span>
                <span class="admin-breadcrumb-sep" aria-hidden="true">›</span>
                <strong>Configurações</strong>
            </nav>
            <div class="admin-topo-acoes">
                <time datetime="2025-05-27T22:31">qua., 27 de mai. · 22:31</time>
                <a href="admin" class="btn-secundario admin-log-btn">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="6" y="3" width="12" height="18" rx="2" fill="none" stroke="currentColor" stroke-width="1.8"/>
                        <path d="M9 3.5h6v3H9z" fill="currentColor"/>
                        <path d="M9 11h6M9 14.5h6M9 18h4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                    </svg>
                    Ver log completo
                </a>
            </div>
        </div>

        <header class="fila-cabecalho">
            <div>
                <h1>Configurações</h1>
                <p>Preferências da conta e da plataforma</p>
            </div>
        </header>

        <div class="cfg-grid">
            <section class="admin-widget">
                <h3>Perfil do administrador</h3>
                <div class="cfg-field">
                    <label>Nome</label>
                    <input class="cfg-input" type="text" value="Admin">
                </div>
                <div class="cfg-field">
                    <label>E-mail</label>
                    <input class="cfg-input" type="email" value="admin@ajudae.org">
                </div>
                <div class="cfg-field">
                    <label>Cargo</label>
                    <input class="cfg-input" type="text" value="Superadministrador" readonly>
                </div>
            </section>

            <section class="admin-widget">
                <h3>Notificações</h3>
                <div class="cfg-row">
                    <div class="cfg-row-info"><strong>Novos eventos na fila</strong><small>E-mail quando um evento é enviado</small></div>
                    <label class="cfg-switch"><input type="checkbox" checked><span></span></label>
                </div>
                <div class="cfg-row">
                    <div class="cfg-row-info"><strong>Denúncia crítica</strong><small>Alerta imediato para casos graves</small></div>
                    <label class="cfg-switch"><input type="checkbox" checked><span></span></label>
                </div>
                <div class="cfg-row">
                    <div class="cfg-row-info"><strong>Vencimento de LGPD</strong><small>Avisar quando faltar menos de 48h</small></div>
                    <label class="cfg-switch"><input type="checkbox" checked><span></span></label>
                </div>
                <div class="cfg-row">
                    <div class="cfg-row-info"><strong>Resumo diário</strong><small>Relatório por e-mail toda manhã</small></div>
                    <label class="cfg-switch"><input type="checkbox"><span></span></label>
                </div>
            </section>

            <section class="admin-widget">
                <h3>Moderação</h3>
                <div class="cfg-field">
                    <label>XP mínimo para virar Organizador</label>
                    <input class="cfg-input" type="number" value="500">
                </div>
                <div class="cfg-field">
                    <label>Prazo de resposta LGPD (dias)</label>
                    <input class="cfg-input" type="number" value="15">
                </div>
                <div class="cfg-row">
                    <div class="cfg-row-info"><strong>Aprovação automática</strong><small>ONGs verificadas publicam sem revisão</small></div>
                    <label class="cfg-switch"><input type="checkbox"><span></span></label>
                </div>
            </section>

            <section class="admin-widget">
                <h3>Segurança</h3>
                <div class="cfg-row">
                    <div class="cfg-row-info"><strong>Autenticação em 2 fatores</strong><small>Exigir código extra no login</small></div>
                    <label class="cfg-switch"><input type="checkbox" checked><span></span></label>
                </div>
                <div class="cfg-row">
                    <div class="cfg-row-info"><strong>Sessões ativas</strong><small>2 dispositivos conectados</small></div>
                    <x-botao-secundario href="#" texto="Encerrar outras" />
                </div>
            </section>
        </div>

        <div class="cfg-acoes">
            <x-botao-secundario href="#" texto="Cancelar" />
            <x-botao-primario href="#" texto="Salvar alterações" />
        </div>
    </main>
</div>
</body>
</html>