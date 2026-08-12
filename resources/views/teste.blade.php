<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajudaê</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <x-navbar />
    <x-navbar-feed />

    <div class="pagina-conteudo">
        <div class="grupo-botoes">
            <x-botao-primario href="/cadastro" texto="Começar agora — é grátis" />
            <x-botao-secundario href="/ong" texto="Cadastrar minha ONG" />
            <x-botao-declined href="/ong" texto="Deletar minha ONG" />
            <x-botao-dourado href="/ong" texto="Atualizar minha ONG" />
        </div>

        <x-input nome="email" tipo="email" placeholder="seuemail@exemplo.com" />
    </div>

    <x-footer />
</body>
</html>