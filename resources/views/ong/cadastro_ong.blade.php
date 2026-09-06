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
  @include('/components/navbar')
</header>

<div class="hero">
  <div class="badge">🏢 Para pessoas jurídicas com CNPJ</div>
  <h1>Cadastrar minha ONG</h1>
  <p>Conecte sua organização a voluntários verificados. O cadastro passa por análise do administrador antes da ativação.</p>
</div>

@if ($errors->any())
  <div class="card" style="border-color:#e0a3a3; background:#fdf0f0;">
    <strong style="display:block; margin-bottom:8px;">Corrija os campos abaixo:</strong>
    <ul style="margin:0; padding-left:18px;">
      @foreach ($errors->all() as $erro)
        <li>{{ $erro }}</li>
      @endforeach
    </ul>
  </div>
@endif

<form method="POST" action="{{ route('ong.cadastrar') }}" id="form-cadastro-ong">
  @csrf

  <div class="card">
    <div class="section-label">Identificação</div>

    <div class="field full" style="margin-bottom:20px;">
      <label>CNPJ <span class="req">*</span></label>
      <div class="cnpj-row">
        <input type="text" id="cnpj" name="cnpj" inputmode="numeric" maxlength="18"
          placeholder="00.000.000/0001-00" value="{{ old('cnpj') }}">
      </div>
      @error('cnpj')
        <span class="field-note" style="color:#b4483c;">{{ $message }}</span>
      @enderror
    </div>

    {{-- Estes campos ficam desabilitados visualmente (preenchidos via consulta do CNPJ),
         mas inputs "disabled" não são enviados no submit — por isso cada um tem um
         input hidden gêmeo com o mesmo "name", que o JS mantém sincronizado. --}}
    <div class="row">
      <div class="field">
        <label>Razão social <span class="hint">(auto)</span></label>
        <input type="text" id="razao-display" placeholder="Após consulta" value="{{ old('razao_social') }}" disabled>
        <input type="hidden" id="razao-hidden" name="razao_social" value="{{ old('razao_social') }}">
      </div>
      <div class="field">
        <label>Nome fantasia <span class="hint">(auto)</span></label>
        <input type="text" id="fantasia-display" placeholder="Após consulta" value="{{ old('nome_fantasia') }}" disabled>
        <input type="hidden" id="fantasia-hidden" name="nome_fantasia" value="{{ old('nome_fantasia') }}">
      </div>
    </div>
    @error('razao_social')
      <span class="field-note" style="color:#b4483c;">{{ $message }}</span>
    @enderror

    <div class="row">
      <div class="field">
        <label>Cidade <span class="hint">(auto)</span></label>
        <input type="text" id="cidade-display" placeholder="Após consulta" value="{{ old('cidade') }}" disabled>
        <input type="hidden" id="cidade-hidden" name="cidade" value="{{ old('cidade') }}">
      </div>
      <div class="field">
        <label>Estado <span class="hint">(auto)</span></label>
        <input type="text" id="estado-display" placeholder="Após consulta" value="{{ old('estado') }}" disabled>
        <input type="hidden" id="estado-hidden" name="estado" value="{{ old('estado') }}">
      </div>
    </div>
    @error('cidade')
      <span class="field-note" style="color:#b4483c;">{{ $message }}</span>
    @enderror
  </div>

  <div class="card">
    <div class="section-label">Acesso e contato</div>

    <div class="row">
      <div class="field">
        <label>E-mail institucional <span class="req">*</span></label>
        <input type="email" name="email" placeholder="contato@suaong.org.br" value="{{ old('email') }}">
        @error('email')
          <span class="field-note" style="color:#b4483c;">{{ $message }}</span>
        @enderror
      </div>
      <div class="field">
        <label>Telefone <span class="req">*</span></label>
        <input type="tel" id="telefone" name="telefone" inputmode="numeric" maxlength="15"
          placeholder="(11) 3000-0000" value="{{ old('telefone') }}">
        @error('telefone')
          <span class="field-note" style="color:#b4483c;">{{ $message }}</span>
        @enderror
      </div>
    </div>
    <div class="field-note" style="margin:-10px 0 20px;">Usado pelo admin para entrar em contato na verificação</div>

    <div class="row">
      <div class="field">
        <label>Senha <span class="req">*</span></label>
        <input type="password" name="senha" placeholder="Mínimo 8 caracteres">
        @error('senha')
          <span class="field-note" style="color:#b4483c;">{{ $message }}</span>
        @enderror
      </div>
      <div class="field">
        <label>Confirmar senha <span class="req">*</span></label>
        <input type="password" name="senha_confirmation" placeholder="Repita a senha">
      </div>
    </div>

    <div class="field full">
      <label>Descrição da ONG <span class="req">*</span></label>
      <textarea name="descricao" placeholder="Descreva a missão, área de atuação e histórico da sua organização...">{{ old('descricao') }}</textarea>
      @error('descricao')
        <span class="field-note" style="color:#b4483c;">{{ $message }}</span>
      @enderror
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
      <input type="file" name="logo" accept="image/png, image/jpeg" hidden id="logo-cadastro">
    </div>
    @error('logo')
      <span class="field-note" style="color:#b4483c;">{{ $message }}</span>
    @enderror
  </div>

  <div class="card">
    <div class="section-label">Termos e responsabilidades</div>

    <div class="check-row">
      <label class="switch">
        <input type="checkbox" name="aceite_termo_responsabilidade" value="1" {{ old('aceite_termo_responsabilidade') ? 'checked' : '' }}>
        <span class="slider"></span>
      </label>
      <div>Aceito o <a href="#">Termo de Responsabilidade da ONG</a> — declaro que tenho autoridade legal para representar esta organização e que a ONG é responsável pelos eventos publicados sob seu nome</div>
    </div>
    @error('aceite_termo_responsabilidade')
      <span class="field-note" style="color:#b4483c;">{{ $message }}</span>
    @enderror

    <div class="check-row">
      <label class="switch">
        <input type="checkbox" name="aceite_privacidade" value="1" {{ old('aceite_privacidade') ? 'checked' : '' }}>
        <span class="slider"></span>
      </label>
      <div>Li e concordo com a <a href="#">Política de Privacidade</a> e as regras de moderação. Estou ciente de que o cadastro ficará pendente até a verificação pelo administrador</div>
    </div>
    @error('aceite_privacidade')
      <span class="field-note" style="color:#b4483c;">{{ $message }}</span>
    @enderror
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

  // Preenche um par "display" (visual, disabled) + "hidden" (o que realmente é enviado)
  function preencherAuto(prefixo, valor) {
    document.getElementById(prefixo + '-display').value = valor ?? '';
    document.getElementById(prefixo + '-hidden').value = valor ?? '';
  }

  async function consultarCNPJ(cnpjLimpo) {
    try {
      const resposta = await fetch(`{{ url('/api/ong/consultar-cnpj') }}/${cnpjLimpo}`, {
        headers: { 'Accept': 'application/json' },
      });
      if (!resposta.ok) throw new Error('CNPJ não encontrado');

      const dados = await resposta.json();
      preencherAuto('razao', dados.razao_social);
      preencherAuto('fantasia', dados.nome_fantasia);
      preencherAuto('cidade', dados.cidade);
      preencherAuto('estado', dados.estado);
    } catch (erro) {
      preencherAuto('razao', '');
      preencherAuto('fantasia', '');
      preencherAuto('cidade', '');
      preencherAuto('estado', '');
    }
  }

  if (cnpjInput) {
    cnpjInput.addEventListener('input', (e) => {
      e.target.value = maskCNPJ(e.target.value);
    });

    cnpjInput.addEventListener('blur', (e) => {
      const cnpjLimpo = e.target.value.replace(/\D/g, '');
      if (cnpjLimpo.length === 14) {
        consultarCNPJ(cnpjLimpo);
      }
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