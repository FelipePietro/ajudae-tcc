<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidatura — Ajudaê</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

    <nav class="navbar">
        <a href="/" class="navbar-brand">
            <span class="brand-aju">Ajud</span><span class="brand-dae">aê</span>
        </a>
        <ul class="navbar-links">
            <li><a href="/evento/{{ $id_evento }}">&lt; Voltar ao evento</a></li>
        </ul>
    </nav>

    <div class="stepper">
        <div class="stepper-item concluido">
            <div class="stepper-circulo">✓</div>
            <span>Ver evento</span>
        </div>
        <div class="stepper-linha concluido"></div>
        <div class="stepper-item ativo">
            <div class="stepper-circulo">2</div>
            <span>Candidatura</span>
        </div>
        <div class="stepper-linha"></div>
        <div class="stepper-item">
            <div class="stepper-circulo">3</div>
            <span>Aguardar aprovação</span>
        </div>
    </div>

    <div class="candidatura-wrapper">

        <div class="card-evento">
            <div class="card-evento-icone">🌿</div>
            <div class="card-evento-info">
                <strong>{{ $nome_evento }} — {{ $nome_ong }}</strong>
                <p>📅 {{ $data_evento }} &nbsp; 📍 {{ $endereco_evento }} &nbsp; 👥 {{ $vagas_evento }} vagas restantes</p>
            </div>
        </div>

        <div class="card-perfil">
            <p class="card-perfil-label">O organizador verá seu perfil</p>
            <div class="card-perfil-topo">
                <a href="/perfil" class="avatar">LP</a>
                {{-- Depois trocar por: {{ substr(Auth::user()->nm_pessoa, 0, 2) }} --}}
                <div>
                    <strong>{{ $nome_user }}</strong>
                    <p>{{ $cidade_user }}, {{ $uf_user }}</p>
                </div>
            </div>
            <div class="badges-row">
                <span class="badge badge-nivel">⭐ {{ $nivel_user }} — {{ $titulo_nivel }}</span>
                <span class="badge badge-xp">⚡ {{ $xp_user }} XP</span>
                <span class="badge badge-conquistas">🏅 {{ $badges_user }} badges</span>
                <span class="badge badge-eventos">📅 {{ $eventos_user }} eventos</span>
            </div>
            <div class="habilidades-row">
                @foreach($habilidades_user as $habilidade)
                    <span class="tag-habilidade">{{ $habilidade }}</span>
                @endforeach
            </div>
            <a href="/perfil" class="link-editar">Editar perfil antes de enviar →</a>
        </div>

        <div class="card-candidatura">
            <h2>Sua candidatura</h2>
            <p class="subtitulo">Conte um pouco sobre sua motivação para participar deste evento</p>

            <form action="/candidatura/confirmar" method="POST">
                @csrf

                <label class="label-campo">
                    Mensagem de motivação <span class="text-muted">— opcional</span>
                </label>
                <textarea
                    name="mensagem"
                    class="textarea-candidatura"
                    placeholder="Tenho muito interesse em causas ambientais..."
                    maxlength="500"
                ></textarea>
                <p class="contador-chars">0 / 500</p>
                <p class="dica">Esta mensagem é opcional, mas aumenta sua chance de aprovação.</p>

                <p class="label-campo">Confirmação de disponibilidade <span class="text-danger">*</span></p>

                <label class="label-checkbox">
                    <input type="checkbox" name="disponibilidade" required>
                    <span>Confirmo que estarei disponível em <strong>{{ $data_evento }}, das {{ $hora_inicio_evento }} às {{ $hora_fim_evento }}</strong></span>
                </label>

                <label class="label-checkbox">
                    <input type="checkbox" name="termos" required>
                    <span>Estou ciente de que o organizador analisará meu perfil completo antes de aprovar</span>
                </label>

                <div class="aviso-analise">
                    <span>📋</span>
                    <p><strong>Sobre a análise:</strong> O organizador verá seu perfil completo: foto, bio, habilidades, XP, badges e histórico de participações. Mantenha seu perfil atualizado para aumentar suas chances.</p>
                </div>

                <div class="botoes-candidatura">
                    <a href="/evento/{{ $id_evento }}" class="btn-cancelar">Cancelar</a>
                    <button type="submit" class="btn-primario">✅ Confirmar candidatura</button>
                </div>
            </form>

            <p class="texto-aviso">Você pode desistir da candidatura enquanto o status for "pendente".</p>
            <p class="texto-aviso">Após aprovação, o cancelamento pode afetar sua reputação na plataforma.</p>
        </div>

    </div>

    <x-footer />

</body>

</html>