<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cadastrar minha ONG - Ajudae</title>
@vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/cadastro_ong.css'])
</head>
<body>

<header>
  <div class="logo">Ajud<span>ae</span></div>
  <nav>
    <a class="plain" href="#">Início</a>
    <a class="plain" href="#">Entrar</a>
    <button class="btn-outline">Sou uma ONG</button>
    <button class="btn-solid">Me voluntariar</button>
  </nav>
</header>

<div class="hero">
  <div class="badge">🏢 Para pessoas jurídicas com CNPJ</div>
  <h1>Cadastrar minha ONG</h1>
  <p>Conecte sua organização a voluntários verificados. O cadastro passa por análise do administrador antes da ativação.</p>
</div>

<form>

  <div class="card">
    <div class="section-label">Identificação</div>

    <div class="field full" style="margin-bottom:20px;">
      <label>CNPJ <span class="req">*</span></label>
      <div class="cnpj-row">
        <input type="text" id="cnpj" inputmode="numeric" maxlength="18" placeholder="00.000.000/0001-00">
      </div>
    </div>

    <div class="row">
      <div class="field">
        <label>Razão social <span class="hint">(auto)</span></label>
        <input type="text" placeholder="Após consulta" disabled>
      </div>
      <div class="field">
        <label>Nome fantasia <span class="hint">(auto)</span></label>
        <input type="text" placeholder="Após consulta" disabled>
      </div>
    </div>

    <div class="row">
      <div class="field">
        <label>Cidade <span class="hint">(auto)</span></label>
        <input type="text" placeholder="Após consulta" disabled>
      </div>
      <div class="field">
        <label>Estado <span class="hint">(auto)</span></label>
        <input type="text" placeholder="Após consulta" disabled>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="section-label">Acesso e contato</div>

    <div class="row">
      <div class="field">
        <label>E-mail institucional <span class="req">*</span></label>
        <input type="email" placeholder="contato@suaong.org.br">
      </div>
      <div class="field">
        <label>Telefone <span class="req">*</span></label>
        <input type="tel" id="telefone" inputmode="numeric" maxlength="15" placeholder="(11) 3000-0000">
      </div>
    </div>
    <div class="field-note" style="margin:-10px 0 20px;">Usado pelo admin para entrar em contato na verificação</div>

    <div class="row">
      <div class="field">
        <label>Senha <span class="req">*</span></label>
        <input type="password" placeholder="Mínimo 8 caracteres">
      </div>
      <div class="field">
        <label>Confirmar senha <span class="req">*</span></label>
        <input type="password" placeholder="Repita a senha">
      </div>
    </div>

    <div class="field full">
      <label>Descrição da ONG <span class="req">*</span></label>
      <textarea placeholder="Descreva a missão, área de atuação e histórico da sua organização..."></textarea>
    </div>
  </div>

  <div class="card">
    <div class="section-label">Logo e imagem</div>
    <div class="upload-box">
      <div class="upload-icon">🏢</div>
      <div class="upload-text">
        <strong>Logo da ONG</strong>
        <span>PNG ou JPG · Recomendado 400×400px · Máximo 5MB</span>
        <span>Exibida publicamente nos eventos da ONG</span>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="section-label">Termos e responsabilidades</div>

    <div class="check-row">
      <label class="switch">
        <input type="checkbox">
        <span class="slider"></span>
      </label>
      <div>Aceito o <a href="#">Termo de Responsabilidade da ONG</a> — declaro que tenho autoridade legal para representar esta organização e que a ONG é responsável pelos eventos publicados sob seu nome</div>
    </div>

    <div class="check-row">
      <label class="switch">
        <input type="checkbox">
        <span class="slider"></span>
      </label>
      <div>Li e concordo com a <a href="#">Política de Privacidade</a> e as regras de moderação. Estou ciente de que o cadastro ficará pendente até a verificação pelo administrador</div>
    </div>
  </div>

  <button type="submit" class="submit-btn">Enviar cadastro para análise do administrador →</button>

  <div class="info-box">
    <h3>⏳ O que acontece depois?</h3>
    <ul>
      <li>Seu cadastro fica com status <strong>"aguardando aprovação"</strong></li>
      <li>O administrador entrará em contato pelo e-mail institucional para verificar a legitimidade</li>
      <li>Após aprovação, você poderá convidar organizadores como funcionários e criar eventos</li>
      <li>CNPJs inativos ou com natureza jurídica incompatível são bloqueados automaticamente</li>
    </ul>
  </div>

  <div class="footer-link">É um voluntário? <a href="#">Cadastre-se aqui →</a></div>

</form>

<script>
  function maskCNPJ(value) {
    value = value.replace(/\D/g, '').slice(0, 14);
    value = value.replace(/^(\d{2})(\d)/, '$1.$2');
    value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
    value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
    value = value.replace(/(\d{4})(\d{1,2})$/, '$1-$2');
    return value;
  }

  function maskPhone(value) {
    value = value.replace(/\D/g, '').slice(0, 11);
    if (value.length > 10) {
      value = value.replace(/^(\d{2})(\d)/, '($1) $2');
      value = value.replace(/(\d{5})(\d)/, '$1-$2');
    } else {
      value = value.replace(/^(\d{2})(\d)/, '($1) $2');
      value = value.replace(/(\d{4})(\d)/, '$1-$2');
    }
    return value;
  }

  const cnpjInput = document.getElementById('cnpj');
  if (cnpjInput) {
    cnpjInput.addEventListener('input', (e) => {
      e.target.value = maskCNPJ(e.target.value);
    });
  }

  const telefoneInput = document.getElementById('telefone');
  if (telefoneInput) {
    telefoneInput.addEventListener('input', (e) => {
      e.target.value = maskPhone(e.target.value);
    });
  }
</script>

</body>
</html>