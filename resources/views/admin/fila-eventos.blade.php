<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fila de eventos — Ajudaê</title>
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
                    'label' => 'Fila de eventos',
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
                <h1>Fila de eventos</h1>
                <p>7 eventos aguardando aprovação · 3 enviados hoje</p>
            </div>
            <div class="fila-resumo">
                <div><strong>7</strong><span>Na fila</span></div>
                <div><strong>3</strong><span>Hoje</span></div>
                <div><strong>2h</strong><span>Espera média</span></div>
            </div>
        </header>

        <div class="cand-toolbar">
            <label class="cand-busca">
                <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true">
                    <circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="1.8"/>
                    <path d="m20 20-3-3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
                <input type="search" placeholder="Buscar evento, ONG ou organizador...">
            </label>
            <button type="button" class="cand-chip is-on">Todos</button>
            <button type="button" class="cand-chip">ONG</button>
            <button type="button" class="cand-chip">Individual</button>
            <button type="button" class="cand-aprovar-lote">Ordenar: mais recentes</button>
        </div>

        <div class="admin-fila">
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
                        <div><dt>Data</dt><dd>15 jun. 2025, 08:00–16:00</dd></div>
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
                        <h2>Arrecadação de Alimentos — Zona Leste</h2>
                        <div class="admin-tags">
                            <span class="admin-tag admin-tag-verde">ONG</span>
                            <span class="admin-tag-txt">Mãos Solidárias • Organizadora: Beatriz L.</span>
                            <span class="admin-tag admin-tag-ouro">Assistência Social</span>
                        </div>
                    </div>
                    <div class="admin-card-links">
                        <small>enviado há 3h</small>
                        <x-botao-secundario href="#" texto="Ver perfil do org." />
                    </div>
                </header>
                <dl class="admin-meta">
                    <div>
                        <div><dt>Data</dt><dd>28 jun. 2025, 10:00–15:00</dd></div>
                        <div><dt>Vagas</dt><dd>15</dd></div>
                    </div>
                    <div>
                        <div><dt>Local</dt><dd>Av. Sapopemba, 4200 — São Paulo, SP</dd></div>
                        <div><dt>Habilidades</dt><dd>Organização, Atendimento ao público</dd></div>
                    </div>
                </dl>
                <p class="admin-desc">Campanha de arrecadação e triagem de alimentos não perecíveis para famílias cadastradas. Voluntários ajudam na separação e montagem das cestas.</p>
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

            <article class="admin-card">
                <header class="admin-card-topo">
                    <div>
                        <h2>Aula de Reforço Comunitária</h2>
                        <div class="admin-tags">
                            <span class="admin-tag admin-tag-cinza">Ind.</span>
                            <span class="admin-tag-txt">· Marina Alves</span>
                            <span class="admin-tag admin-tag-cinza">Educação</span>
                        </div>
                    </div>
                    <div class="admin-card-links">
                        <small>enviado há 6h</small>
                        <x-botao-secundario href="#" texto="Ver perfil do org." />
                    </div>
                </header>
                <dl class="admin-meta">
                    <div>
                        <div><dt>Data</dt><dd>02 jul. 2025, 14:00–17:00</dd></div>
                        <div><dt>Vagas</dt><dd>10</dd></div>
                    </div>
                    <div>
                        <div><dt>Local</dt><dd>Biblioteca Municipal — Centro, SP</dd></div>
                        <div><dt>Habilidades</dt><dd>Didática, Matemática, Português</dd></div>
                    </div>
                </dl>
                <p class="admin-desc">Reforço escolar gratuito para estudantes do ensino fundamental. Buscamos voluntários para acompanhar pequenos grupos de alunos.</p>
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
    </main>
</div>
</body>
</html>