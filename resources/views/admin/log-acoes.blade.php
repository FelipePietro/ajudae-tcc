<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log de ações — Ajudaê</title>
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
                    'label' => 'Log de ações',
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
                <h1>Log de ações</h1>
                <p>Registro de todas as ações de moderação · 1.204 eventos</p>
            </div>
            <div class="fila-resumo">
                <div><strong>48</strong><span>Hoje</span></div>
                <div><strong>312</strong><span>No mês</span></div>
            </div>
        </header>

        <div class="cand-toolbar">
            <label class="cand-busca">
                <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true">
                    <circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="1.8"/>
                    <path d="m20 20-3-3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
                <input type="search" placeholder="Buscar por ação, admin ou alvo...">
            </label>
            <button type="button" class="cand-chip is-on">Todas</button>
            <button type="button" class="cand-chip">Aprovações</button>
            <button type="button" class="cand-chip">Recusas</button>
            <button type="button" class="cand-chip">Suspensões</button>
            <button type="button" class="cand-aprovar-lote">Exportar CSV</button>
        </div>

        <div class="admin-tabela-wrap">
            <div class="admin-tabela-scroll">
                <table class="admin-tabela">
                    <thead>
                        <tr>
                            <th>Data / hora</th>
                            <th>Administrador</th>
                            <th>Ação</th>
                            <th>Alvo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>27 mai. · 22:18</td>
                            <td>Admin</td>
                            <td><span class="admin-tag admin-tag-verde">Aprovação</span></td>
                            <td>Evento “Doação de Roupas” publicado</td>
                        </tr>
                        <tr>
                            <td>27 mai. · 21:40</td>
                            <td>Admin</td>
                            <td><span class="admin-tag admin-tag-vermelho">Recusa</span></td>
                            <td>Evento “Barraca de Saúde” — local inválido</td>
                        </tr>
                        <tr>
                            <td>27 mai. · 20:05</td>
                            <td>Admin</td>
                            <td><span class="admin-tag admin-tag-verde">Verificação</span></td>
                            <td>ONG “Ação Verde” ativada</td>
                        </tr>
                        <tr>
                            <td>27 mai. · 18:30</td>
                            <td>Admin</td>
                            <td><span class="admin-tag admin-tag-ouro">Upgrade negado</span></td>
                            <td>Roberto Torres — XP insuficiente</td>
                        </tr>
                        <tr>
                            <td>26 mai. · 16:12</td>
                            <td>Admin</td>
                            <td><span class="admin-tag admin-tag-verde">Upgrade aprovado</span></td>
                            <td>Mariana Santos → Organizador</td>
                        </tr>
                        <tr>
                            <td>26 mai. · 14:50</td>
                            <td>Admin</td>
                            <td><span class="admin-tag admin-tag-vermelho">Suspensão</span></td>
                            <td>Usuário “Ricardo Alves” suspenso</td>
                        </tr>
                        <tr>
                            <td>26 mai. · 11:20</td>
                            <td>Admin</td>
                            <td><span class="admin-tag admin-tag-cinza">LGPD</span></td>
                            <td>Exportação de dados enviada — Ana Costa</td>
                        </tr>
                        <tr>
                            <td>25 mai. · 09:45</td>
                            <td>Admin</td>
                            <td><span class="admin-tag admin-tag-vermelho">Recusa</span></td>
                            <td>Cadastro ONG “Casa Aberta” — documentação incompleta</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
</body>
</html>