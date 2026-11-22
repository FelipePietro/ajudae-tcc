<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Ajudae')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f8f6f1] text-[#183c2b]">
    <x-context-navbar default-context="visitante" />
    <main class="mx-auto max-w-4xl rounded-3xl bg-white p-8">
        @yield('content')
    </main>
</body>
</html>
