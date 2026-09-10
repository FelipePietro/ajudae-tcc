<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios — Ajudaê</title>
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
                    'label' => 'Relatórios',
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
                <h1>Relatórios</h1>
                <p>Visão geral da plataforma · últimos 30 dias</p>
            </div>
            <div class="fila-resumo">
                <div><strong>87%</strong><span>Conclusão</span></div>
                <div><strong>4,7</strong><span>Avaliação</span></div>
            </div>
        </header>

        <div class="cand-toolbar">
            <button type="button" class="cand-chip">7 dias</button>
            <button type="button" class="cand-chip is-on">30 dias</button>
            <button type="button" class="cand-chip">90 dias</button>
            <button type="button" class="cand-chip">Ano</button>
            <button type="button" class="cand-aprovar-lote">Exportar relatório</button>
        </div>

        <section class="rel-stats">
            <article class="admin-stat admin-stat-laranja">
                <strong>142</strong>
                <p>Eventos publicados</p>
                <small>↑ 12% no mês</small>
            </article>
            <article class="admin-stat admin-stat-azul">
                <strong>1.284</strong>
                <p>Candidaturas</p>
                <small>↑ 8% no mês</small>
            </article>
            <article class="admin-stat admin-stat-amarelo">
                <strong>2.418</strong>
                <p>Voluntários ativos</p>
                <small>34 novos hoje</small>
            </article>
            <article class="admin-stat admin-stat-claro">
                <strong>38</strong>
                <p>ONGs verificadas</p>
                <small>2 este mês</small>
            </article>
        </section>

        <div class="rel-grid">
            <section class="admin-widget">
                <h3>Eventos publicados por mês</h3>
                <div class="rel-bars">
                    <div class="rel-col"><div class="rel-track"><div class="rel-bar" style="height: 55%"></div></div><small>Jan</small></div>
                    <div class="rel-col"><div class="rel-track"><div class="rel-bar" style="height: 62%"></div></div><small>Fev</small></div>
                    <div class="rel-col"><div class="rel-track"><div class="rel-bar" style="height: 48%"></div></div><small>Mar</small></div>
                    <div class="rel-col"><div class="rel-track"><div class="rel-bar" style="height: 78%"></div></div><small>Abr</small></div>
                    <div class="rel-col"><div class="rel-track"><div class="rel-bar" style="height: 92%"></div></div><small>Mai</small></div>
                    <div class="rel-col"><div class="rel-track"><div class="rel-bar" style="height: 70%"></div></div><small>Jun</small></div>
                </div>
            </section>

            <section class="admin-widget">
                <h3>Eventos por categoria</h3>
                <div class="rel-hbar-item">
                    <div class="rel-hbar-top"><b>Meio Ambiente</b><span>32%</span></div>
                    <div class="rel-hbar"><span style="width: 32%"></span></div>
                </div>
                <div class="rel-hbar-item">
                    <div class="rel-hbar-top"><b>Educação</b><span>26%</span></div>
                    <div class="rel-hbar"><span style="width: 26%"></span></div>
                </div>
                <div class="rel-hbar-item">
                    <div class="rel-hbar-top"><b>Saúde</b><span>18%</span></div>
                    <div class="rel-hbar"><span style="width: 18%"></span></div>
                </div>
                <div class="rel-hbar-item">
                    <div class="rel-hbar-top"><b>Assistência Social</b><span>14%</span></div>
                    <div class="rel-hbar"><span style="width: 14%"></span></div>
                </div>
                <div class="rel-hbar-item">
                    <div class="rel-hbar-top"><b>Cultura</b><span>10%</span></div>
                    <div class="rel-hbar"><span style="width: 10%"></span></div>
                </div>
            </section>
        </div>

        <section class="admin-widget">
            <h3>Top ONGs por eventos</h3>
            <div class="admin-tabela-scroll">
                <table class="admin-tabela">
                    <thead>
                        <tr>
                            <th>ONG</th>
                            <th>Eventos</th>
                            <th>Voluntários</th>
                            <th>Avaliação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Verde SP</td><td>28</td><td>412</td><td>4,9 / 5,0</td></tr>
                        <tr><td>Mãos Solidárias</td><td>21</td><td>350</td><td>4,8 / 5,0</td></tr>
                        <tr><td>Ação Verde</td><td>17</td><td>289</td><td>4,7 / 5,0</td></tr>
                        <tr><td>Instituto Semear</td><td>14</td><td>203</td><td>4,6 / 5,0</td></tr>
                        <tr><td>Pet Amigo</td><td>11</td><td>156</td><td>4,5 / 5,0</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
</body>
</html>