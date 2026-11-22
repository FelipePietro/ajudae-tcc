<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Minha jornada') · Ajudae</title>
    @vite(['resources/css/app.css', config('identity.enabled') ? 'resources/js/identity.js' : 'resources/js/app.js'])
</head>
<body class="account-page" @unless(config('identity.enabled')) data-visual-one @endunless>
    <a class="account-skip" href="#account-main">Ir para o conteúdo</a>
    <x-context-navbar default-context="visitante" :surface="trim($__env->yieldContent('institution-navigation')) ? 'institution' : 'context'" :profile-name="$sponsorshipName ?? null" :profile-url="match ($sponsorshipRole ?? null) {
        'solicitante' => \App\Support\NavigationContext::privateProfileUrl(),
        'padrinho' => request()->fullUrlWithoutQuery('estado'),
        default => null,
    }" />
    <main id="account-main" tabindex="-1" class="account-main">@yield('content')</main>
    <footer class="account-footer"><span>Ajudae · Pessoas, causas e novas possibilidades.</span><a href="{{ route('privacidade') }}">Privacidade</a><a href="{{ route('termos') }}">Termos de uso</a></footer>
</body>
</html>
