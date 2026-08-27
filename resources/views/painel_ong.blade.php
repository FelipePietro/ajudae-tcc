<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Painel da ONG - Ajudae</title>
@vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/painel_ong.css'])
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="app">

  <!-- SIDEBAR -->
<div class="app">
  <x-sidebar active="dashboard" />

  <main class="main">
    {{-- conteúdo específico de cada aba --}}
  </main>
</div>

  <!-- MAIN -->
  <main class="main">

    <div class="breadcrumb">Dashboard <span class="sep">›</span> <span class="current">Painel da ONG</span></div>

    <!-- ORG CARD -->
    <section class="org-card">
      <div class="org-identity">
        <div class="org-logo">🌱</div>
        <div>
          <h1 class="org-name">Instituto Esperança Social</h1>
          <p class="org-sub">Esperança Social · São Paulo, SP</p>
          <div class="org-tags">
            <span class="tag tag-active">✓ Ativa</span>
            <span class="tag tag-cnpj">CNPJ 11.222.333/0001-81</span>
            <span class="tag tag-plain">Assistência social · Educação</span>
            <span class="tag tag-verified">Conta verificada</span>
          </div>
        </div>
      </div>
      <div class="org-actions">
        <button class="btn btn-outline">Editar perfil</button>
        <button class="btn btn-primary">+ Criar evento</button>
      </div>
    </section>

    <!-- STATS -->
    <section class="stats-grid">
      <div class="stat-card">
        <div class="stat-value stat-dark">4</div>
        <div class="stat-label">Eventos ativos</div>
      </div>
      <div class="stat-card">
        <div class="stat-value stat-orange">3</div>
        <div class="stat-label">Funcionários</div>
      </div>
      <div class="stat-card">
        <div class="stat-value stat-blue">128</div>
        <div class="stat-label">Total voluntários</div>
      </div>
      <div class="stat-card">
        <div class="stat-value stat-dark">32</div>
        <div class="stat-label">Eventos realizados</div>
      </div>
      <div class="stat-card">
        <div class="stat-value stat-green">2.580</div>
        <div class="stat-label">Horas doadas (est.)</div>
      </div>
    </section>

    <!-- TABS -->
    <nav class="tabs">
      <button class="tab active">Eventos</button>
      <button class="tab">Funcionários</button>
      <button class="tab">Perfil da ONG</button>
    </nav>

    <!-- CONTENT GRID -->
    <div class="content-grid">

      <!-- LEFT COLUMN -->
      <div class="left-col">

        <!-- EVENTS TABLE -->
        <section class="panel">
          <div class="panel-header">
            <div>
              <h2 class="panel-title">Eventos da ONG</h2>
              <p class="panel-sub">4 ativos · 1 aguardando aprovação</p>
            </div>
            <button class="btn btn-primary">+ Criar evento</button>
          </div>

          <table class="events-table">
            <thead>
              <tr>
                <th>Evento</th>
                <th>Responsável</th>
                <th>Status</th>
                <th>Candidatos</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>
                  <div class="event-name">Limpeza de Parques</div>
                  <div class="event-meta">15 maio 2025 · Horto Florestal</div>
                </td>
                <td>
                  <div class="resp-name">Carlos F.</div>
                  <div class="resp-role">Organizador</div>
                </td>
                <td><span class="status status-published">Publicado</span></td>
                <td><span class="count-pending">8 pend.</span></td>
                <td class="actions-cell">
                  <button class="btn btn-sm btn-outline">Ver</button>
                  <button class="btn btn-sm btn-outline">Editar</button>
                </td>
              </tr>
              <tr>
                <td>
                  <div class="event-name">Doação de Roupas</div>
                  <div class="event-meta">20 maio 2025 · Brás, SP</div>
                </td>
                <td>
                  <div class="resp-name">Maria L.</div>
                  <div class="resp-role">Organizadora</div>
                </td>
                <td><span class="status status-published">Publicado</span></td>
                <td><span class="count-pending">15 pend.</span></td>
                <td class="actions-cell">
                  <button class="btn btn-sm btn-outline">Ver</button>
                  <button class="btn btn-sm btn-outline">Editar</button>
                </td>
              </tr>
              <tr>
                <td>
                  <div class="event-name">Campanha de Vacinação</div>
                  <div class="event-meta">28 maio 2025 · Centro, SP</div>
                </td>
                <td>
                  <div class="resp-none">— sem responsável</div>
                </td>
                <td><span class="status status-waiting">Aguardando aprovação</span></td>
                <td><span class="count-zero">0 pend.</span></td>
                <td class="actions-cell">
                  <button class="btn btn-sm btn-outline">Ver</button>
                  <button class="btn btn-sm btn-outline">Atribuir</button>
                </td>
              </tr>
              <tr>
                <td>
                  <div class="event-name">Aula de Reforço Escolar</div>
                  <div class="event-meta">05 jun 2025 · Cidade Tiradentes</div>
                </td>
                <td>
                  <div class="resp-name">Carlos F.</div>
                  <div class="resp-role">Organizador</div>
                </td>
                <td><span class="status status-published">Publicado</span></td>
                <td><span class="count-pending">4 pend.</span></td>
                <td class="actions-cell">
                  <button class="btn btn-sm btn-outline">Ver</button>
                  <button class="btn btn-sm btn-outline">Editar</button>
                </td>
              </tr>
            </tbody>
          </table>
        </section>

        <!-- STAFF LIST -->
        <section class="panel">
          <div class="panel-header">
            <div>
              <h2 class="panel-title">Funcionários da ONG</h2>
              <p class="panel-sub">2 ativos · 1 convite pendente</p>
            </div>
            <button class="btn btn-outline">Convidar organizador</button>
          </div>

          <ul class="staff-list">
            <li class="staff-row">
              <div class="staff-identity">
                <div class="avatar avatar-green">CF</div>
                <div>
                  <div class="staff-name">Carlos Ferreira</div>
                  <div class="staff-meta">2 eventos designados · Organizador principal</div>
                </div>
              </div>
              <div class="staff-actions">
                <span class="pill pill-accepted">Aceito</span>
                <button class="btn btn-sm btn-danger">Remover</button>
              </div>
            </li>
            <li class="staff-row">
              <div class="staff-identity">
                <div class="avatar avatar-yellow">ML</div>
                <div>
                  <div class="staff-name">Maria Lima</div>
                  <div class="staff-meta">1 evento designado</div>
                </div>
              </div>
              <div class="staff-actions">
                <span class="pill pill-accepted">Aceito</span>
                <button class="btn btn-sm btn-danger">Remover</button>
              </div>
            </li>
            <li class="staff-row">
              <div class="staff-identity">
                <div class="avatar avatar-blue">PS</div>
                <div>
                  <div class="staff-name">Pedro Santos</div>
                  <div class="staff-meta">Nenhum evento · convite aguardando resposta</div>
                </div>
              </div>
              <div class="staff-actions">
                <span class="pill pill-pending">Pendente</span>
                <button class="btn btn-sm btn-outline">Remover</button>
              </div>
            </li>
          </ul>

          <div class="notice">
            <span class="notice-icon">⚠</span>
            A remoção de um funcionário é bloqueada se ele estiver designado como responsável em evento ativo. Faça a substituição primeiro.
          </div>
        </section>

      </div>

      <!-- RIGHT COLUMN -->
      <div class="right-col">

        <section class="panel">
          <h2 class="panel-title">Dados cadastrais</h2>
          <ul class="info-list">
            <li>
              <span class="info-icon">🏛</span>
              <div>
                <div class="info-label">Razão social</div>
                <div class="info-value">Instituto Esperança Social</div>
              </div>
            </li>
            <li>
              <span class="info-icon">🪪</span>
              <div>
                <div class="info-label">CNPJ</div>
                <div class="info-value">11.222.333/0001-81</div>
              </div>
            </li>
            <li>
              <span class="info-icon">📍</span>
              <div>
                <div class="info-label">Cidade / Estado</div>
                <div class="info-value">São Paulo, SP</div>
              </div>
            </li>
            <li>
              <span class="info-icon">🌐</span>
              <div>
                <div class="info-label">Site</div>
                <div class="info-value info-link">esperancasocial.org.br</div>
              </div>
            </li>
            <li>
              <span class="info-icon">📅</span>
              <div>
                <div class="info-label">Cadastro aprovado em</div>
                <div class="info-value">12 jan. 2024</div>
              </div>
            </li>
          </ul>
        </section>

        <section class="panel">
          <h2 class="panel-title">Atividade recente</h2>
          <ul class="activity-list">
            <li class="activity-row">
              <span class="dot dot-green"></span>
              <div>
                <div class="activity-text">Carlos aprovou candidatura de Ana Costa para <strong>Limpeza de Parques</strong></div>
                <div class="activity-time">hoje, 14:32</div>
              </div>
            </li>
            <li class="activity-row">
              <span class="dot dot-orange"></span>
              <div>
                <div class="activity-text">Evento <strong>Campanha de Vacinação</strong> enviado para aprovação</div>
                <div class="activity-time">ontem, 09:15</div>
              </div>
            </li>
            <li class="activity-row">
              <span class="dot dot-blue"></span>
              <div>
                <div class="activity-text">Pedro Santos convidado para a equipe via e-mail</div>
                <div class="activity-time">há 2 dias</div>
              </div>
            </li>
            <li class="activity-row">
              <span class="dot dot-green"></span>
              <div>
                <div class="activity-text"><strong>Doação de Roupas</strong> publicado — 15 candidaturas recebidas</div>
                <div class="activity-time">há 3 dias</div>
              </div>
            </li>
          </ul>
        </section>

        <section class="panel">
          <h2 class="panel-title">Acesso rápido</h2>
          <div class="quick-actions">
            <button class="btn btn-primary btn-block">+ Criar novo evento</button>
            <button class="btn btn-outline btn-block">Convidar organizador</button>
            <button class="btn btn-outline btn-block">Ver relatório completo</button>
          </div>
        </section>

      </div>

    </div>

  </main>
</div>

</body>
</html>