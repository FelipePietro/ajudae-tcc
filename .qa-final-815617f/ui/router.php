<?php
$root = getcwd();
$qa = __DIR__;
$public = realpath($root.'/public');
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = realpath($public.$uri);
if ($file && str_starts_with($file, $public.DIRECTORY_SEPARATOR) && is_file($file) && !str_ends_with($file, '.php')) {
    return false;
}
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';

$app->resolving(Illuminate\Foundation\Vite::class, fn($vite) => $vite->useHotFile($qa.'/vite-dev-disabled'));
$app->handleRequest(Illuminate\Http\Request::capture());
$log = [
    'method' => $_SERVER['REQUEST_METHOD'],
    'path' => parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH),
    'status' => http_response_code(),
    'port' => $_SERVER['SERVER_PORT'] ?? null,
];
file_put_contents($qa.'/http.log', json_encode($log).PHP_EOL, FILE_APPEND | LOCK_EX);
