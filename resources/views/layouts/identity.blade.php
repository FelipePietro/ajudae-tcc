<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Minha conta') · Ajudae</title>
    @vite(['resources/css/app.css', 'resources/js/identity.js'])
</head>
<body class="account-page">
    <a class="account-skip" href="#account-main">Ir para o conteúdo</a>
    <x-context-navbar />
    <main id="account-main" tabindex="-1" class="account-main">@yield('content')</main>
    <footer class="account-footer"><a href="{{ route('privacidade') }}">Privacidade</a><a href="{{ route('termos') }}">Termos de uso</a></footer>
</body>
</html>
