<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Editar perfil da ONG - Ajudae</title>
@vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/editar_ong.css'])
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:ital,wght@0,600;0,700;1,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="m-0 bg-bg text-text-main font-[family-name:var(--font-body)] antialiased">

<header class="border-b border-border bg-bg">
  <div class="w-full px-5 py-[7px] sm:px-[30px] flex items-center justify-start">
    <a href="#" class="font-[family-name:var(--font-display)] font-bold text-[22px] text-text-main mr-auto no-underline">
      Ajud<span class="text-accent">ae</span>
    </a>
    <nav class="hidden sm:flex gap-8 mr-8 text-sm text-text-main">
      <a href="#" class="no-underline hover:text-accent-dark">Início</a>
      <a href="#" class="no-underline hover:text-accent-dark">Eventos</a>
      <a href="#" class="no-underline hover:text-accent-dark">Voluntários</a>
    </nav>
    <div class="flex gap-3 items-center">
      <a href="#" class="no-underline inline-flex items-center justify-center px-5 py-2.5 rounded-full text-sm font-semibold bg-green-dark text-white hover:opacity-90 transition">Sair</a>
    </div>
  </div>
</header>

<main class="max-w-[780px] mx-auto px-[18px] pt-8 pb-[60px] sm:px-6 sm:pt-12 sm:pb-20">

  <span class="inline-block bg-accent-soft text-accent-dark text-[13px] font-semibold px-3.5 py-1.5 rounded-full mb-5">🏢 Perfil da organização</span>

  <h1 class="font-[family-name:var(--font-serif)] font-bold text-[30px] sm:text-[40px] leading-[1.15] mb-3.5">Editar perfil da ONG</h1>
  <p class="text-text-muted text-base leading-relaxed max-w-[620px] mb-7">
    Atualize as informações públicas e de contato da sua organização. Alterações em dados
    de identificação podem passar novamente por verificação do administrador.
  </p>

  @if (!empty($ong['verificada']))
    <div class="flex items-center gap-3 bg-[#EEF3EB] border border-[#CFE0C8] rounded-[10px] px-[18px] py-3.5 mb-8">
      <span class="w-2.5 h-2.5 rounded-full bg-[#4C8B44] flex-shrink-0"></span>
      <div>
        <strong class="block text-sm text-[#2F4A2A]">Cadastro verificado</strong>
        <span class="text-[13px] text-[#5C7455]">Sua ONG está ativa desde {{ $ong['ativa_desde'] }} · ID #{{ $ong['id'] }}</span>
      </div>
    </div>
  @endif

  <form class="w-full" method="POST" action="{{ route('perfil-ong.atualizar', $ong['id']) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <!-- IDENTIFICAÇÃO -->
    <section class="bg-card border border-border rounded-2xl p-[22px] sm:p-8 mb-6">
      <h2 class="text-[13px] font-semibold uppercase tracking-[0.04em] text-text-muted mb-6 pb-3.5 border-b border-border">Identificação</h2>

      <div class="mb-5">
        <label for="cnpj" class="block text-sm font-semibold mb-2">CNPJ</label>
        <input type="text" id="cnpj" value="{{ $ong['cnpj'] }}" readonly
          class="w-full px-3.5 py-3 border border-border rounded-[7px] bg-field-disabled text-text-muted text-sm cursor-not-allowed">
        <span class="block text-[12.5px] text-text-muted mt-1.5">O CNPJ não pode ser alterado. Para corrigi-lo, entre em contato com o suporte.</span>
      </div>

      <div class="grid grid-cols-1 gap-0 sm:grid-cols-2 sm:gap-5 mb-5">
        <div>
          <label for="razao" class="block text-sm font-semibold mb-2">Razão social</label>
          <input type="text" id="razao" name="razao_social" value="{{ old('razao_social', $ong['razao_social']) }}"
            class="w-full px-3.5 py-3 border border-border rounded-[7px] bg-field text-sm text-text-main placeholder:text-[#A9A392] focus:outline-none focus:border-accent focus:shadow-[0_0_0_3px_rgba(227,138,44,0.15)] transition">
          @error('razao_social')
            <span class="block text-[12.5px] text-red-600 mt-1.5">{{ $message }}</span>
          @enderror
        </div>
        <div>
          <label for="fantasia" class="block text-sm font-semibold mb-2">Nome fantasia</label>
          <input type="text" id="fantasia" name="nome_fantasia" value="{{ old('nome_fantasia', $ong['nome_fantasia']) }}"
            class="w-full px-3.5 py-3 border border-border rounded-[7px] bg-field text-sm text-text-main placeholder:text-[#A9A392] focus:outline-none focus:border-accent focus:shadow-[0_0_0_3px_rgba(227,138,44,0.15)] transition">
          @error('nome_fantasia')
            <span class="block text-[12.5px] text-red-600 mt-1.5">{{ $message }}</span>
          @enderror
        </div>
      </div>

      <div class="grid grid-cols-1 gap-0 sm:grid-cols-2 sm:gap-5">
        <div>
          <label for="cidade" class="block text-sm font-semibold mb-2">Cidade</label>
          <input type="text" id="cidade" name="cidade" value="{{ old('cidade', $ong['cidade']) }}"
            class="w-full px-3.5 py-3 border border-border rounded-[7px] bg-field text-sm text-text-main placeholder:text-[#A9A392] focus:outline-none focus:border-accent focus:shadow-[0_0_0_3px_rgba(227,138,44,0.15)] transition">
          @error('cidade')
            <span class="block text-[12.5px] text-red-600 mt-1.5">{{ $message }}</span>
          @enderror
        </div>
        <div>
          <label for="estado" class="block text-sm font-semibold mb-2">Estado</label>
          <input type="text" id="estado" name="estado" value="{{ old('estado', $ong['estado']) }}"
            class="w-full px-3.5 py-3 border border-border rounded-[7px] bg-field text-sm text-text-main placeholder:text-[#A9A392] focus:outline-none focus:border-accent focus:shadow-[0_0_0_3px_rgba(227,138,44,0.15)] transition">
          @error('estado')
            <span class="block text-[12.5px] text-red-600 mt-1.5">{{ $message }}</span>
          @enderror
        </div>
      </div>
    </section>

    <!-- ACESSO E CONTATO -->
    <section class="bg-card border border-border rounded-2xl p-[22px] sm:p-8 mb-6">
      <h2 class="text-[13px] font-semibold uppercase tracking-[0.04em] text-text-muted mb-6 pb-3.5 border-b border-border">Acesso e contato</h2>

      <div class="grid grid-cols-1 gap-0 sm:grid-cols-2 sm:gap-5 mb-5">
        <div>
          <label for="email" class="block text-sm font-semibold mb-2">E-mail institucional <span class="text-accent-dark">*</span></label>
          <input type="email" id="email" name="email" value="{{ old('email', $ong['email']) }}" required
            class="w-full px-3.5 py-3 border border-border rounded-[7px] bg-field text-sm text-text-main placeholder:text-[#A9A392] focus:outline-none focus:border-accent focus:shadow-[0_0_0_3px_rgba(227,138,44,0.15)] transition">
          <span class="block text-[12.5px] text-text-muted mt-1.5">Usado pelo administrador para contato e verificação</span>
          @error('email')
            <span class="block text-[12.5px] text-red-600 mt-1.5">{{ $message }}</span>
          @enderror
        </div>
        <div>
          <label for="telefone" class="block text-sm font-semibold mb-2">Telefone</label>
          <input type="tel" id="telefone" name="telefone" value="{{ old('telefone', $ong['telefone']) }}"
            class="w-full px-3.5 py-3 border border-border rounded-[7px] bg-field text-sm text-text-main placeholder:text-[#A9A392] focus:outline-none focus:border-accent focus:shadow-[0_0_0_3px_rgba(227,138,44,0.15)] transition">
          @error('telefone')
            <span class="block text-[12.5px] text-red-600 mt-1.5">{{ $message }}</span>
          @enderror
        </div>
      </div>

      <div class="grid grid-cols-1 gap-0 sm:grid-cols-2 sm:gap-5 mb-5">
        <div>
          <label for="senha" class="block text-sm font-semibold mb-2">Nova senha</label>
          <input type="password" id="senha" name="senha" placeholder="Deixe em branco para manter a atual"
            class="w-full px-3.5 py-3 border border-border rounded-[7px] bg-field text-sm text-text-main placeholder:text-[#A9A392] focus:outline-none focus:border-accent focus:shadow-[0_0_0_3px_rgba(227,138,44,0.15)] transition">
          @error('senha')
            <span class="block text-[12.5px] text-red-600 mt-1.5">{{ $message }}</span>
          @enderror
        </div>
        <div>
          <label for="confirmar-senha" class="block text-sm font-semibold mb-2">Confirmar nova senha</label>
          <input type="password" id="confirmar-senha" name="senha_confirmation" placeholder="Repita a nova senha"
            class="w-full px-3.5 py-3 border border-border rounded-[7px] bg-field text-sm text-text-main placeholder:text-[#A9A392] focus:outline-none focus:border-accent focus:shadow-[0_0_0_3px_rgba(227,138,44,0.15)] transition">
        </div>
      </div>

      <div>
        <label for="descricao" class="block text-sm font-semibold mb-2">Descrição da ONG <span class="text-accent-dark">*</span></label>
        <textarea id="descricao" name="descricao" required
          class="w-full px-3.5 py-3 border border-border rounded-[7px] bg-field text-sm text-text-main leading-relaxed resize-y min-h-[110px] placeholder:text-[#A9A392] focus:outline-none focus:border-accent focus:shadow-[0_0_0_3px_rgba(227,138,44,0.15)] transition">{{ old('descricao', $ong['descricao']) }}</textarea>
        <span class="block text-[12.5px] text-text-muted mt-1.5">Descreva a missão, área de atuação e histórico da sua organização</span>
        @error('descricao')
          <span class="block text-[12.5px] text-red-600 mt-1.5">{{ $message }}</span>
        @enderror
      </div>
    </section>

    <!-- LOGO E IMAGEM -->
    <section class="bg-card border border-border rounded-2xl p-[22px] sm:p-8 mb-6">
      <h2 class="text-[13px] font-semibold uppercase tracking-[0.04em] text-text-muted mb-6 pb-3.5 border-b border-border">Logo e imagem</h2>

      <div class="flex flex-col sm:flex-row gap-5 items-start border border-dashed border-border rounded-[10px] p-5 bg-field">
        <div class="w-[72px] h-[72px] rounded-[7px] overflow-hidden bg-field-disabled border border-border flex-shrink-0 flex items-center justify-center">
          <img src="{{ $ong['logo_url'] ?? asset('images/logo-placeholder.png') }}" alt="Logo atual da ONG" class="w-full h-full object-cover">
        </div>
        <div>
          <p class="text-sm font-semibold mb-1">Logo da ONG</p>
          <p class="text-[12.5px] text-text-muted mb-0.5">PNG ou JPG · Recomendado 400×400px · Máximo 5MB</p>
          <p class="text-[12.5px] text-text-muted mb-0.5">Exibida publicamente nos eventos da ONG</p>
          <div class="flex items-center gap-2 mt-3">
            <label class="cursor-pointer inline-flex items-center justify-center px-4 py-2 rounded-full text-[13px] font-semibold border border-text-main text-text-main hover:bg-black/[0.04] transition" for="logo-upload">Alterar imagem</label>
            <input type="file" id="logo-upload" name="logo" accept="image/png, image/jpeg" hidden>
            <button type="button" class="border-none bg-none text-[#B4483C] text-[13px] font-semibold cursor-pointer px-1 py-2 hover:underline" onclick="document.getElementById('remover-logo').value = 1;">Remover</button>
            <input type="hidden" id="remover-logo" name="remover_logo" value="0">
          </div>
          @error('logo')
            <span class="block text-[12.5px] text-red-600 mt-1.5">{{ $message }}</span>
          @enderror
        </div>
      </div>
    </section>

    <!-- AÇÕES -->
    <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 mb-6">
      <a href="{{ route('dashboard') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 rounded-full text-sm font-semibold bg-transparent text-text-muted border border-border hover:bg-black/[0.03] transition no-underline">Cancelar</a>
      <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-7 py-3.5 rounded-full text-[15px] font-semibold bg-accent text-white hover:bg-accent-dark transition">Salvar alterações →</button>
    </div>

    <div class="bg-accent-soft border border-[#F0D2A3] rounded-[10px] px-6 py-5">
      <p class="text-sm font-bold text-accent-dark mb-2.5">⚠️ Antes de salvar</p>
      <ul class="m-0 pl-[18px] text-[#6B5230] text-[13.5px] leading-[1.7]">
        <li>Alterar o e-mail institucional pode exigir nova verificação de identidade</li>
        <li>Alterações na descrição e logo ficam visíveis imediatamente após salvar</li>
        <li>Deixe os campos de senha em branco caso não deseje alterá-la</li>
      </ul>
    </div>

  </form>

</main>

</body>
</html>