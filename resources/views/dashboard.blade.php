<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Ajudaê</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="ong-body">
<div class="ong-shell">
    <x-ong-sidebar />

    <main class="ong-main">
        <div class="dash-topo">
            <nav class="cand-breadcrumb">Dashboard — visão geral</nav>
            <div class="dash-topo-acoes">
                <a href="#" class="dash-sino" aria-label="Notificações">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M6 9a6 6 0 1 1 12 0c0 7 3 7 3 9H3c0-2 3-2 3-9Z"/>
                        <path d="M10 20a2 2 0 0 0 4 0"/>
                    </svg>
                    <small></small>
                </a>
                <x-botao-primario href="#" texto="+ Criar evento" />
            </div>
        </div>

        <section class="dash-welcome">
            <div>
                <h1>Olá de volta, Carlos 👋</h1>
                <p>Você tem 12 candidatos aguardando revisão e 3 eventos ativos.</p>
            </div>
            <aside class="dash-nivel">
                <span class="dash-nivel-ico" aria-hidden="true">🏆</span>
                <div class="dash-nivel-info">
                    <strong>Seu nível</strong>
                    <b>Nível 5 — Mestre</b>
                    <div class="dash-xp-meta"><span>XP</span><span>2.040/3.000</span></div>
                    <div class="dash-xp" aria-hidden="true"><span style="width: 68%"></span></div>
                </div>
            </aside>
        </section>

        <section class="dash-stats">
            <article class="dash-stat dash-stat-verde">
                <small>Eventos ativos</small>
                <strong>3</strong>
                <p>↑ +1 esta semana</p>
            </article>
            <article class="dash-stat dash-stat-ouro">
                <small>Candidatos pendentes</small>
                <strong>12</strong>
                <p>⚠ Aguardando revisão</p>
            </article>
            <article class="dash-stat dash-stat-azul">
                <small>Voluntários confirmados</small>
                <strong>45</strong>
                <p>↑ +6 esta semana</p>
            </article>
            <article class="dash-stat dash-stat-cinza">
                <small>Total de eventos realizados</small>
                <strong>18</strong>
                <p>+ Histórico completo</p>
            </article>
        </section>

        <div class="dash-corpo">
            <section class="dash-card">
                <header class="dash-card-topo">
                    <div>
                        <h2>Meus eventos</h2>
                        <p>3 ativos · última atualização: hoje</p>
                    </div>
                    <a href="#" class="dash-ver">Ver todos →</a>
                </header>

                <div class="dash-tabela-cols">
                    <span>Evento</span>
                    <span>Status</span>
                    <span>Vagas</span>
                    <span>Candidatos</span>
                    <span>Ações</span>
                </div>

                <article class="dash-evento">
                    <div>
                        <span class="dash-label">Evento</span>
                        <h3>Limpeza de Parques</h3>
                        <p>15 maio 2025 · Horto Florestal, SP</p>
                    </div>
                    <div>
                        <span class="dash-label">Status</span>
                        <span class="dash-pill dash-pill-verde">Publicado</span>
                    </div>
                    <div>
                        <span class="dash-label">Vagas</span>
                        <div class="dash-vagas">
                            <div class="dash-barra" aria-hidden="true"><span style="width: 73%"></span></div>
                            <b>22/30</b>
                        </div>
                    </div>
                    <div>
                        <span class="dash-label">Candidatos</span>
                        <span class="dash-pendentes">8 pendentes</span>
                    </div>
                    <div>
                        <span class="dash-label">Ações</span>
                        <div class="dash-acoes">
                            <a href="/candidatos" aria-label="Ver candidatos">
                                <svg viewBox="0 0 24 24" fill="none" stroke="#2a5f9e" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </a>
                            <a href="#" aria-label="Editar evento">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                            </a>
                            <a href="#" aria-label="Cancelar evento">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 6 6 18M6 6l12 12"/></svg>
                            </a>
                        </div>
                    </div>
                </article>

                <article class="dash-evento">
                    <div>
                        <span class="dash-label">Evento</span>
                        <h3>Mutirão de Pintura</h3>
                        <p>22 maio 2025 · Vila Madalena, SP</p>
                    </div>
                    <div>
                        <span class="dash-label">Status</span>
                        <span class="dash-pill dash-pill-ouro">Aguardando aprovação</span>
                    </div>
                    <div>
                        <span class="dash-label">Vagas</span>
                        <div class="dash-vagas">
                            <div class="dash-barra" aria-hidden="true"><span style="width: 0%"></span></div>
                            <b>0/20</b>
                        </div>
                    </div>
                    <div>
                        <span class="dash-label">Candidatos</span>
                        <span class="dash-vazio">—</span>
                    </div>
                    <div>
                        <span class="dash-label">Ações</span>
                        <div class="dash-acoes">
                            <a href="#" aria-label="Visualizar evento">
                                <svg viewBox="0 0 24 24" fill="none" stroke="#8a5a32" stroke-width="1.8"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                            <a href="#" aria-label="Editar evento">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                            </a>
                            <a href="#" aria-label="Cancelar evento">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 6 6 18M6 6l12 12"/></svg>
                            </a>
                        </div>
                    </div>
                </article>

                <article class="dash-evento">
                    <div>
                        <span class="dash-label">Evento</span>
                        <h3>Aula de Culinária Social</h3>
                        <p>28 maio 2025 · Pinheiros, SP</p>
                    </div>
                    <div>
                        <span class="dash-label">Status</span>
                        <span class="dash-pill dash-pill-verde">Publicado</span>
                    </div>
                    <div>
                        <span class="dash-label">Vagas</span>
                        <div class="dash-vagas">
                            <div class="dash-barra" aria-hidden="true"><span style="width: 27%"></span></div>
                            <b>4/15</b>
                        </div>
                    </div>
                    <div>
                        <span class="dash-label">Candidatos</span>
                        <span class="dash-pendentes">4 pendentes</span>
                    </div>
                    <div>
                        <span class="dash-label">Ações</span>
                        <div class="dash-acoes">
                            <a href="/candidatos" aria-label="Ver candidatos">
                                <svg viewBox="0 0 24 24" fill="none" stroke="#2a5f9e" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </a>
                            <a href="#" aria-label="Editar evento">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                            </a>
                            <a href="#" aria-label="Cancelar evento">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 6 6 18M6 6l12 12"/></svg>
                            </a>
                        </div>
                    </div>
                </article>
            </section>

            <div class="dash-lado">
                <section class="dash-card">
                    <header class="dash-card-topo">
                        <div>
                            <h2>Candidatos pendentes</h2>
                            <p>12 aguardando revisão</p>
                        </div>
                        <a href="/candidatos" class="dash-ver">Ver todos</a>
                    </header>
                    <ul class="dash-lista">
                        <li class="dash-cand">
                            <span class="dash-cand-avatar dash-av-verde">AC</span>
                            <div>
                                <strong>Ana Costa</strong>
                                <small>Limpeza de Parques · Nível 5</small>
                            </div>
                            <div class="dash-cand-btns">
                                <button type="button" class="dash-mini-ok" aria-label="Aprovar">✓</button>
                                <button type="button" class="dash-mini-no" aria-label="Recusar">✕</button>
                            </div>
                        </li>
                        <li class="dash-cand">
                            <span class="dash-cand-avatar dash-av-ouro">BM</span>
                            <div>
                                <strong>Bruno Melo</strong>
                                <small>Limpeza de Parques · Nível 3</small>
                            </div>
                            <div class="dash-cand-btns">
                                <button type="button" class="dash-mini-ok" aria-label="Aprovar">✓</button>
                                <button type="button" class="dash-mini-no" aria-label="Recusar">✕</button>
                            </div>
                        </li>
                        <li class="dash-cand">
                            <span class="dash-cand-avatar dash-av-azul">JR</span>
                            <div>
                                <strong>Juliana Ramos</strong>
                                <small>Aula de Culinária Social · Nível 4</small>
                            </div>
                            <div class="dash-cand-btns">
                                <button type="button" class="dash-mini-ok" aria-label="Aprovar">✓</button>
                                <button type="button" class="dash-mini-no" aria-label="Recusar">✕</button>
                            </div>
                        </li>
                        <li class="dash-cand">
                            <span class="dash-cand-avatar dash-av-cinza">TS</span>
                            <div>
                                <strong>Thiago Silva</strong>
                                <small>Limpeza de Parques · Nível 2</small>
                            </div>
                            <div class="dash-cand-btns">
                                <button type="button" class="dash-mini-ok" aria-label="Aprovar">✓</button>
                                <button type="button" class="dash-mini-no" aria-label="Recusar">✕</button>
                            </div>
                        </li>
                    </ul>
                </section>

                <section class="dash-card">
                    <header class="dash-card-topo">
                        <h2>Ações rápidas</h2>
                    </header>
                    <nav class="dash-atalhos">
                        <a href="#" class="dash-atalho">
                            <span class="dash-atalho-ico dash-atalho-verde" aria-hidden="true">+</span>
                            <span>
                                <strong>Criar novo evento</strong>
                                <small>Publicar ou salvar rascunho</small>
                            </span>
                        </a>
                        <a href="#" class="dash-atalho">
                            <span class="dash-atalho-ico dash-atalho-ouro" aria-hidden="true">📢</span>
                            <span>
                                <strong>Confirmar presenças</strong>
                                <small>Conceder XP e badges</small>
                            </span>
                        </a>
                    </nav>
                </section>
            </div>
        </div>
    </main>
</div>
</body>
</html>