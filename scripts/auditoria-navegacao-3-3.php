<?php

// Read-only frontend inventory. Run locally; output contains synthetic GETs only.
use App\Http\Middleware\DemoSafety;
use App\Services\WebApi;
use App\Support\VisualOneFixtures;
use App\Support\VisualThreeFixtures;
use App\Support\VisualTwoFixtures;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

putenv('SESSION_DRIVER=array');
putenv('CACHE_STORE=array');
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();
config(['session.driver' => 'array', 'cache.default' => 'array']);
Http::preventStrayRequests();
DB::swap(new class
{
    public function __call(string $method, array $arguments): never
    {
        throw new RuntimeException('Database forbidden in frontend inventory.');
    }
});
$app->bind(WebApi::class, fn () => throw new RuntimeException('BFF forbidden in frontend inventory.'));
$seen = [];
View::composer('*', function ($view) use (&$seen) {
    $seen[] = ltrim($view->getName(), '/.');
});
$paths = [];
foreach (Route::getRoutes() as $route) {
    if (in_array('GET', $route->methods(), true) && in_array(DemoSafety::class, $route->middleware(), true)) {
        $paths[] = '/'.preg_replace('/\{[^}]+\}/', '1', $route->uri());
    }
}
$paths = array_unique([...$paths,
    '/perfil?area=privada&contexto=pessoa',
    '/perfil?area=publica&pessoa=1&contexto=pessoa',
    '/perfil?area=publica&pessoa=2&contexto=ong',
    '/perfil?area=publica&pessoa=2&contexto=visitante',
    '/ong/perfil?area=privada&contexto=ong',
    '/ong/perfil?ong=2&contexto=pessoa',
    '/organizador?contexto=organizador&jornada=independente-eventos',
    '/organizador?contexto=organizador-vinculado&jornada=vinculado-varios',
    '/ong/eventos/8/editar?contexto=organizador&jornada=independente-eventos',
]);
$rows = [];
foreach ($paths as $path) {
    $app['session']->flush(); // Array store in this isolated CLI process only.
    $seen = [];
    $request = Request::create('http://localhost:8000'.$path);
    $response = $kernel->handle($request);
    preg_match_all('~<a\b[^>]*data-brand-home[^>]*href="([^"]+)"~', $response->getContent(), $brands);
    $original = $response->getOriginalContent();
    $rows[] = [
        'url' => 'http://localhost:8000'.$path,
        'route' => $request->route()?->getName(),
        'status' => $response->getStatusCode(),
        'redirect' => $response->headers->get('Location'),
        'view' => $original instanceof Illuminate\View\View ? ltrim($original->getName(), '/.') : null,
        'included_views' => array_values(array_unique($seen)),
        'brand_hrefs' => array_map(fn ($url) => html_entity_decode($url), $brands[1]),
        'manual_homologation' => 'not_proven_by_automated_GET',
    ];
    $kernel->terminate($request, $response);
}
$views = [];
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(resource_path('views'))) as $file) {
    if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
        $views[] = str_replace('\\', '/', substr($file->getPathname(), strlen(base_path()) + 1));
    }
}
sort($views);
echo json_encode([
    'date' => '2026-10-29',
    'rows' => $rows,
    'all_blade_files' => $views,
    'scenario_catalogs' => [
        'person_account' => VisualOneFixtures::make('pessoa')['demo']['scenarios'],
        'ong_account' => VisualOneFixtures::make('ong')['demo']['scenarios'],
        'events_and_applications' => VisualTwoFixtures::SCENARIOS,
        'organizer' => VisualThreeFixtures::SCENARIOS,
    ],
    'limits' => 'GET rendering and inclusion map only; not browser interaction or manual homologation. No API, BFF or database.',
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
