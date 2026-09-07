<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upgrades org. — Ajudaê</title>
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
                <strong>Upgrades de organizador</strong>
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
                <h1>Upgrades para Organizador</h1>
                <p>3 solicitações pendentes · 2 novas hoje</p>
            </div>
            <div class="fila-resumo">
                <div><strong>3</strong><span>Pendentes</span></div>
                <div><strong>12</strong><span>Aprovados</span></div>
                <div><strong>4</strong><span>Negados</span></div>
            </div>
        </header>

        <div class="cand-toolbar">
            <label class="cand-busca">
                <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true">
                    <circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="1.8"/>
                    <path d="m20 20-3-3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
                <input type="search" placeholder="Buscar voluntário...">
            </label>
            <button type="button" class="cand-chip is-on">Pendentes</button>
            <button type="button" class="cand-chip">Aprovados</button>
            <button type="button" class="cand-chip">Negados</button>
            <button type="button" class="cand-aprovar-lote">Ordenar: maior XP</button>
        </div>

        <div class="admin-fila">
            <article class="admin-card">
                <header class="admin-card-topo">
                    <div>
                        <h2>Lucas Andrade</h2>
                        <div class="admin-tags">
                            <span class="admin-tag admin-tag-ouro">Pendente</span>
                            <span class="admin-tag-txt">Voluntário • Nível 5</span>
                            <span class="admin-tag admin-tag-cinza">São Paulo, SP</span>
                        </div>
                    </div>
                    <div class="admin-card-links">
                        <small>solicitado há 2h</small>
                        <x-botao-secundario href="#" texto="Ver perfil" />
                    </div>
                </header>
                <dl class="admin-meta">
                    <div>
                        <div><dt>Membro desde</dt><dd>Mar. 2024</dd></div>
                        <div><dt>Eventos concluídos</dt><dd>18</dd></div>
                    </div>
                    <div>
                        <div><dt>Avaliação média</dt><dd>4,9 / 5,0</dd></div>
                        <div><dt>Última atividade</dt><dd>hoje</dd></div>
                    </div>
                </dl>
                <p class="cand-hab-label">Progresso para Organizador</p>
                <div class="dash-xp-meta"><span>480 / 500 XP</span><span>96%</span></div>
                <div class="dash-xp"><span style="width: 96%"></span></div>
                <label class="admin-motivo">
                    Motivo da negação (obrigatório ao negar)
                    <textarea rows="2" placeholder="Ex: XP insuficiente, aguardar mais eventos concluídos..."></textarea>
                </label>
                <footer class="admin-card-acoes">
                    <div>
                        <x-botao-primario href="#" texto="Aprovar upgrade" />
                        <x-botao-declined href="#" texto="Negar com motivo" />
                    </div>
                    <x-botao-secundario href="#" texto="Ver histórico" />
                </footer>
            </article>

            <article class="admin-card">
                <header class="admin-card-topo">
                    <div>
                        <h2>Fernanda Dias</h2>
                        <div class="admin-tags">
                            <span class="admin-tag admin-tag-ouro">Pendente</span>
                            <span class="admin-tag-txt">Voluntária • Nível 4</span>
                            <span class="admin-tag admin-tag-cinza">Osasco, SP</span>
                        </div>
                    </div>
                    <div class="admin-card-links">
                        <small>solicitado há 5h</small>
                        <x-botao-secundario href="#" texto="Ver perfil" />
                    </div>
                </header>
                <dl class="admin-meta">
                    <div>
                        <div><dt>Membro desde</dt><dd>Jun. 2024</dd></div>
                        <div><dt>Eventos concluídos</dt><dd>11</dd></div>
                    </div>
                    <div>
                        <div><dt>Avaliação média</dt><dd>4,6 / 5,0</dd></div>
                        <div><dt>Última atividade</dt><dd>ontem</dd></div>
                    </div>
                </dl>
                <p class="cand-hab-label">Progresso para Organizador</p>
                <div class="dash-xp-meta"><span>350 / 500 XP</span><span>70%</span></div>
                <div class="dash-xp"><span style="width: 70%"></span></div>
                <label class="admin-motivo">
                    Motivo da negação (obrigatório ao negar)
                    <textarea rows="2" placeholder="Ex: XP insuficiente, aguardar mais eventos concluídos..."></textarea>
                </label>
                <footer class="admin-card-acoes">
                    <div>
                        <x-botao-primario href="#" texto="Aprovar upgrade" />
                        <x-botao-declined href="#" texto="Negar com motivo" />
                    </div>
                    <x-botao-secundario href="#" texto="Ver histórico" />
                </footer>
            </article>

            <article class="admin-card">
                <header class="admin-card-topo">
                    <div>
                        <h2>Marcos Vinícius</h2>
                        <div class="admin-tags">
                            <span class="admin-tag admin-tag-ouro">Pendente</span>
                            <span class="admin-tag-txt">Voluntário • Nível 6</span>
                            <span class="admin-tag admin-tag-cinza">Guarulhos, SP</span>
                        </div>
                    </div>
                    <div class="admin-card-links">
                        <small>solicitado ontem</small>
                        <x-botao-secundario href="#" texto="Ver perfil" />
                    </div>
                </header>
                <dl class="admin-meta">
                    <div>
                        <div><dt>Membro desde</dt><dd>Jan. 2024</dd></div>
                        <div><dt>Eventos concluídos</dt><dd>25</dd></div>
                    </div>
                    <div>
                        <div><dt>Avaliação média</dt><dd>5,0 / 5,0</dd></div>
                        <div><dt>Última atividade</dt><dd>há 2 dias</dd></div>
                    </div>
                </dl>
                <p class="cand-hab-label">Progresso para Organizador</p>
                <div class="dash-xp-meta"><span>500 / 500 XP</span><span>100%</span></div>
                <div class="dash-xp"><span style="width: 100%"></span></div>
                <label class="admin-motivo">
                    Motivo da negação (obrigatório ao negar)
                    <textarea rows="2" placeholder="Ex: XP insuficiente, aguardar mais eventos concluídos..."></textarea>
                </label>
                <footer class="admin-card-acoes">
                    <div>
                        <x-botao-primario href="#" texto="Aprovar upgrade" />
                        <x-botao-declined href="#" texto="Negar com motivo" />
                    </div>
                    <x-botao-secundario href="#" texto="Ver histórico" />
                </footer>
            </article>
        </div>
    </main>
</div>
</body>
</html>