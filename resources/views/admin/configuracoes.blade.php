<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações — Ajudae</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="ong-body">
<div class="ong-shell">
            <x-admin-sidebar />

            <main class="ong-main admin-main">
                <div class="admin-topo">
        <x-breadcrumb-admin
            :itens="[
                [
                    'label' => 'Configurações',
                ]
            ]"
        >
            <x-slot:acoes>
                <div class="admin-topo-acoes">
                    <time datetime="2025-05-27T22:31">
                        qua., 27 de mai. · 22:31
                    </time>

                    <a
                        href="{{ route('admin.log-acoes') }}"
                        class="btn-secundario admin-log-btn"
                    >
                        Ver log completo
                    </a>
                </div>
            </x-slot:acoes>
        </x-breadcrumb-admin>

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