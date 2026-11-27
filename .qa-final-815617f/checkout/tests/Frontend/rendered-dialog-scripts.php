<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

// Read-only rendering for JS syntax tests; no real authentication, API or database.
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$scripts = [];
foreach (['/ong/eventos/criar?contexto=organizador', '/ong/eventos/9/editar?contexto=organizador&jornada=independente-varios', '/ong/perfil/excluir?contexto=ong', '/ong/perfil/exclusao-pendente?contexto=ong', '/perfil?pessoa=2&contexto=ong'] as $path) {
    $request = Request::create($path);
    $response = $kernel->handle($request);
    if ($response->getStatusCode() !== 200) {
        throw new RuntimeException('Unexpected frontend status for '.$path);
    }
    preg_match_all('~<script\b(?![^>]*\bsrc=)(?![^>]*application/json)[^>]*>(.*?)</script>~s', $response->getContent(), $matches);
    foreach ($matches[1] as $source) {
        $scripts[] = ['path' => $path, 'source' => $source];
    }
    $kernel->terminate($request, $response);
}
echo json_encode($scripts, JSON_THROW_ON_ERROR);
