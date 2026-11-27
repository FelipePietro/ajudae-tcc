<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · Ajudae</title>
    @vite(['resources/css/app.css', 'resources/css/painel_ong.css', 'resources/js/identity.js'])
</head>
<body class="account-page bg-[#F7F6F2] text-[#292820]">
    <a class="account-skip" href="#workspace-main">Ir para o conteúdo</a>
    @if(isset($institutionalEntry))
        <x-navbar-ong :web-navigation="\App\Support\WebNavigation::for(request())" />
    @else
        <x-context-navbar />
    @endif
    <main id="workspace-main" tabindex="-1" @class(['px-4 py-6 sm:px-6 lg:px-7', 'ong-page-content' => isset($institutionalEntry)])>
        <nav aria-label="Localização" class="mb-5 flex flex-wrap items-center gap-2 text-sm">
            <a href="{{ route('inicio') }}">Início</a> <span aria-hidden="true">›</span>
            <span aria-current="page">@yield('title')</span>
        </nav>
        @yield('content')
    </main>
</body>
</html>
