<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel admin — Ajudaê</title>
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
                <strong>Painel de administração</strong>
            </nav>
            <div class="admin-topo-acoes">
                <time datetime="2025-05-27T22:31">qua., 27 de mai. · 22:31</time>
                <a href="#" class="btn-secundario admin-log-btn">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="6" y="3" width="12" height="18" rx="2" fill="none" stroke="currentColor" stroke-width="1.8"/>
                        <path d="M9 3.5h6v3H9z" fill="currentColor"/>
                        <path d="M9 11h6M9 14.5h6M9 18h4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                    </svg>
                    Ver log completo
                </a>
            </div>
        </div>

        <p class="admin-alerta"><strong>17</strong> <b>itens pendentes de revisão</b> — 7 eventos aguardam aprovação, 3 upgrades para organizador, 1 ONG cadastrada, 2 denúncias ativas e 4 solicitações LGPD. <span>2 solicitações LGPD vencem em menos de 48h.</span></p>

        <section class="admin-stats">
            <article class="admin-stat admin-stat-laranja">
                <strong>3</strong>
                <p>Upgrades para Organizador</p>
                <small>↑ 2 novos hoje</small>
            </article>
            <article class="admin-stat admin-stat-azul">
                <strong>1</strong>
                <p>Cadastros de ONG pendentes</p>
                <small>há 6h na fila</small>
            </article>
            <article class="admin-stat admin-stat-amarelo">
                <strong>7</strong>
                <p>Eventos para aprovação</p>
                <small>3 enviados hoje</small>
            </article>
            <article class="admin-stat admin-stat-vermelho">
                <strong>2</strong>
                <p>Denúncias abertas</p>
                <small>1 crítica</small>
            </article>
            <article class="admin-stat admin-stat-claro">
                <strong>4</strong>
                <p>Solicitações LGPD</p>
                <small>2 vencem em 48h ⚠️</small>
            </article>
        </section>

        <div class="admin-corpo">
            <div class="admin-fila">
                <div class="admin-tabs">
                    <span class="is-active">Eventos <em>7</em></span>
                    <span>ONGs <em>1</em></span>
                    <span>Upgrades <em>3</em></span>
                    <span>Denúncias <em>2</em></span>
                    <span>LGPD <em>4</em></span>
                </div>

                <article class="admin-card">
                    <header class="admin-card-topo">
                        <div>
                            <h2>Limpeza de Parques — Parque da Cantareira</h2>
                            <div class="admin-tags">
                                <span class="admin-tag admin-tag-verde">ONG</span>
                                <span class="admin-tag-txt">Verde SP • Organizador: Carlos F.</span>
                                <span class="admin-tag admin-tag-ouro">Meio Ambiente</span>
                            </div>
                        </div>
                        <div class="admin-card-links">
                            <small>enviado há 2h</small>
                            <x-botao-secundario href="#" texto="Ver perfil do org." />
                        </div>
                    </header>
                    <dl class="admin-meta">
                        <div>
                            <div><dt>Data</dt><dd>15 maio 2025, 08:00–16:00</dd></div>
                            <div><dt>Vagas</dt><dd>30</dd></div>
                        </div>
                        <div>
                            <div><dt>Local</dt><dd>Rua do Horto, 1488 — Horto Florestal, SP</dd></div>
                            <div><dt>Habilidades</dt><dd>Trabalho em equipe, Disposição física</dd></div>
                        </div>
                    </dl>
                    <p class="admin-desc">Junte-se a nós para uma manhã de limpeza e preservação do parque. Traremos luvas, sacos e ferramentas. Leve protetor solar e roupa confortável.</p>
                    <label class="admin-motivo">
                        Motivo da recusa (obrigatório ao recusar)
                        <textarea rows="2" placeholder="Ex: Endereço incompleto, solicite revisão do organizador..."></textarea>
                    </label>
                    <footer class="admin-card-acoes">
                        <div>
                        <x-botao-primario href="#" texto="Aprovar e publicar" />
                        <x-botao-declined href="#" texto="Recusar com motivo" />
                        </div>
                        <x-botao-secundario href="#" texto="Ver evento completo" />
                    </footer>
                </article>

                <article class="admin-card">
                    <header class="admin-card-topo">
                        <div>
                            <h2>Mutirão de Pintura Comunitária</h2>
                            <div class="admin-tags">
                                <span class="admin-tag admin-tag-cinza">Ind.</span>
                                <span class="admin-tag-txt">· Carlos Ferreira</span>
                                <span class="admin-tag admin-tag-cinza">Habitação</span>
                            </div>
                        </div>
                        <div class="admin-card-links">
                            <small>enviado há 4h</small>
                            <x-botao-secundario href="#" texto="Ver perfil do org." />
                        </div>
                    </header>
                    <dl class="admin-meta">
                        <div>
                            <div><dt>Data</dt><dd>20 jun. 2025, 09:00–17:00</dd></div>
                            <div><dt>Vagas</dt><dd>20</dd></div>
                        </div>
                        <div>
                            <div><dt>Local</dt><dd>Rua das Flores, 200 — Vila Madalena, SP</dd></div>
                            <div><dt>Habilidades</dt><dd>Pintura, Trabalho em equipe</dd></div>
                        </div>
                    </dl>
                    <p class="admin-desc">Vamos revitalizar a fachada do centro comunitário com tinta doada. Não é preciso experiência prévia.</p>
                    <label class="admin-motivo">
                        Motivo da recusa (obrigatório ao recusar)
                        <textarea rows="2" placeholder="Ex: Endereço incompleto, solicite revisão do organizador..."></textarea>
                    </label>
                    <footer class="admin-card-acoes">
                        <div>
                            <x-botao-primario href="#" texto="Aprovar e publicar" />
                            <x-botao-declined href="#" texto="Recusar com motivo" />
                        </div>
                        <x-botao-secundario href="#" texto="Ver evento completo" />
                    </footer>
                </article>
            </div>

            <aside>
                <section class="admin-widget">
                    <h3>Atividade recente</h3>
                    <ul>
                        <li><span class="admin-dot admin-dot-preto"></span><p>Evento <strong>Doação de Roupas</strong> aprovado e publicado</p><small>10min</small></li>
                        <li><span class="admin-dot admin-dot-ouro"></span><p>Upgrade negado para <strong>Roberto Torres</strong> — XP insuficiente</p><small>1h</small></li>
                        <li><span class="admin-dot admin-dot-azul"></span><p>ONG <strong>Ação Verde</strong> ativada após verificação</p><small>3h</small></li>
                        <li><span class="admin-dot admin-dot-vermelho"></span><p>Evento <strong>Barraca de Saúde</strong> recusado — local inválido</p><small>5h</small></li>
                        <li><span class="admin-dot admin-dot-preto"></span><p>Upgrade aprovado para <strong>Mariana Santos</strong></p><small>ontem</small></li>
                        <li><span class="admin-dot admin-dot-ouro"></span><p>LGPD: exportação enviada para <strong>Ana Costa</strong></p><small>ontem</small></li>
                    </ul>
                </section>

                <section class="admin-widget">
                    <h3>Plataforma hoje</h3>
                    <dl class="admin-hoje">
                        <div><dt>Usuários ativos</dt><dd>2.418</dd></div>
                        <div><dt>Novos cadastros</dt><dd>+34</dd></div>
                        <div><dt>Eventos publicados</dt><dd>142</dd></div>
                        <div><dt>Candidaturas hoje</dt><dd>+287</dd></div>
                        <div><dt>ONGs verificadas</dt><dd>38</dd></div>
                        <div class="is-alerta"><dt>Usuários suspensos</dt><dd>3</dd></div>
                    </dl>
                </section>

                <section class="admin-widget">
                    <h3>Acesso rápido</h3>
                    <nav class="admin-atalhos">
                        <a href="#">Fila de eventos</a>
                        <a href="#">Solicitações LGPD</a>
                        <a href="#">Denúncias abertas</a>
                        <a href="#">Upgrades de organizador</a>
                    </nav>
                </section>
            </aside>
        </div>
    </main>
</div>
</body>
</html>