<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários — Ajudaê</title>
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
                    'label' => 'Usuários',
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
                <h1>Usuários</h1>
                <p>2.418 usuários ativos · 34 novos hoje</p>
            </div>
            <div class="fila-resumo">
                <div><strong>2.418</strong><span>Ativos</span></div>
                <div><strong>34</strong><span>Novos hoje</span></div>
                <div><strong>3</strong><span>Suspensos</span></div>
            </div>
        </header>

        <div class="cand-toolbar">
            <label class="cand-busca">
                <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true">
                    <circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="1.8"/>
                    <path d="m20 20-3-3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
                <input type="search" placeholder="Buscar por nome ou e-mail...">
            </label>
            <button type="button" class="cand-chip is-on">Todos</button>
            <button type="button" class="cand-chip">Voluntários</button>
            <button type="button" class="cand-chip">Organizadores</button>
            <button type="button" class="cand-chip">Suspensos</button>
            <button type="button" class="cand-aprovar-lote">Exportar CSV</button>
        </div>

        <div class="admin-tabela-wrap">
            <div class="admin-tabela-scroll">
                <table class="admin-tabela">
                    <thead>
                        <tr>
                            <th>Usuário</th>
                            <th>Papel</th>
                            <th>Nível</th>
                            <th>Eventos</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><div class="tab-user"><span class="tab-user-av">AC</span><div><strong>Ana Costa</strong><small>ana.costa@email.com</small></div></div></td>
                            <td><span class="admin-tag admin-tag-ouro">Organizador</span></td>
                            <td>Nível 7</td>
                            <td>24</td>
                            <td><span class="admin-tag admin-tag-verde">Ativo</span></td>
                            <td>
                                <div class="tab-acoes">
                                    <a href="#" title="Ver perfil"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5c-5 0-9 4.5-10 7 1 2.5 5 7 10 7s9-4.5 10-7c-1-2.5-5-7-10-7Z" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="1.6"/></svg></a>
                                    <a href="#" class="is-perigo" title="Suspender"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M6.5 6.5l11 11" stroke="currentColor" stroke-width="1.6"/></svg></a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><div class="tab-user"><span class="tab-user-av">RT</span><div><strong>Roberto Torres</strong><small>roberto.torres@email.com</small></div></div></td>
                            <td><span class="admin-tag admin-tag-cinza">Voluntário</span></td>
                            <td>Nível 4</td>
                            <td>12</td>
                            <td><span class="admin-tag admin-tag-verde">Ativo</span></td>
                            <td>
                                <div class="tab-acoes">
                                    <a href="#" title="Ver perfil"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5c-5 0-9 4.5-10 7 1 2.5 5 7 10 7s9-4.5 10-7c-1-2.5-5-7-10-7Z" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="1.6"/></svg></a>
                                    <a href="#" class="is-perigo" title="Suspender"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M6.5 6.5l11 11" stroke="currentColor" stroke-width="1.6"/></svg></a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><div class="tab-user"><span class="tab-user-av">MS</span><div><strong>Mariana Santos</strong><small>mariana.santos@email.com</small></div></div></td>
                            <td><span class="admin-tag admin-tag-ouro">Organizador</span></td>
                            <td>Nível 8</td>
                            <td>31</td>
                            <td><span class="admin-tag admin-tag-verde">Ativo</span></td>
                            <td>
                                <div class="tab-acoes">
                                    <a href="#" title="Ver perfil"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5c-5 0-9 4.5-10 7 1 2.5 5 7 10 7s9-4.5 10-7c-1-2.5-5-7-10-7Z" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="1.6"/></svg></a>
                                    <a href="#" class="is-perigo" title="Suspender"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M6.5 6.5l11 11" stroke="currentColor" stroke-width="1.6"/></svg></a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><div class="tab-user"><span class="tab-user-av">LA</span><div><strong>Lucas Andrade</strong><small>lucas.andrade@email.com</small></div></div></td>
                            <td><span class="admin-tag admin-tag-cinza">Voluntário</span></td>
                            <td>Nível 5</td>
                            <td>18</td>
                            <td><span class="admin-tag admin-tag-verde">Ativo</span></td>
                            <td>
                                <div class="tab-acoes">
                                    <a href="#" title="Ver perfil"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5c-5 0-9 4.5-10 7 1 2.5 5 7 10 7s9-4.5 10-7c-1-2.5-5-7-10-7Z" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="1.6"/></svg></a>
                                    <a href="#" class="is-perigo" title="Suspender"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M6.5 6.5l11 11" stroke="currentColor" stroke-width="1.6"/></svg></a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><div class="tab-user"><span class="tab-user-av">RA</span><div><strong>Ricardo Alves</strong><small>ricardo.alves@email.com</small></div></div></td>
                            <td><span class="admin-tag admin-tag-cinza">Voluntário</span></td>
                            <td>Nível 3</td>
                            <td>7</td>
                            <td><span class="admin-tag admin-tag-vermelho">Suspenso</span></td>
                            <td>
                                <div class="tab-acoes">
                                    <a href="#" title="Ver perfil"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5c-5 0-9 4.5-10 7 1 2.5 5 7 10 7s9-4.5 10-7c-1-2.5-5-7-10-7Z" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="1.6"/></svg></a>
                                    <a href="#" class="is-perigo" title="Reativar"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M6.5 6.5l11 11" stroke="currentColor" stroke-width="1.6"/></svg></a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><div class="tab-user"><span class="tab-user-av">BL</span><div><strong>Beatriz Lima</strong><small>beatriz.lima@email.com</small></div></div></td>
                            <td><span class="admin-tag admin-tag-ouro">Organizador</span></td>
                            <td>Nível 6</td>
                            <td>15</td>
                            <td><span class="admin-tag admin-tag-verde">Ativo</span></td>
                            <td>
                                <div class="tab-acoes">
                                    <a href="#" title="Ver perfil"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5c-5 0-9 4.5-10 7 1 2.5 5 7 10 7s9-4.5 10-7c-1-2.5-5-7-10-7Z" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="1.6"/></svg></a>
                                    <a href="#" class="is-perigo" title="Suspender"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M6.5 6.5l11 11" stroke="currentColor" stroke-width="1.6"/></svg></a>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
</body>
</html>