<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidatos — Ajudaê</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="ong-body">
<div class="ong-shell">
    <x-ong-sidebar />

    <main class="ong-main">
        <nav class="cand-breadcrumb">Meus eventos <span>›</span> Candidatos — Limpeza de Parques</nav>

        <section class="cand-evento">
            <div class="cand-evento-info">
                <span class="cand-evento-folha" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M12 3c1.8 3.2 2.2 5.4 1.4 7.2C12.6 12 11 13 9.5 14.2 8 15.4 7 17 7 19c2.4-1 4.4-1.2 6.2-.4 1.6.7 2.8 2.2 3.8 4.2 1.6-4.6.6-8.2-1.4-10.2C13.8 10.8 12.6 9.4 12 3Z" fill="#2F6A49"/>
                    </svg>
                </span>
                <div>
                    <h1>Limpeza de Parques</h1>
                    <p>
                        <span>15 maio 2025, 08:00–16:00</span>
                        <span>Horto Florestal, São Paulo, SP</span>
                        <span class="cand-status">Publicado</span>
                    </p>
                </div>
            </div>
            <div class="cand-evento-stats">
                <div><strong>5</strong><span>Pendentes</span></div>
                <div><strong>3</strong><span>Aprovados</span></div>
                <div><strong>30</strong><span>Vagas total</span></div>
            </div>
        </section>

        <div class="cand-tabs">
            <span class="is-active">Pendente <small>5</small></span>
            <span>Aprovado <small>3</small></span>
            <span>Recusado <small>0</small></span>
            <span>Confirmar presença <small>0</small></span>
        </div>

        <div class="cand-toolbar">
            <label class="cand-busca">
                <span aria-hidden="true">⌕</span>
                <input type="search" placeholder="Buscar candidato..." readonly>
            </label>
            <button type="button" class="cand-chip is-on">Todos os níveis</button>
            <button type="button" class="cand-chip">Alta XP primeiro</button>
            <button type="button" class="cand-aprovar-lote">Aprovar selecionados</button>
        </div>

        <div class="cand-lista">
            <article class="cand-card">
                <header class="cand-card-topo">
                    <div class="cand-card-pessoa">
                        <span class="cand-avatar">AC</span>
                        <div>
                            <div class="cand-card-nome">
                                <strong>Ana Costa</strong>
                                <em>Nível 5 — Especialista</em>
                            </div>
                            <p class="cand-card-meta">
                                <span>★ 1.200 XP</span>
                                <span>🏅 8 badges</span>
                                <span>🎟 14 eventos participados</span>
                            </p>
                        </div>
                    </div>
                    <span class="cand-match">2 habilidades coincidem</span>
                </header>
                <p class="cand-hab-label">Habilidades</p>
                <div class="cand-habs">
                    <span class="cand-hab is-match">✓ Trabalho em equipe</span>
                    <span class="cand-hab is-match">✓ Jardinagem</span>
                    <span class="cand-hab">Comunicação</span>
                    <span class="cand-hab">Fotografia</span>
                </div>
                <blockquote>
                    “Sou muito engajada em causas ambientais e tenho experiência com atividades de limpeza. Participei de 3 mutirões similares na Cantareira e adoraria contribuir!”
                    <small>Candidatura enviada em 28 abr. 2025, 14:32</small>
                </blockquote>
                <footer class="cand-card-acoes">
                    <div>
                        <x-botao-primario href="#" texto="Aprovar candidatura" />
                        <x-botao-declined href="#" texto="Recusar" />
                        <x-botao-secundario href="#" texto="Ver perfil completo" />
                    </div>
                    <small>Candidatura enviada há 2 dias</small>
                </footer>
            </article>

            <article class="cand-card">
                <header class="cand-card-topo">
                    <div class="cand-card-pessoa">
                        <span class="cand-avatar">BM</span>
                        <div>
                            <div class="cand-card-nome">
                                <strong>Bruno Melo</strong>
                                <em>Nível 3 — Colaborador</em>
                            </div>
                            <p class="cand-card-meta">
                                <span>★ 450 XP</span>
                                <span>🏅 3 badges</span>
                                <span>🎟 5 eventos participados</span>
                            </p>
                        </div>
                    </div>
                    <span class="cand-match">1 habilidade coincide</span>
                </header>
                <p class="cand-hab-label">Habilidades</p>
                <div class="cand-habs">
                    <span class="cand-hab is-match">✓ Disposição física</span>
                    <span class="cand-hab">Comunicação</span>
                    <span class="cand-hab">Transporte</span>
                </div>
                <blockquote>
                    “Posso ajudar com a logística e com a coleta. Tenho disponibilidade o dia inteiro e já participei de ações de rua em Guarulhos.”
                    <small>Candidatura enviada em 27 abr. 2025, 09:18</small>
                </blockquote>
                <footer class="cand-card-acoes">
                    <div>
                        <x-botao-primario href="#" texto="Aprovar candidatura" />
                        <x-botao-declined href="#" texto="Recusar" />
                        <x-botao-secundario href="#" texto="Ver perfil completo" />
                    </div>
                    <small>Candidatura enviada há 3 dias</small>
                </footer>
            </article>

            <article class="cand-card">
                <header class="cand-card-topo">
                    <div class="cand-card-pessoa">
                        <span class="cand-avatar">JR</span>
                        <div>
                            <div class="cand-card-nome">
                                <strong>Juliana Ramos</strong>
                                <em>Nível 4 — Especialista</em>
                            </div>
                            <p class="cand-card-meta">
                                <span>★ 920 XP</span>
                                <span>🏅 6 badges</span>
                                <span>🎟 11 eventos participados</span>
                            </p>
                        </div>
                    </div>
                    <span class="cand-match">3 habilidades coincidem</span>
                </header>
                <p class="cand-hab-label">Habilidades</p>
                <div class="cand-habs">
                    <span class="cand-hab is-match">✓ Trabalho em equipe</span>
                    <span class="cand-hab is-match">✓ Consciência ambiental</span>
                    <span class="cand-hab is-match">✓ Liderança</span>
                    <span class="cand-hab">Primeiros socorros</span>
                </div>
                <blockquote>
                    “Gosto de organizar grupos e orientar quem está chegando. Posso ajudar a dividir as equipes no parque e cuidar da segurança básica.”
                    <small>Candidatura enviada em 26 abr. 2025, 19:05</small>
                </blockquote>
                <footer class="cand-card-acoes">
                    <div>
                        <x-botao-primario href="#" texto="Aprovar candidatura" />
                        <x-botao-declined href="#" texto="Recusar" />
                        <x-botao-secundario href="#" texto="Ver perfil completo" />
                    </div>
                    <small>Candidatura enviada há 4 dias</small>
                </footer>
            </article>
        </div>

        <p class="cand-dica">
            <span aria-hidden="true">💡</span>
            Confirmar presença após o evento: acesse a aba “Confirmar presença” para marcar quem efetivamente compareceu. Isso concede XP e badges automaticamente aos voluntários confirmados.
        </p>
    </main>
</div>
</body>
</html>