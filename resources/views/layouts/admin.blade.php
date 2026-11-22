<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} — Ajudae</title>
    @vite(['resources/css/app.css', isset($integratedAdmin) ? 'resources/js/identity.js' : 'resources/js/app.js'])
</head>
<body class="ong-body">
@isset($integratedAdmin)
    <a class="account-skip" href="#admin-main">Ir para o conteúdo</a>
    <div class="ong-shell" data-integrated-admin-shell>
        <x-admin-sidebar :pages="[]" :web-navigation="\App\Support\WebNavigation::for(request())" />
        <main id="admin-main" tabindex="-1" class="ong-main admin-main admin-review admin-original-layout">
            <x-breadcrumb-admin :itens="[['label' => $title]]" />
            @yield('content')
        </main>
    </div>
@elseif($admin)
    <div class="ong-shell">
        <x-admin-sidebar :pages="$pages" :params="$params" :queues="$queues" />
        <main class="ong-main admin-main admin-review {{ in_array($page, ['admin','usuarios','log-acoes','relatorios','configuracoes']) ? 'admin-original-layout' : '' }}" data-admin-review data-variant="{{ $variant }}">
            <x-breadcrumb-admin :itens="[['label' => $title]]" :params="$params">
                @if($page !== 'log-acoes' && $title !== 'Acesso restrito' && isset($pages['log-acoes']))
                    <x-slot:acoes>
                        <div class="admin-topo-acoes admin-breadcrumb-log">
                            <x-botao-secundario :href="route('admin.log-acoes', $params)" texto="Ver log completo" size="sm" class="admin-log-btn" />
                        </div>
                    </x-slot:acoes>
                @endif
            </x-breadcrumb-admin>
            @yield('content')
        </main>
    </div>
@else
    <x-context-navbar />
    <main class="mx-auto max-w-3xl px-4 py-12">
        @yield('content')
    </main>
@endif
</body>
</html>
